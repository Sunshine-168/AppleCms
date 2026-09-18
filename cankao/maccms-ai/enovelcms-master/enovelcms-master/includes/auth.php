<?php
/**
 * 用户认证检查
 * 包含此文件会自动验证用户是否登录，未登录则跳转到登录页
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/config.php';
}

if (!isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/user/login');
}