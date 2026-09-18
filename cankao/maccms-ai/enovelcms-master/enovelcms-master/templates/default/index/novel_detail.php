<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container novel-detail-page">
    <div class="novel-detail-grid">
        <div class="detail-cover">
            <img src="/<?= h($novel['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($novel['title']) ?>">
            <div class="action-buttons">
                <?php 
                $firstChapter = null;
                foreach ($chaptersByVolume as $volGroup) {
                    if (!empty($volGroup['chapters'])) {
                        $firstChapter = $volGroup['chapters'][0];
                        break;
                    }
                }
                if ($firstChapter): ?>
                <a href="/read/<?= $novel['id'] ?>/<?= $firstChapter['id'] ?>" class="btn-primary btn-block">
                    <i class="fas fa-book-open"></i> <?= $lang->get('start_reading') ?>
                </a>
                <?php endif; ?>
                <button class="btn-outline btn-block add-bookshelf" data-novel-id="<?= $novel['id'] ?>">
                    <i class="fas fa-bookmark"></i> <?= $lang->get('add_to_bookshelf') ?>
                </button>
            </div>
        </div>
        <div class="detail-info">
            <h1><?= h($novel['title']) ?></h1>
            <div class="meta-row">
                <span><i class="fas fa-user"></i> <?= h($novel['author']) ?></span>
                <span><i class="fas fa-tag"></i> <?= h($novel['category_name']) ?></span>
                <span><i class="fas fa-<?= $novel['status'] ? 'check-circle' : 'sync-alt' ?>"></i>
                    <?= $novel['status'] ? $lang->get('finished') : $lang->get('ongoing') ?>
                </span>
            </div>
            <div class="stats-row">
                <div class="stat">
                    <span><?= number_format($totalWords) ?></span>
                    <label><?= $lang->get('words_unit') ?></label>
                </div>
                <div class="stat">
                    <span><?= number_format($novel['views']) ?></span>
                    <label><?= $lang->get('views_label') ?></label>
                </div>
                <div class="stat">
                    <span><?= number_format($novel['favorites'] ?? 0) ?></span>
                    <label><?= $lang->get('favorites') ?></label>
                </div>
                <div class="stat">
                    <span><?= array_reduce($chaptersByVolume, function($carry, $group) { return $carry + count($group['chapters']); }, 0) ?></span>
                    <label><?= $lang->get('chapters') ?></label>
                </div>
            </div>
            <div class="description">
                <h3><?= $lang->get('description') ?></h3>
                <div class="description-content">
                    <?= nl2br(h($novel['description'])) ?>
                </div>
            </div>
        </div>
    </div>

    <?= showAd('novel_detail_top') ?>

    <div class="chapters-section">
        <div class="section-header">
            <h2><i class="fas fa-list-ul"></i> <?= sprintf($lang->get('chapters_count'), array_reduce($chaptersByVolume, function($carry, $group) { return $carry + count($group['chapters']); }, 0)) ?></h2>
            <div class="chapter-sort">
                <button class="sort-btn active" data-order="asc"><?= $lang->get('asc') ?></button>
                <button class="sort-btn" data-order="desc"><?= $lang->get('desc') ?></button>
            </div>
        </div>

        <div class="chapters-accordion" id="chaptersAccordion">
            <?php $globalIndex = 1; ?>
            <?php foreach ($chaptersByVolume as $volId => $group): ?>
            <div class="volume-group" data-volume-id="<?= $volId ?>">
                <div class="volume-header">
                    <h3><?= h($group['volume']['title']) ?></h3>
                    <span class="volume-chapter-count">(<?= count($group['chapters']) ?> <?= $lang->get('chapters') ?>)</span>
                </div>
                <div class="volume-chapters">
                    <?php foreach ($group['chapters'] as $ch): ?>
                    <div class="chapter-item" data-sort="<?= (int)$ch['sort'] ?>" data-id="<?= $ch['id'] ?>">
                        <a href="/read/<?= $novel['id'] ?>/<?= $ch['id'] ?>">
                            <span class="chapter-title-info"><?= $globalIndex ?><?= $lang->get('chapter_separator') ?><?= h($ch['title']) ?></span>
                            <span class="word-count"><?= number_format($ch['word_count']) ?> <?= $lang->get('words_unit') ?></span>
                        </a>
                    </div>
                    <?php $globalIndex++; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php $related = DiyString('related_novels'); ?>
    <?php if (!empty($related)): ?>
    <section class="related-novels">
        <h3><i class="fas fa-share-alt"></i> <?= $lang->get('you_may_also_like') ?></h3>
        <div class="novel-grid small-grid">
            <?php foreach(array_slice($related, 0, 6) as $n): ?>
            <div class="novel-card compact">
                <a href="/novel/<?= $n['id'] ?>">
                    <div class="card-cover">
                        <img src="/<?= h($n['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($n['title']) ?>">
                    </div>
                    <div class="card-info">
                        <h4><?= h($n['title']) ?></h4>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?= showAd('novel_detail_bottom') ?>
