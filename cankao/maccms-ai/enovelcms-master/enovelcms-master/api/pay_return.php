<?php
require_once __DIR__ . '/../includes/config.php';

$trade_status = input('trade_status', '', 'GET');
$out_trade_no = input('out_trade_no', '', 'GET');

$message = '';
if ($trade_status == 'TRADE_SUCCESS') {
    $message = $lang->get('pay_success');
} else {
    $message = $lang->get('pay_failed');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= h($message) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container" style="text-align:center; padding:50px;">
    <h2><?= h($message) ?></h2>
    <p><a href="/user/" class="btn"><?= $lang->get('user_center') ?></a> 
    <a href="/user/vip" class="btn"><?= $lang->get('vip_recharge') ?></a></p>
    <?php redirect(BASE_URL . '/user/');?>
</div>
</body>
</html>