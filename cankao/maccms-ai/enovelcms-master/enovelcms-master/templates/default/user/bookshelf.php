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
            <a href="/user/sign" class="user-nav-item"><i class="fas fa-calendar-check"></i> <span><?= $lang->get('daily_sign') ?></span></a>
            <a href="/user/bookshelf" class="user-nav-item active"><i class="fas fa-bookmark"></i> <span><?= $lang->get('bookshelf') ?></span></a>
            <a href="/user/history" class="user-nav-item"><i class="fas fa-history"></i> <span><?= $lang->get('history') ?></span></a>
            <div class="user-nav-divider"></div>
            <a href="/user/logout" class="user-nav-item"><i class="fas fa-sign-out-alt"></i> <span><?= $lang->get('logout') ?></span></a>
        </nav>
    </aside>
    <div class="user-content">
        <h2><i class="fas fa-bookmark"></i> <?= $lang->get('bookshelf') ?></h2>
        <?php if (empty($books)): ?>
            <div class="empty-state"><i class="fas fa-book-open"></i><p><?= $lang->get('empty_bookshelf') ?></p><a href="/library" class="btn-primary"><?= $lang->get('browse_novels') ?></a></div>
        <?php else: ?>
            <div class="bookshelf-list">
                <?php foreach($books as $b): ?>
                <div class="bookshelf-item" data-novel-id="<?= $b['novel_id'] ?>">
                    <div class="bookshelf-cover">
                        <a href="/novel/<?= $b['novel_id'] ?>">
                            <img src="/<?= h($b['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($b['title']) ?>">
                        </a>
                    </div>
                    <div class="bookshelf-info">
                        <h3><a href="/novel/<?= $b['novel_id'] ?>"><?= h($b['title']) ?></a></h3>
                        <p class="author"><?= h($b['author']) ?></p>
                        <p class="last-read">
                            <i class="fas fa-history"></i> <?= $lang->get('last_read') ?>：
                            <?php if (!empty($b['last_chapter_title']) && $b['last_read_chapter'] > 0): ?>
                                <a href="/read/<?= $b['novel_id'] ?>/<?= $b['last_read_chapter'] ?>"><?= h($b['last_chapter_title']) ?></a>
                            <?php else: ?>
                                <a href="/novel/<?= $b['novel_id'] ?>"><?= $lang->get('start_reading') ?></a>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="bookshelf-actions">
                        <?php if (!empty($b['last_chapter_title']) && $b['last_read_chapter'] > 0): ?>
                            <a href="/read/<?= $b['novel_id'] ?>/<?= $b['last_read_chapter'] ?>" class="btn-primary btn-sm"><?= $lang->get('continue_reading') ?></a>
                        <?php else: ?>
                            <a href="/novel/<?= $b['novel_id'] ?>" class="btn-primary btn-sm"><?= $lang->get('start_reading') ?></a>
                        <?php endif; ?>
                        <button class="btn-outline btn-sm remove-bookshelf" data-novel-id="<?= $b['novel_id'] ?>"><?= $lang->get('remove_from_bookshelf') ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>