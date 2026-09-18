<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <div class="rank-header">
        <h1><?= $lang->get('rank') ?></h1>
        <p><?= $lang->get('rank_description') ?></p>
    </div>

    <div class="rank-tabs">
        <button class="rank-tab <?= $rankType == 'views' ? 'active' : '' ?>" data-type="views">
            <i class="fas fa-eye"></i> <?= $lang->get('rank_views') ?>
        </button>
        <button class="rank-tab <?= $rankType == 'words' ? 'active' : '' ?>" data-type="words">
            <i class="fas fa-file-alt"></i> <?= $lang->get('rank_words') ?>
        </button>
        <button class="rank-tab <?= $rankType == 'favorites' ? 'active' : '' ?>" data-type="favorites">
            <i class="fas fa-bookmark"></i> <?= $lang->get('rank_favorites') ?>
        </button>
    </div>

    <div class="rank-list-large">
        <?php if (!empty($novels)): ?>
            <?php foreach ($novels as $idx => $n): ?>
            <div class="rank-item">
                <div class="rank-number <?= $idx < 3 ? 'top-three' : '' ?>">
                    <?= $idx + 1 ?>
                </div>
                <a href="/novel/<?= $n['id'] ?>" class="rank-item-link">
                    <img src="/<?= h($n['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($n['title']) ?>">
                    <div class="rank-info">
                        <h3><?= h($n['title']) ?></h3>
                        <p><?= h($n['author']) ?></p>
                        <div class="rank-meta">
                            <?php if ($rankType == 'views'): ?>
                                <span><i class="fas fa-eye"></i> <?= number_format($n['views']) ?></span>
                            <?php elseif ($rankType == 'words'): ?>
                                <span><i class="fas fa-file-alt"></i> <?= number_format($n['total_words']) ?> <?= $lang->get('words_unit') ?></span>
                            <?php else: ?>
                                <span><i class="fas fa-bookmark"></i> <?= number_format($n['favorites']) ?> <?= $lang->get('favorites') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <div class="rank-score">
                    <?php if ($rankType == 'views'): ?>
                        <i class="fas fa-fire"></i> <?= number_format($n['views']) ?>
                    <?php elseif ($rankType == 'words'): ?>
                        <i class="fas fa-chart-line"></i> <?= number_format($n['total_words']) ?>
                    <?php else: ?>
                        <i class="fas fa-heart"></i> <?= number_format($n['favorites']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p><?= $lang->get('no_data') ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?= showAd('rank_top') ?>
</div>

<script>
document.querySelectorAll('.rank-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const type = this.dataset.type;
        window.location.href = '/rank?type=' + type;
    });
});
</script>
<?php require_once THEME_PATH . 'index/footer.php'; ?>