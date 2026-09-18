<?php defined('ROOT_PATH') or die('URLError')?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $lang->get('404_title') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="error-box">
    <h1>404</h1>
    <h2><?= $lang->get('404_title') ?></h2>
    <p><?= $lang->get('404_message') ?></p>
    <a href="/" class="btn"><?= $lang->get('back_to_home') ?></a>
</div>
</body>
</html>