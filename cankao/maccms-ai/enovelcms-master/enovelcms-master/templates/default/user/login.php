<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <h2><?= $lang->get('login') ?></h2>
    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <div>
            <label><?= $lang->get('username_or_email') ?></label>
            <input type="text" name="username" placeholder="<?= $lang->get('please_enter_username_or_email') ?>" required>
        </div>
        <div>
            <label><?= $lang->get('password') ?></label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label><?= $lang->get('captcha') ?></label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="captcha" required style="flex:1;">
                <img src="/api/captcha.php" id="captchaImg" onclick="this.src='/api/captcha.php?'+Math.random()" style="height:40px; cursor:pointer; border-radius:4px;" alt="captcha">
            </div>
        </div>
        <button type="submit"><?= $lang->get('login') ?></button>
    </form>
    <p style="text-align: center;"><a href="/user/forgot"><?= $lang->get('forgot_password') ?></a> | <?= $lang->get('no_account') ?>？<a href="/user/register"><?= $lang->get('register') ?></a></p>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>