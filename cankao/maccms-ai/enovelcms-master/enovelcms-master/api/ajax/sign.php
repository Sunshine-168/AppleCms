<?php
require_once __DIR__ . '/../../includes/config.php';
if (!isLoggedIn()) {
    echo json_encode(['code' => 0, 'msg' => $lang->get('please_login')]);
    exit;
}
$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

$exists = $db->fetch($db->query(
    "SELECT id FROM gold_logs WHERE user_id = ? AND type = 'sign' AND DATE(created_at) = ?",
    [$userId, $today]
));
if ($exists) {
    echo json_encode(['code' => 0, 'msg' => $lang->get('already_signed')]);
    exit;
}

$result = doSign($userId);
if ($result['success']) {
    echo json_encode(['code' => 1, 'msg' => sprintf($lang->get('sign_success'), $result['reward']) . '，' . sprintf($lang->get('continuous_sign'), $result['continuous_days'])]);
} else {
    echo json_encode(['code' => 0, 'msg' => $result['message']]);
}