<?php defined('ROOT_PATH') or die('URLError')?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $lang->get('system_error_title') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="error-box">
    <h1><i class="fas fa-exclamation-triangle"></i> <?= $lang->get('system_error_title') ?></h1>
    <p><?= $lang->get('system_error_message') ?></p>
    <?php if (defined('DEBUG_MODE') && DEBUG_MODE === true && isset($error_detail_for_view)): ?>
        <div class="detail">
            <strong><?= $lang->get('error_detail_label') ?>:</strong><br>
            <?= nl2br(htmlspecialchars($error_detail_for_view)) ?>
        </div>
    <?php endif; ?>
    <a href="/" class="btn"><?= $lang->get('back_to_home') ?></a>
</div>
</body>
</html>