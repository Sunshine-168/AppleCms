<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="container">
    <div class="library-hero">
        <h1><?= $lang->get('library') ?></h1>
        <p><?= $lang->get('discover_novels') ?></p>
    </div>

    <div class="filter-section">
        <div class="filter-group">
            <div class="filter-label"><?= $lang->get('category_label') ?></div>
            <div class="filter-options" id="categoryOptions">
                <?php
                $baseParams = [
                    'status' => $status,
                    'order_by' => $orderBy,
                    'min_words' => $minWords,
                    'max_words' => $maxWords
                ];
                ?>
                <a href="?category=0&<?= http_build_query($baseParams) ?>" class="filter-option <?= $categoryId == 0 ? 'active' : '' ?>"><?= $lang->get('all_categories') ?></a>
                <?php foreach ($categories as $cat): ?>
                <a href="?category=<?= $cat['id'] ?>&<?= http_build_query($baseParams) ?>" class="filter-option <?= $categoryId == $cat['id'] ? 'active' : '' ?>"><?= h($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-label"><?= $lang->get('status_label') ?></div>
            <div class="filter-options" id="statusOptions">
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => 'all'])) ?>" class="filter-option <?= $status == 'all' ? 'active' : '' ?>"><?= $lang->get('status_all') ?></a>
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => 'ongoing'])) ?>" class="filter-option <?= $status == 'ongoing' ? 'active' : '' ?>"><?= $lang->get('ongoing') ?></a>
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => 'finished'])) ?>" class="filter-option <?= $status == 'finished' ? 'active' : '' ?>"><?= $lang->get('finished') ?></a>
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-label"><?= $lang->get('sort_by') ?></div>
            <div class="filter-options" id="sortOptions">
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => $status, 'order_by' => 'id'])) ?>" class="filter-option <?= $orderBy == 'id' ? 'active' : '' ?>"><?= $lang->get('sort_latest') ?></a>
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => $status, 'order_by' => 'updated_at'])) ?>" class="filter-option <?= $orderBy == 'updated_at' ? 'active' : '' ?>"><?= $lang->get('sort_update') ?></a>
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => $status, 'order_by' => 'views'])) ?>" class="filter-option <?= $orderBy == 'views' ? 'active' : '' ?>"><?= $lang->get('sort_views') ?></a>
                <a href="?<?= http_build_query(array_merge($baseParams, ['category' => $categoryId, 'status' => $status, 'order_by' => 'favorites'])) ?>" class="filter-option <?= $orderBy == 'favorites' ? 'active' : '' ?>"><?= $lang->get('favorites') ?></a>
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-label"><?= $lang->get('word_range') ?></div>
            <div class="word-range-inputs">
                <input type="number" id="minWords" placeholder="<?= $lang->get('min_words') ?>" value="<?= $minWords ?: '' ?>">
                <span>—</span>
                <input type="number" id="maxWords" placeholder="<?= $lang->get('max_words') ?>" value="<?= $maxWords ?: '' ?>">
                <button type="button" id="applyWordsBtn" class="btn-icon"><i class="fas fa-check"></i></button>
                <button type="button" id="resetWordsBtn" class="btn-icon"><i class="fas fa-undo-alt"></i></button>
            </div>
        </div>
    </div>
