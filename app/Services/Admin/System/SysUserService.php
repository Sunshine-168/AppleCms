<?php
namespace App\Services\Admin\System;

use App\Models\System\SysDictModel;
use App\Models\System\SysOperateLogModel;
use App\Models\System\SysRoleModel;
use App\Models\System\SysSystemLogModel;
use App\Models\System\SysUserLogModel;
use App\Models\System\SysUserModel;
use App\Models\System\SysUserRoleModel;
use App\Support\AdminOpLog;
use App\Support\Captcha;
use App\Support\Utils\Result;
use App\Support\Utils\Syslog;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;


/**
 * 系统用户服务
 */
class SysUserService
{
    public SysUserModel $sysUserModel;
    public SysUserLogModel $sysUserLogModel;
    public SysOperateLogModel $sysOperateLogModel;
    public SysSystemLogModel $sysSystemLogModel;
    public SysDictModel $sysDictModel;
    public SysUserRoleModel $sysUserRoleModel;
    public SysRoleModel $sysRoleModel;

    /**
     * 构造方法
     * 初始化系统用户、系统字典、登录日志与用户角色模型
     */
    public function __construct()
    {
        $this->sysUserModel          = new SysUserModel();
        $this->sysDictModel          = new SysDictModel();
        $this->sysUserLogModel       = new SysUserLogModel();
        $this->sysOperateLogModel    = new SysOperateLogModel();
        $this->sysSystemLogModel     = new SysSystemLogModel();
        $this->sysUserRoleModel      = new SysUserRoleModel();
        $this->sysRoleModel          = new SysRoleModel();
    }

