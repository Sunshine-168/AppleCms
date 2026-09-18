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
            <a href="/user/" class="user-nav-item"><i class="fas fa-tachometer-alt"></i> <span><?= $lang->get('user_center') ?></span></a>
            <a href="/user/profile" class="user-nav-item"><i class="fas fa-user-edit"></i> <span><?= $lang->get('profile') ?></span></a>
            <a href="/user/gold" class="user-nav-item"><i class="fas fa-coins"></i> <span><?= $lang->get('gold_log') ?></span></a>
            <a href="/user/vip" class="user-nav-item active"><i class="fas fa-crown"></i> <span><?= $lang->get('vip_center') ?></span></a>
            <a href="/user/sign" class="user-nav-item"><i class="fas fa-calendar-check"></i> <span><?= $lang->get('daily_sign') ?></span></a>
            <a href="/user/bookshelf" class="user-nav-item"><i class="fas fa-bookmark"></i> <span><?= $lang->get('bookshelf') ?></span></a>
            <a href="/user/history" class="user-nav-item"><i class="fas fa-history"></i> <span><?= $lang->get('history') ?></span></a>
            <div class="user-nav-divider"></div>
            <a href="/user/logout" class="user-nav-item"><i class="fas fa-sign-out-alt"></i> <span><?= $lang->get('logout') ?></span></a>
        </nav>
    </aside>
    <div class="user-content">
        <h2><i class="fas fa-crown"></i> <?= $lang->get('vip_center') ?></h2>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <div class="vip-status-card <?= $vipActive ? 'active' : '' ?>">
            <i class="fas fa-gem"></i>
            <div class="status-text"><?= $vipActive ? $lang->get('vip_member') : $lang->get('normal_user') ?></div>
            <?php if ($vipActive): ?>
                <div class="expire-info"><?= $lang->get('expire_date') ?>: <?= h($user['vip_expire']) ?></div>
            <?php else: ?>
                <div class="upgrade-tip"><?= $lang->get('vip_desc') ?></div>
            <?php endif; ?>
        </div>

        <?php if ($goldPerMonth > 0): ?>
        <div class="vip-exchange-box">
            <h3><i class="fas fa-coins"></i> <?= $lang->get('exchange_vip_title') ?></h3>
            <p><?= sprintf($lang->get('exchange_rate_info'), $goldPerMonth) ?></p>
            <form method="post" class="vip-form">
                <div class="month-selector">
                    <label><?= $lang->get('select_months') ?>：</label>
                    <select name="months" class="vip-select">
                        <option value="1">1 <?= $lang->get('month') ?> (<?= $goldPerMonth ?> <?= $lang->get('gold') ?>)</option>
                        <option value="3">3 <?= $lang->get('months') ?> (<?= $goldPerMonth * 3 ?> <?= $lang->get('gold') ?>)</option>
                        <option value="6">6 <?= $lang->get('months') ?> (<?= $goldPerMonth * 6 ?> <?= $lang->get('gold') ?>)</option>
                        <option value="12">12 <?= $lang->get('months') ?> (<?= $goldPerMonth * 12 ?> <?= $lang->get('gold') ?>)</option>
                    </select>
                </div>
                <button type="submit" name="exchange_vip" class="btn-primary"><?= $lang->get('exchange_vip') ?></button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($pricePerMonth > 0): ?>
        <div class="vip-pay-box">
            <h3><i class="fas fa-credit-card"></i> <?= $lang->get('recharge_vip_title') ?></h3>
            <p><?= sprintf($lang->get('price_info'), $pricePerMonth) ?></p>
            <form method="post" class="vip-form">
                <div class="month-selector">
                    <label><?= $lang->get('select_months') ?>：</label>
                    <select name="months" class="vip-select">
                        <option value="1">1 <?= $lang->get('month') ?> (<?= sprintf('%.2f', $pricePerMonth) ?><?= $lang->get('currency_unit') ?>)</option>
                        <option value="3">3 <?= $lang->get('months') ?> (<?= sprintf('%.2f', $pricePerMonth * 3) ?><?= $lang->get('currency_unit') ?>)</option>
                        <option value="6">6 <?= $lang->get('months') ?> (<?= sprintf('%.2f', $pricePerMonth * 6) ?><?= $lang->get('currency_unit') ?>)</option>
                        <option value="12">12 <?= $lang->get('months') ?> (<?= sprintf('%.2f', $pricePerMonth * 12) ?><?= $lang->get('currency_unit') ?>)</option>
                    </select>
                </div>
                <div class="month-selector">
                    <label><?= $lang->get('payment_method') ?>：</label>
                    <select name="pay_type" class="vip-select">
                        <option value="alipay"><?= $lang->get('payment_alipay') ?></option>
                        <option value="wxpay"><?= $lang->get('payment_wxpay') ?></option>
                    </select>
                </div>
                <button type="submit" name="pay_vip" class="btn-primary"><?= $lang->get('pay_now') ?></button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pay-method { margin: 15px 0; }
.pay-method label { margin-right: 15px; font-weight: normal; }
.pay-method input { margin-right: 5px; }
</style>
<?php require_once THEME_PATH . 'index/footer.php'; ?>