<?php

namespace App\Support;

use App\Models\System\SysOperateLogModel;
use App\Support\Utils\IpAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/** 后台改数据记一行，给人看的摘要，不是访问流水 */
class AdminOpLog
{
    private static int $quiet = 0;

    private static bool $written = false;

    /** @var list<string>|null */
    private static ?array $columns = null;

    public static function beginRequest(): void
    {
        self::$written = false;
        self::$columns = null;
    }

    public static function wrote(): bool
    {
        return self::$written;
    }

    public static function quiet(callable $fn): mixed
    {
        self::$quiet++;
        try {
            return $fn();
        } finally {
            self::$quiet--;
        }
    }

    /**
     * @param  array{code?:int,msg?:string,data?:mixed}  $result
     * @param  array<string, mixed>  $meta
     * @return array{code?:int,msg?:string,data?:mixed}
     */
    public static function ifOk(array $result, string $action, string $summary, array $meta = []): array
    {
        if ((int) ($result['code'] ?? 1) === 0) {
            self::write($action, $summary, $meta);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $meta  module, target_type, target_id, payload, url
     */
    public static function write(string $action, string $summary, array $meta = []): void
    {
        if (self::$quiet > 0) {
            return;
        }
        $summary = trim($summary);
        if ($summary === '') {
            return;
        }
        try {
            if (! Schema::hasTable('sys_operate_log')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $request = request();
        $uid = (int) ($meta['uid'] ?? session('admin_uid', 0));
        $username = (string) ($meta['username'] ?? session('admin_username', ''));
        if ($uid < 1 && $request instanceof Request) {
            $uid = (int) $request->attributes->get('admin_uid', $uid);
            $username = (string) $request->attributes->get('admin_username', $username);
        }

        $module = (string) ($meta['module'] ?? '');
        $ip = (string) ($meta['ip'] ?? ($request?->ip() ?? ''));
        $url = (string) ($meta['url'] ?? '');
        if ($url === '' && $request instanceof Request) {
            $url = $request->fullUrl();
        }
        $ua = (string) ($meta['ua'] ?? ($request?->userAgent() ?? ''));
        $path = $request instanceof Request ? $request->path() : '';
        $route = '';
        if ($request instanceof Request && $request->route()) {
            $route = (string) $request->route()->uri();
        }
        $payload = $meta['payload'] ?? null;
        if (! is_array($payload) && $request instanceof Request) {
            $payload = $request->all();
        }
        if (! is_array($payload)) {
            $payload = [];
        }
        $payloadJson = self::encodePayload($payload);

        $now = time();
        $row = [
            'uid' => $uid,
            'username' => self::cut($username, 50),
            'title' => self::cut($summary, 100),
            'permission' => self::cut($action !== '' ? $action : ($route ?: $path), 100),
            'module' => self::cut($module, 50),
            'method' => self::cut(strtoupper((string) ($request?->method() ?? 'POST')), 10),
            'url' => self::cut($url, 255),
            'route' => self::cut($route ?: $path, 150),
            'request_data' => $payloadJson,
            'response_code' => 0,
            'response_msg' => '',
            'status' => 1,
            'duration_ms' => 0,
            'login_ip' => self::cut($ip, 45),
            'ip_address' => self::cut(IpAddress::region($ip), 255),
            'user_agent' => self::cut($ua, 255),
            'referer' => self::cut((string) ($request?->headers->get('referer', '') ?? ''), 255),
            'target_type' => self::cut((string) ($meta['target_type'] ?? ''), 50),
            'target_id' => (int) ($meta['target_id'] ?? 0),
            'create_time' => $now,
            'update_time' => $now,
        ];
        if (self::hasColumn('action')) {
            $row['action'] = self::cut($action, 50);
        }

        $insert = [];
        foreach ($row as $key => $value) {
            if (self::hasColumn($key)) {
                $insert[$key] = $value;
            }
        }
        if ($insert === []) {
            return;
        }

        try {
            SysOperateLogModel::query()->create($insert);
            self::$written = true;
            if ($request instanceof Request) {
                $request->attributes->set('admin_op_log_written', true);
            }
        } catch (\Throwable) {
        }
    }

    /**
     * 没走 helper 的后台写操作：用路径凑一句中文，成功才记。
     */
    public static function fromSuccessfulRequest(Request $request, Response $response): void
    {
        if (self::$quiet > 0 || self::$written || $request->attributes->get('admin_op_log_written')) {
            return;
        }
        if (! self::shouldCapture($request)) {
            return;
        }
        if (! self::responseOk($response)) {
            return;
        }
        $input = $request->all();
        $summary = self::labelFromPath(
            strtoupper($request->method()),
            $request->path(),
            is_array($input) ? $input : []
        );
        if ($summary === null) {
            return;
        }
        $module = self::moduleFromPath($request->path());
        $targetId = self::idFromInput(is_array($input) ? $input : []);
        self::write((string) (self::actionFromPath($request->path(), strtoupper($request->method())) ?? 'save'), $summary, [
            'module' => $module,
            'target_type' => $module,
            'target_id' => $targetId,
            'payload' => is_array($input) ? $input : [],
        ]);
    }

    public static function object(string $module): string
    {
        return self::objects()[$module] ?? $module;
    }

    public static function named(string $object, string $subject = '', int $id = 0): string
    {
        $subject = trim($subject);
        if (in_array($object, ['评论', '留言', '报错', '订单', '提现', '播放失败'], true)) {
            $out = $object;
            if ($id > 0) {
                $out .= ' #'.$id;
            }
            if ($subject !== '') {
                $out .= '「'.self::cut($subject, 24).'」';
            }

            return $out;
        }
        if ($subject !== '') {
            return $object.'《'.self::cut($subject, 40).'》';
        }
        if ($id > 0) {
            return $object.' #'.$id;
        }

        return $object;
    }

    public static function subjectFrom(array $data, ?object $row = null): string
    {
        foreach (['title', 'name', 'code', 'username', 'author_name', 'order_no', 'content', 'email', 'api_url', 'path'] as $key) {
            $value = $data[$key] ?? null;
            if ($value === null && $row !== null) {
                $value = $row->{$key} ?? null;
            }
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }

    public static function moduleSaveSummary(string $module, bool $isUpdate, string $subject, int $id): string
    {
        $named = self::named(self::object($module), $subject, $id);
        $verb = $isUpdate ? '保存了' : '新增了';

        return $verb.$named;
    }

    public static function moduleDeleteSummary(string $module, string $subject, int $id): string
    {
        return '删除了'.self::named(self::object($module), $subject, $id);
    }

    public static function moduleBatchSummary(string $module, string $action, mixed $value, int $count): string
    {
        $object = self::object($module);
        $n = $count.' 条';

        return match ($action) {
            'delete' => '批量删除了 '.$n.$object,
            'status' => match ($module) {
                'withdraws' => match ((int) $value) {
                    1 => '批量打款 '.$count.' 条提现',
                    2 => '批量拒绝了 '.$n.'提现',
                    default => '批量改了 '.$n.'提现状态',
                },
                'comments' => (int) $value === 1 ? '批量通过了 '.$n.'评论' : '批量隐藏了 '.$n.'评论',
                'guestbooks' => (int) $value === 1 ? '批量通过了 '.$n.'留言' : '批量隐藏了 '.$n.'留言',
                default => '批量改了 '.$n.$object.'状态',
            },
            'points' => '给 '.$n.'会员调了积分',
            'read' => '把 '.$n.$object.'标成已读',
            'offline' => '下线了 '.$n.'播放失败线路',
            'type' => '批量改了 '.$n.$object.'分类',
            'group' => '批量改了 '.$n.'会员的分组',
            'slot' => '批量改了 '.$n.'广告位',
            'engine' => '批量改了 '.$n.'播放器内核',
            default => '批量处理了 '.$n.$object,
        };
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function labelFromPath(string $method, string $path, array $input = []): ?string
    {
        $path = trim($path, '/');
        $subject = self::subjectFrom($input);
        $id = self::idFromInput($input);
        $ids = $input['ids'] ?? null;
        $count = is_array($ids) ? count($ids) : (is_string($ids) && $ids !== '' ? count(array_filter(explode(',', $ids))) : 0);

        $exact = [
            'admin/video/settings' => '改了站点设置',
            'admin/video/settings/test-mail' => '发了测试邮件',
            'admin/system/tools/cache/flush' => '清了数据缓存',
            'admin/system/tools/cache/clear' => '清了缓存',
            'admin/set/user/password' => '修改了自己的密码',
            'admin/video/cards/generate' => '生成了积分卡密',
            'admin/video/invites/generate' => '生成了邀请码',
            'admin/video/collect_tasks/run' => '运行了采集任务',
            'admin/video/collects/run' => '运行了采集',
            'admin/video/collects/resume' => '继续了采集',
            'admin/video/collects/retry' => '重试了采集失败项',
            'admin/video/collects/bind' => '绑定了采集分类',
            'admin/video/unions/adopt' => '接入了资源联盟',
            'admin/video/players/ensure' => '补齐了默认播放器',
            'admin/video/hits-reset' => '重置了点击量',
            'admin/video/batch-replace-url' => '批量替换了播放地址',
            'admin/video/collect-due' => '跑了到期采集',
            'admin/video/make/cache' => '保存了全页缓存设置',
            'admin/video/make/cache-clear' => '清空了全页缓存',
            'admin/video/make/cache-warm' => '预热了全页缓存',
            'admin/video/make/disk' => '保存了磁盘静态页设置',
            'admin/video/make/start' => '开始生成静态页',
            'admin/video/make/cancel' => '停止了静态页任务',
            'admin/video/make/clear' => '删掉了静态页',
            'admin/video/make/run' => '生成了静态页',
            'admin/video/push/run' => '推送了搜索引擎',
            'admin/video/safety/scan' => '扫了挂马',
            'admin/video/templates/save' => '保存了主题模板',
            'admin/video/templates/backup' => '备份了主题模板',
            'admin/video/templates/rollback' => '回滚了主题模板',
            'admin/user/add' => '新增了管理员'.($subject !== '' ? ' '.$subject : ''),
            'admin/user/update' => '保存了管理员'.($subject !== '' ? ' '.$subject : ''),
            'admin/user/delete' => '删除了管理员'.($id > 0 ? ' #'.$id : ''),
            'admin/system/roles/add' => '新增了角色'.($subject !== '' ? '《'.$subject.'》' : ''),
            'admin/system/roles/update' => '保存了角色'.($subject !== '' ? '《'.$subject.'》' : ''),
            'admin/system/roles/delete' => '删除了角色'.($id > 0 ? ' #'.$id : ''),
            'admin/system/roles/perms/set' => '改了角色权限',
            'admin/system/menus/add' => '新增了菜单'.($subject !== '' ? '《'.$subject.'》' : ''),
            'admin/system/menus/update' => '保存了菜单'.($subject !== '' ? '《'.$subject.'》' : ''),
            'admin/system/menus/delete' => '删除了菜单'.($id > 0 ? ' #'.$id : ''),
            'admin/system/dicts/add' => '新增了字典'.($subject !== '' ? '《'.$subject.'》' : ''),
            'admin/system/dicts/update' => '保存了字典'.($subject !== '' ? '《'.$subject.'》' : ''),
            'admin/system/dicts/delete' => '删除了字典'.($id > 0 ? ' #'.$id : ''),
            'admin/system/attachments/upload' => '上传了附件',
            'admin/system/attachments/delete' => '删除了附件',
            'admin/system/database/backup/run' => '备份了数据库',
            'admin/system/database/backup/delete' => '删除了数据库备份',
            'admin/system/database/restore/run' => '还原了数据库',
            'admin/system/database/sql/run' => '执行了 SQL',
            'admin/system/database/replace/run' => '批量替换了数据',
            'admin/system/tools/schedule/save' => '保存了定时任务',
            'admin/system/tools/schedule/delete' => '删除了定时任务',
            'admin/system/tools/schedule/status' => '改了定时任务开关',
            'admin/system/tools/schedule/run' => '立刻跑了定时任务',
            'admin/video/collect_temps/promote' => '转入了待审采集'.($count > 0 ? ' '.$count.' 条' : ''),
        ];
        if (isset($exact[$path])) {
            return $exact[$path];
        }

        if (preg_match('#^admin/plugins/([^/]+)/toggle$#', $path, $m)) {
            $on = ! empty($input['enabled']);

            return ($on ? '启用了插件 ' : '停用了插件 ').$m[1];
        }
        if (preg_match('#^admin/plugins/([^/]+)/uninstall$#', $path, $m)) {
            return '卸载了插件 '.$m[1];
        }
        if ($path === 'admin/plugins/upload') {
            return '上传了插件';
        }
        if (preg_match('#^admin/video/topics/(\d+)/videos$#', $path, $m)) {
            return '给专题绑了影片 #'.$m[1];
        }
        if (preg_match('#^admin/video/topics/(\d+)/arts$#', $path, $m)) {
            return '给专题绑了文章 #'.$m[1];
        }
        if (preg_match('#^admin/video/tools/([^/]+)/run$#', $path, $m)) {
            $tool = $m[1];
            $act = (string) ($input['action'] ?? '');
            if (in_array($act, ['list', 'probe', 'scan'], true)) {
                return null;
            }

            return match ($tool.'.'.$act) {
                'recycle.restore' => '从回收站恢复了影片'.($count > 0 ? ' '.$count.' 部' : ''),
                'recycle.purge' => '彻底删除了回收站影片'.($count > 0 ? ' '.$count.' 部' : ''),
                'recycle.empty' => '清空了回收站',
                'images.localize' => '下载了远程封面',
                'players.replace' => '批量换了播放器',
                'annex.delete' => '删了没用的附件',
                default => null,
            };
        }

        $resource = self::pathResource($path);
        if ($resource === null) {
            return null;
        }
        $object = self::object($resource['module']);
        $tail = $resource['tail'];
        if ($tail === 'batch') {
            $batchAction = (string) ($input['action'] ?? '');
            if ($count > 0) {
                return self::moduleBatchSummary($resource['module'], $batchAction !== '' ? $batchAction : 'batch', $input['value'] ?? '', $count);
            }

            return '批量处理了'.$object;
        }
        if ($tail === 'delete') {
            return '删除了'.self::named($object, $subject, $id);
        }
        if ($tail === 'save' || $tail === 'add' || $tail === 'update') {
            $isUpdate = $id > 0 || $tail === 'update';

            return ($isUpdate ? '保存了' : '新增了').self::named($object, $subject, $id);
        }

        return null;
    }

    public static function shouldCapture(Request $request): bool
    {
        if (! $request->is('admin/*') && ! $request->is('api/admin/*')) {
            return false;
        }
        $method = strtoupper($request->method());
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return false;
        }
        $path = $request->path();
        $skip = [
            'admin/login',
            'api/admin/login',
            'admin/logout',
            'admin/ui-locale',
            'admin/video/audits/try',
            'admin/video/make/step',
            'admin/video/make/status',
            'admin/video/collects/suggest',
            'admin/video/templates/read',
        ];
        if (in_array($path, $skip, true)) {
            return false;
        }
        $action = (string) $request->input('action', '');
        if (in_array($action, ['list', 'probe', 'scan', 'try', 'suggest'], true)) {
            return false;
        }

        return true;
    }

    private static function responseOk(Response $response): bool
    {
        $http = $response->getStatusCode();
        if ($http >= 400) {
            return false;
        }
        if ($response instanceof JsonResponse) {
            $payload = $response->getData(true);
            if (is_array($payload) && array_key_exists('code', $payload) && is_numeric($payload['code'])) {
                $code = (int) $payload['code'];

                return $code === 0 || ($code >= 200 && $code < 300);
            }
        }

        return $http >= 200 && $http < 400;
    }

    /**
     * @return array{module:string,tail:string}|null
     */
    private static function pathResource(string $path): ?array
    {
        if (preg_match('#^admin/video/([^/]+)/(save|delete|batch)$#', $path, $m)) {
            $mod = $m[1];
            $map = [
                'types' => 'types',
                'tags' => 'tags',
                'actors' => 'actors',
                'sources' => 'sources',
                'episodes' => 'episodes',
                'collects' => 'collects',
            ];

            return ['module' => $map[$mod] ?? $mod, 'tail' => $m[2]];
        }
        if (preg_match('#^admin/video/(save|delete|batch)$#', $path, $m)) {
            return ['module' => 'videos', 'tail' => $m[1]];
        }

        return null;
    }

    private static function moduleFromPath(string $path): string
    {
        $resource = self::pathResource($path);
        if ($resource !== null) {
            return self::object($resource['module']);
        }
        if (str_starts_with($path, 'admin/plugins')) {
            return '插件';
        }
        if (str_starts_with($path, 'admin/video/settings')) {
            return '站点设置';
        }
        if (str_starts_with($path, 'admin/user')) {
            return '管理员';
        }
        if (str_contains($path, '/cache')) {
            return '缓存';
        }
        if (str_contains($path, '/templates')) {
            return '模板';
        }

        return '后台';
    }

    private static function actionFromPath(string $path, string $method): ?string
    {
        if (str_ends_with($path, '/delete') || $method === 'DELETE') {
            return 'delete';
        }
        if (str_ends_with($path, '/batch')) {
            return 'batch';
        }
        if (str_contains($path, '/toggle')) {
            return 'toggle';
        }
        if (str_contains($path, '/upload')) {
            return 'upload';
        }
        if (str_contains($path, '/uninstall')) {
            return 'uninstall';
        }
        if (str_contains($path, '/generate')) {
            return 'generate';
        }
        if (str_contains($path, '/run') || str_contains($path, '/resume')) {
            return 'run';
        }

        return 'save';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private static function idFromInput(array $input): int
    {
        foreach (['id', 'user_id', 'video_id'] as $key) {
            if (isset($input[$key]) && is_numeric($input[$key])) {
                return (int) $input[$key];
            }
        }

        return 0;
    }

    /**
     * @return array<string, string>
     */
    private static function objects(): array
    {
        return [
            'videos' => '影片',
            'comments' => '评论',
            'topics' => '专题',
            'arts' => '文章',
            'slides' => '幻灯片',
            'members' => '会员',
            'orders' => '订单',
            'groups' => '会员组',
            'cards' => '卡密',
            'plogs' => '积分流水',
            'ads' => '广告',
            'links' => '友链',
            'players' => '播放器',
            'unions' => '资源联盟',
            'collect_logs' => '采集日志',
            'collect_tasks' => '采集任务',
            'collect_temps' => '待审采集',
            'searchwords' => '搜索词',
            'reports' => '报错',
            'guestbooks' => '留言',
            'playfails' => '播放失败',
            'pms' => '站内信',
            'notifies' => '通知',
            'withdraws' => '提现',
            'invites' => '邀请码',
            'favorites' => '收藏',
            'audits' => '审核规则',
            'downloaders' => '下载器',
            'servers' => '服务器组',
            'types' => '分类',
            'tags' => '标签',
            'actors' => '演员',
            'sources' => '线路',
            'episodes' => '剧集',
            'collects' => '采集源',
            'settings' => '站点设置',
            'plugins' => '插件',
            'users' => '管理员',
            'roles' => '角色',
            'menus' => '菜单',
            'dicts' => '字典',
            'cache' => '缓存',
            'templates' => '模板',
            'schedule' => '定时任务',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function encodePayload(array $data): string
    {
        $clean = self::scrub($data);
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($json) || $json === '[]' || $json === '{}') {
            return '';
        }

        return self::cut($json, 2000);
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    private static function scrub(array $data): array
    {
        $sensitive = [
            'password', 'pwd', 'pass', 'current_password', 'new_password', 'confirm_password',
            'password_confirmation', 'token', 'access_token', 'smtp_pass', 's3_secret', 's3_key',
            'ai_key', 'pay_wechat_key', 'pay_alipay_key', 'secret', 'app_key', 'provide_key',
            'inbound_key', 'sms_key', 'sms_secret', 'weixin_secret', 'weixin_token', 'baidu_push_token',
            'shenma_push_token', 'bing_push_token',
        ];
        $out = [];
        foreach ($data as $key => $value) {
            $name = strtolower((string) $key);
            if (in_array($name, $sensitive, true) || str_contains($name, 'password') || str_ends_with($name, '_secret') || (str_ends_with($name, '_key') && $name !== 'user_key')) {
                $out[$key] = '***';
                continue;
            }
            if (is_array($value)) {
                $out[$key] = self::scrub($value);
                continue;
            }
            if (is_scalar($value) || $value === null) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    private static function hasColumn(string $column): bool
    {
        if (self::$columns === null) {
            try {
                self::$columns = Schema::getColumnListing('sys_operate_log');
            } catch (\Throwable) {
                self::$columns = [];
            }
        }

        return in_array($column, self::$columns, true);
    }

    private static function cut(string $value, int $max): string
    {
        if ($max <= 0 || $value === '') {
            return $value;
        }

        return mb_strlen($value, 'UTF-8') > $max ? mb_substr($value, 0, $max, 'UTF-8') : $value;
    }
}
