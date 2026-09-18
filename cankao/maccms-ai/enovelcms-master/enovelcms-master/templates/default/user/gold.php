<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="user-center-layout">
    <aside class="user-sidebar">
        <div class="user-sidebar-header"><div class="user-avatar"><i class="fas fa-user"></i></div><h3><?= h($user['username']) ?></h3><p><?= $vipActive ? $lang->get('vip_member') : $lang->get('normal_user') ?></p></div>
        <nav class="user-nav">
            <a href="/user/" class="user-nav-item"><i class="fas fa-tachometer-alt"></i> <span><?= $lang->get('user_center') ?></span></a>
            <a href="/user/profile" class="user-nav-item"><i class="fas fa-user-edit"></i> <span><?= $lang->get('profile') ?></span></a>
            <a href="/user/gold" class="user-nav-item active"><i class="fas fa-coins"></i> <span><?= $lang->get('gold_log') ?></span></a>
            <a href="/user/vip" class="user-nav-item"><i class="fas fa-crown"></i> <span><?= $lang->get('vip_center') ?></span></a>
            <a href="/user/sign" class="user-nav-item"><i class="fas fa-calendar-check"></i> <span><?= $lang->get('daily_sign') ?></span></a>
            <a href="/user/bookshelf" class="user-nav-item"><i class="fas fa-bookmark"></i> <span><?= $lang->get('bookshelf') ?></span></a>
            <a href="/user/history" class="user-nav-item"><i class="fas fa-history"></i> <span><?= $lang->get('history') ?></span></a>
            <div class="user-nav-divider"></div>
            <a href="/user/logout" class="user-nav-item"><i class="fas fa-sign-out-alt"></i> <span><?= $lang->get('logout') ?></span></a>
        </nav>
    </aside>
    <div class="user-content">
        <h2><i class="fas fa-coins"></i> <?= $lang->get('gold_log') ?></h2>
        <div class="user-info-card"><p><strong><?= $lang->get('current_gold') ?>：</strong> <?= number_format($user['gold']) ?></p></div>
        <?php if (empty($logs)): ?>
            <div class="empty-state"><i class="fas fa-receipt"></i><p><?= $lang->get('no_gold_logs') ?></p></div>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th><?= $lang->get('gold_log_time') ?></th><th><?= $lang->get('gold_log_type') ?></th><th><?= $lang->get('gold_log_change') ?></th><th><?= $lang->get('gold_log_after') ?></th><th><?= $lang->get('gold_log_remark') ?></th></tr></thead>
                <tbody>
                <?php foreach ($logs as $log): 
                    $typeMap = ['sign' => $lang->get('gold_type_sign'), 'exchange_vip' => $lang->get('gold_type_exchange_vip'), 'admin' => $lang->get('gold_type_admin')];
                    $typeName = $typeMap[$log['type']] ?? $log['type'];
                    $changeClass = $log['gold_change'] >= 0 ? 'text-success' : 'text-danger';
                ?>
                <tr><td><?= $log['created_at'] ?></td><td><?= $typeName ?></td><td class="<?= $changeClass ?>"><?= $log['gold_change'] >= 0 ? '+' : '' ?><?= $log['gold_change'] ?></td><td><?= $log['gold_after'] ?></td><td><?= h($log['remark']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>