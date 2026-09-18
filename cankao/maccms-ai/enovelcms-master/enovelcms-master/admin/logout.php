<?php
/**
 * 管理员退出
 */
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['admin']);
redirect(BASE_URL . '/admin/login.php');