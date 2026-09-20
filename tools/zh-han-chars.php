<?php

$root = dirname(__DIR__);
$zh = include $root.'/resources/lang/zh_cn/admin.php';

function collect_text(array $a): string
{
    $s = '';
    foreach ($a as $v) {
        $s .= is_array($v) ? collect_text($v) : (string) $v;
    }

    return $s;
}

$text = collect_text($zh);
$chars = [];
$len = mb_strlen($text, 'UTF-8');
for ($i = 0; $i < $len; $i++) {
    $c = mb_substr($text, $i, 1, 'UTF-8');
    $o = unpack('N', mb_convert_encoding($c, 'UCS-4BE', 'UTF-8'));
    $cp = $o[1] ?? 0;
    if ($cp >= 0x4E00 && $cp <= 0x9FFF) {
        $chars[$c] = true;
    }
}
$han = array_keys($chars);
sort($han);
file_put_contents($root.'/tools/zh-cn-han.txt', implode('', $han));
echo 'unique han='.count($han).PHP_EOL;

$ids = [];
if (function_exists('transliterator_list_ids')) {
    foreach (transliterator_list_ids() as $id) {
        if (stripos($id, 'han') !== false || stripos($id, 'hant') !== false || stripos($id, 'trad') !== false) {
            $ids[] = $id;
        }
    }
}
echo 'ids='.implode('|', $ids).PHP_EOL;
