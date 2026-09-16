<?php
namespace App\Services\Admin\System;

use App\Models\System\SysDictModel;
use App\Models\System\SysOperateLogModel;
use App\Models\System\SysRoleModel;
use App\Models\System\SysSystemLogModel;
use App\Models\System\SysUserLogModel;
use App\Models\System\SysUserModel;
use App\Models\System\SysUserRoleModel;
use App\Support\Utils\Result;
use App\Support\Utils\Syslog;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
     * 用户列表
     * 支持按用户名筛选，返回分页数据
     * @param string $username
     * @return array
     */
    public function getSysUserLists(string $username, int $limit): array
    {
        $where = [];

        if ($username)
        {
            $where[] = ['username', '=', $username];
        }

        $data = $this->sysUserModel->paginates($where, '*', $limit, ['id' => 'desc']);

        foreach ($data['data'] as &$item)
        {
            $item['login_time'] = date('Y-m-d H:i:s', $item['login_time']);
            $item['role_name']  = $this->sysRoleModel->findById($item['role_id'])['name'] ?? '';
        }

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
        if ($password === '')
        {
            $password = '123456';
        }

        $insert = [
            'username'      => $username,
            'password'      => $password,
            'email'         => $email,
            'remark'        => $remark,
            'role'          => $role,
            'role_id'       => $roleId,
            'create_time'   => time(),
            'update_time'   => time(),
        ];

        $res = $this->sysUserModel->inserts($insert);

        if (!$res)
        {
            return Result::fail();
        }

        return Result::success();
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
        $update = [
            'username'      => $username,
            'email'         => $email,
            'remark'        => $remark,
            'update_time'   => time(),
        ];

        if ($password !== '')
        {
            $update['password'] = $password;
        }

        if ($role !== null)
        {
            $update['role'] = $role;
        }

        if ($roleId !== null)
        {
            $update['role_id'] = $roleId;
        }

        $res = $this->sysUserModel->updateById($id, $update);

        if (!$res)
        {
            return Result::fail();
        }

        return Result::success();
    }

    /**
     * 删除用户
     * @param int $id
     * @return array
     */
    public function deleteSysUser(int $id): array
    {
        $res = $this->sysUserModel->deleteById($id);

        if (!$res)
        {
            return Result::fail();
        }

        return Result::success();
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
        $lockKey = 'admin.login.lock.'.md5((string) $ip);
        if (Cache::has($lockKey)) {
            return Result::fail('登录失败次数过多，请 15 分钟后再试');
        }

        $captcha = trim($vscode);

        if ($captcha === '')
        {
            return Result::fail('请输入验证码');
        }

        $expectedCaptcha = (string) session('captcha', '');

        if ($expectedCaptcha === '')
        {
            return Result::fail('验证码已失效，请刷新');
        }

        if (strcasecmp($expectedCaptcha, $captcha) !== 0)
        {
            session()->forget('captcha');

            return Result::fail('验证码错误，请刷新');
        }

        $where   = [];
        $where[] = ['username', '=', $username];
        $where[] = ['password', '=', $password];
        $user    = $this->sysUserModel->findByCondition($where);

        if (!$user)
        {
            $failKey = 'admin.login.fail.'.md5((string) $ip);
            $fails = (int) Cache::get($failKey, 0) + 1;
            Cache::put($failKey, $fails, 900);
            if ($fails >= 5) {
                Cache::put($lockKey, 1, 900);
            }

            return Result::fail('账号或者密码错误');
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
     * 支持按用户名、登录IP、时间范围筛选，返回分页数据
     * @param array $params
     * @return array
     */
    public function getSysUserLoginLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 10);
        if ($limit < 1)
        {
            $limit = 10;
        }

        // 按用户名筛选
        if (!empty($params['username']))
        {
            $where[] = ['username', '=', $params['username']];
        }

        // 按登录IP筛选
        if (!empty($params['login_ip']))
        {
            $where[] = ['login_ip', '=', $params['login_ip']];
        }

        // 按时间范围筛选
        if (!empty($params['start_time']))
        {
            $where[] = ['create_time', '>=', strtotime($params['start_time'])];
        }

        if (!empty($params['end_time']))
        {
            $where[] = ['create_time', '<=', strtotime($params['end_time']) + 86400];
        }

        $data = $this->sysUserLogModel->paginates($where, '*', $limit);

        foreach ($data['data'] as &$item)
        {
            if (empty($item['create_time']) && !empty($item['create_at']))
            {
                $item['create_time'] = $item['create_at'];
                continue;
            }

            if (!empty($item['create_time']) && is_numeric($item['create_time']))
            {
                $timestamp = (int) $item['create_time'];
                if ($timestamp > 0)
                {
                    $item['create_time'] = date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        return Result::success($data);
    }

    /**
     * 获取系统用户操作日志列表
     * 支持按用户名、登录IP、方法、URL、路由、状态、时间范围筛选，返回分页数据
     * @param array $params
     * @return array
     */
    public function getSysOperateLogLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 10);
        if ($limit < 1)
        {
            $limit = 10;
        }

        if (!empty($params['username']))
        {
            $where[] = ['username', '=', $params['username']];
        }

        if (!empty($params['login_ip']))
        {
            $where[] = ['login_ip', '=', $params['login_ip']];
        }

        if (!empty($params['method']))
        {
            $where[] = ['method', '=', strtoupper((string) $params['method'])];
        }

        if (!empty($params['url']))
        {
            $where[] = ['url', '=', $params['url']];
        }

        if (!empty($params['route']))
        {
            $where[] = ['route', '=', $params['route']];
        }

        if ($params['status'] !== '' && $params['status'] !== null)
        {
            $where[] = ['status', '=', (int) $params['status']];
        }

        if (!empty($params['start_time']))
        {
            $where[] = ['create_time', '>=', strtotime((string) $params['start_time'])];
        }

        if (!empty($params['end_time']))
        {
            $where[] = ['create_time', '<=', strtotime((string) $params['end_time']) + 86400];
        }

        $data = $this->sysOperateLogModel->paginates($where, '*', $limit, ['id' => 'desc']);

        foreach ($data['data'] as &$item)
        {
            if (empty($item['create_time']) && !empty($item['create_at']))
            {
                $item['create_time'] = $item['create_at'];
                continue;
            }

            if (!empty($item['create_time']) && is_numeric($item['create_time']))
            {
                $timestamp = (int) $item['create_time'];
                if ($timestamp > 0)
                {
                    $item['create_time'] = date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        return Result::success($data);
    }
    /**
     * 获取系统日志列表
     * 支持按日志级别、渠道、模块、用户名、用户ID、请求ID、方法、URL、IP、时间范围筛选，返回分页数据
     * @param array $params
     * @return array
     */
    public function getSysSystemLogLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 10);
        if ($limit < 1)
        {
            $limit = 10;
        }

        if (!empty($params['level']))
        {
            $where[] = ['level', '=', (string) $params['level']];
        }

        if (!empty($params['channel']))
        {
            $where[] = ['channel', '=', (string) $params['channel']];
        }

        if (!empty($params['module']))
        {
            $where[] = ['module', '=', (string) $params['module']];
        }

        if (!empty($params['username']))
        {
            $where[] = ['username', '=', (string) $params['username']];
        }

        if ($params['uid'] !== '' && $params['uid'] !== null)
        {
            $uid = (int) $params['uid'];
            if ($uid > 0)
            {
                $where[] = ['uid', '=', $uid];
            }
        }

        if (!empty($params['request_id']))
        {
            $where[] = ['request_id', '=', (string) $params['request_id']];
        }

        if (!empty($params['method']))
        {
            $where[] = ['method', '=', strtoupper((string) $params['method'])];
        }

        if (!empty($params['url']))
        {
            $where[] = ['url', '=', '%' . (string) $params['url'] . '%'];
        }

        if (!empty($params['ip']))
        {
            $where[] = ['ip', '=', (string) $params['ip']];
        }

        if (!empty($params['start_time']))
        {
            $where[] = ['create_time', '>=', strtotime((string) $params['start_time'])];
        }

        if (!empty($params['end_time']))
        {
            $where[] = ['create_time', '<=', strtotime((string) $params['end_time']) + 86400];
        }

        $data = $this->sysSystemLogModel->paginates($where, '*', $limit, ['id' => 'desc']);

        foreach ($data['data'] as &$item)
        {
            if (empty($item['create_time']) && !empty($item['create_at']))
            {
                $item['create_time'] = $item['create_at'];
                continue;
            }

            if (!empty($item['create_time']) && is_numeric($item['create_time']))
            {
                $timestamp = (int) $item['create_time'];
                if ($timestamp > 0)
                {
                    $item['create_time'] = date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

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

        $dbPassword = (string) ($user['password'] ?? '');
        if ($dbPassword !== $currentPassword)
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

        return Result::success([], '修改成功');
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
