<?php
class Language {
    private $lang = 'zh-cn';
    private $translations = [];
    private $area = 'backend';
    private $theme = 'default';

    public function __construct($area = 'backend') {
        $this->area = $area;
        if ($area === 'backend') {
            $this->loadSystem();
        } else {
            $this->loadFrontend();
        }
    }

    public function setArea($area) {
        $this->area = $area;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
    }

    public function setLang($lang) {
        if ($this->isLangAvailable($lang)) {
            $this->lang = $lang;
        }
    }

    private function loadSystem() {
        global $db;
        $defaultLang = getSetting('site_lang', $db) ?: 'zh-cn';
        $this->lang = $defaultLang;
        $file = ROOT_PATH . "includes/lang/{$this->lang}.php";
        if (file_exists($file)) {
            $this->translations = include $file;
        } else {
            $file = ROOT_PATH . "includes/lang/zh-cn.php";
            $this->translations = file_exists($file) ? include $file : [];
        }
    }

    private function loadFrontend() {
        global $db;
        $userLang = $_SESSION['lang'] ?? $_COOKIE['lang'] ?? null;
        if ($userLang && $this->isLangAvailable($userLang)) {
            $this->lang = $userLang;
        } else {
            $defaultLang = getSetting('site_lang', $db) ?: 'zh-cn';
            $this->lang = $defaultLang;
        }
        $this->doLoadFrontend();
    }

    private function doLoadFrontend() {
        $theme = $this->theme ?: 'default';
        $langFile = ROOT_PATH . "templates/{$theme}/lang/{$this->lang}.php";
        if (file_exists($langFile)) {
            $this->translations = include $langFile;
        } else {
            $fallback = ROOT_PATH . "includes/lang/{$this->lang}.php";
            if (file_exists($fallback)) {
                $this->translations = include $fallback;
            } else {
                $this->translations = [];
            }
        }
    }

    public function reload() {
        if ($this->area === 'backend') {
            $this->loadSystem();
        } else {
            $this->doLoadFrontend();
        }
    }

    public function get($key, $default = '') {
        return $this->translations[$key] ?? $default;
    }

    public function current() {
        return $this->lang;
    }

    public function getAllLanguages() {
        $config = include ROOT_PATH . 'includes/lang/lang_config.php';
        return $config['available'] ?? [];
    }

    private function isLangAvailable($code) {
        $available = $this->getAllLanguages();
        return isset($available[$code]);
    }
}