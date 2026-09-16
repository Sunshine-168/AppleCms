<?php

namespace App\Support\Utils;

use Illuminate\Support\Facades\Config;

class ErrorCodeLoader
{
    protected static array $map = [];

    public static function loadAll(): void
    {
        $langPath = lang_path();
        $allowLang = Config::get('lang.allow_lang_list', ['zh_CN', 'en']);

        foreach ($allowLang as $lang) {
            $file = $langPath . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'error.php';

            if (!is_file($file)) {
                continue;
            }

            foreach ((array) include $file as $key => $msg) {
                self::$map[$lang][$key] = $msg;
            }
        }
    }

    public static function get(string $errorKey, ?string $lang = null): string
    {
        $lang = $lang ?: self::detectLang();

        if (!isset(self::$map[$lang])) {
            self::loadAll();
        }

        return self::$map[$lang][$errorKey] ?? '未知错误';
    }

    public static function detectLang(): string
    {
        $lang = request()->input(Config::get('lang.detect_var', 'lang'));

        if (!$lang && Config::get('lang.use_cookie', true)) {
            $lang = request()->cookie(Config::get('lang.cookie_var', 'think_lang'));
        }

        if (!$lang) {
            $headerVar = Config::get('lang.header_var', 'Accept-Language');
            $header = strtolower((string) request()->header($headerVar));
            $acceptMap = Config::get('lang.accept_language', []);
            $lang = $acceptMap[$header] ?? null;
        }

        if (!$lang) {
            $lang = Config::get('lang.default_lang', 'zh_CN');
        }

        $allowList = Config::get('lang.allow_lang_list', ['zh_CN', 'en']);
        if (!in_array($lang, $allowList, true)) {
            $lang = Config::get('lang.default_lang', 'zh_CN');
        }

        return (string) $lang;
    }
}
