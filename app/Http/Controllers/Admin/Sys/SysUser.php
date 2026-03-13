<?php
namespace app\admin\controller;

use app\common\utils\Ajax;
use app\common\utils\ServiceFactory;
use think\facade\Request;
use think\response\Json;

/**
 * 系统用户
 */
class SysUser
{
    protected mixed $systemUserService;

    public function __construct()
    {
        $this->systemUserService = ServiceFactory::make();
    }

    /**
     * 获取系统用户列表
     * @return Json
     */
    public function getSystemUserLists(): Json
    {
        $username   = Request::param('username', '');

        $data       = $this->systemUserService->getSysUserLists($username);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取用户信息
     * @return Json
     */
    public function getSystemUserInfo(): Json
    {
        $id     = Request::param('id', 0);

        $data   = $this->systemUserService->getSysUserInfo($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取用户已绑定角色ID
     * 传入参数：user_id（用户ID）
     * 返回数据：该用户绑定的 role_id 数组
     */
    public function getUserRoleIds(): Json
    {
    
        $userId = (int)Request::param('user_id', 0);
        $data   = $this->systemUserService->getUserRoleIds($userId);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 设置用户角色
     * 传入参数：user_id（用户ID）、role_ids（角色ID数组）
     * 处理逻辑：先清空用户已有角色，再批量重建绑定关系
     */
    public function setUserRoles(): Json
    {
      
        $userId   = (int)Request::param('user_id', 0);
        $roleIds  = Request::param('role_ids', []);
        if (!is_array($roleIds)) $roleIds = [];
        $data     = $this->systemUserService->setUserRoles($userId, $roleIds);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 添加用户
     * @return Json
     */
    public function addSystemUser(): Json
    {
        $param = Request::only([
            'username' => '',
            'password' => '123456',
        ]);

        $data = $this->systemUserService->addSysUser($param['username'], $param['password']);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新用户
     * @return Json
     */
    public function updateSystemUser(): Json
    {
        $param = Request::only([
            'id'       => 0,
            'username' => '',
            'password' => '123456',
        ]);

        $data = $this->systemUserService->updateSysUser($param['id'], $param['username'], $param['password']);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除用户
     * @return Json
     */
    public function deleteSystemUser(): Json
    {
        $id     = Request::param('id', 0);

        $data   = $this->systemUserService->deleteSysUser($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 登入
     * @return Json
     */
    public function login(): Json
    {
        $params = Request::only([
            'username' => '',
            'password' => '',
            'vscode'   => ''
        ]);

        $data           = $this->systemUserService->login($params['username'], $params['password'], $params['vscode']);

        return Ajax::message($data['code'],$data['msg'],$data['data']);

    }

    /**
     * 系统用户登录日志列表
     * @return Json
     */
    public function getSystemUserLoginLists(): Json
    {
        $params = Request::only([
            'username'   => '',
            'login_ip'   => '',
            'start_time' => '',
            'end_time'   => '',
            'page'       => 1,
            'limit'      => 10,
        ]);

        $data = $this->systemUserService->getSysUserLoginLists($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
