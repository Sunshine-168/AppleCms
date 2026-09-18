<?php defined('ROOT_PATH') or die('URLError')?>
<?php require_once THEME_PATH . 'index/header.php'; ?>
<div class="read-container">
    <div class="read-header">
        <div class="breadcrumb">
            <a href="/"><?= $lang->get('home') ?></a> / 
            <a href="/novel/<?= $novelId ?>"><?= h($novel['title']) ?></a> / 
            <span><?= h($chapter['title']) ?></span>
        </div>
        <div class="read-controls">
            <button id="fontDecrease" class="ctrl-btn"><i class="fas fa-font"></i>-</button>
            <button id="fontIncrease" class="ctrl-btn"><i class="fas fa-font"></i>+</button>
            <button id="themeToggle" class="ctrl-btn"><i class="fas fa-moon"></i></button>
        </div>
    </div>
    
    <article class="chapter-content" id="chapterContent">
        <h1 class="chapter-title"><?= h($chapter['title']) ?></h1>
        <div class="content-body">
            <?= showAd('read_top') ?>
            <?= applyAdLinkReplacements(safe_nl2br($content)) ?>
            <?= showAd('read_bottom') ?>
        </div>
    </article>
    
    <div class="chapter-nav">
        <?php if ($prev): ?>
        <a href="/read/<?= $novelId ?>/<?= $prev['id'] ?>" class="nav-btn"><i class="fas fa-chevron-left"></i> <?= $lang->get('previous_chapter') ?></a>
        <?php else: ?>
        <span class="nav-btn disabled"><i class="fas fa-chevron-left"></i> <?= $lang->get('previous_chapter') ?></span>
        <?php endif; ?>
        
        <a href="/novel/<?= $novelId ?>" class="nav-btn"><i class="fas fa-list"></i> <?= $lang->get('chapter_list') ?></a>
        
        <?php if ($next): ?>
        <a href="/read/<?= $novelId ?>/<?= $next['id'] ?>" class="nav-btn"><?= $lang->get('next_chapter') ?> <i class="fas fa-chevron-right"></i></a>
        <?php else: ?>
        <span class="nav-btn disabled"><?= $lang->get('next_chapter') ?> <i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
</div>
<?php require_once THEME_PATH . 'index/footer.php'; ?>