<?= showAd('library_top') ?>
    <div class="result-stats">
        <span><?= $lang->get('total_reward') ?> <strong><?= number_format($total ?? 0) ?></strong> <?= $lang->get('novel') ?></span>
        <div class="view-toggle">
            <button class="view-btn active" data-view="list"><i class="fas fa-list"></i> <?= $lang->get('list') ?></button>
            <button class="view-btn" data-view="grid"><i class="fas fa-th"></i> <?= $lang->get('grid') ?></button>
        </div>
    </div>

    <div class="novel-list-container list-view" id="novelContainer">
        <?php if (!empty($novels)): ?>
            <?php foreach($novels as $n): ?>
            <div class="novel-item">
                <div class="novel-cover">
                    <a href="/novel/<?= $n['id'] ?>">
                        <img src="/<?= h($n['cover'] ?: 'assets/images/default_cover.jpg') ?>" alt="<?= h($n['title']) ?>">
                    </a>
                </div>
                <div class="novel-info">
                    <h3 class="novel-title"><a href="/novel/<?= $n['id'] ?>"><?= h($n['title']) ?></a></h3>
                    <p class="novel-author"><?= $lang->get('author_label') ?><?= $lang->get('label_separator') ?><?= h($n['author']) ?></p>
                    <div class="novel-meta">
                        <span><i class="fas fa-eye"></i> <?= number_format($n['views']) ?></span>
                        <span><i class="fas fa-file-alt"></i> <?= number_format($n['total_words']) ?> <?= $lang->get('words_unit') ?></span>
                        <span><i class="fas fa-bookmark"></i> <?= number_format($n['favorites']) ?> <?= $lang->get('favorites') ?></span>
                        <span class="status-badge <?= $n['status'] == 0 ? 'ongoing' : 'finished' ?>">
                            <?= $n['status'] == 0 ? $lang->get('ongoing') : $lang->get('finished') ?>
                        </span>
                    </div>
                    <p class="novel-desc"><?= h(mb_substr(strip_tags($n['description']), 0, 120)) ?>...</p>
                    <div class="novel-last">
                        <?php if (!empty($n['last_chapter_title'])): ?>
                        <span><i class="fas fa-book-open"></i> <?= $lang->get('latest_chapter') ?>：<a href="/read/<?= $n['id'] ?>/<?= $n['last_chapter_id'] ?>"><?= h($n['last_chapter_title']) ?></a></span>
                        <?php endif; ?>
                        <span><i class="fas fa-calendar-alt"></i> <?= $lang->get('last_update') ?>：<?= date('Y-m-d', strtotime($n['updated_at'])) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-book-open"></i><p><?= $lang->get('no_novels') ?></p></div>
        <?php endif; ?>
    </div>

    <?php if (($totalPages ?? 0) > 1): ?>
    <div class="pagination">
        <?php
        $queryParams = [
            'category' => $categoryId,
            'status' => $status,
            'order_by' => $orderBy,
            'min_words' => $minWords,
            'max_words' => $maxWords
        ];
        function buildPageUrl($page, $params) {
            $params['page'] = $page;
            return '?' . http_build_query(array_filter($params, function($v) { return $v !== '' && $v !== null && $v !== 0; }));
        }
        ?>
        <?php if ($page > 1): ?>
        <a href="<?= buildPageUrl($page-1, $queryParams) ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
        <a href="<?= buildPageUrl($i, $queryParams) ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="<?= buildPageUrl($page+1, $queryParams) ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
(function() {
    const container = document.getElementById('novelContainer');
    const viewBtns = document.querySelectorAll('.view-btn');
    const savedView = localStorage.getItem('libraryView') || 'list';

    function setView(view) {
        if (view === 'grid') {
            container.classList.add('grid-view');
            container.classList.remove('list-view');
        } else {
            container.classList.add('list-view');
            container.classList.remove('grid-view');
        }
        viewBtns.forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        localStorage.setItem('libraryView', view);
    }

    setView(savedView);

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            setView(this.dataset.view);
        });
    });
})();

(function() {
    const minInput = document.getElementById('minWords');
    const maxInput = document.getElementById('maxWords');
    const applyBtn = document.getElementById('applyWordsBtn');
    const resetWordsBtn = document.getElementById('resetWordsBtn');

    function getCurrentUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            category: params.get('category') || '0',
            status: params.get('status') || 'all',
            order_by: params.get('order_by') || 'id',
            min_words: params.get('min_words') || '0',
            max_words: params.get('max_words') || '0',
            page: params.get('page') || '1'
        };
    }

    function buildUrlWithWords(min, max) {
        const current = getCurrentUrlParams();
        const newParams = {
            category: current.category,
            status: current.status,
            order_by: current.order_by,
            min_words: min === '' ? 0 : min,
            max_words: max === '' ? 0 : max,
            page: 1
        };
        const filtered = {};
        for (let [k, v] of Object.entries(newParams)) {
            if (v !== '' && v !== null && v !== 0) filtered[k] = v;
        }
        return '?' + new URLSearchParams(filtered).toString();
    }

    function applyWordRange() {
        let minVal = minInput.value.trim() === '' ? 0 : parseInt(minInput.value, 10);
        let maxVal = maxInput.value.trim() === '' ? 0 : parseInt(maxInput.value, 10);
        if (isNaN(minVal)) minVal = 0;
        if (isNaN(maxVal)) maxVal = 0;
        if (minVal < 0) minVal = 0;
        if (maxVal < 0) maxVal = 0;
        const url = buildUrlWithWords(minVal, maxVal);
        window.location.href = url;
    }

    function resetWordRange() {
        minInput.value = '';
        maxInput.value = '';
        applyWordRange();
    }

    if (applyBtn) applyBtn.addEventListener('click', applyWordRange);
    if (resetWordsBtn) resetWordsBtn.addEventListener('click', resetWordRange);
    minInput?.addEventListener('keypress', function(e) { if (e.key === 'Enter') applyWordRange(); });
    maxInput?.addEventListener('keypress', function(e) { if (e.key === 'Enter') applyWordRange(); });
})();
</script>
<?php require_once THEME_PATH . 'index/footer.php'; ?>