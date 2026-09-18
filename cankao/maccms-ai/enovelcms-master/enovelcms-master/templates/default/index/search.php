<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="search-page">
    <div class="search-header">
        <h1 class="search-title"><?= $lang->get('search') ?></h1>
        <p class="search-subtitle"><?= $lang->get('search_tip') ?></p>
    </div>

    <?php if (isset($needCaptcha) && $needCaptcha): ?>
    <div class="search-captcha-box">
        <div class="search-captcha-error"><?= $captchaError ?: $lang->get('search_frequency_tip') ?></div>
        <div class="search-captcha-input-group">
            <input type="text" id="captchaInput" placeholder="<?= $lang->get('captcha') ?>" class="search-captcha-input">
            <img src="/api/captcha.php" id="captchaImg" onclick="this.src='/api/captcha.php?'+Math.random()" class="search-captcha-img">
        </div>
        <button id="submitWithCaptcha" class="search-captcha-submit"><?= $lang->get('search') ?></button>
    </div>
    <?php endif; ?>

    <?php if (isset($searchExecuted) && $searchExecuted): ?>
        <?php if (empty($novels)): ?>
            <div class="search-empty">
                <p><?= $lang->get('no_novels') ?></p>
            </div>
        <?php else: ?>
            <div class="search-result-bar">
                <span class="search-result-count"><?= sprintf($lang->get('found_total'), $total) ?></span>
                <div class="search-view-toggle">
                    <button class="view-btn active" data-view="grid"><?= $lang->get('grid') ?></button>
                    <button class="view-btn" data-view="list"><?= $lang->get('list') ?></button>
                </div>
            </div>

            <div id="novelListContainer" class="novel-list-container grid-view search-novel-grid">
                <?php foreach ($novels as $novel): ?>
                    <div class="novel-item search-novel-card">
                        <div class="novel-cover search-novel-cover">
                            <a href="/novel/<?= $novel['id'] ?>">
                                <img src="/<?= h($novel['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($novel['title']) ?>">
                                <span class="status-badge <?= $novel['status'] ? 'finished' : 'ongoing' ?>"><?= $novel['status'] ? $lang->get('finished') : $lang->get('ongoing') ?></span>
                            </a>
                        </div>
                        <div class="novel-info search-novel-info">
                            <h3 class="novel-title search-novel-title">
                                <a href="/novel/<?= $novel['id'] ?>"><?= h($novel['title']) ?></a>
                            </h3>
                            <div class="novel-author search-novel-author"><?= h($novel['author']) ?></div>
                            <div class="novel-meta search-novel-meta">
                                <span><i class="fas fa-eye"></i> <?= number_format($novel['views']) ?></span>
                                <span><i class="fas fa-font"></i> <?= number_format($novel['total_words']) ?></span>
                                <span><i class="fas fa-heart"></i> <?= $novel['favorites'] ?></span>
                            </div>
                            <div class="novel-desc search-novel-desc"><?= h(mb_substr(strip_tags($novel['description']), 0, 80)) ?>...</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="search-pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?q=<?= urlencode($keyword) ?>&page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif (!isset($searchExecuted)): ?>
        <div class="search-welcome">
            <p><?= $lang->get('search_tip') ?></p>
        </div>
    <?php endif; ?>
    <?= showAd('rank_top') ?>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>