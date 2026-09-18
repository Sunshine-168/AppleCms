<?php
/**
 * 支付异步通知处理（易支付）
 */
require_once __DIR__ . '/../includes/config.php';

$data = [];
foreach ($_GET as $key => $value) {
    $data[$key] = input($key, '', 'GET', false);
}

$apiUrl = getSetting('yipay_api_url', $db);
$pid = getSetting('yipay_pid', $db);
$key = getSetting('yipay_key', $db);

if (empty($apiUrl) || empty($pid) || empty($key)) {
    echo 'fail';
    exit;
}

require_once ROOT_PATH . 'includes/Payment/YiPay.php';
$pay = new YiPay($apiUrl, $pid, $key);

if ($pay->verifyNotify($data) && isset($data['trade_status']) && $data['trade_status'] == 'TRADE_SUCCESS') {
    $out_trade_no = $data['out_trade_no'] ?? '';
    if (empty($out_trade_no)) {
        echo 'fail';
        exit;
    }
    
    $order = $db->fetch($db->query("SELECT * FROM orders WHERE out_trade_no = ?", [$out_trade_no]));
    if (!$order) {
        echo 'fail';
        exit;
    }
    
    if ($order['status'] == 0) {
        $db->query("UPDATE orders SET status = 1, pay_time = NOW() WHERE id = ?", [$order['id']]);
        
        if ($order['type'] == 'vip') {
            $months = (int)$order['months'];
            if ($months <= 0) $months = 1;
            $userId = $order['user_id'];
            
            $user = $db->fetch($db->query("SELECT vip_expire FROM users WHERE id = ?", [$userId]));
            $currentExpire = $user['vip_expire'];
            
            if (empty($currentExpire) || strtotime($currentExpire) < time()) {
                $newExpire = date('Y-m-d H:i:s', strtotime("+ $months months"));
            } else {
                $newExpire = date('Y-m-d H:i:s', strtotime($currentExpire . " + $months months"));
            }
            
            $db->query("UPDATE users SET vip_level = 1, vip_expire = ? WHERE id = ?", [$newExpire, $userId]);
        }
    }
    echo 'success';
} else {
    echo 'fail';
}