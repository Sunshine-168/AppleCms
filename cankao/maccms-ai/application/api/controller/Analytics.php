<?php
namespace app\api\controller;

use app\common\util\AnalyticsAggregator;
use think\Cache;
use think\Db;
use think\Request;

class Analytics extends Base
{
    // page_leave 回填的 stay_ms 上限（毫秒）：24 小时。超过视为客户端上报的异常/伪造值，做钳制而非直接丢弃。
    const MAX_STAY_MS = 86400000;

    public function session(Request $request)
    {
        $payload = $this->payload($request);
        $guard = $this->guardAnalyticsWrite('session', $payload);
        if ($guard !== null) {
            return $guard;
        }
        $sessionKey = $this->strVal($payload, 'session_key', 64);
        if ($sessionKey === '') {
            return $this->jsonError('session_key required');
        }
        $visitorId = $this->strVal($payload, 'visitor_id', 64);
        $userId = intval(cookie('user_id'));
        $startedAt = $this->intVal($payload, 'started_at', time());
        $endedAt = $this->intVal($payload, 'ended_at', 0);
        $pageCount = max(0, $this->intVal($payload, 'page_count', 0));
        $duration = max(0, $this->intVal($payload, 'duration_sec', 0));

        $data = [
            'session_key' => $sessionKey,
            'visitor_id' => $visitorId,
            'user_id' => max(0, $userId),
            'device_type' => $this->strVal($payload, 'device_type', 16),
            'os' => $this->strVal($payload, 'os', 32),
            'browser' => $this->strVal($payload, 'browser', 32),
            'app_version' => $this->strVal($payload, 'app_version', 32),
            'region_code' => $this->strVal($payload, 'region_code', 16),
            'channel' => $this->strVal($payload, 'channel', 64),
            'entry_path' => $this->strVal($payload, 'entry_path', 512),
            'exit_path' => $this->strVal($payload, 'exit_path', 512),
            'page_count' => $pageCount,
            'duration_sec' => $duration,
            'is_bounce' => $this->intVal($payload, 'is_bounce', $pageCount <= 1 ? 1 : 0) ? 1 : 0,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ];

        $id = Db::name('AnalyticsSession')->where('session_key', $sessionKey)->value('session_id');
        if ($id) {
            Db::name('AnalyticsSession')->where('session_id', $id)->update($data);
        } else {
            $id = Db::name('AnalyticsSession')->insertGetId($data);
        }

        return json(['code' => 1, 'msg' => 'ok', 'data' => ['session_id' => intval($id)]]);
    }

    public function pageview(Request $request)
    {
        $payload = $this->payload($request);
        $guard = $this->guardAnalyticsWrite('pageview', $payload);
        if ($guard !== null) {
            return $guard;
        }
        $path = $this->strVal($payload, 'path', 512);
        if ($path === '') {
            return $this->jsonError('path required');
        }
        if (!$this->isAllowedPath($path)) {
            return $this->jsonError('path invalid');
        }
        $ts = $this->intVal($payload, 'ts', time());
        $sessionId = $this->resolveSessionId($payload);
        $visitorId = $this->strVal($payload, 'visitor_id', 64);
        $userId = intval(cookie('user_id'));
        $stayMs = max(0, $this->intVal($payload, 'stay_ms', 0));
        $mid = $this->intVal($payload, 'mid', 0);
        $rid = $this->intVal($payload, 'rid', 0);
        $typeId = $this->intVal($payload, 'type_id', 0);

        $id = Db::name('AnalyticsPageview')->insertGetId([
            'session_id' => $sessionId,
            'visitor_id' => $visitorId,
            'user_id' => max(0, $userId),
            'path' => $path,
            'mid' => max(0, $mid),
            'rid' => max(0, $rid),
            'type_id' => max(0, $typeId),
            'stay_ms' => $stayMs,
            'prev_path' => $this->strVal($payload, 'prev_path', 512),
            'referer_host' => $this->strVal($payload, 'referer_host', 255),
            'ts' => $ts,
            'stat_date' => date('Y-m-d', $ts),
        ]);

        if ($sessionId > 0) {
            $session = Db::name('AnalyticsSession')->where('session_id', $sessionId)->find();
            if (!empty($session)) {
                $startedAt = intval($session['started_at']);
                $newPageCount = intval($session['page_count']) + 1;
                $newDuration = $startedAt > 0 ? max(0, $ts - $startedAt) : intval($session['duration_sec']);
                Db::name('AnalyticsSession')->where('session_id', $sessionId)->update([
                    'exit_path' => $path,
                    'ended_at' => max(intval($session['ended_at']), $ts),
                    'page_count' => $newPageCount,
                    'duration_sec' => max(intval($session['duration_sec']), $newDuration),
                    'is_bounce' => $newPageCount <= 1 ? 1 : 0,
                ]);
            }
        }

        return json(['code' => 1, 'msg' => 'ok', 'data' => ['pageview_id' => intval($id)]]);
    }

