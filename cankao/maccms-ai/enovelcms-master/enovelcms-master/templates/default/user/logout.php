<?php 
defined('ROOT_PATH') or die('URLError'); 
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['user_id']);
redirect(BASE_URL . '/');