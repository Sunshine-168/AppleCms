<?php
require_once __DIR__ . '/../../includes/config.php';
if (!isAdmin()) {
    echo json_encode(['code' => 0, 'message' => $lang->get('no_permission')]);
    exit;
}
$id = (int)input('id', 0, 'GET');
if (!$id) {
    echo json_encode(['code' => 0, 'message' => $lang->get('invalid_params')]);
    exit;
}
$chapter = $db->fetch($db->query("SELECT file_path FROM chapters WHERE id=?", [$id]));
if (!$chapter) {
    echo json_encode(['code' => 0, 'message' => $lang->get('chapter_not_exist')]);
    exit;
}
$fullPath = ROOT_PATH . $chapter['file_path'];
if (!file_exists($fullPath)) {
    echo json_encode(['code' => 0, 'message' => $lang->get('file_not_exist')]);
    exit;
}
$content = file_get_contents($fullPath);
if ($content === false) {
    echo json_encode(['code' => 0, 'message' => $lang->get('content_read_failed')]);
    exit;
}
echo json_encode(['code' => 1, 'content' => $content]);