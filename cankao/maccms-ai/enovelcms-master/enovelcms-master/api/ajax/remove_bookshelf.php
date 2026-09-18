<?php
require_once __DIR__ . '/../../includes/config.php';
if (!isLoggedIn()) {
    echo json_encode(['code' => 0, 'message' => $lang->get('please_login')]);
    exit;
}
$novelId = (int)input('novel_id', 0, 'POST');
if (!$novelId) {
    echo json_encode(['code' => 0, 'message' => $lang->get('invalid_params')]);
    exit;
}
$userId = $_SESSION['user_id'];
$db->query("DELETE FROM bookshelf WHERE user_id = ? AND novel_id = ?", [$userId, $novelId]);
$db->query("UPDATE novels SET favorites = favorites - 1 WHERE id = ?", [$novelId]);
echo json_encode(['code' => 1, 'message' => $lang->get('remove_from_bookshelf') . $lang->get('operation_success')]);