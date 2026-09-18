<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="user-center-layout">
    <aside class="user-sidebar">
        <div class="user-sidebar-header"><div class="user-avatar"><i class="fas fa-user"></i></div><h3><?= h($user['username']) ?></h3><p><?= $vipActive ? $lang->get('vip_member') : $lang->get('normal_user') ?></p></div>
        <nav class="user-nav">
            <a href="/user/" class="user-nav-item"><i class="fas fa-tachometer-alt"></i> <span><?= $lang->get('user_center') ?></span></a>
            <a href="/user/profile" class="user-nav-item"><i class="fas fa-user-edit"></i> <span><?= $lang->get('profile') ?></span></a>
            <a href="/user/gold" class="user-nav-item"><i class="fas fa-coins"></i> <span><?= $lang->get('gold_log') ?></span></a>
            <a href="/user/vip" class="user-nav-item"><i class="fas fa-crown"></i> <span><?= $lang->get('vip_center') ?></span></a>
            <a href="/user/sign" class="user-nav-item"><i class="fas fa-calendar-check"></i> <span><?= $lang->get('daily_sign') ?></span></a>
            <a href="/user/bookshelf" class="user-nav-item"><i class="fas fa-bookmark"></i> <span><?= $lang->get('bookshelf') ?></span></a>
            <a href="/user/history" class="user-nav-item active"><i class="fas fa-history"></i> <span><?= $lang->get('history') ?></span></a>
            <div class="user-nav-divider"></div>
            <a href="/user/logout" class="user-nav-item"><i class="fas fa-sign-out-alt"></i> <span><?= $lang->get('logout') ?></span></a>
        </nav>
    </aside>
    <div class="user-content">
        <h2><i class="fas fa-history"></i> <?= $lang->get('history') ?></h2>
        <?php if (empty($history)): ?>
            <div class="empty-state"><i class="fas fa-clock"></i><p><?= $lang->get('empty_history') ?></p></div>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th><?= $lang->get('novel') ?></th><th><?= $lang->get('chapter') ?></th><th><?= $lang->get('read_time') ?></th></tr></thead>
                <tbody>
                <?php foreach ($history as $h): ?>
                <tr><td><a href="/novel/<?= $h['novel_id'] ?>"><?= h($h['novel_title']) ?></a></td><td><a href="/read/<?= $h['novel_id'] ?>/<?= $h['chapter_id'] ?>"><?= h($h['chapter_title']) ?></a></td><td><?= h($h['read_at']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>