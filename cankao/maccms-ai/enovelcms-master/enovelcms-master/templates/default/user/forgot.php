<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <h2><?= $lang->get('forgot_password_title') ?></h2>
    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p class="success"><?= h($message) ?></p>
    <?php endif; ?>
    
    <?php if ($step == 'request' || $step == 'send_code'): ?>
        <form method="post" action="" id="forgotForm">
            <div>
                <label><?= $lang->get('registered_email') ?></label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label><?= $lang->get('captcha') ?></label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="captcha" id="captchaInput" required style="flex:1;">
                    <img src="/api/captcha.php" id="captchaImg" onclick="this.src='/api/captcha.php?'+Math.random()" style="height:40px; cursor:pointer; border-radius:4px;" alt="captcha">
                </div>
            </div>
            <div>
                <label><?= $lang->get('verify_code') ?></label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="code" required style="flex:1;">
                    <button type="button" id="sendCodeBtn" class="btn-small"><?= $lang->get('send_code') ?></button>
                </div>
            </div>
            <button type="submit"><?= $lang->get('reset_password_button') ?></button>
        </form>
        <p style="text-align: center;"><a href="/user/login"><?= $lang->get('back_to_login') ?></a></p>
    <?php elseif ($step == 'done'): ?>
        <p style="text-align: center;"><a href="/user/login" class="btn"><?= $lang->get('login') ?></a></p>
    <?php endif; ?>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>