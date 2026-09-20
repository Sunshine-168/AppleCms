<?php
$t = transliterator_create('Hans-Hant');
echo ($t ? $t->transliterate('后台分类采集默认信息数据视频文件软件') : 'fail').PHP_EOL;
$t2 = transliterator_create('Simplified-Traditional');
echo ($t2 ? $t2->transliterate('后台分类采集默认信息数据视频文件软件') : 'fail2').PHP_EOL;
