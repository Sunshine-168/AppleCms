<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <div class="hero-slider"><?= showAd('home_banner') ?></div>

    <?php $featured = DiyString('featured_novels'); ?>
    <?php if (!empty($featured)): ?>
    <section>
        <div class="section-header">
            <h2><i class="fas fa-star"></i> <?= DiyStrTitle('featured_novels') ?></h2>
            <a href="/library" class="view-all"><?= $lang->get('view_all') ?> <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="novel-grid">
            <?php foreach($featured as $n): ?>
            <div class="novel-card">
                <a href="/novel/<?= $n['id'] ?>">
                    <div class="card-cover"><img src="/<?= h($n['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($n['title']) ?>"></div>
                    <div class="card-info">
                        <h3><?= h($n['title']) ?></h3>
                        <p class="author"><?= h($n['author']) ?></p>
                        <div class="card-stats"><span><i class="fas fa-eye"></i> <?= number_format($n['views']) ?></span></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php $latestUpdates = DiyString('latest_updates'); ?>
    <?php if (!empty($latestUpdates)): ?>
    <section>
        <div class="section-header">
            <h2><i class="fas fa-clock"></i> <?= DiyStrTitle('latest_updates') ?: $lang->get('latest_updates') ?></h2>
            <a href="/library?order_by=updated_at" class="view-all"><?= $lang->get('view_all') ?> <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="chapter-update-table">
            <table class="update-table">
                <thead><tr><th><?= $lang->get('category_label') ?></th><th><?= $lang->get('novel') ?></th><th><?= $lang->get('author_label') ?></th><th><?= $lang->get('latest_chapter') ?></th><th><?= $lang->get('last_update') ?></th></tr></thead>
                <tbody>
                    <?php foreach ($latestUpdates as $novel): ?>
                    <tr>
                        <td><?= h($novel['category_name']) ?></td>
                        <td><a href="/novel/<?= $novel['id'] ?>"><?= h($novel['title']) ?></a></td>
                        <td><?= h($novel['author']) ?></td>
                        <td>
                            <?php if ($novel['latest_chapter_id']): ?>
                                <a href="/read/<?= $novel['id'] ?>/<?= $novel['latest_chapter_id'] ?>"><?= h($novel['latest_chapter_title']) ?></a>
                            <?php else: ?>
                                <?= $lang->get('no_chapter') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($novel['updated_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

    <?= showAd('home_sidebar_top') ?>

    <div class="rank-row">
        <div class="rank-col">
            <?php $rankViews = DiyString('rank_views'); ?>
            <?php if (!empty($rankViews)): ?>
            <div class="rank-box">
                <h3><i class="fas fa-chart-line"></i> <?= DiyStrTitle('rank_views') ?: $lang->get('rank_views') ?></h3>
                <ol class="rank-list">
                    <?php foreach($rankViews as $idx => $n): ?>
                    <li><span class="rank-num"><?= $idx+1 ?></span> <a href="/novel/<?= $n['id'] ?>"><?= h($n['title']) ?></a> <span class="rank-value"><?= number_format($n['views']) ?></span></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php else: ?>
            <div class="rank-box empty-placeholder"></div>
            <?php endif; ?>
        </div>
        <div class="rank-col">
            <?php $rankFavorites = DiyString('rank_favorites'); ?>
            <?php if (!empty($rankFavorites)): ?>
            <div class="rank-box">
                <h3><i class="fas fa-heart"></i> <?= DiyStrTitle('rank_favorites') ?: $lang->get('rank_favorites') ?></h3>
                <ol class="rank-list">
                    <?php foreach($rankFavorites as $idx => $n): ?>
                    <li><span class="rank-num"><?= $idx+1 ?></span> <a href="/novel/<?= $n['id'] ?>"><?= h($n['title']) ?></a> <span class="rank-value"><?= number_format($n['favorites']) ?></span></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php else: ?>
            <div class="rank-box empty-placeholder"></div>
            <?php endif; ?>
        </div>
        <div class="rank-col">
            <?php $rankWords = DiyString('rank_words'); ?>
            <?php if (!empty($rankWords)): ?>
            <div class="rank-box">
                <h3><i class="fas fa-file-alt"></i> <?= DiyStrTitle('rank_words') ?: $lang->get('rank_words') ?></h3>
                <ol class="rank-list">
                    <?php foreach($rankWords as $idx => $n): ?>
                    <li><span class="rank-num"><?= $idx+1 ?></span> <a href="/novel/<?= $n['id'] ?>"><?= h($n['title']) ?></a> <span class="rank-value"><?= number_format($n['total_words']) ?> <?= $lang->get('words_unit') ?></span></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php else: ?>
            <div class="rank-box empty-placeholder"></div>
            <?php endif; ?>
        </div>
    </div>

    <?php $random = DiyString('random_picks'); ?>
    <?php if (!empty($random)): ?>
    <div class="random-box">
        <h3><i class="fas fa-dice-d6"></i> <?= DiyStrTitle('random_picks') ?></h3>
        <div class="random-tags">
            <?php foreach($random as $n): ?>
            <a href="/novel/<?= $n['id'] ?>" class="random-tag"><?= h($n['title']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?= showAd('home_sidebar_bottom') ?>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>