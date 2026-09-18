<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> <?= $lang->get('author') ?></h1>
        <p><?= $lang->get('author_pages_intro') ?></p>
    </div>
    
    <?= showAd('author_top') ?>
    
    <div class="author-grid">
        <?php foreach ($authors as $a): ?>
        <div class="author-card">
            <div class="author-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="author-info">
                <h3><?= h($a['author']) ?></h3>
                <p><i class="fas fa-book"></i> <?= $lang->get('novel_count') ?>: <?= $a['novel_count'] ?></p>
                <a href="/search?author=<?= urlencode($a['author']) ?>" class="btn-sm"><?= $lang->get('view_works') ?></a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($authors)): ?>
        <div class="empty-state"><i class="fas fa-user-slash"></i><p><?= $lang->get('no_authors') ?></p></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>