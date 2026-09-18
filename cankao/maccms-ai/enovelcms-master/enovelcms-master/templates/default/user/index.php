<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="user-center-layout">
    <aside class="user-sidebar">
        <div class="user-sidebar-header">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <h3><?= h($user['username']) ?></h3>
            <p><?= $vipActive ? '<span class="vip-badge">' . $lang->get('vip_member') . '</span>' : $lang->get('normal_user') ?></p>
        </div>
        <nav class="user-nav">
            <a href="/user/" class="user-nav-item active"><i class="fas fa-tachometer-alt"></i> <span><?= $lang->get('user_center') ?></span></a>
            <a href="/user/profile" class="user-nav-item"><i class="fas fa-user-edit"></i> <span><?= $lang->get('profile') ?></span></a>
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
        <h2><i class="fas fa-tachometer-alt"></i> <?= $lang->get('user_center') ?></h2>
        <div class="user-info-card">
            <div class="info-row"><strong><?= $lang->get('username') ?>：</strong> <?= h($user['username']) ?></div>
            <div class="info-row"><strong><?= $lang->get('email') ?>：</strong> <?= h($user['email']) ?></div>
            <div class="info-row"><strong><?= $lang->get('vip_status') ?>：</strong> 
                <?php if ($vipActive): ?>
                    <span class="vip-tag"><?= $lang->get('vip_member') ?></span> 
                    <span class="expire">(<?= $lang->get('expire_date') ?>: <?= h($user['vip_expire']) ?>)</span>
                <?php else: ?>
                    <?= $lang->get('normal_user') ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="user-stats-grid">
            <div class="stat-card"><i class="fas fa-coins"></i><div class="stat-value"><?= number_format($user['gold']) ?></div><div><?= $lang->get('current_gold') ?></div></div>
            <div class="stat-card"><i class="fas fa-calendar-day"></i><div class="stat-value"><?= (int)$user['sign_days'] ?></div><div><?= $lang->get('continuous_sign_days') ?></div></div>
            <div class="stat-card"><i class="fas fa-book"></i><div class="stat-value"><?= $bookshelfCount ?></div><div><?= $lang->get('bookshelf') ?></div></div>
            <div class="stat-card"><i class="fas fa-clock"></i><div class="stat-value"><?= $historyCount ?></div><div><?= $lang->get('history') ?></div></div>
        </div>
        <?php if (!$todaySigned): ?>
        <div class="sign-cta"><a href="/user/sign" class="btn-primary btn-large"><?= $lang->get('sign_button') ?></a></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>