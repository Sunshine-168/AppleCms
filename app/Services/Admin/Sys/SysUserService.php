<?php
namespace app\admin\service\v1;

use app\common\model\SysDictModel;
use app\common\model\SysUserLogModel;
use app\common\model\SysUserRoleModel;
use app\common\model\SysUserModel;
use app\common\utils\GoogleAuthenticator;
use app\common\utils\Result;
use app\common\utils\Syslog;
use Exception;
use think\facade\Db;
use think\facade\Request;
use Zhuzhichao\IpLocationZh\Ip;

/**
 * 系统用户服务
 * 提供后台系统用户的增删改查、登录、日志查询与角色绑定管理
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
        $rows = [];
        $time = time();
        foreach ($roleIds as $rid) {
            $rows[] = ['user_id' => (int)$userId, 'role_id' => (int)$rid, 'create_time' => $time, 'update_time' => $time];
        }
        if (!empty($rows)) {
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
        $time    = Request::time();

        $where   = [];
        $where[] = ['username', '=', $username];
        $where[] = ['password', '=', $password];
        $user    = $this->sysUserModel->findByCondition($where);

        if (!$user)
        {
            return Result::fail('账号或者密码错误');
        }

        if (env('APP_ENV') == 'pro' && env('GOOGLE_AUTH', false))
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

            $GoogleAuthenticator = new GoogleAuthenticator();
            $googleCheck         = $GoogleAuthenticator->verifyCode($google, $vscode);

            if (!$googleCheck)
            {
                return Result::fail('谷歌验证码错误');
            }
        }

        $ip         = Request::ip();
        $ipAddress  = join(',', array_filter(Ip::find($ip)));

        Db::startTrans();

        try {

            $update = [
                'login_time'  => $time,
                'login_ip'    => Request::ip(),
                'ip_address'  => $ipAddress,
                'login_agent' => $_SERVER['HTTP_USER_AGENT'],
                'token'       => md5($time . $user['id'])
            ];

            $res = $this->sysUserModel->updateById($user['id'], $update);

            if (!$res)
            {
                Db::rollback();
                return Result::fail('登入失败');
            }

            $insert = [
                'uid'         => $user['id'],
                'username'    => $user['username'],
                'login_ip'    => Request::ip(),
                'login_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip_address'  => $ipAddress
            ];

            $res = $this->sysUserLogModel->inserts($insert);

            if (!$res)
            {
                Db::rollback();
                return Result::fail('登入失败');
            }

            Db::commit();

            return Result::success($update, '登录成功');
        }
        catch (Exception $e)
        {
            Db::rollback();
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

        $data = $this->sysUserLogModel->paginates($where,'*',pageSize());

        return Result::success($data);
    }

}
