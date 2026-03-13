<?php
namespace App\Traits;

use Illuminate\Support\Facades\Request;

trait I18nTrait
{
    /**
     * 前端语言码 → 标准语言目录
     */
    protected function normalizeLang(string $lang): string
    {
        $lang = strtolower($lang);

        return match ($lang) {
            'zh', 'zh-cn', 'zh_cn' => 'zh_CN',
            'en', 'en-us', 'en_us' => 'en',
            'bn', 'bn-bd', 'bn_bd' => 'bn',
            default                => 'zh_CN',
        };
    }

    /**
     * 跟随请求语言的翻译（核心方法）
     */
    protected function t(string $key, array $params = [], ?string $lang = null): string
    {
        $lang = $lang ?: Request::header('lang', 'zh');
        $lang = $this->normalizeLang($lang);
        $originLang = app()->getLocale();

        try {
            app()->setLocale($lang);
            $text = __($key, $params, $lang);

            if ($text === $key) {
                return $key;
            }

            foreach ($params as $k => $v) {
                $text = str_replace(
                    ['{' . $k . '}', ':' . $k],
                    (string)$v,
                    $text
                );
            }

            return $text;
        } finally {
            app()->setLocale($originLang);
        }
    }

    /**
     * 强制中文翻译（用于日志 / 数据库）
     */
    protected function tChinese(string $key, array $params = []): string
    {
        $lang = 'zh_CN';
        $originLang = app()->getLocale();

        try {
            app()->setLocale($lang);
            $text = __($key, $params, $lang);

            if ($text === $key) {
                return $key;
            }

            foreach ($params as $k => $v) {
                $text = str_replace(
                    ['{' . $k . '}', ':' . $k],
                    (string)$v,
                    $text
                );
            }

            return $text;
        } finally {
            app()->setLocale($originLang);
        }
    }
}
