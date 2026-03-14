<?php
namespace App\Services\System;

use App\Models\Sys\SysDictModel;
use App\Models\Sys\SysUserLogModel;
use App\Models\Sys\SysUserModel;
use App\Models\Sys\SysUserRoleModel;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Utils\Result;
use Utils\Syslog;
use function App\Services\System\pageSize;
use function App\Services\System\request;
use Zhuzhichao\IpLocationZh\Ip;


/**
 * 系统用户服务
 */
class SysUserService
{
    public SysUserModel $sysUserModel;
    public SysUserLogModel $sysUserLogModel;
    public SysDictModel $sysDictModel;
    public SysUserRoleModel $sysUserRoleModel;

    /**
     * 构造方法
     * 初始化系统用户、系统字典、登录日志与用户角色模型
     */
    public function __construct()
    {
        $this->sysUserModel          = new SysUserModel();
        $this->sysDictModel          = new SysDictModel();
        $this->sysUserLogModel       = new SysUserLogModel();
        $this->sysUserRoleModel      = new SysUserRoleModel();
    }

    /**
     * 用户列表
     * 支持按用户名筛选，返回分页数据
     * @param string $username
     * @return array
     */
    public function getSysUserLists(string $username): array
    {
        $where = [];

        if ($username)
        {
            $where[] = ['username', '=', $username];
        }

        $data = $this->sysUserModel->paginates($where);

        foreach ($data['data'] as &$item)
        {
            $item['login_time'] = date('Y-m-d H:i:s', $item['login_time']);
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
            $rows[] = ['user_id' => (int)$userId, 'role_id' => (int)$rid, 'create_time' => $time, 'update_time' => $time];
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
    public function addSysUser(string $username, string $password): array
    {
        $insert = [
            'username'      => $username,
            'password'      => $password,
            'role'          => 1,  // 默认为普通管理员（需要角色权限），而非超级管理员
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
    public function updateSysUser(int $id, string $username, string $password): array
    {
        $update = [
            'username'      => $username,
            'password'      => $password,
            'update_time'   => time(),
        ];

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
     * 校验账号密码（生产环境可开启谷歌验证码），写入登录日志并更新 token
     * @param string $username
     * @param string $password
     * @param string $vscode
     * @return array
     */
    public function login(string $username, string $password, string $vscode): array
    {
        $time    = time();

        $where   = [];
        $where[] = ['username', '=', $username];
        $where[] = ['password', '=', $password];
        $user    = $this->sysUserModel->findByCondition($where);

        if (!$user)
        {
            return Result::fail('账号或者密码错误');
        }

        $appEnv = (string) env('APP_ENV', 'production');
        $isPro = in_array($appEnv, ['pro', 'production'], true);
        if ($isPro && env('GOOGLE_AUTH', false))
        {
            if ($vscode === '')
            {
                return Result::fail('请输入谷歌验证码');
            }

            $google = $this->sysDictModel->getValue('google');
            if (empty($google))
            {
                return Result::fail('谷歌验证未配置');
            }

            if (!$this->verifyTotp((string) $google, $vscode))
            {
                return Result::fail('谷歌验证码错误');
            }
        }

        $ip         = Request::ip();
        $ipAddress  = join(',', array_filter(Ip::find($ip)));

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
                'ip_address'  => $ipAddress
            ];

            $res = $this->sysUserLogModel->inserts($insert);

            if (!$res)
            {
                DB::rollBack();
                return Result::fail('登入失败');
            }

            DB::commit();

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
     * 获取系统用户登录日志列表
     * 支持按用户名、登录IP、时间范围筛选，返回分页数据
     * @param array $params
     * @return array
     */
    public function getSysUserLoginLists(array $params): array
    {
        $where = [];

        // 按用户名筛选
        if (!empty($params['username']))
        {
            $where[] = ['username', '=', $params['username']];
        }

        // 按登录IP筛选
        if (!empty($params['login_ip']))
        {
            $where[] = ['login_ip', 'like', '%' . $params['login_ip'] . '%'];
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

        $data = $this->sysUserLogModel->paginates($where,'*', 10);

        return Result::success($data);
    }

    private function verifyTotp(string $secret, string $code, int $window = 1, int $period = 30, int $digits = 6): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if ($code === null || $code === '') {
            return false;
        }

        if (!preg_match('/^\d+$/', $code)) {
            return false;
        }

        $key = $this->base32Decode($secret);
        if ($key === '') {
            return false;
        }

        $counter = (int) floor(time() / $period);
        for ($i = -$window; $i <= $window; $i++) {
            $otp = $this->hotp($key, $counter + $i, $digits);
            if (hash_equals($otp, str_pad($code, $digits, '0', STR_PAD_LEFT))) {
                return true;
            }
        }

        return false;
    }

    private function hotp(string $key, int $counter, int $digits): string
    {
        $binCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $part = substr($hash, $offset, 4);
        $value = unpack('N', $part)[1] & 0x7FFFFFFF;

        $mod = 10 ** $digits;
        $otp = (string) ($value % $mod);
        return str_pad($otp, $digits, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $secret = preg_replace('/[^A-Z2-7=]/', '', $secret) ?? '';
        $secret = rtrim($secret, '=');

        if ($secret === '') {
            return '';
        }

        $buffer = 0;
        $bitsLeft = 0;
        $result = '';

        $len = strlen($secret);
        for ($i = 0; $i < $len; $i++) {
            $val = strpos($alphabet, $secret[$i]);
            if ($val === false) {
                return '';
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $result;
    }

}
