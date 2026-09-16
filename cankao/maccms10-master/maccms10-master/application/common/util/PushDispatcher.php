<?php
namespace app\common\util;

use app\common\model\PushSubscription;
use think\Db;

/**
 * Web Push 发送调度器（纯 PHP / openssl 实现，无外部依赖）
 *
 * 已实现：
 *   - generateVapidKeys()：openssl EC prime256v1 生成 VAPID 密钥对
 *   - dispatch()：对每个订阅执行 RFC 8291/8188 aes128gcm 加密 + VAPID ES256 JWT，
 *     经 stream(https) POST 到 endpoint；对 404/410 调用 cleanup() 清理失效订阅。
 *
 * 依赖：openssl（EC/ECDH/GCM）、hash_hkdf（PHP >= 7.1.2）、allow_url_fopen。
 * 密钥格式：
 *   - vapid_public : base64url(0x04||X||Y, 65B)
 *   - vapid_private: base64url(d, 32B)
 *   - 订阅 p256dh  : base64url(0x04||X||Y, 65B)
 *   - 订阅 auth    : base64url(16B)
 */
class PushDispatcher
{
    /** P-256 记录大小（单记录足够容纳短通知） */
    const RECORD_SIZE = 4096;
    /** JWT 过期时间（秒，需 <= 24h） */
    const JWT_TTL = 43200;
    /** 推送 TTL（秒） */
    const PUSH_TTL = 2419200;

