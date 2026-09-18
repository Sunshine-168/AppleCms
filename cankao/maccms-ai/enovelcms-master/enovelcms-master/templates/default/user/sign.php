<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="user-center-layout">
    <aside class="user-sidebar">
        <div class="user-sidebar-header"><div class="user-avatar"><i class="fas fa-user"></i></div><h3><?= h($user['username']) ?></h3><p><?= $vipActive ? '<span class="vip-badge">' . $lang->get('vip_member') . '</span>' : $lang->get('normal_user') ?></p></div>
        <nav class="user-nav">
            <a href="/user/" class="user-nav-item"><i class="fas fa-tachometer-alt"></i> <span><?= $lang->get('user_center') ?></span></a>
            <a href="/user/profile" class="user-nav-item"><i class="fas fa-user-edit"></i> <span><?= $lang->get('profile') ?></span></a>
            <a href="/user/gold" class="user-nav-item"><i class="fas fa-coins"></i> <span><?= $lang->get('gold_log') ?></span></a>
            <a href="/user/vip" class="user-nav-item"><i class="fas fa-crown"></i> <span><?= $lang->get('vip_center') ?></span></a>
            <a href="/user/sign" class="user-nav-item active"><i class="fas fa-calendar-check"></i> <span><?= $lang->get('daily_sign') ?></span></a>
            <a href="/user/bookshelf" class="user-nav-item"><i class="fas fa-bookmark"></i> <span><?= $lang->get('bookshelf') ?></span></a>
            <a href="/user/history" class="user-nav-item"><i class="fas fa-history"></i> <span><?= $lang->get('history') ?></span></a>
            <div class="user-nav-divider"></div>
            <a href="/user/logout" class="user-nav-item"><i class="fas fa-sign-out-alt"></i> <span><?= $lang->get('logout') ?></span></a>
        </nav>
    </aside>
    <div class="user-content">
        <h2><i class="fas fa-calendar-check"></i> <?= $lang->get('sign_today') ?></h2>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <div class="sign-card">
            <div class="sign-icon"><i class="fas fa-star"></i></div>
            <h3><?= $lang->get('daily_sign') ?></h3>
            <div class="sign-reward-preview">
                <div class="reward-label"><?= $lang->get('today_sign_reward_preview') ?></div>
                <div class="reward-number">+<?= $rewardPreview['total'] ?></div>
                <div class="reward-unit"><?= $lang->get('gold') ?></div>
                <div class="reward-detail"><?= $lang->get('base_gold') ?>: <?= $rewardPreview['base_gold'] ?> <?php if ($rewardPreview['bonus'] > 0): ?>| <?= $lang->get('bonus') ?>: +<?= $rewardPreview['bonus'] ?><?php endif; ?></div>
            </div>
            <?php if ($todaySigned): ?>
                <div class="signed-badge"><i class="fas fa-check-circle"></i> <?= $lang->get('already_signed_today') ?></div>
            <?php else: ?>
                <form method="post"><button type="submit" class="sign-btn"><?= $lang->get('sign_button') ?></button></form>
            <?php endif; ?>
        </div>
        <div class="user-stats-mini">
            <div><i class="fas fa-coins"></i> <?= $lang->get('current_gold') ?>: <strong><?= number_format($user['gold']) ?></strong></div>
            <div><i class="fas fa-calendar-alt"></i> <?= $lang->get('continuous_sign_days') ?>: <strong><?= (int)$user['sign_days'] ?></strong> <?= $lang->get('days') ?></div>
        </div>
        <p class="rule-tip"><?= str_replace(['%min%', '%max%'], [getSetting('sign_min_continue_bonus', $db), getSetting('sign_max_continue_bonus', $db)], $lang->get('sign_rule')) ?></p>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>