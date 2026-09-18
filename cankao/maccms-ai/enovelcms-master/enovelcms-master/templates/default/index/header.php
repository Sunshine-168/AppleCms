<?php defined('ROOT_PATH') or die('URLError')?>
<!DOCTYPE html>
<html lang="<?= $current_lang == 'zh-cn' ? 'zh-CN' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $finalTitle = '';
    if (!empty($seo['title'])) {
        $finalTitle = $seo['title'];
    } elseif (!empty($pageTitle)) {
        $finalTitle = $pageTitle . ' - ' . getSetting('site_name', $db);
    } else {
        $finalTitle = getSetting('site_name', $db) ?: $lang->get('site_default_name');
    }
    ?>
    <title><?= h($finalTitle) ?></title>
    <?php if (!empty($seo['keywords'])): ?>
    <meta name="keywords" content="<?= h($seo['keywords']) ?>">
    <?php endif; ?>
    <?php if (!empty($seo['description'])): ?>
    <meta name="description" content="<?= h($seo['description']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= THEME_URL ?>css/style.css">
    <?= getSetting('analytics_code', $db) ?>
</head>
<body>
<div class="app-wrapper">
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <a href="/"><?= h(getSetting('site_name', $db) ?: $lang->get('site_default_name')) ?></a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="/" class="<?= $_SERVER['REQUEST_URI'] == '/' ? 'active' : '' ?>"><?= $lang->get('home') ?></a></li>
                        <li><a href="/library" class="<?= strpos($_SERVER['REQUEST_URI'], '/library') !== false ? 'active' : '' ?>"><?= $lang->get('library') ?></a></li>
                        <li><a href="/rank" class="<?= strpos($_SERVER['REQUEST_URI'], '/rank') !== false ? 'active' : '' ?>"><?= $lang->get('rank') ?></a></li>
                        <li><a href="/authors" class="<?= strpos($_SERVER['REQUEST_URI'], '/authors') !== false ? 'active' : '' ?>"><?= $lang->get('author') ?></a></li>
                        <?php if (isLoggedIn()): ?>
                        <li><a href="/user/bookshelf" class="<?= strpos($_SERVER['REQUEST_URI'], '/user/bookshelf') !== false ? 'active' : '' ?>"><?= $lang->get('bookshelf') ?></a></li>
                        <li><a href="/user/history" class="<?= strpos($_SERVER['REQUEST_URI'], '/user/history') !== false ? 'active' : '' ?>"><?= $lang->get('history') ?></a></li>
                        <li><a href="/user/vip" class="<?= strpos($_SERVER['REQUEST_URI'], '/user/vip') !== false ? 'active' : '' ?>"><?= $lang->get('vip') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button id="searchBtn" class="search-icon-btn" aria-label="<?= $lang->get('search');?>">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <?php if (isLoggedIn()): ?>
                        <span class="gold-badge"><i class="fas fa-coins"></i> <?= number_format($userGold) ?></span>
                        <div class="user-menu">
                            <span class="username"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?> <i class="fas fa-chevron-down"></i></span>
                            <div class="user-dropdown">
                                <a href="/user/"><i class="fas fa-user"></i> <?= $lang->get('user_center') ?></a>
                                <a href="/user/logout"><i class="fas fa-sign-out-alt"></i> <?= $lang->get('logout') ?></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/user/login" class="btn-outline"><?= $lang->get('login') ?></a>
                        <a href="/user/register" class="btn-primary"><?= $lang->get('register') ?></a>
                    <?php endif; ?>
                    <div class="lang-switch">
                        <a href="/api/ajax/switch_lang.php?lang=zh-cn" class="<?= $current_lang == 'zh-cn' ? 'active' : '' ?>">中文</a>
                        <span>/</span>
                        <a href="/api/ajax/switch_lang.php?lang=en-us" class="<?= $current_lang == 'en-us' ? 'active' : '' ?>">EN</a>
                    </div>
                    <button class="mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                </div>
            </div>
        </div>
    </header>

    <div id="searchOverlay" class="search-overlay">
        <div class="search-modal">
            <div class="search-close">
                <button id="closeSearchBtn"><i class="fas fa-times"></i></button>
            </div>
            <h3><i class="fas fa-search"></i> <?= $lang->get('search');?></h3>
            <div class="search-input-group">
                <input type="text" id="searchKeyword" placeholder="<?= $lang->get('search_placeholder');?>" autocomplete="off">
                <button id="submitSearch"><?= $lang->get('search');?></button>
            </div>
            <div class="search-tip">
                <i class="fas fa-info-circle"></i> <?= $lang->get('search_tip');?>
            </div>
        </div>
    </div>

    <main class="site-main">