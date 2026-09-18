<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="user-center-layout">
    <aside class="user-sidebar">
        <div class="user-sidebar-header">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <h3><?= h($user['username']) ?></h3>
            <p><?= $vipActive ? $lang->get('vip_member') : $lang->get('normal_user') ?></p>
        </div>
        <nav class="user-nav">
            <a href="/user/" class="user-nav-item"><i class="fas fa-tachometer-alt"></i> <span><?= $lang->get('user_center') ?></span></a>
            <a href="/user/profile" class="user-nav-item active"><i class="fas fa-user-edit"></i> <span><?= $lang->get('profile') ?></span></a>
            <a href="/user/gold" class="user-nav-item"><i class="fas fa-coins"></i> <span><?= $lang->get('gold_log') ?></span></a>
            <a href="/user/vip" class="user-nav-item"><i class="fas fa-crown"></i> <span><?= $lang->get('vip_center') ?></span></a>
            <a href="/user/sign" class="user-nav-item"><i class="fas fa-calendar-check"></i> <span><?= $lang->get('daily_sign') ?></span></a>
            <a href="/user/bookshelf" class="user-nav-item"><i class="fas fa-bookmark"></i> <span><?= $lang->get('bookshelf') ?></span></a>
            <a href="/user/history" class="user-nav-item"><i class="fas fa-history"></i> <span><?= $lang->get('history') ?></span></a>
            <div class="user-nav-divider"></div>
            <a href="/user/logout" class="user-nav-item"><i class="fas fa-sign-out-alt"></i> <span><?= $lang->get('logout') ?></span></a>
        </nav>
    </aside>
    <div class="user-content">
        <h2><i class="fas fa-user-edit"></i> <?= $lang->get('profile') ?></h2>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label><?= $lang->get('username') ?></label><input type="text" value="<?= h($user['username']) ?>" disabled></div>
            <div class="form-group"><label><?= $lang->get('email') ?></label><input type="email" name="email" value="<?= h($user['email']) ?>"></div>
            <hr style="border: none; border-top: 2px dashed #b8e1f5; opacity: 0.7; margin: 20px 0;">
            <h3><i class="fas fa-key"></i> <?= $lang->get('change_password') ?></h3>
            <hr style="border: none; border-top: 2px solid #D8D8D8; opacity: 0.7; margin: 20px 0;">
            <div class="form-group"><label><?= $lang->get('old_password') ?></label><input type="password" name="old_password"></div>
            <div class="form-group"><label><?= $lang->get('new_password') ?></label><input type="password" name="new_password"></div>
            <div class="form-group"><label><?= $lang->get('confirm_password') ?></label><input type="password" name="confirm_password"></div>
            <button type="submit" class="btn-primary"><?= $lang->get('save') ?></button>
        </form>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>