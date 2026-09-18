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
$exist = $db->fetch($db->query("SELECT id FROM bookshelf WHERE user_id=? AND novel_id=?", [$userId, $novelId]));
if ($exist) {
    echo json_encode(['code' => 0, 'message' => $lang->get('already_in_bookshelf')]);
} else {
    $db->query("INSERT INTO bookshelf (user_id, novel_id) VALUES (?,?)", [$userId, $novelId]);
    $db->query("UPDATE novels SET favorites = favorites + 1 WHERE id = ?", [$novelId]);
    echo json_encode(['code' => 1, 'message' => $lang->get('add_to_bookshelf') . $lang->get('operation_success')]);
}