    public function event(Request $request)
    {
        $payload = $this->payload($request);
        $guard = $this->guardAnalyticsWrite('event', $payload);
        if ($guard !== null) {
            return $guard;
        }
        $eventCode = $this->strVal($payload, 'event_code', 48);
        if ($eventCode === '') {
            return $this->jsonError('event_code required');
        }
        if (!$this->isAllowedEventCode($eventCode)) {
            return $this->jsonError('event_code invalid');
        }
        $ts = $this->intVal($payload, 'ts', time());
        $sessionId = $this->resolveSessionId($payload);
        $visitorId = $this->strVal($payload, 'visitor_id', 64);
        $userId = intval(cookie('user_id'));
        $props = isset($payload['props']) ? $payload['props'] : [];
        if (!is_array($props)) {
            $props = [];
        }
        $propsJson = json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($propsJson === false) {
            $propsJson = '{}';
        }
        if (mb_strlen($propsJson,'utf-8') > 2048) {
            $propsJson = '{}';
            error_log("Throws error: analytics_event props greater than 2048 props={$propsJson} where sessionId={$sessionId} and eventCode={$eventCode}");
        }

        $id = Db::name('AnalyticsEvent')->insertGetId([
            'event_code' => $eventCode,
            'session_id' => $sessionId,
            'visitor_id' => $visitorId,
            'user_id' => max(0, $userId),
            'device_type' => $this->strVal($payload, 'device_type', 16),
            'region_code' => $this->strVal($payload, 'region_code', 16),
            'mid' => max(0, $this->intVal($payload, 'mid', 0)),
            'rid' => max(0, $this->intVal($payload, 'rid', 0)),
            'props' => $propsJson,
            'ts' => $ts,
            'stat_date' => date('Y-m-d', $ts),
        ]);

        if ($eventCode === 'page_leave') {
            $this->backfillStayMs($sessionId, $payload, $props);
        }

        return json(['code' => 1, 'msg' => 'ok', 'data' => ['event_id' => intval($id)]]);
    }

    public function aggregate(Request $request)
    {
        // 这是重操作且接口公开，只允许 POST 并严格限流，防止匿名刷爆数据库。
        if (!$request->isPost()) {
            return $this->jsonError('method not allowed');
        }
        if (!$this->consumeAnalyticsRate('aggregate', 'ip', request()->ip(0, true), 3, 60)
            || !$this->consumeAnalyticsRate('aggregate', 'global', 'all', 10, 60)) {
            return $this->jsonError('rate limit exceeded');
        }
        $mode = strtolower($this->strVal($request->param(), 'mode', 16));
        if ($mode !== 'day') {
            $mode = 'hour';
        }
        if ($mode === 'day') {
            // 全天重聚合是重操作：仅允许后台已登录管理员触发。
            // 定时任务经 api/Timming 直接调用聚合器，不走此公开接口，因此不受影响。
            $adminChk = (new \app\common\model\Admin())->checkLogin();
            $isAdmin = is_array($adminChk) && isset($adminChk['code']) && intval($adminChk['code']) <= 1;
            if (!$isAdmin) {
                return $this->jsonError('day mode requires admin');
            }
        }
        $date = $this->strVal($request->param(), 'date', 32);
        $res = $mode === 'day'
            ? AnalyticsAggregator::runDay($date)
            : AnalyticsAggregator::runHour($date);
        return json($res);
    }

