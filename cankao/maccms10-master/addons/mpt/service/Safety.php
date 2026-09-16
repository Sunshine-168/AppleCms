<?php

namespace addons\mpt\service;

use app\common\util\HttpClient;
use think\Config;

/**
 * MPT 插件的传输与落盘安全层。
 *
 * ★ SSRF 的处理方式与 VodAiCover 不同，这里说明为什么 ★
 * VodAiCover 面对的是「大模型返回的任意图片 URL」，只能靠公网 IP 白名单硬扛。
 * 本插件面对的是「站长自己填的 api_base + MPT 返回的相对路径」：
 *   - api_base 由后台管理员输入，信任级别等同其它后台配置，且自建 MPT 常见就是
 *     http://127.0.0.1:8080，用公网 IP 校验反而会把正常部署挡死；
 *   - 响应体里的 videos[] 一律**只当作路径**处理——剥掉 scheme/host/query、
 *     拒绝 ..，再拼回 api_base。响应永远无法把请求引到别的主机上。
 * 这比 IP 白名单更强（连同域名的其它端口都到不了），也不误伤自建部署。
 */
class Safety
{
    /** 下载单个成片的体积上限（字节） */
    const MAX_DOWNLOAD_BYTES = 209715200; // 200MB

    /**
     * 规范化 api_base：只允许 http/https，去掉尾部斜杠与路径以外的杂质。
     * @return string 失败返回空串
     */
    public static function normalizeBase($base)
    {
        $base = trim((string) $base);
        if ($base === '') {
            return '';
        }
        $parts = @parse_url($base);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }
        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            return '';
        }
        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return '';
        }
        $out = $scheme . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $out .= ':' . intval($parts['port']);
        }
        if (!empty($parts['path'])) {
            $out .= rtrim($parts['path'], '/');
        }

        return rtrim($out, '/');
    }

    /**
     * 把一个「本该是标量、但来自对端 JSON / 大模型输出」的值安全地转成字符串。
     *
     * ★ 不能直接 (string) ★
     * 对端完全可能把 message / error / subject 这类字段给成对象或数组（换个版本、
     * 换个错误分支就变了）。(string) 一个数组在 PHP 7.0 下是一句
     * 「Array to string conversion」notice 加一个字面量 "Array"：
     *   - display_errors 开着时那句 notice 会**先于** JSON 打进响应体，
     *     前端 JSON.parse 直接抛错，站长看到的是「bad response」而不是真实原因；
     *   - 就算 notice 被压掉，落库/展示出来的也是没有信息量的 "Array"。
     * 这几个字段恰好全在**错误路径**上——最需要看清原因的时候反而看不到。
     *
     * bool/null 也一并挡掉：(string) false 是空串、(string) true 是 "1"，
     * 两者当错误文案都是噪音。
     *
     * @return string 非标量一律返回空串，由调用方回落到自己的兜底文案
     */
    public static function scalarText($raw)
    {
        if (is_string($raw)) {
            return $raw;
        }

        return is_int($raw) || is_float($raw) ? (string) $raw : '';
    }

    /**
     * 把 MPT 响应里的 videos[] 条目压成「相对 storage/tasks/ 的安全路径」。
     *
     * 实测坑：两个接口的路径基准不一样。
     * GET /api/v1/tasks/{id} 的 videos[] 给的是 `/tasks/{task_id}/final-1.mp4`
     * （相对 storage/ 的路径），而 GET /api/v1/download/{file_path} 的 file_path
     * 是**相对 storage/tasks/** 的。直接把 videos[] 的值拼进 download 会变成
     * storage/tasks/tasks/{id}/final-1.mp4，稳定 404 —— 必须先去掉开头的 `tasks/`。
     *
     * @return string 失败返回空串
     */
    public static function normalizeResultPath($raw)
    {
        // ★ 先卡类型，不能直接 (string) ★
        // 调用方是 MptClient::queryTask()，传进来的是远端 JSON 里 videos[] 的元素，
        // 对端完全可能给成对象/数组（换个版本、换个错误分支就变了）。
        // (string) 一个数组在 PHP 7.0 下是一句 notice + 字面量 "Array" ——
        // 而 "Array" 恰好能通过下面那条 `^[A-Za-z0-9_\-./]+$` 白名单，于是拼出
        // api/v1/download/Array 去下载，稳定 404，任务被判成「生成失败」，
        // 站长看到的原因与真实原因（响应格式不对）毫无关系。
        // 顺带那句 notice 在 display_errors 开着时会先于 JSON 打进响应体。
        if (!is_string($raw) && !is_numeric($raw)) {
            return '';
        }
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }
        // 响应里若给了绝对 URL，只取 path，主机一律丢弃（防止被引到别的主机）
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $raw)) {
            $p = @parse_url($raw, PHP_URL_PATH);
            $raw = is_string($p) ? $p : '';
        }
        $tmp = explode('?', $raw, 2);
        $tmp = explode('#', $tmp[0], 2);
        $raw = $tmp[0];
        $raw = str_replace('\\', '/', $raw);
        $raw = ltrim($raw, '/');
        if (strncmp($raw, 'tasks/', 6) === 0) {
            $raw = substr($raw, 6);
        }
        if ($raw === '' || strpos($raw, '..') !== false) {
            return '';
        }
        // 只放行常规文件名字符
        if (!preg_match('#^[A-Za-z0-9_\-./]+$#', $raw)) {
            return '';
        }

        return $raw;
    }

    public static function joinUrl($base, $path)
    {
        return rtrim((string) $base, '/') . '/' . ltrim((string) $path, '/');
    }

    /**
     * POST JSON（**不跟随重定向**），返回解码后的数组；失败返回 null。
     *
     * ⚠️ 这里刻意不用 HttpClient::curlPostWithTimeout()：它 CURLOPT_FOLLOWLOCATION=1
     * 且没有 MAXREDIRS 上限。本请求带着 x-api-key，一旦对端回一个
     * 302 Location: http://169.254.169.254/... ，curl 会把金钥原样重放到那个主机上，
     * 正好推翻本类顶部「响应永远无法把请求引到别的主机」的承诺。
     * GET 侧用的 HttpClient::curlGetNoRedirect() 没有这个问题，POST 侧只能自己来。
     */
    public static function postJson($url, array $body, array $headers, $timeout, $verifySsl = true)
    {
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return null;
        }
        $headers[] = 'Content-Type: application/json';
        $timeout = self::clampTimeout($timeout);

        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, max(2, min(10, $timeout)));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        self::applySslOpts($ch, $verifySsl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $raw = @curl_exec($ch);
        @curl_close($ch);

        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $out = json_decode($raw, true);

        return is_array($out) ? $out : null;
    }

    /**
     * 证书校验开关。**默认必须是开的**，只有站长在设置里显式关掉才放行。
     *
     * 存在的理由：自建 MPT 用自签证书跑 https 是常见部署，硬编 verify=true
     * 会让这类站点完全连不上、且没有任何出路。关掉时打一条日志，免得某天排查
     * 中间人问题的人不知道这台机器是裸奔的。
     */
    protected static function applySslOpts($ch, $verify)
    {
        if ($verify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            return;
        }
        \think\Log::warning('mpt: TLS verification disabled by addon config (verify_ssl=0)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    /**
     * GET JSON（不跟随重定向），返回解码后的数组；失败返回 null。
     *
     * 默认路径仍然直接复用核心 HttpClient::curlGetNoRedirect()（它已经是
     * 不跟随重定向 + 强制校验证书的）。只有站长显式关掉校验时才走下面这个
     * 自带实现——核心那个方法把 verify 写死了，而改核心不在本插件的边界内。
     */
    public static function getJson($url, array $headers, $timeout, $verifySsl = true)
    {
        $timeout = self::clampTimeout($timeout);
        if ($verifySsl) {
            $raw = HttpClient::curlGetNoRedirect($url, $timeout, $headers);
        } else {
            $ch = @curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, max(2, min(10, $timeout)));
            curl_setopt($ch, CURLOPT_TIMEOUT, max(3, $timeout));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            self::applySslOpts($ch, false);
            if ($headers) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            $raw = @curl_exec($ch);
            @curl_close($ch);
        }
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $out = json_decode($raw, true);

        return is_array($out) ? $out : null;
    }

    /**
     * 流式下载到文件。不跟随重定向、限体积。
     * @return bool
     */
    public static function downloadToFile($url, array $headers, $timeout, $savePath, $verifySsl = true)
    {
        $dir = dirname($savePath);
        if (!self::ensureDir($dir)) {
            return false;
        }
        $fp = @fopen($savePath, 'wb');
        if ($fp === false) {
            return false;
        }
        // 下载可能持续几分钟，而这段跑在一个普通 HTTP 请求里（默认 max_execution_time 30s）。
        // 不解开时限的话进程会在半路被 fatal 掉，跳过下面的 @unlink 清理，
        // 在 upload/mpt/ 留一个截断的 mp4，任务也卡到 TASK_TTL 才被判超时。
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        // 成片可能几十 MB，下载超时与接口超时分开，给足时间
        curl_setopt($ch, CURLOPT_TIMEOUT, max(60, self::clampTimeout($timeout) * 6));
        self::applySslOpts($ch, $verifySsl);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($res, $dlTotal, $dlNow) {
            // 超过上限立刻中断，避免磁盘被打满
            return ($dlNow > self::MAX_DOWNLOAD_BYTES || $dlTotal > self::MAX_DOWNLOAD_BYTES) ? 1 : 0;
        });
        $ok = @curl_exec($ch);
        $code = (int) @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @curl_close($ch);
        @fclose($fp);

        // 只用来兜住「HTTP 200 但正文是一段 JSON 错误」这种情况，不是质量判据。
        // 阈值定得低（一个合法 mp4 的 ftyp+moov 头就不止这么多），免得把一条
        // 很短的成片误杀成失败——那种情况站长会以为生成坏了，实际文件是好的。
        if ($ok === false || $code < 200 || $code >= 300 || !is_file($savePath) || filesize($savePath) < 256) {
            @unlink($savePath);

            return false;
        }

        return true;
    }

    /**
     * 分配落盘路径，返回相对站点根的路径（如 upload/mpt/20260825-1/xxx.mp4）。
     * 目录分片规则照 VodAiCover::allocateSavePath()，单目录不超过 1000 个文件。
     */
    public static function allocateSavePath($objId, $ext = 'mp4')
    {
        $ext = preg_replace('/[^a-z0-9]/i', '', (string) $ext);
        if ($ext === '') {
            $ext = 'mp4';
        }
        $ymd = date('Ymd');
        $nDir = $ymd;
        for ($i = 1; $i <= 100; $i++) {
            $nDir = $ymd . '-' . $i;
            $path1 = ROOT_PATH . 'upload/mpt/' . $nDir . '/';
            if (file_exists($path1)) {
                $farr = glob($path1 . '*.*');
                if ($farr && count($farr) > 999) {
                    continue;
                }
                break;
            }
            break;
        }

        return 'upload/mpt/' . $nDir . '/' . md5(microtime(true) . '_' . intval($objId)) . '.' . strtolower($ext);
    }

    public static function ensureDir($dir)
    {
        if (is_dir($dir)) {
            return true;
        }

        return @mkdir($dir, 0777, true);
    }

    /**
     * 插件自己的 runtime 文件（锁、版本标记）路径。
     *
     * 全部收进 runtime/mpt/ 而不是散在 runtime/ 根下：本插件一共要放 8 个文件
     * （poll / advance / ratelimit / vodplayer / config / schema 六把锁，
     * 外加 schema.ver 与 schema.fail 两个标记），摊在核心的 runtime/ 里
     * 既看不出是谁的，卸载时也没法一次收干净（见 Mpt::uninstall()）。
     *
     * ⚠️ 不能改放 runtime/cache|log|temp 之下：后台「清空缓存」
     * （application/admin/controller/Base.php:161-163）会把那三个目录整个删掉，
     * schema.ver 一没，每次 advance() 都要重跑一轮 SHOW COLUMNS。
     */
    public static function runtimeFile($name)
    {
        $dir = RUNTIME_PATH . 'mpt' . DS;
        self::ensureDir($dir);

        return $dir . $name;
    }

    /**
     * 清影片详情缓存。照 VodAiCover::bustVodDetailCache()（该方法是 private，无法复用）。
     */
    public static function bustVodDetailCache($vodId, $vodEn)
    {
        $vodId = intval($vodId);
        $vodEn = (string) $vodEn;
        \think\Cache::rm('vod_detail_' . $vodId);
        if ($vodEn !== '') {
            \think\Cache::rm('vod_detail_' . $vodEn);
            \think\Cache::rm('vod_detail_' . $vodId . '_' . $vodEn);
        }
        $flag = isset($GLOBALS['config']['app']['cache_flag']) ? (string) $GLOBALS['config']['app']['cache_flag'] : '';
        if ($flag !== '' && $vodEn !== '') {
            \think\Cache::rm($flag . '_vod_detail_' . $vodId . '_' . $vodEn);
        }
    }

    /**
     * 错误脱敏：抹掉 key、完整 URL 与服务器绝对路径，只留可读原因。
     * 详情写日志，对外只给这一份。
     */
    public static function safeMessage($detail, array $secrets = array())
    {
        $msg = (string) $detail;
        foreach ($secrets as $s) {
            $s = (string) $s;
            if (strlen($s) >= 6) {
                $msg = str_replace($s, '***', $msg);
            }
        }
        $msg = preg_replace('#[a-z][a-z0-9+.-]*://[^\s"\']+#i', '[url]', $msg);
        $msg = str_replace(rtrim(ROOT_PATH, '/'), '[root]', $msg);
        // 远端服务的报错里也常带它自己的绝对路径（如 /srv/mpt/app/services/task.py）。
        // 只抹本站 ROOT_PATH 不够——对站长来说那同样是无意义且不该外泄的服务器路径。
        //
        // ★ 要求至少三段，不是两段 ★
        // 两段就抹的话，`/api/v1 returned 500`、`/tasks/{id} 404` 这类**接口路径**
        // 也会变成 `[path] returned 500` —— 那恰恰是排查时最有用的一句信息，而它
        // 根本不是服务器路径，抹掉纯属损失。真正的绝对路径（/srv/... /var/www/...
        // /usr/local/...）几乎总是三段以上，收紧一段不影响脱敏效果。
        // 前面那个 (?<![\w]) 已经保证只匹配以 / 开头的整段，`9/16`、`client/server`
        // 这类中间带斜杠的写法本来就不会被命中。
        $msg = preg_replace('#(?<![\w])/[A-Za-z0-9_.\-]+(?:/[A-Za-z0-9_.\-]+){2,}#', '[path]', $msg);
        $msg = preg_replace('#[A-Za-z]:\\\\[^\s"\']+#', '[path]', $msg);
        $msg = preg_replace('/\s+/', ' ', $msg);
        $msg = trim($msg);
        // 必须按字符截，不能按字节：byte-wise substr 会把一个中文字劈开，
        // 之后 json_encode() 遇到非法 UTF-8 直接返回 false，接口回一个空 body，
        // 前端 JSON.parse 抛错——站长看到的就不是真实原因了。
        if (mb_strlen($msg, 'UTF-8') > 300) {
            $msg = mb_substr($msg, 0, 300, 'UTF-8');
        }

        return $msg;
    }

    /**
     * 本插件要求的后台权限节点。
     *
     * 选 vod/info（影片添加/编辑，见 application/admin/common/auth.php:974-977）
     * 是因为本插件干的事就是「改影片」：写 vod_play_* 、顺带补 extra/vodplayer.php
     * 与 static/js/playerconfig.js。能编辑影片的人做这些不越界，反之则明显越界。
     */
    const ADMIN_NODE = 'vod/info';

    /**
     * 后台节点鉴权。
     *
     * ★ 光判断「登录了」是不够的 ★
     * session('admin_auth')==='1' 只等价于核心的 model('Admin')->checkLogin()，
     * 那是**第一层**。核心 Base::_initialize()（application/admin/controller/Base.php:45）
     * 在它之后一定还会跑 check_auth($cl,$ac)，按 admin_info['admin_auth'] 里的
     * `控制器/方法` 节点表放行，只有 admin_id=='1' 无条件通过（:104）。
     * 插件控制器不走 Base，这一层要自己补——否则一个只被授权管评论的子管理员
     * 也能改任意影片的播放来源、并触发对 extra/vodplayer.php、
     * static/js/playerconfig.js、static/player/*.js 的写入，那是站点级配置。
     *
     * 匹配规则与核心一致：逗号包裹后做子串查找。
     */
    public static function adminAllowed($node = self::ADMIN_NODE)
    {
        $info = session('admin_info');
        if (!is_array($info) || empty($info['admin_id'])) {
            return false;
        }
        if ((string) $info['admin_id'] === '1') {
            return true;
        }
        $auths = ',' . (isset($info['admin_auth']) ? (string) $info['admin_auth'] : '') . ',';

        return strpos($auths, ',' . $node . ',') !== false;
    }

    /**
     * 会话级 CSRF token：一次生成，页面渲染与后续 ajax 看到同一个。
     *
     * 放在这里而不是插件主类上：get_addon_autoload_config() 会把主类的每个
     * public 方法都登记成钩子写进 application/extra/addons.php。
     */
    public static function csrfToken()
    {
        $token = session('mpt_csrf_token');
        if (empty($token)) {
            $token = self::randomToken();
            session('mpt_csrf_token', $token);
        }

        return (string) $token;
    }

    /**
     * 生成一个 128-bit 的随机令牌（32 位十六进制）。
     *
     * ★ 必须是 CSPRNG，md5(uniqid()+mt_rand()) 不够 ★
     * uniqid() 的主体是微秒级时间戳（可预测），mt_rand() 是梅森旋转（非密码学安全，
     * 观察到足够输出即可还原内部状态）。再套一层 md5 也只是把一个熵很低的输入
     * 摊成 128 位，并不会凭空长出熵来。本方法产出的两个东西都是纯凭据：
     *   - cron_token 是 api/cron 这个免登录端点的**唯一**凭据；
     *   - csrfToken 是 submit/retry/remove 的唯一防伪造凭据。
     *
     * 写法照核心 mac_admin_csrf_token()（application/common.php:4224-4231）：
     * 优先 random_bytes()，只有在极少数无熵源环境抛异常时才回落到旧写法，
     * 确保功能不中断。random_bytes() 是 PHP 7.0 原生函数，不需要 polyfill。
     */
    public static function randomToken()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            // 无熵源：退化但不中断，与核心同一口径
            return md5(uniqid('mpt_', true) . mt_rand());
        }
    }

    /**
     * MPT 允许的本地素材扩展名。
     * 与服务端 app/controllers/v1/video.py::upload_video_material_file 的白名单一致，
     * 提前在本地拦掉，免得站长等十分钟才被告知文件类型不对。
     */
    public static $materialExts = array('mp4', 'mov', 'avi', 'flv', 'mkv', 'jpg', 'jpeg', 'png');

    /**
     * 规范化一个本地素材文件名。
     *
     * 只接受**纯文件名**：MPT 侧会用 resolve_path_within_directory() 把它锁死在
     * storage/local_videos/ 内（app/services/video.py:1284），这里同样不放行任何
     * 目录分隔符或 ..，两端都收紧，别指望对方兜底。
     *
     * @return string 不合法返回空串
     */
    public static function sanitizeMaterialName($name)
    {
        $name = trim((string) $name);
        if ($name === '' || strlen($name) > 200) {
            return '';
        }
        if (strpos($name, '/') !== false || strpos($name, '\\') !== false || strpos($name, '..') !== false) {
            return '';
        }
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.\-]*$/', $name)) {
            return '';
        }
        $dot = strrpos($name, '.');
        if ($dot === false) {
            return '';
        }
        $ext = strtolower(substr($name, $dot + 1));
        if (!in_array($ext, self::$materialExts, true)) {
            return '';
        }

        return $name;
    }

    /**
     * 把配置里那一坨文本拆成合法素材名列表（去重、限量）。
     */
    public static function parseMaterialList($raw, $max = 30)
    {
        $out = array();
        foreach (preg_split('/[,，\r\n\s]+/u', (string) $raw) as $one) {
            $n = self::sanitizeMaterialName($one);
            if ($n !== '' && !in_array($n, $out, true)) {
                $out[] = $n;
            }
            if (count($out) >= $max) {
                break;
            }
        }

        return $out;
    }

    /**
     * 按管理员计数的频率限制。
     *
     * ⚠️ 不复用 VodAiCover::consumeGenerateRateLimit()：它虽然是 public，
     * 但缓存键是写死的 `admin_vod_aicover_rl_*`，共用等于让生成宣传片去消耗
     * AI 封面的配额（反之亦然）——两个功能的额度会互相偷。各自开一个命名空间。
     *
     * @param string $action 动作名，不同动作各自计数
     * @return bool true=放行
     */
    public static function consumeRateLimit($action, $adminId, $perMinute = 5, $perHour = 30)
    {
        $adminId = (int) $adminId;
        if ($adminId <= 0) {
            return false;
        }

        return self::consumeBucket($action, 'a' . $adminId, $perMinute, $perHour);
    }

    /**
     * 按来源 IP 计数的频率限制，供**不要求后台会话**的端点使用（目前只有 api/cron）。
     *
     * ★ 必须与按管理员的那套分开计数 ★
     * 两者混用的话，一个换 IP 的攻击者能把站长的配额刷光，反过来站长正常操作也会
     * 挤掉计划任务的配额。桶前缀不同，互不影响 —— 与核心其它限流点的口径一致。
     *
     * IP 取 mac_get_client_ip()（它已经处理过可信代理，不会被伪造的
     * X-Forwarded-For 骗到），再 md5 一次只是为了让缓存键定长且不含冒号。
     */
    public static function consumeIpRateLimit($action, $perMinute = 30, $perHour = 300)
    {
        $ip = function_exists('mac_get_client_ip') ? (string) mac_get_client_ip() : '';
        if ($ip === '') {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        }

        return self::consumeBucket($action, 'i' . md5($ip), $perMinute, $perHour);
    }

    /**
     * 「读 → 判 → 写」三步的公共实现，调用方给出计数桶。
     *
     * ★ 计数必须串行 ★
     * 「读 → 判 → 写」是三步，中间没有互斥的话并发请求会读到同一个 n 再各写 n+1，
     * 配额直接漏过去。不用 Cache::inc()：File 驱动下它同样是读改写（不是原子的），
     * 而且 key 不存在时会用 expire=0 落一个**永不过期**的计数，限流桶再也不会重置。
     * 这里用文件锁，与驱动无关，行为可预期。
     *
     * @param string $action 动作名，不同动作各自计数
     * @param string $bucket 计数主体（'a<管理员ID>' / 'i<IP哈希>'）
     * @return bool true=放行
     */
    protected static function consumeBucket($action, $bucket, $perMinute, $perHour)
    {
        $action = preg_replace('/[^a-z0-9_]/i', '', (string) $action);

        $lock = @fopen(self::runtimeFile('ratelimit.lock'), 'c');
        if ($lock !== false && !@flock($lock, LOCK_EX)) {
            @fclose($lock);
            $lock = false;
        }

        $minKey = 'mpt_rl_' . $action . '_min:' . $bucket . ':' . (int) floor(time() / 60);
        $hourKey = 'mpt_rl_' . $action . '_hour:' . $bucket . ':' . (int) floor(time() / 3600);

        $pass = true;
        $nMin = (int) \think\Cache::get($minKey, 0);
        $nHour = (int) \think\Cache::get($hourKey, 0);
        if ($nMin >= $perMinute || $nHour >= $perHour) {
            $pass = false;
        } else {
            \think\Cache::set($minKey, $nMin + 1, 70);
            \think\Cache::set($hourKey, $nHour + 1, 3700);
        }

        if ($lock !== false) {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }

        return $pass;
    }

    /**
     * 删除插件自己产出的成片。
     * 只允许删 upload/mpt/ 下的文件——传进来的值来自 DB，仍当不可信处理。
     */
    public static function deleteResultFile($relPath)
    {
        $rel = str_replace('\\', '/', trim((string) $relPath));
        if ($rel === '' || strpos($rel, '..') !== false) {
            return false;
        }
        if (strncmp($rel, 'upload/mpt/', 11) !== 0) {
            return false;
        }
        $abs = ROOT_PATH . $rel;

        return is_file($abs) ? @unlink($abs) : false;
    }

    /**
     * mac_arr2file() 只写文件，不会更新 ThinkPHP 已经载入的 Config。
     * 补写完 extra/*.php 后必须同步一次内存态，否则同一个请求里后续
     * config('xxx') 拿到的还是旧值。
     */
    public static function syncConfig($name, array $value)
    {
        Config::set($name, $value);
    }

    /**
     * 拼一个指向本插件的绝对地址（站点根 + 入口 + 插件路径）。
     *
     * ★ 为什么不能到处硬拼 '/index.php/addons/mpt/...' ★
     * 那是 PATH_INFO 形式。站点关掉 PATH_INFO（宝塔里常见的「兼容模式」）时
     * 这类地址一律 404，而本插件的任务台、全部 ajax、影片编辑页的入口按钮
     * 走的都是它 —— 症状是插件装上了、设置也填了，点进去却是一片 404，
     * 站长完全无从判断是哪一层坏了。
     * Mpt::appInit() 已经在 Request::pathinfo() 把 $_GET[var_pathinfo] 清掉之前
     * 记下了本次请求用的是哪种形式，这里跟着走即可。
     *
     * ⚠️ 必须走 index.php 入口：后台入口（admin.php 等）会把非 admin 模块 302 掉。
     *
     * @param string $tail 形如 'api/poll' / 'admin/index'，不以斜杠开头
     * @return string
     */
    public static function addonUrl($tail)
    {
        $entry = rtrim(MAC_PATH, '/') . '/index.php';
        $tail = 'addons/mpt/' . ltrim((string) $tail, '/');

        if (!empty(\addons\mpt\Mpt::$compatUrl)) {
            $var = (string) Config::get('var_pathinfo');
            if ($var === '') {
                $var = 's';
            }

            return $entry . '?' . rawurlencode($var) . '=/' . $tail;
        }

        return $entry . '/' . $tail;
    }

    /**
     * 重新 include 一个可能刚被别的进程改写过的 PHP 文件。
     *
     * ★ 光加锁是不够的，还要绕开 opcache ★
     * 启用 opcache 且 validate_timestamps=1 时，PHP 只会**每 revalidate_freq 秒**
     * （默认 2）核对一次 mtime。也就是说文件已经在盘上换了内容，这几秒内
     * include 拿到的仍然是旧的 opcode —— 「读 → 合并 → 写回」的第一步就已经是
     * 陈旧快照，接着那次写回会把别人的修改静默回滚掉。文件锁挡不住这一层，
     * 它跟并发无关，是缓存问题。
     * opcache_invalidate() 在没装 opcache 时不存在，用 function_exists 兜住。
     *
     * @return mixed include 的返回值；文件不存在时返回 null
     */
    public static function includeFresh($file)
    {
        if (!is_file($file)) {
            return null;
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }

        // 同一次请求里这个文件多半已经被 include 过（App::init 载 extra/），
        // 所以只能用 include，include_once 会直接返回 true 而不是数组。
        return @include $file;
    }

    /**
     * 原子地把一段内容写进一个**共享的、别人会读的**文件：先写同目录下的临时文件，
     * 再 rename 覆盖。写 runtime/ 下自家的标记文件不必用它（见下面第 3 段）。
     *
     * ★ file_put_contents(..., LOCK_EX) 挡不住读者 ★
     * LOCK_EX 只排斥同样用 flock 的**写者**，对一个正在 `include` / `file_get_contents`
     * 这个文件的读者没有任何约束。覆写不是原子的：读者完全可能读到写了一半的文件。
     * 后果按文件不同：
     *   - config.php 撕裂 → 每一个 include 它的请求当场 ParseError（核心
     *     Addons::getConfig() 那句 include 没有任何保护），插件全废；
     *   - playerconfig.js / static/player/*.js 撕裂 → 前台播放器起不来。
     * 加锁解决不了这一层，只有「写别处 + rename」能——rename() 在同一文件系统上
     * 是原子的，读者要么看到旧文件、要么看到新文件，没有中间态。
     *
     * 口径与 Assets::copyAtomic() 一致（那边的源是一个文件，这边是一段字符串）。
     * runtime/ 下的版本标记/失败标记不走这里：它们只被自己读，撕裂最多让指纹对不上
     * 而多跑一次幂等的 ensure()，为它多付一次 rename 不值当。
     *
     * @return bool
     */
    public static function putFileAtomic($path, $content)
    {
        if (!self::ensureDir(dirname($path))) {
            return false;
        }
        // 临时文件必须与目标同目录：rename() 只在同一文件系统上原子，
        // 跨挂载点会退化成「拷贝+删除」，那就白设计了。
        $tmp = $path . '.' . uniqid('', true) . '.mpt-tmp';
        if (@file_put_contents($tmp, $content) === false) {
            @unlink($tmp);

            return false;
        }
        // 目标已存在时沿用它的权限位：rename 之后生效的是临时文件的权限，
        // 而临时文件是按当前 umask 建的。站长特意调过 config.php 的权限
        // （或站点要求 0640）时，不带这一下就会被悄悄改回 0644。
        if (is_file($path)) {
            $mode = @fileperms($path);
            if ($mode !== false) {
                @chmod($tmp, $mode & 0777);
            }
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);

            return false;
        }

        return true;
    }

    /**
     * 刚写完一个 PHP 文件后，让后续的 include 立刻看到新内容。
     * 理由同 includeFresh()：不失效的话本进程稍后再 include 还是旧 opcode。
     */
    public static function invalidateFile($file)
    {
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }
    }

    /**
     * 本插件自带的语言包，与 application/lang/ 的 9 种口径一致。
     * 语言名只能取自这个白名单，见 loadLang()。
     */
    public static $langs = array(
        'zh-cn', 'zh-tw', 'en-us', 'ja-jp', 'ko-kr',
        'de-de', 'fr-fr', 'es-es', 'pt-pt',
    );

    /**
     * 载入插件语言包。
     *
     * ★ 为什么放在 Safety 而不是插件主类上 ★
     * get_addon_autoload_config()（vendor/karsonzhang/fastadmin-addons/src/common.php:213）
     * 会把插件主类上**每一个** public 方法都登记成同名钩子写进 application/extra/addons.php，
     * 主类上一个 public loadLang() 就会凭空多出一个 load_lang 钩子。而这个方法必须能被
     * ConfigSchema 调到（理由见下），只能挪到一个普通 service 类上。
     *
     * ★ 绝对不能在 app_init 钩子里指望它生效 ★
     * 插件的 app_init 钩子跑得比 application/tags.php 里的核心行为**更早**：
     * vendor/karsonzhang/fastadmin-addons/src/common.php:45 那个闭包在 composer
     * autoload 阶段就 Hook::add 进去了，而 tags.php 是 App::init() 里才
     * Hook::import（append）的。于是那一刻有两件事都还没发生：
     *   1. application/common/behavior/Init.php:127-128 还没把站点语言写进
     *      default_lang —— 读到的是 application/config.php:48 的静态默认 zh-cn；
     *   2. thinkphp/library/think/App.php:98 的 Lang::range($config['default_lang'])
     *      还没执行，Lang::$range 仍是类默认的 zh-cn。Lang::load() 会把条目装进
     *      **zh-cn 这个 range**，紧接着 range 被切成站点语言，装进去的东西再也读不到。
     * 所以改成谁要用谁自己调，且下面那道 Lang::has() 探测是**按 range 判**的：
     * appInit 那次装进 zh-cn range 之后，range 被切走，探测在新 range 上仍然为假，
     * 会再装一次真正生效的。不能用「装过就不再装」的静态标志位，那会把后面那次挡掉。
     *
     * 语言来源是 config('default_lang')：maccms 的站点语言存在
     * $config['app']['lang'] 里。maccms **没有**前端可控的语言 cookie
     * （lang_switch_on 在 application/config.php:44 是 false，ThinkPHP 的
     * Lang::detect() 从不执行），所以这里不需要、也不应该去读任何请求参数。
     *
     * ★ 即便如此，语言名仍然走白名单 ★
     * \think\Lang::load() 内部是 `include $_file`。只要有任何一天这个值变得
     * 部分可控（加个语言切换、换个来源），直接拼路径就是一条从未登录前台请求
     * 直达任意 .php 包含的 LFI→RCE 链 —— runtime/temp/ 的编译模板、
     * runtime/cache/ 的缓存都可写且可包含。语言包本来就是固定的有限几个，
     * 没有任何需要动态拼接的理由，白名单最稳。
     */
    public static function loadLang()
    {
        // 已经在当前 range 上装过就别再 include 一遍：config.php 一次请求里会被
        // include 好几次（get_addon_config / get_addon_fullconfig 各一次），
        // 而 ConfigSchema::items() 每次都会调到这里。
        // 探测键取一个本插件必然存在的条目，Lang::has() 是按当前 range 判的。
        if (\think\Lang::has('mpt/cfg_api_base')) {
            return;
        }
        $locale = (string) Config::get('default_lang');
        if (!in_array($locale, self::$langs, true)) {
            // 站点用的语种本插件没带（本插件带 9 种，理论上不会走到这里），
            // 回落：中文系 → zh-cn，其它 → en-us
            $locale = (strpos($locale, 'zh') === 0) ? 'zh-cn' : 'en-us';
        }
        $langFile = ADDON_PATH . 'mpt' . DS . 'lang' . DS . $locale . '.php';
        if (is_file($langFile)) {
            \think\Lang::load($langFile);
        }
    }

    /**
     * 规范化一个要拼进 HTTP 请求头的值：剥掉全部 C0 控制字符与 DEL。
     *
     * ★ 收口函数必须放在两边都够得着的地方 ★
     * 这个值最终原样拼进 `x-api-key: {key}` 交给 CURLOPT_HTTPHEADER
     * （MptClient::headers()），而 libcurl 不会替我们做任何清洗：\r\n 会截断
     * 请求头并注入新头，\0 会截断 C 字符串。核心的 Addon::validateAddonConfigRows()
     * （application/admin/controller/Addon.php:830）对 type=string 只卡 65535 长度，
     * 值里的换行会原样落进 config.php —— 一个有 addon/config 节点的子管理员即可
     * 往出站请求里塞任意请求头。trim() 只管首尾，管不了中间。
     *
     * 早先这个方法是 TaskRunner 上的 protected，于是 MptClient 那一侧够不着、
     * 只有一句 trim()。它是 public 构造、收任意数组，配置读取处的收口挡不到它。
     * 挪到 Safety 上，两个入口各自调一次，谁都不依赖「上游恰好已经洗过了」。
     *
     * 不用白名单：api_key 的字符集由对端决定，各家 token 格式不一样，白名单会把
     * 正常的 key 误杀成「没配」。只去掉真正有害的那一类字符，其余控制字符本来
     * 也不可能出现在一个合法凭据里。
     */
    public static function sanitizeHeaderValue($raw)
    {
        return trim(preg_replace('/[\x00-\x1F\x7F]/', '', (string) $raw));
    }

    public static function clampTimeout($t)
    {
        $t = intval($t);
        if ($t < 5) {
            $t = 5;
        }
        if ($t > 60) {
            $t = 60;
        }

        return $t;
    }
}