</div>

<script>
(function() {
    let originalHtml = null;
    const accordion = document.getElementById('chaptersAccordion');
    function sortGrid(order) {
        if (!accordion) return;
        if (!originalHtml) {
            originalHtml = accordion.innerHTML;
        }

        if (order === 'asc') {
            accordion.innerHTML = originalHtml;
            bindVolumeToggleEvents();
        } else {
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = originalHtml;
            const volumeGroups = Array.from(tempDiv.querySelectorAll('.volume-group'));
            volumeGroups.reverse();
            volumeGroups.forEach(group => {
                const chaptersContainer = group.querySelector('.volume-chapters');
                if (chaptersContainer) {
                    const items = Array.from(chaptersContainer.querySelectorAll('.chapter-item'));
                    items.reverse();
                    chaptersContainer.innerHTML = '';
                    items.forEach(item => chaptersContainer.appendChild(item));
                }
            });
            accordion.innerHTML = '';
            volumeGroups.forEach(group => accordion.appendChild(group));
            bindVolumeToggleEvents();
        }
        renumberChapters();
    }
    function bindVolumeToggleEvents() {
        const volumeHeaders = document.querySelectorAll('.volume-header');
        volumeHeaders.forEach(header => {
            header.removeEventListener('click', header._clickHandler);
            const handler = function() {
                const group = this.closest('.volume-group');
                const chaptersDiv = group.querySelector('.volume-chapters');
                const icon = this.querySelector('.toggle-icon');
                if (chaptersDiv.style.display === 'none') {
                    chaptersDiv.style.display = 'grid';
                    if (icon) icon.classList.add('open');
                } else {
                    chaptersDiv.style.display = 'none';
                    if (icon) icon.classList.remove('open');
                }
            };
            header._clickHandler = handler;
            header.addEventListener('click', handler);
        });
    }
    function renumberChapters() {
        let globalIndex = 1;
        const allChapterItems = document.querySelectorAll('#chaptersAccordion .chapter-item');
        allChapterItems.forEach(item => {
            const titleSpan = item.querySelector('.chapter-title-info');
            if (titleSpan) {
                let originalText = titleSpan.getAttribute('data-original-title');
                if (!originalText) {
                    originalText = titleSpan.innerText.replace(/^\d+、/, '');
                    titleSpan.setAttribute('data-original-title', originalText);
                }
                titleSpan.innerText = globalIndex + '、' + originalText;
            }
            globalIndex++;
        });
    }
    bindVolumeToggleEvents();
    const sortBtns = document.querySelectorAll('.chapter-sort .sort-btn');
    sortBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const order = this.dataset.order;
            sortBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            sortGrid(order);
        });
    });
})();
</script>
<?php require_once THEME_PATH . 'index/footer.php'; ?>