    private function payload(Request $request)
    {
        $param = $request->param();
        if (!empty($param)) {
            return $param;
        }
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function resolveSessionId($payload)
    {
        // 只认服务端可校验的 session_key（session() 建立时由后端种下、随机 64 位），
        // 忽略客户端直传的数字 session_id：后者可被枚举，用来定位并篡改他人会话的 pageview。
        $sessionKey = $this->strVal($payload, 'session_key', 64);
        if ($sessionKey === '') {
            return 0;
        }
        $exists = Db::name('AnalyticsSession')->where('session_key', $sessionKey)->value('session_id');
        return intval($exists);
    }

    /**
     * 把前端 page_leave 带回来的 stay_ms 回填到服务端已经写好的 pageview 行。
     *
     * 服务端埋点是 pageview 行的唯一创建者，但它拿不到「用户在这页待了多久」——
     * 那只有浏览器知道。所以前端不再 insert，只 update。
     * 匹配：同 session + 同 path + 30 分钟内的最新一行。匹配不到就丢弃，绝不新建行，
     * 否则同一次浏览会出现两条记录，PV 直接翻倍。
     */
    private function backfillStayMs($sessionId, $payload, $props)
    {
        $sessionId = intval($sessionId);
        if ($sessionId <= 0) {
            return;
        }
        $stayMs = 0;
        if (isset($props['stay_ms'])) {
            $stayMs = intval($props['stay_ms']);
        }
        if ($stayMs <= 0) {
            return;
        }
        if ($stayMs > self::MAX_STAY_MS) {
            $stayMs = self::MAX_STAY_MS;
        }
        $path = $this->strVal($payload, 'path', 512);
        if ($path === '' || !$this->isAllowedPath($path)) {
            return;
        }

        // 除 session_id + path + 时间窗外再绑定 visitor_id：pageview 行写入时已记录 visitor_id，
        // 回填必须由同一 visitor 触发，杜绝借他人会话的 pageview 越权改写 stay_ms。
        $visitorId = $this->strVal($payload, 'visitor_id', 64);
        if ($visitorId === '') {
            return;
        }
        $row = Db::name('AnalyticsPageview')
            ->where('session_id', $sessionId)
            ->where('visitor_id', $visitorId)
            ->where('path', $path)
            ->where('ts', '>=', time() - 1800)
            ->order('ts desc,analytics_pageview_id desc')
            ->find();
        if (empty($row)) {
            return;
        }
        Db::name('AnalyticsPageview')
            ->where('analytics_pageview_id', intval($row['analytics_pageview_id']))
            ->update(['stay_ms' => $stayMs]);
    }

    private function strVal($src, $key, $max)
    {
        $value = isset($src[$key]) ? trim((string)$src[$key]) : '';
        if ($value === '') {
            return '';
        }
        return mb_substr(mac_filter_xss($value), 0, $max);
    }

    private function intVal($src, $key, $default)
    {
        if (!isset($src[$key]) || $src[$key] === '') {
            return intval($default);
        }
        return intval($src[$key]);
    }

    private function jsonError($msg)
    {
        return json(['code' => 0, 'msg' => $msg]);
    }

    private function guardAnalyticsWrite($action, $payload)
    {
        $policy = $this->analyticsRatePolicy();
        if (!$this->consumeAnalyticsRate($action, 'ip', request()->ip(0, true), intval($policy['ip_limit']), intval($policy['window_sec']))) {
            return $this->jsonError('rate limit exceeded');
        }

        $visitorId = $this->strVal($payload, 'visitor_id', 64);
        if ($visitorId !== '' && !$this->consumeAnalyticsRate($action, 'visitor', $visitorId, intval($policy['visitor_limit']), intval($policy['window_sec']))) {
            return $this->jsonError('rate limit exceeded');
        }
        return null;
    }

    private function consumeAnalyticsRate($action, $scope, $subject, $limitPerWindow, $windowSec)
    {
        if ($limitPerWindow <= 0) {
            return true;
        }
        $bucket = intval(time() / max(1, intval($windowSec)));
        $key = 'analytics:rate:' . md5($scope . '|' . $action . '|' . $subject . '|' . $bucket);
        $count = intval(Cache::get($key, 0));
        if ($count >= intval($limitPerWindow)) {
            return false;
        }
        Cache::set($key, $count + 1, max(1, intval($windowSec) + 1));
        return true;
    }

    private function analyticsRatePolicy()
    {
        $cfg = config('maccms');
        $apiCfg = isset($cfg['api']) && is_array($cfg['api']) ? $cfg['api'] : [];
        $analyticsCfg = isset($apiCfg['analytics']) && is_array($apiCfg['analytics']) ? $apiCfg['analytics'] : [];
        return [
            'window_sec' => max(1, intval(isset($analyticsCfg['rate_window_sec']) ? $analyticsCfg['rate_window_sec'] : 60)),
            'visitor_limit' => max(0, intval(isset($analyticsCfg['rate_limit_visitor']) ? $analyticsCfg['rate_limit_visitor'] : 60)),
            'ip_limit' => max(0, intval(isset($analyticsCfg['rate_limit_ip']) ? $analyticsCfg['rate_limit_ip'] : 120)),
        ];
    }

    private function isAllowedPath($path)
    {
        if ($path === '') {
            return false;
        }
        if (preg_match('/[\x00-\x1F]/', $path)) {
            return false;
        }
        return preg_match('#^[/A-Za-z0-9_\-.\?\&=%:+]+$#', $path) === 1;
    }

    private function isAllowedEventCode($eventCode)
    {
        return preg_match('/^[a-z0-9_:\-\.]{1,48}$/', strtolower($eventCode)) === 1;
    }
}