    /**
     * Base64 URL-safe 编码（无填充）
     */
    public static function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe 解码
     */
    public static function base64urlDecode($data)
    {
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * 生成 VAPID 密钥对（P-256 / prime256v1）
     * @return array|false ['public'=>base64url(65B uncompressed point), 'private'=>base64url(32B d)]
     */
    public static function generateVapidKeys()
    {
        if (!function_exists('openssl_pkey_new')) {
            return false;
        }
        $conf = [
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];
        $res = @openssl_pkey_new($conf);
        if ($res === false) {
            return false;
        }
        $details = openssl_pkey_get_details($res);
        if ($details === false || !isset($details['ec']['x'], $details['ec']['y'], $details['ec']['d'])) {
            return false;
        }
        // 未压缩公钥点：0x04 || X(32) || Y(32)
        $x = str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT);
        $y = str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
        $d = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);
        $public  = "\x04" . $x . $y;
        return [
            'public'  => self::base64urlEncode($public),
            'private' => self::base64urlEncode($d),
        ];
    }

    /**
     * 读取 push 配置
     */
    protected static function conf()
    {
        $cfg = config('maccms');
        return isset($cfg['push']) && is_array($cfg['push']) ? $cfg['push'] : [];
    }

    /**
     * 是否已启用且配置完整
     */
    public static function isReady()
    {
        $c = self::conf();
        return !empty($c['enable'])
            && !empty($c['vapid_public'])
            && !empty($c['vapid_private'])
            && function_exists('openssl_pkey_derive')
            && function_exists('hash_hkdf')
            && in_array('aes-128-gcm', openssl_get_cipher_methods(), true);
    }

    /**
     * 记录调度日志（避免依赖不存在的日志表）
     */
    protected static function log($msg)
    {
        if (function_exists('trace')) {
            @trace('[PushDispatcher] ' . $msg, 'info');
        }
    }

    /**
     * 向单个用户的所有订阅推送
     * @param int   $userId
     * @param array $payload ['title','body','url','icon']
     * @return array ['code','sent','failed','msg']
     */
    public static function sendToUser($userId, $payload)
    {
        if (!self::isReady()) {
            return ['code' => 0, 'sent' => 0, 'failed' => 0, 'msg' => 'push_not_ready'];
        }
        $model = new PushSubscription();
        $subs = $model->listByUser($userId);
        return self::dispatch($subs, $payload);
    }

    /**
     * 全员广播
     * @param array $payload
     * @return array
     */
    public static function broadcastToAll($payload)
    {
        if (!self::isReady()) {
            return ['code' => 0, 'sent' => 0, 'failed' => 0, 'msg' => 'push_not_ready'];
        }
        $model = new PushSubscription();
        $subs = $model->listAll();
        return self::dispatch($subs, $payload);
    }

    /**
     * 将一条全员广播写入待推送队列（异步派发用）。
     * 管理员广播接口只需入队（毫秒级），真正的逐条 HTTPS POST 由定时任务
     * PushDispatcher::runQueue() 分批执行，避免在管理员请求内同步阻塞导致超时/502。
     * @param array $payload ['title','body','url','icon']
     * @return array ['code'=>1|0,'msg'=>...,'queue_id'=>int]
     */
    public static function enqueueBroadcast($payload)
    {
        $payload = self::normalizePayload($payload);
        $now = time();
        $queueId = Db::name('push_queue')->insertGetId([
            'title'       => mb_substr($payload['title'], 0, 255, 'UTF-8'),
            'body'        => mb_substr($payload['body'], 0, 255, 'UTF-8'),
            'url'         => substr($payload['url'], 0, 512),
            'icon'        => substr($payload['icon'], 0, 255),
            'sent'        => 0,
            'failed'      => 0,
            'last_id'     => 0,
            'status'      => 0,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        return ['code' => 1, 'msg' => 'queued', 'queue_id' => (int)$queueId];
    }

    /**
     * 处理待推送队列（由定时任务 timming/pushbroadcast 调用）。
     * 使用 subscription_id 游标分批派发，单次运行最多处理 $maxSubsPerRun 条订阅，
     * 处理完一条队列记录（游标到底）即标记完成，避免单次请求击穿 max_execution_time。
     * @param int $batchSize     单批订阅数
     * @param int $maxSubsPerRun 单次运行订阅处理上限
     * @return array
     */
    public static function runQueue($batchSize = 100, $maxSubsPerRun = 500)
    {
        if (!self::isReady()) {
            return ['code' => 0, 'msg' => 'push_not_ready', 'processed' => 0];
        }
        $batchSize     = max(1, intval($batchSize));
        $maxSubsPerRun = max($batchSize, intval($maxSubsPerRun));

        $model = new PushSubscription();
        $processed = 0;

        while ($processed < $maxSubsPerRun) {
            // 取最旧一条未完成（待处理/处理中）的队列记录
            $row = Db::name('push_queue')
                ->where('status', 'in', [0, 1])
                ->order('queue_id asc')
                ->find();
            if (empty($row)) {
                break;
            }
            if (intval($row['status']) === 0) {
                Db::name('push_queue')->where('queue_id', $row['queue_id'])
                    ->update(['status' => 1, 'update_time' => time()]);
            }

            $lastId = intval($row['last_id']);
            $limit  = min($batchSize, $maxSubsPerRun - $processed);
            $subs   = $model->listAfterId($lastId, $limit);
            $cnt    = count($subs);

            if ($cnt === 0) {
                // 游标到底：本条广播派发完成
                Db::name('push_queue')->where('queue_id', $row['queue_id'])
                    ->update(['status' => 2, 'update_time' => time()]);
                continue;
            }

            $res = self::dispatch($subs, [
                'title' => $row['title'],
                'body'  => $row['body'],
                'url'   => $row['url'],
                'icon'  => $row['icon'],
            ]);

            // 本批最大 subscription_id 作为新游标（listAfterId 已按 id 升序）
            $maxId = $lastId;
            foreach ($subs as $s) {
                $sid = intval($s['subscription_id']);
                if ($sid > $maxId) {
                    $maxId = $sid;
                }
            }

            $update = [
                'last_id'     => $maxId,
                'sent'        => intval($row['sent']) + intval($res['sent']),
                'failed'      => intval($row['failed']) + intval($res['failed']),
                'update_time' => time(),
            ];
            // 本批不足 limit，说明后面已无更多订阅，直接标记完成
            if ($cnt < $limit) {
                $update['status'] = 2;
            }
            Db::name('push_queue')->where('queue_id', $row['queue_id'])->update($update);
            // 让本地 $row 反映最新累计值，供同一队列记录的下一轮循环使用
            $row['last_id'] = $maxId;
            $row['sent']    = $update['sent'];
            $row['failed']  = $update['failed'];

            $processed += $cnt;
        }

        return ['code' => 1, 'msg' => 'ok', 'processed' => $processed];
    }

    /**
     * 遍历订阅执行真实发送
     * 单条失败不影响整批；404/410 视为订阅失效并清理。
     */
    protected static function dispatch($subs, $payload)
    {
        $sent = 0;
        $failed = 0;
        $cleaned = 0;
        $payload = self::normalizePayload($payload);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $conf = self::conf();
        $vapidPublic  = (string)$conf['vapid_public'];
        $vapidPrivate = (string)$conf['vapid_private'];
        $subject = isset($conf['vapid_subject']) && $conf['vapid_subject'] !== ''
            ? (string)$conf['vapid_subject'] : 'mailto:admin@example.com';

        // 预构建 VAPID 私钥 PEM（同一 endpoint 源可复用 JWT，这里按 aud 缓存）
        $privPem = self::buildEcPrivatePem($vapidPrivate, $vapidPublic);
        if ($privPem === false) {
            self::log('invalid vapid private key');
            return ['code' => 0, 'sent' => 0, 'failed' => 0, 'msg' => 'vapid_key_invalid'];
        }
        $jwtCache = [];

        foreach ($subs as $sub) {
            $endpoint = isset($sub['endpoint']) ? (string)$sub['endpoint'] : '';
            $p256dh   = isset($sub['p256dh']) ? (string)$sub['p256dh'] : '';
            $auth     = isset($sub['auth']) ? (string)$sub['auth'] : '';
            if ($endpoint === '' || $p256dh === '' || $auth === '') {
                $failed++;
                continue;
            }
            // 纵深防御：发送前对 endpoint 做与入口一致的白名单校验，
            // 拦截历史遗留/直接写库/导入等旁路数据造成的发送时 SSRF。
            if (!PushSubscription::isAllowedEndpoint($endpoint)) {
                $failed++;
                self::log('blocked non-whitelisted endpoint=' . substr($endpoint, 0, 60));
                continue;
            }
            try {
                $aud = self::audienceFromEndpoint($endpoint);
                if (!isset($jwtCache[$aud])) {
                    $jwtCache[$aud] = self::createVapidJwt($aud, $subject, $privPem);
                }
                $jwt = $jwtCache[$aud];
                if ($jwt === false) {
                    $failed++;
                    continue;
                }

                $body = self::encryptPayload($json, $p256dh, $auth);
                if ($body === false) {
                    $failed++;
                    continue;
                }

                $status = self::postToEndpoint($endpoint, $body, $jwt, $vapidPublic);
                if ($status === 404 || $status === 410) {
                    self::cleanup($endpoint);
                    $cleaned++;
                    $failed++;
                } elseif ($status >= 200 && $status < 300) {
                    $sent++;
                } else {
                    $failed++;
                    self::log('push failed status=' . $status . ' endpoint=' . substr($endpoint, 0, 60));
                }
            } catch (\Exception $e) {
                $failed++;
                self::log('push exception: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $failed++;
                self::log('push throwable: ' . $e->getMessage());
            }
        }

        self::log('dispatch subs=' . count($subs) . " sent={$sent} failed={$failed} cleaned={$cleaned}");
        // 按 sent/failed 反映整体状态：无订阅=ok；全失败=code 0；部分失败=partial；全成功=ok
        if ($sent === 0 && $failed > 0) {
            $code = 0;
            $msg  = 'all_failed';
        } elseif ($failed > 0) {
            $code = 1;
            $msg  = 'partial';
        } else {
            $code = 1;
            $msg  = 'ok';
        }
        return [
            'code'   => $code,
            'sent'   => $sent,
            'failed' => $failed,
            'msg'    => $msg,
        ];
    }

    /**
     * 清理失效订阅（410/404 时调用）
     */
    public static function cleanup($endpoint)
    {
        $endpoint = trim((string)$endpoint);
        if ($endpoint === '') {
            return;
        }
        Db::name('push_subscription')->where('endpoint', $endpoint)->delete();
    }

    /**
     * 规范化推送内容
     */
    protected static function normalizePayload($payload)
    {
        if (!is_array($payload)) {
            $payload = ['title' => (string)$payload];
        }
        return [
            'title' => isset($payload['title']) ? (string)$payload['title'] : '',
            'body'  => isset($payload['body']) ? (string)$payload['body'] : '',
            'url'   => isset($payload['url']) ? (string)$payload['url'] : '/',
            'icon'  => isset($payload['icon']) ? (string)$payload['icon'] : '',
        ];
    }

    // ===================== 以下为 openssl 底层实现 =====================

    /**
     * 由 endpoint 取得 VAPID aud（scheme://host[:port]，不含路径）
     */
    protected static function audienceFromEndpoint($endpoint)
    {
        $p = parse_url($endpoint);
        if (!$p || empty($p['scheme']) || empty($p['host'])) {
            return '';
        }
        $aud = $p['scheme'] . '://' . $p['host'];
        if (!empty($p['port'])) {
            $aud .= ':' . $p['port'];
        }
        return $aud;
    }

    /**
     * 生成 VAPID JWT（ES256）
     * @return string|false
     */
    protected static function createVapidJwt($aud, $subject, $privPem)
    {
        if ($aud === '') {
            return false;
        }
        $header = self::base64urlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = self::base64urlEncode(json_encode([
            'aud' => $aud,
            'exp' => time() + self::JWT_TTL,
            'sub' => $subject,
        ], JSON_UNESCAPED_SLASHES));
        $signingInput = $header . '.' . $claims;

        $derSig = '';
        $ok = openssl_sign($signingInput, $derSig, $privPem, OPENSSL_ALGO_SHA256);
        if (!$ok || $derSig === '') {
            return false;
        }
        $rawSig = self::derToRawSignature($derSig);
        if ($rawSig === false) {
            return false;
        }
        return $signingInput . '.' . self::base64urlEncode($rawSig);
    }

    /**
     * ECDSA DER 签名 -> 原始 64B (r||s)
     * @return string|false
     */
    protected static function derToRawSignature($der)
    {
        $off = 0;
        $len = strlen($der);
        if ($len < 8 || ord($der[$off++]) !== 0x30) {
            return false;
        }
        $seqLen = ord($der[$off++]);
        if ($seqLen & 0x80) {
            $n = $seqLen & 0x7f;
            $seqLen = 0;
            while ($n-- > 0 && $off < $len) {
                $seqLen = ($seqLen << 8) | ord($der[$off++]);
            }
        }
        // r
        if (ord($der[$off++]) !== 0x02) {
            return false;
        }
        $rLen = ord($der[$off++]);
        $r = substr($der, $off, $rLen);
        $off += $rLen;
        // s
        if ($off >= $len || ord($der[$off++]) !== 0x02) {
            return false;
        }
        $sLen = ord($der[$off++]);
        $s = substr($der, $off, $sLen);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        if (strlen($r) > 32 || strlen($s) > 32) {
            return false;
        }
        $r = str_pad($r, 32, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, 32, "\x00", STR_PAD_LEFT);
        return $r . $s;
    }

    /**
     * RFC 8291 + RFC 8188：对 payload 做 aes128gcm 内容加密
     * @param string $plaintext JSON 明文
     * @param string $p256dhB64 订阅公钥（base64url，65B 点）
     * @param string $authB64   订阅 auth（base64url，16B）
     * @return string|false 完整的 aes128gcm body
     */
    protected static function encryptPayload($plaintext, $p256dhB64, $authB64)
    {
        $uaPublic = self::base64urlDecode($p256dhB64);   // 65B
        $authSecret = self::base64urlDecode($authB64);   // 16B
        if (strlen($uaPublic) !== 65 || $uaPublic[0] !== "\x04") {
            return false;
        }

        // 1) 本地临时 EC 密钥对
        $localKey = @openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if ($localKey === false) {
            return false;
        }
        $localDetails = openssl_pkey_get_details($localKey);
        if ($localDetails === false || !isset($localDetails['ec']['x'], $localDetails['ec']['y'])) {
            return false;
        }
        $asPublic = "\x04"
            . str_pad($localDetails['ec']['x'], 32, "\0", STR_PAD_LEFT)
            . str_pad($localDetails['ec']['y'], 32, "\0", STR_PAD_LEFT); // 65B

        // 2) ECDH 共享密钥
        $peerPem = self::buildEcPublicPem($uaPublic);
        if ($peerPem === false) {
            return false;
        }
        $peerKey = openssl_pkey_get_public($peerPem);
        if ($peerKey === false) {
            return false;
        }
        $ecdh = openssl_pkey_derive($peerKey, $localKey, 256);
        if ($ecdh === false || strlen($ecdh) === 0) {
            return false;
        }
        $ecdh = str_pad($ecdh, 32, "\0", STR_PAD_LEFT);

        // 3) RFC 8291：由 ECDH + auth 派生 IKM
        $info = "WebPush: info\x00" . $uaPublic . $asPublic;
        $ikm  = hash_hkdf('sha256', $ecdh, 32, $info, $authSecret);

        // 4) RFC 8188：由随机 salt 派生 CEK / NONCE
        $salt  = random_bytes(16);
        $cek   = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00", $salt);

        // 5) 单记录：明文 + 分隔符 0x02
        $record = $plaintext . "\x02";
        $tag = '';
        $cipher = openssl_encrypt($record, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($cipher === false) {
            return false;
        }

        // 6) 组装 body：salt(16) || rs(4) || idlen(1) || keyid(asPublic,65) || cipher||tag
        $header = $salt
            . pack('N', self::RECORD_SIZE)
            . chr(65)
            . $asPublic;
        return $header . $cipher . $tag;
    }

    /**
     * POST 到推送端点（stream https，无 curl 依赖）
     * @return int HTTP 状态码（0 表示连接失败）
     */
    protected static function postToEndpoint($endpoint, $body, $jwt, $vapidPublic)
    {
        $headers = [
            'Content-Type: application/octet-stream',
            'Content-Encoding: aes128gcm',
            'TTL: ' . self::PUSH_TTL,
            'Authorization: vapid t=' . $jwt . ',k=' . $vapidPublic,
            'Content-Length: ' . strlen($body),
        ];
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => implode("\r\n", $headers),
                'content'       => $body,
                'ignore_errors' => true,
                'timeout'       => 5,
                'protocol_version' => 1.1,
                // 防 SSRF：显式禁止跟随重定向，避免攻击者用自控公网 endpoint 返回
                // 302 Location: http://169.254.169.254/... 绕过入口白名单（PHP 默认 follow_location=1）
                'follow_location' => 0,
                'max_redirects'   => 0,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);
        $http_response_header = [];
        $resp = @file_get_contents($endpoint, false, $ctx);
        // $http_response_header 由 file_get_contents 注入当前作用域
        if (empty($http_response_header) || !isset($http_response_header[0])) {
            return 0;
        }
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $http_response_header[0], $m)) {
            return (int)$m[1];
        }
        return 0;
    }

    /**
     * 由原始 32B 私钥 d + 65B 公钥点构建 PKCS#8 EC 私钥 PEM
     * @return string|false
     */
    protected static function buildEcPrivatePem($privB64, $pubB64)
    {
        $d = self::base64urlDecode($privB64);
        $pub = self::base64urlDecode($pubB64);
        if (strlen($d) !== 32 || strlen($pub) !== 65 || $pub[0] !== "\x04") {
            return false;
        }
        // PKCS#8 PrivateKeyInfo for prime256v1（长度已按 32B d / 65B point 固定）
        // 结构：SEQ(135) | ver0 | AlgId{OID ecPublicKey + OID prime256v1}
        //       | OCTET(109){ ECPrivateKey SEQ(107){ ver1 | privKey(32) | [1] BIT STRING(66) } }
        $algId = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" . "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $der = "\x30\x81\x87\x02\x01\x00\x30\x13" . $algId
            . "\x04\x6d\x30\x6b\x02\x01\x01\x04\x20" . $d
            . "\xa1\x44\x03\x42\x00" . $pub;
        return self::derToPem($der, 'PRIVATE KEY');
    }

    /**
     * 由 65B 公钥点构建 SubjectPublicKeyInfo EC 公钥 PEM
     * @return string|false
     */
    protected static function buildEcPublicPem($pub)
    {
        if (strlen($pub) !== 65 || $pub[0] !== "\x04") {
            return false;
        }
        // SPKI：SEQ(89) | AlgId{OID ecPublicKey + OID prime256v1} | BIT STRING(66)
        $algId = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" . "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $der = "\x30\x59\x30\x13" . $algId . "\x03\x42\x00" . $pub;
        return self::derToPem($der, 'PUBLIC KEY');
    }


    /**
     * DER -> PEM
     */
    protected static function derToPem($der, $label)
    {
        $b64 = base64_encode($der);
        $pem = "-----BEGIN {$label}-----\n"
            . chunk_split($b64, 64, "\n")
            . "-----END {$label}-----\n";
        return $pem;
    }
}
