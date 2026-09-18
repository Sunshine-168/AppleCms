<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <h2><?= $lang->get('register') ?></h2>
    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="post" id="registerForm">
        <?php
        // 获取当前模式
        $mode = getSetting('register_mode', $db) ?: 'normal';
        ?>
        <div>
            <label><?= $lang->get('username') ?></label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label><?= $lang->get('password') ?></label>
            <input type="password" name="password" required>
        </div>

        <?php if ($mode === 'email'): ?>
        <div>
            <label><?= $lang->get('email') ?> <span style="color:red;">*</span></label>
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
        <?php else: ?>
            <input type="hidden" name="email" value="">
            <input type="hidden" name="code" value="">
            <div>
                <label><?= $lang->get('captcha') ?></label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="captcha" id="captchaInput" required style="flex:1;">
                    <img src="/api/captcha.php" id="captchaImg" onclick="this.src='/api/captcha.php?'+Math.random()" style="height:40px; cursor:pointer; border-radius:4px;" alt="captcha">
                </div>
            </div>
        <?php endif; ?>

        <button type="submit"><?= $lang->get('register') ?></button>
    </form>
    <p style="text-align: center;"><?= $lang->get('have_account') ?>？<a href="/user/login"><?= $lang->get('login') ?></a></p>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>