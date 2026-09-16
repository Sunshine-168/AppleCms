<?php
namespace app\common\model;

use think\Db;

/**
 * Web Push 订阅存储模型
 * 表：mac_push_subscription
 */
class PushSubscription extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'push_subscription';
    // 主键
    protected $pk = 'subscription_id';
    // 不使用自动时间戳（create_time/update_time 手动写入）
    protected $createTime = '';
    protected $updateTime = '';

    /** 单用户订阅数上限（防止刷表撑爆；新订阅超限时淘汰最旧） */
    const MAX_PER_USER = 10;

    /**
     * 允许的推送服务域名后缀白名单（防存储型 SSRF）。
     * 仅放行主流推送服务：命中列表中的域名或其子域，其余一律拒绝。
     */
    protected static $endpointHostWhitelist = [
        // Google（Chrome/Chromium）：FCM 新端点 fcm.googleapis.com、
        // 旧 GCM 端点 android.googleapis.com，以及 *.google.com 变体（如 jmt17.google.com）
        'googleapis.com',
        'google.com',
        // Firefox
        'push.services.mozilla.com',
        // Edge / Windows
        'notify.windows.com',
        // Safari / Apple
        'push.apple.com',
    ];

    /**
     * 写入或更新一条订阅（按 user_id + endpoint 唯一）
     * @param array $data ['user_id','endpoint','p256dh','auth','user_agent']
     * @return array ['code'=>1|0,'msg'=>...]
     */
    public function saveSubscription($data)
    {
        $userId   = isset($data['user_id']) ? intval($data['user_id']) : 0;
        $endpoint = isset($data['endpoint']) ? trim((string)$data['endpoint']) : '';
        $p256dh   = isset($data['p256dh']) ? trim((string)$data['p256dh']) : '';
        $auth     = isset($data['auth']) ? trim((string)$data['auth']) : '';
        $ua       = isset($data['user_agent']) ? substr((string)$data['user_agent'], 0, 255) : '';

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            return ['code' => 0, 'msg' => lang('param_err')];
        }
        if (strlen($endpoint) > 512) {
            return ['code' => 0, 'msg' => lang('param_err')];
        }
        // 存储型 SSRF 防护：入口即拒绝非法/内网/元数据 endpoint，不放到发送时才校验
        if (!self::isAllowedEndpoint($endpoint)) {
            return ['code' => 0, 'msg' => lang('param_err')];
        }

        $now = time();
        $where = ['user_id' => $userId, 'endpoint' => $endpoint];
        $exist = Db::name('push_subscription')->where($where)->find();

        if ($exist) {
            Db::name('push_subscription')->where($where)->update([
                'p256dh'      => $p256dh,
                'auth'        => $auth,
                'user_agent'  => $ua,
                'update_time' => $now,
            ]);
            return ['code' => 1, 'msg' => lang('save_ok')];
        }

        // 配额控制：单用户订阅数达到上限时，先淘汰最旧的若干条再插入
        $cnt = (int)Db::name('push_subscription')->where('user_id', $userId)->count();
        if ($cnt >= self::MAX_PER_USER) {
            $evict = Db::name('push_subscription')
                ->where('user_id', $userId)
                ->order('subscription_id asc')
                ->limit($cnt - self::MAX_PER_USER + 1)
                ->column('subscription_id');
            if (!empty($evict)) {
                Db::name('push_subscription')->where('subscription_id', 'in', $evict)->delete();
            }
        }

        try {
            Db::name('push_subscription')->insert([
                'user_id'     => $userId,
                'endpoint'    => $endpoint,
                'p256dh'      => $p256dh,
                'auth'        => $auth,
                'user_agent'  => $ua,
                'create_time' => $now,
                'update_time' => $now,
            ]);
        } catch (\Exception $e) {
            // 唯一键为 (user_id, endpoint(191)) 前缀索引：当两条 endpoint 前 191 字节相同、
            // 全长不同而 find() 判为不存在时，insert 会命中唯一键冲突。此处降级为 update，
            // 避免未捕获异常导致 API 500。
            Db::name('push_subscription')->where($where)->update([
                'p256dh'      => $p256dh,
                'auth'        => $auth,
                'user_agent'  => $ua,
                'update_time' => $now,
            ]);
        }
        return ['code' => 1, 'msg' => lang('save_ok')];
    }

    /**
     * endpoint 白名单校验（防存储型 SSRF）：
     *   1. scheme 必须为 https；
     *   2. 拒绝 IP 字面量 host（IPv4/IPv6/带方括号）及十进制/十六进制 IP 变体；
     *   3. host 必须命中推送服务域名后缀白名单。
     * 说明：设为 public static，供发送端 PushDispatcher 在真正 POST 前做二次校验（纵深防御），
     * 避免历史遗留/直接写库/导入等旁路数据造成发送时 SSRF。
     * @param string $endpoint
     * @return bool
     */
    public static function isAllowedEndpoint($endpoint)
    {
        $p = parse_url($endpoint);
        if (!is_array($p) || empty($p['scheme']) || empty($p['host'])) {
            return false;
        }
        if (strtolower($p['scheme']) !== 'https') {
            return false;
        }
        $host = strtolower($p['host']);
        // 去除 IPv6 方括号后判断是否为 IP 字面量
        $hostNoBracket = trim($host, '[]');
        if (filter_var($hostNoBracket, FILTER_VALIDATE_IP) !== false) {
            return false;
        }
        // 纯数字 / 0x 十六进制 host（十进制/十六进制 IP 变体，如 2130706433、0x7f000001）
        if (preg_match('/^(0x[0-9a-f]+|\d+)$/i', $host)) {
            return false;
        }
        // 必须是带点的域名
        if (strpos($host, '.') === false) {
            return false;
        }
        foreach (self::$endpointHostWhitelist as $suffix) {
            if ($host === $suffix || substr($host, -(strlen($suffix) + 1)) === '.' . $suffix) {
                return true;
            }
        }
        return false;
    }


    /**
     * 删除订阅（按 endpoint；可选限定 user_id）
     *
     * 注意：$userId 为 null 时会跨用户删除同一 endpoint 的所有订阅，
     * 仅限「服务端失效清理」等可信场景调用；面向用户的退订务必传入 $userId，
     * 避免越权删除他人订阅。
     */
    public function deleteByEndpoint($endpoint, $userId = null)
    {
        $endpoint = trim((string)$endpoint);
        if ($endpoint === '') {
            return ['code' => 0, 'msg' => lang('param_err')];
        }
        $where = ['endpoint' => $endpoint];
        if ($userId !== null) {
            $where['user_id'] = intval($userId);
        }
        Db::name('push_subscription')->where($where)->delete();
        return ['code' => 1, 'msg' => lang('del_ok')];
    }

    /**
     * 读取某用户的所有订阅
     */
    public function listByUser($userId)
    {
        return Db::name('push_subscription')->where('user_id', intval($userId))->select();
    }

    /**
     * 读取全部订阅（用于全员广播；生产建议分批游标）
     */
    public function listAll($limit = 0, $offset = 0)
    {
        $query = Db::name('push_subscription')->order('subscription_id asc');
        if ($limit > 0) {
            $query = $query->limit(intval($offset), intval($limit));
        }
        return $query->select();
    }

    /**
     * 游标分批读取订阅（subscription_id 递增游标；用于全员广播队列分批派发）。
     * 相比 offset 分页，游标不受派发过程中失效订阅被清理导致的行位移影响。
     * @param int $lastId 上次已处理的最大 subscription_id
     * @param int $limit  本批数量上限
     * @return array
     */
    public function listAfterId($lastId, $limit)
    {
        return Db::name('push_subscription')
            ->where('subscription_id', '>', intval($lastId))
            ->order('subscription_id asc')
            ->limit(intval($limit))
            ->select();
    }

    /**
     * 有效订阅总数
     */
    public function countValid()
    {
        return (int)Db::name('push_subscription')->count();
    }
}