    /**
     * 管理员页：角色下拉与人数
     *
     * @return array{roles:list<array<string,mixed>>,queues:array<string,int>}
     */
    public function adminBoard(): array
    {
        $roles = [];
        $queues = ['all' => 0, 'founder' => 0, 'staff' => 0, 'never' => 0];
        try {
            $queues['all'] = $this->sysUserModel->countByCondition([]);
            $queues['founder'] = $this->sysUserModel->countByCondition([['id', '=', 1]]);
            $queues['staff'] = max(0, $queues['all'] - $queues['founder']);
            $queues['never'] = $this->sysUserModel->countByCondition([['login_time', '<=', 0]]);
            $rows = $this->sysRoleModel->selectByCondition([], ['id', 'name', 'status'], ['sort' => 'desc', 'id' => 'desc']);
            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id < 1) {
                    continue;
                }
                $row['count'] = $this->sysUserModel->countByCondition([['role_id', '=', $id]]);
                $roles[] = $row;
            }
        } catch (\Throwable) {
        }

        return compact('roles', 'queues');
    }

    /**
     * 用户列表
     *
     * @param  array{q?:string,kind?:string,role_id?:string,limit?:int}  $params
     */
    public function getSysUserLists(array $params): array
    {
        $where = [];
        $q = trim((string) ($params['q'] ?? ''));
        $kind = (string) ($params['kind'] ?? '');
        $roleId = (int) ($params['role_id'] ?? 0);
        $limit = (int) ($params['limit'] ?? 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where['or'] = [
                ['username', 'like', $like],
                ['email', 'like', $like],
            ];
        }
        if ($kind === 'founder') {
            $where[] = ['id', '=', 1];
        } elseif ($kind === 'staff') {
            $where[] = ['id', '<>', 1];
        } elseif ($kind === 'never') {
            $where[] = ['login_time', '<=', 0];
        } elseif ($roleId > 0) {
            $where[] = ['role_id', '=', $roleId];
        }

        $data = $this->sysUserModel->paginates($where, '*', $limit, ['id' => 'asc']);
        $currentId = (int) session('admin_uid', 0);
        foreach ($data['data'] as &$item) {
            $item = $this->decorateAdmin($item, $currentId);
        }
        unset($item);

        return Result::success($data);
    }

    /**
     * 获取用户已绑定角色ID
     * 根据用户ID返回其 `role_id` 数组
     */
    public function getUserRoleIds(int $userId): array
    {
        $ids = $this->sysUserRoleModel->uniqueColumnByCondition(['user_id' => $userId], 'role_id');
        return Result::success($ids);
    }

    /**
     * 设置用户角色
     * 传入用户ID与角色ID数组，先清空后批量重建绑定关系
     */
    public function setUserRoles(int $userId, array $roleIds): array
    {
        $this->sysUserRoleModel->deleteByCondition(['user_id' => $userId]);

        $rows  = [];
        $time  = time();

        foreach ($roleIds as $rid)
        {
            $rows[] = ['user_id' => $userId, 'role_id' => (int)$rid, 'create_time' => $time, 'update_time' => $time];
        }

        if (!empty($rows))
        {
            $this->sysUserRoleModel->insertsAll($rows);
        }

        return Result::success();
    }

    /**
     * 获取用户信息
     * @param int $id
     * @return array
     */
    public function getSysUserInfo(int $id): array
    {
        $data = $this->sysUserModel->findById($id);

        return Result::success($data);
    }

    /**
     * 添加用户
     * @param string $username
     * @param string $password
     * @return array
     */
    public function addSysUser(string $username, string $password, string $email = '', string $remark = '', int $role = 1, int $roleId = 0): array
    {
        $username = trim($username);
        $password = trim($password);
        $email = trim($email);
        $remark = trim($remark);
        if ($username === '') {
            return Result::fail('请填写登录名');
        }
        if (mb_strlen($username) < 2) {
            return Result::fail('登录名至少 2 个字');
        }
        if ($password === '') {
            return Result::fail('请填写密码');
        }
        if (strlen($password) < 6) {
            return Result::fail('密码至少 6 位');
        }
        if ($this->sysUserModel->findByCondition([['username', '=', $username]])) {
            return Result::fail('这个登录名已经有人用了');
        }

        $insert = [
            'username' => $username,
            'password' => $this->hashLoginPassword($password),
            'email' => $email,
            'remark' => $remark,
            'role' => 1,
            'role_id' => max(0, $roleId),
            'login_time' => 0,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $res = $this->sysUserModel->inserts($insert);
        if (! $res) {
            return Result::fail('没能添加');
        }

        return AdminOpLog::ifOk(Result::success([], '已添加，可以登录后台'), 'save', '新增了管理员 '.$username, [
            'module' => '管理员',
            'target_type' => 'users',
            'payload' => ['username' => $username],
        ]);
    }

    /**
     * 更新用户
     * 注意：如果需要修改用户的 role 字段，需要在控制器传入该参数
     * @param int $id
     * @param string $username
     * @param string $password
     * @return array
     */
    public function updateSysUser(int $id, string $username, string $password, string $email = '', string $remark = '', ?int $role = null, ?int $roleId = null): array
    {
        if ($id < 1) {
            return Result::fail('管理员不存在');
        }
        $user = $this->sysUserModel->findById($id);
        if (! $user) {
            return Result::fail('管理员不存在');
        }
        $username = trim($username);
        $password = trim($password);
        $email = trim($email);
        $remark = trim($remark);
        if ($username === '') {
            return Result::fail('请填写登录名');
        }
        $dup = $this->sysUserModel->findByCondition([['username', '=', $username]]);
        if ($dup && (int) ($dup['id'] ?? 0) !== $id) {
            return Result::fail('这个登录名已经有人用了');
        }
        if ($password !== '' && strlen($password) < 6) {
            return Result::fail('密码至少 6 位');
        }

        $update = [
            'username' => $username,
            'email' => $email,
            'remark' => $remark,
            'update_time' => time(),
        ];
        if ($password !== '') {
            $update['password'] = $this->hashLoginPassword($password);
        }
        if ($id === 1) {
            $update['role'] = 0;
        } else {
            $update['role'] = 1;
            if ($roleId !== null) {
                $update['role_id'] = max(0, $roleId);
            }
        }

        $res = $this->sysUserModel->updateById($id, $update);
        if (! $res) {
            return Result::fail('没能保存');
        }
        $this->forgetUserCache($id);
        if ($id === (int) session('admin_uid', 0)) {
            session(['admin_username' => $username]);
        }

        return AdminOpLog::ifOk(Result::success([], '已保存'), 'save', '保存了管理员 '.$username, [
            'module' => '管理员',
            'target_type' => 'users',
            'target_id' => $id,
            'payload' => ['username' => $username, 'password_changed' => $password !== ''],
        ]);
    }

    /**
     * 删除用户
     * @param int $id
     * @return array
     */
    public function deleteSysUser(int $id): array
    {
        if ($id < 1) {
            return Result::fail('管理员不存在');
        }
        if ($id === 1) {
            return Result::fail('创始人不能删');
        }
        if ($id === (int) session('admin_uid', 0)) {
            return Result::fail('不能删自己正在用的账号');
        }
        $total = $this->sysUserModel->countByCondition([]);
        if ($total <= 1) {
            return Result::fail('至少留一位管理员');
        }
        $gone = (string) (($this->sysUserModel->findById($id)['username'] ?? ''));

        $res = $this->sysUserModel->deleteById($id);
        if (! $res) {
            return Result::fail('没能删除');
        }
        $this->forgetUserCache($id);

        return AdminOpLog::ifOk(Result::success([], '已删除'), 'delete', '删除了管理员'.($gone !== '' ? ' '.$gone : ' #'.$id), [
            'module' => '管理员',
            'target_type' => 'users',
            'target_id' => $id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function decorateAdmin(array $item, int $currentId): array
    {
        $id = (int) ($item['id'] ?? 0);
        $roleId = (int) ($item['role_id'] ?? 0);
        $isFounder = $id === 1;
        $isSelf = $id > 0 && $id === $currentId;
        $roleName = '';
        if ($roleId > 0) {
            $roleName = (string) ($this->sysRoleModel->findById($roleId)['name'] ?? '');
        }
        $item['is_founder'] = $isFounder;
        $item['is_self'] = $isSelf;
        $item['can_delete'] = ! $isFounder && ! $isSelf;
        $item['role_name'] = $roleName;
        $item['kind_label'] = $isFounder ? '创始人' : ($roleName !== '' ? $roleName : '未分角色');
        $item['login_text'] = $this->loginText($item);
        $item['never_login'] = ($item['login_text'] === '从未登录');
        $stamp = (int) ($item['login_time'] ?? 0);
        $item['login_time'] = $stamp > 0 ? date('Y-m-d H:i:s', $stamp) : '';

        return $item;
    }

    /** @param  array<string, mixed>  $item */
    protected function loginText(array $item): string
    {
        $stamp = (int) ($item['login_time'] ?? 0);
        if ($stamp <= 0) {
            return '从未登录';
        }
        $today = strtotime('today');
        $time = date('H:i', $stamp);
        if ($stamp >= $today) {
            return '今天 '.$time;
        }
        if ($stamp >= $today - 86400) {
            return '昨天 '.$time;
        }

        return date('m-d H:i', $stamp);
    }

    protected function forgetUserCache(int $id): void
    {
        Cache::forget('admin_user_'.$id);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function decorateOperateLog(array $item, string $me): array
    {
        $stamp = 0;
        if (! empty($item['create_time']) && is_numeric($item['create_time'])) {
            $stamp = (int) $item['create_time'];
        } elseif (! empty($item['create_at']) && is_numeric($item['create_at'])) {
            $stamp = (int) $item['create_at'];
        }
        $ip = trim((string) ($item['login_ip'] ?? ''));
        $summary = trim((string) ($item['title'] ?? ''));
        $module = trim((string) ($item['module'] ?? ''));
        $item['is_self'] = $me !== '' && (string) ($item['username'] ?? '') === $me;
        $item['summary'] = $summary !== '' ? $summary : '做了一次操作';
        $item['module_text'] = $module;
        $item['time_text'] = $this->loginLogTimeText($stamp);
        $item['place_text'] = $this->ipPlaceText($ip, (string) ($item['ip_address'] ?? ''));
        $item['device_text'] = $this->deviceText((string) ($item['user_agent'] ?? ''));
        $item['create_time'] = $stamp > 0 ? date('Y-m-d H:i:s', $stamp) : (string) ($item['create_time'] ?? '');
        $extra = [];
        $url = trim((string) ($item['url'] ?? ''));
        if ($url !== '') {
            $path = parse_url($url, PHP_URL_PATH);
            $extra[] = is_string($path) && $path !== '' ? $path : $url;
        }
        $targetId = (int) ($item['target_id'] ?? 0);
        if ($targetId > 0) {
            $extra[] = '编号 '.$targetId;
        }
        $item['extra_text'] = implode(' · ', $extra);

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function decorateSystemLog(array $item, string $me): array
    {
        $stamp = 0;
        if (! empty($item['create_time']) && is_numeric($item['create_time'])) {
            $stamp = (int) $item['create_time'];
        } elseif (! empty($item['create_at']) && is_numeric($item['create_at'])) {
            $stamp = (int) $item['create_at'];
        }
        $class = trim((string) ($item['exception_class'] ?? ''));
        $msg = trim((string) ($item['exception_message'] ?? ''));
        if ($msg === '') {
            $msg = trim((string) ($item['message'] ?? ''));
        }
        $url = trim((string) ($item['url'] ?? ''));
        $file = trim((string) ($item['file'] ?? ''));
        $line = (int) ($item['line'] ?? 0);
        $level = strtolower(trim((string) ($item['level'] ?? '')));
        $username = trim((string) ($item['username'] ?? ''));
        $extra = trim((string) ($item['extra'] ?? ''));
        $item['is_self'] = $me !== '' && $username === $me;
        $item['is_error'] = in_array($level, ['error', 'critical', 'alert', 'emergency'], true) || $class !== '';
        $item['kind_text'] = $this->systemLogKind($class, $level);
        $item['summary'] = $this->systemLogSummary($msg);
        $item['path_text'] = $this->systemLogPath($url);
        $item['file_text'] = $this->systemLogFile($file, $line);
        $item['who_text'] = $username !== '' ? $username : '系统';
        $item['area_text'] = str_contains($url, '/admin') || (string) ($item['module'] ?? '') === 'admin' ? '后台' : '前台';
        $item['time_text'] = $this->loginLogTimeText($stamp);
        $item['create_time'] = $stamp > 0 ? date('Y-m-d H:i:s', $stamp) : (string) ($item['create_time'] ?? '');
        $bits = [];
        if ($extra !== '') {
            $bits[] = $this->systemLogExtraHint($extra);
        }
        $channel = trim((string) ($item['channel'] ?? ''));
        if ($channel !== '' && $channel !== 'system') {
            $bits[] = $this->systemLogChannel($channel);
        }
        $item['extra_text'] = implode(' · ', array_filter($bits));
        $item['detail_message'] = $msg;
        $item['detail_class'] = $class !== '' ? $class : '';
        $item['detail_file'] = $file !== '' ? ($file.($line > 0 ? ':'.$line : '')) : '';
        $item['detail_url'] = $url;
        $item['detail_trace'] = mb_substr(trim((string) ($item['trace'] ?? '')), 0, 8000);

        return $item;
    }

    protected function systemLogKind(string $class, string $level): string
    {
        $short = $class !== '' ? (string) class_basename($class) : '';
        if (str_contains($class, 'QueryException') || str_contains($class, 'PDOException')) {
            return '数据库';
        }
        if (str_contains($class, 'ModelNotFound')) {
            return '找不到记录';
        }
        if (str_contains($class, 'NotFoundHttp')) {
            return '页面不存在';
        }
        if (str_contains($class, 'Authentication') || str_contains($class, 'Unauthorized')) {
            return '未登录';
        }
        if ($short === 'InvalidArgumentException') {
            return '参数不对';
        }
        if ($short === 'RuntimeException') {
            return '程序出错';
        }
        if ($short !== '') {
            return $short;
        }

        return match ($level) {
            'warning' => '偏慢/警告',
            'info', 'notice', 'debug' => '记录',
            default => '报错',
        };
    }

    protected function systemLogSummary(string $msg): string
    {
        $msg = trim(preg_replace('/\s+/', ' ', $msg) ?? $msg);
        if ($msg === '') {
            return '一次程序报错';
        }
        if (function_exists('mb_strlen') && mb_strlen($msg) > 120) {
            return mb_substr($msg, 0, 120).'…';
        }
        if (strlen($msg) > 120) {
            return substr($msg, 0, 120).'…';
        }

        return $msg;
    }

    protected function systemLogPath(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
        $query = (string) (parse_url($url, PHP_URL_QUERY) ?: '');
        if ($path === '' && $query === '') {
            return $url;
        }
        $shown = $path !== '' ? $path : '/';
        if ($query !== '') {
            $shown .= '?'.$query;
        }
        if (function_exists('mb_strlen') && mb_strlen($shown) > 80) {
            return mb_substr($shown, 0, 80).'…';
        }

        return $shown;
    }

    protected function systemLogFile(string $file, int $line): string
    {
        $file = trim($file);
        if ($file === '') {
            return '';
        }
        $base = str_replace('\\', '/', $file);
        $name = basename($base);

        return $line > 0 ? $name.':'.$line : $name;
    }

    protected function systemLogChannel(string $channel): string
    {
        return match ($channel) {
            'repository' => '查库',
            'cache' => '缓存',
            'admin' => '后台',
            'request' => '请求',
            'transaction' => '事务',
            default => $channel,
        };
    }

    protected function systemLogExtraHint(string $extra): string
    {
        $extra = trim($extra);
        if ($extra === '') {
            return '';
        }
        if (str_starts_with($extra, '{') || str_starts_with($extra, '[')) {
            return '';
        }
        if (function_exists('mb_strlen') && mb_strlen($extra) > 40) {
            return mb_substr($extra, 0, 40).'…';
        }

        return $extra;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function decorateLoginLog(array $item, string $me): array
    {
        $stamp = 0;
        if (! empty($item['create_time']) && is_numeric($item['create_time'])) {
            $stamp = (int) $item['create_time'];
        } elseif (! empty($item['create_at']) && is_numeric($item['create_at'])) {
            $stamp = (int) $item['create_at'];
        }
        $ip = trim((string) ($item['login_ip'] ?? ''));
        $ua = (string) ($item['login_agent'] ?? '');
        $item['is_self'] = $me !== '' && (string) ($item['username'] ?? '') === $me;
        $item['time_text'] = $this->loginLogTimeText($stamp);
        $item['place_text'] = $this->ipPlaceText($ip, (string) ($item['ip_address'] ?? ''));
        $item['device_text'] = $this->deviceText($ua);
        $item['create_time'] = $stamp > 0 ? date('Y-m-d H:i:s', $stamp) : (string) ($item['create_time'] ?? '');

        return $item;
    }

    protected function loginLogTimeText(int $stamp): string
    {
        if ($stamp <= 0) {
            return '';
        }
        $clock = date('H:i:s', $stamp);
        $today = strtotime('today');
        if ($stamp >= $today) {
            return '今天 '.$clock;
        }
        if ($stamp >= $today - 86400) {
            return '昨天 '.$clock;
        }
        if ((int) date('Y', $stamp) === (int) date('Y')) {
            return date('m-d ', $stamp).$clock;
        }

        return date('Y-m-d ', $stamp).$clock;
    }

    protected function ipPlaceText(string $ip, string $stored): string
    {
        $ip = trim($ip);
        if ($ip === '' || $ip === '0.0.0.0') {
            return '';
        }
        if ($ip === '::1' || $ip === '127.0.0.1' || str_starts_with($ip, '127.')) {
            return '本机';
        }
        if (str_starts_with($ip, '172.17.')) {
            return '内网（Docker）';
        }
        $valid = filter_var($ip, FILTER_VALIDATE_IP);
        $public = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if ($valid && $public === false) {
            return '内网';
        }
        $stored = trim(str_replace(['，'], ',', $stored));
        if ($stored === '' || $stored === '0') {
            return '';
        }
        $parts = [];
        foreach (explode(',', $stored) as $part) {
            $part = trim($part);
            if ($part !== '' && $part !== '0') {
                $parts[] = $part;
            }
        }

        return implode(' ', $parts);
    }

    protected function deviceText(string $ua): string
    {
        $ua = trim($ua);
        if ($ua === '') {
            return '';
        }
        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => '',
        };
        $browser = match (true) {
            str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Cursor/') => 'Cursor',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            default => '',
        };

        return trim($browser.($os !== '' ? ' · '.$os : ''), ' ·');
    }

    /**
     * 登入
     * 校验账号密码与图片验证码，写入登录日志并更新 token
     * @param string $username
     * @param string $password
     * @param string $vscode
     * @return array
     */
    public function login(string $username, string $password, string $vscode): array
    {
        $time    = time();
        $ip      = Request::ip();
        $username = trim($username);
        $password = trim($password);
        if ($username === '' || $password === '') {
            return Result::fail('请填写账号和密码');
        }
        $lockKey = 'admin.login.lock.'.md5((string) $ip);
        if (Cache::has($lockKey)) {
            return Result::fail('登录失败次数过多，请 15 分钟后再试');
        }

        $captcha = trim($vscode);

        if ($captcha === '')
        {
            return Result::fail('请输入验证码');
        }

        if (! Captcha::check($captcha))
        {
            return Result::fail('验证码错误');
        }

        $user = $this->sysUserModel->findByCondition([['username', '=', $username]]);

        if (! $user || ! $this->passwordMatches($password, $this->storedPassword((int) ($user['id'] ?? 0), $user)))
        {
            if (! $user && (int) DB::table('sys_user')->count() === 0) {
                return Result::fail('还没有管理员，请重新安装');
            }
            $failKey = 'admin.login.fail.'.md5((string) $ip);
            $fails = (int) Cache::get($failKey, 0) + 1;
            Cache::put($failKey, $fails, 900);
            if ($fails >= 5) {
                Cache::put($lockKey, 1, 900);
            }

            return Result::fail('账号或者密码错误');
        }
        if ((int) ($user['status'] ?? 1) !== 1) {
            return Result::fail('账号已停用');
        }
        Cache::forget('admin.login.fail.'.md5((string) $ip));

        $ipAddress  = \App\Support\Utils\IpAddress::region((string) $ip);

        DB::beginTransaction();

        try {

            $update = [
                'login_time'  => $time,
                'login_ip'    => $ip,
                'ip_address'  => $ipAddress,
                'login_agent' => (string) \request()->userAgent(),
                'token'       => md5($time . $user['id'])
            ];

            $res = $this->sysUserModel->updateById($user['id'], $update);

            if (!$res)
            {
                DB::rollBack();
                return Result::fail('登入失败');
            }

            $insert = [
                'uid'         => $user['id'],
                'username'    => $user['username'],
                'login_ip'    => $ip,
                'login_agent' => (string) \request()->userAgent(),
                'ip_address'  => $ipAddress,
                'create_time' => $time,
                'update_time' => $time,
            ];

            $res = $this->sysUserLogModel->inserts($insert);

            if (!$res)
            {
                DB::rollBack();
                return Result::fail('登入失败');
            }

            DB::commit();

            session([
                'admin_uid' => (int) $user['id'],
                'admin_username' => (string) $user['username'],
            ]);

            return Result::success($update, '登录成功');
        }
        catch (Exception $e)
        {
            DB::rollBack();
            Syslog::exception('admin', $e);
            return Result::fail('登入失败');
        }
    }
    /**
     * 退出登录
     * 清空用户 token 并更新登录时间
     * @param int $uid
     * @param string $token
     * @return array
     */
    public function logout(int $uid = 0, string $token = ''): array
    {
        $time = time();

        if ($uid > 0)
        {
            $this->sysUserModel->updateById($uid, [
                'token' => '',
                'update_time' => $time,
            ]);
        }
        elseif ($token !== '')
        {
            $user = $this->sysUserModel->findByCondition([
                ['token', '=', $token],
            ]);

            if (!empty($user) && !empty($user['id']))
            {
                $this->sysUserModel->updateById((int) $user['id'], [
                    'token' => '',
                    'update_time' => $time,
                ]);
            }
        }

        return Result::success([], '退出成功');
    }

    /**
     * 获取系统用户登录日志列表
     *
     * @param  array{q?:string,username?:string,login_ip?:string,mine?:bool,start_time?:string,end_time?:string,limit?:int}  $params
     */
    public function getSysUserLoginLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 20);
        if ($limit < 1) {
            $limit = 20;
        }
        $q = trim((string) ($params['q'] ?? ''));
        $username = trim((string) ($params['username'] ?? ''));
        $ip = trim((string) ($params['login_ip'] ?? ''));
        if (! empty($params['mine'])) {
            $me = trim((string) session('admin_username', ''));
            if ($me !== '') {
                $where[] = ['username', '=', $me];
            }
        } elseif ($username !== '' && $q === '') {
            $where[] = ['username', 'like', '%'.$username.'%'];
        }
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where['or'] = [
                ['username', 'like', $like],
                ['login_ip', 'like', $like],
                ['ip_address', 'like', $like],
            ];
        }
        if ($ip !== '') {
            $where[] = ['login_ip', '=', $ip];
        }
        if (! empty($params['start_time'])) {
            $where[] = ['create_time', '>=', strtotime((string) $params['start_time'])];
        }
        if (! empty($params['end_time'])) {
            $where[] = ['create_time', '<=', strtotime((string) $params['end_time']) + 86400];
        }

        $data = $this->sysUserLogModel->paginates($where, '*', $limit, ['id' => 'desc']);
        if (! isset($data['data']) || ! is_array($data['data'])) {
            $data = ['total' => 0, 'data' => []];
        }
        $me = (string) session('admin_username', '');
        foreach ($data['data'] as &$item) {
            $item = $this->decorateLoginLog($item, $me);
        }
        unset($item);

        return Result::success($data);
    }

    /**
     * 获取系统用户操作日志列表
     *
     * @param  array{q?:string,username?:string,login_ip?:string,mine?:bool,start_time?:string,end_time?:string,limit?:int}  $params
     */
    public function getSysOperateLogLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 20);
        if ($limit < 1) {
            $limit = 20;
        }
        $q = trim((string) ($params['q'] ?? ''));
        $username = trim((string) ($params['username'] ?? ''));
        $ip = trim((string) ($params['login_ip'] ?? ''));
        if (! empty($params['mine'])) {
            $me = trim((string) session('admin_username', ''));
            if ($me !== '') {
                $where[] = ['username', '=', $me];
            }
        } elseif ($username !== '' && $q === '') {
            $where[] = ['username', 'like', '%'.$username.'%'];
        }
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where['or'] = [
                ['username', 'like', $like],
                ['title', 'like', $like],
                ['module', 'like', $like],
                ['login_ip', 'like', $like],
            ];
        }
        if ($ip !== '') {
            $where[] = ['login_ip', '=', $ip];
        }
        if (! empty($params['start_time'])) {
            $where[] = ['create_time', '>=', strtotime((string) $params['start_time'])];
        }
        if (! empty($params['end_time'])) {
            $where[] = ['create_time', '<=', strtotime((string) $params['end_time']) + 86400];
        }

        $data = $this->sysOperateLogModel->paginates($where, '*', $limit, ['id' => 'desc']);
        if (! isset($data['data']) || ! is_array($data['data'])) {
            $data = ['total' => 0, 'data' => []];
        }
        $me = (string) session('admin_username', '');
        foreach ($data['data'] as &$item) {
            $item = $this->decorateOperateLog($item, $me);
        }
        unset($item);

        return Result::success($data);
    }
    /**
     * @param  array{q?:string,level?:string,area?:string,ip?:string,start_time?:string,end_time?:string,limit?:int}  $params
     */
    public function getSysSystemLogLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 20);
        if ($limit < 1) {
            $limit = 20;
        }
        $q = trim((string) ($params['q'] ?? ''));
        $level = strtolower(trim((string) ($params['level'] ?? '')));
        $area = strtolower(trim((string) ($params['area'] ?? '')));
        $ip = trim((string) ($params['ip'] ?? ''));
        if ($level === 'error') {
            $where[] = ['level', 'in', ['error', 'critical', 'alert', 'emergency']];
        } elseif ($level !== '') {
            $where[] = ['level', '=', $level];
        }
        if ($area === 'admin') {
            $where[] = ['url', 'like', '%/admin%'];
        } elseif ($area === 'front') {
            $where[] = ['url', 'not like', '%/admin%'];
        }
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where['or'] = [
                ['message', 'like', $like],
                ['exception_message', 'like', $like],
                ['exception_class', 'like', $like],
                ['url', 'like', $like],
                ['username', 'like', $like],
                ['ip', 'like', $like],
                ['extra', 'like', $like],
            ];
        }
        if ($ip !== '') {
            $where[] = ['ip', '=', $ip];
        }
        if (! empty($params['start_time'])) {
            $where[] = ['create_time', '>=', strtotime((string) $params['start_time'])];
        }
        if (! empty($params['end_time'])) {
            $where[] = ['create_time', '<=', strtotime((string) $params['end_time']) + 86400];
        }

        $data = $this->sysSystemLogModel->paginates($where, '*', $limit, ['id' => 'desc']);
        if (! isset($data['data']) || ! is_array($data['data'])) {
            $data = ['total' => 0, 'data' => []];
        }
        $me = (string) session('admin_username', '');
        foreach ($data['data'] as &$item) {
            $item = $this->decorateSystemLog($item, $me);
        }
        unset($item);

        return Result::success($data);
    }
    /**
     * 修改密码
     * @param int $uid
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array
     */
    public function changePassword(int $uid, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        if ($uid <= 0)
        {
            return Result::fail('未登录');
        }

        $currentPassword = (string) $currentPassword;
        $newPassword     = (string) $newPassword;
        $confirmPassword = (string) $confirmPassword;

        if (trim($currentPassword) === '')
        {
            return Result::fail('请输入当前密码');
        }

        if (trim($newPassword) === '')
        {
            return Result::fail('请输入新密码');
        }

        if (strlen($newPassword) < 6)
        {
            return Result::fail('新密码至少6位');
        }

        if ($newPassword !== $confirmPassword)
        {
            return Result::fail('两次新密码不一致');
        }

        if ($currentPassword === $newPassword)
        {
            return Result::fail('新密码不能与当前密码相同');
        }

        $user = $this->sysUserModel->findById($uid);
        if (!$user)
        {
            return Result::fail('用户不存在');
        }

        if (! $this->passwordMatches($currentPassword, $this->storedPassword($uid, $user)))
        {
            return Result::fail('当前密码错误');
        }

        $res = $this->sysUserModel->updateById($uid, [
            'password'    => $newPassword,
            'update_time' => time(),
        ]);

        if (!$res)
        {
            return Result::fail('修改失败');
        }

        return AdminOpLog::ifOk(Result::success([], '修改成功'), 'save', '修改了自己的密码', [
            'module' => '管理员',
            'target_type' => 'users',
            'target_id' => $uid,
        ]);
    }

    public function unlock(int $uid, string $password): array
    {
        $password = trim($password);
        if ($uid <= 0) {
            return Result::fail('未登录');
        }
        if ($password === '') {
            return Result::fail(admin_t('top.unlock_empty'));
        }
        $user = $this->sysUserModel->findById($uid);
        if (! $user) {
            try {
                $row = DB::table('sys_user')->where('id', $uid)->first();
                $user = $row ? (array) $row : [];
            } catch (Exception) {
                $user = [];
            }
        }
        if (! $user) {
            return Result::fail('用户不存在');
        }
        if (! $this->passwordMatches($password, $this->storedPassword($uid, $user))) {
            return Result::fail(admin_t('top.unlock_fail'));
        }

        return Result::success([], admin_t('top.unlock_ok'));
    }

    /**
     * 读取库里的密码。模型若藏了 password 字段，退回直接查表。
     */
    private function storedPassword(int $uid, array $user = []): string
    {
        $fromRow = (string) ($user['password'] ?? '');
        if ($fromRow !== '') {
            return $fromRow;
        }
        if ($uid <= 0) {
            return '';
        }
        try {
            return (string) (DB::table('sys_user')->where('id', $uid)->value('password') ?? '');
        } catch (Exception) {
            return '';
        }
    }

    /**
     * 登录密码校验：Laravel Hash、遗留 md5、明文。
     */
    private function passwordMatches(string $plain, string $stored): bool
    {
        if ($plain === '' || $stored === '') {
            return false;
        }
        if (Hash::isHashed($stored)) {
            return Hash::check($plain, $stored);
        }
        if (preg_match('/^[0-9a-f]{32}$/i', $stored) === 1) {
            return hash_equals(strtolower($stored), md5($plain));
        }

        return hash_equals($stored, $plain);
    }

    private function hashLoginPassword(string $plain): string
    {
        return Hash::make($plain);
    }

    private function verifyTotp(string $secret, string $code, int $window = 1, int $period = 30, int $digits = 6): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if ($code === null || $code === '')
        {
            return false;
        }

        if (!preg_match('/^\d+$/', $code))
        {
            return false;
        }

        $key = $this->base32Decode($secret);

        if ($key === '')
        {
            return false;
        }

        $counter = (int) floor(time() / $period);

        for ($i = -$window; $i <= $window; $i++)
        {
            $otp = $this->hotp($key, $counter + $i, $digits);

            if (hash_equals($otp, str_pad($code, $digits, '0', STR_PAD_LEFT)))
            {
                return true;
            }
        }

        return false;
    }

    private function hotp(string $key, int $counter, int $digits): string
    {
        $binCounter = pack('N*', 0) . pack('N*', $counter);
        $hash       = hash_hmac('sha1', $binCounter, $key, true);

        $offset     = ord(substr($hash, -1)) & 0x0F;
        $part       = substr($hash, $offset, 4);
        $value      = unpack('N', $part)[1] & 0x7FFFFFFF;

        $mod        = 10 ** $digits;
        $otp        = (string) ($value % $mod);

        return str_pad($otp, $digits, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret   = strtoupper($secret);
        $secret   = preg_replace('/[^A-Z2-7=]/', '', $secret) ?? '';
        $secret   = rtrim($secret, '=');

        if ($secret === '')
        {
            return '';
        }

        $buffer   = 0;
        $bitsLeft = 0;
        $result   = '';

        $len      = strlen($secret);

        for ($i = 0; $i < $len; $i++)
        {
            $val = strpos($alphabet, $secret[$i]);

            if ($val === false)
            {
                return '';
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8)
            {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $result;
    }

}
