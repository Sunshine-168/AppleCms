<?php

namespace App\Support;

use App\Models\System\SysUserModel;
use Illuminate\Support\Facades\Schema;

/** 后台界面语言（和前台内容语言分开）。语种与 maccms-ai 对齐。 */
class AdminUi
{
    public const ZH = 'zh_cn';

    public const EN = 'en';

    /**
     * 下拉顺序与对照系统一致：DE / US / ES / FR / JP / KR / PT / CN 简体 / CN 繁體
     *
     * @var array<string, array{label: string, tag: string, html: string, laravel: string, aliases: list<string>}>
     */
    private const LOCALES = [
        'de' => [
            'label' => 'Deutsch',
            'tag' => 'DE',
            'html' => 'de',
            'laravel' => 'de',
            'aliases' => ['de-de', 'de_de'],
        ],
        'en' => [
            'label' => 'English',
            'tag' => 'US',
            'html' => 'en',
            'laravel' => 'en',
            'aliases' => ['en-us', 'en_us', 'en-gb'],
        ],
        'es' => [
            'label' => 'Español',
            'tag' => 'ES',
            'html' => 'es',
            'laravel' => 'es',
            'aliases' => ['es-es', 'es_es'],
        ],
        'fr' => [
            'label' => 'Français',
            'tag' => 'FR',
            'html' => 'fr',
            'laravel' => 'fr',
            'aliases' => ['fr-fr', 'fr_fr'],
        ],
        'ja' => [
            'label' => '日本語',
            'tag' => 'JP',
            'html' => 'ja',
            'laravel' => 'ja',
            'aliases' => ['ja-jp', 'ja_jp'],
        ],
        'ko' => [
            'label' => '한국어',
            'tag' => 'KR',
            'html' => 'ko',
            'laravel' => 'ko',
            'aliases' => ['ko-kr', 'ko_kr'],
        ],
        'pt' => [
            'label' => 'Português',
            'tag' => 'PT',
            'html' => 'pt',
            'laravel' => 'pt',
            'aliases' => ['pt-pt', 'pt_pt', 'pt-br', 'pt_br'],
        ],
        'zh_cn' => [
            'label' => '简体中文',
            'tag' => 'CN',
            'html' => 'zh-CN',
            'laravel' => 'zh_cn',
            'aliases' => ['zh-cn', 'zh', 'cn', 'zh-hans'],
        ],
        'zh_tw' => [
            'label' => '繁體中文',
            'tag' => 'CN',
            'html' => 'zh-TW',
            'laravel' => 'zh_tw',
            'aliases' => ['zh-tw', 'zh_hk', 'zh-hk', 'zh-hant'],
        ],
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        $out = [];
        foreach (self::LOCALES as $code => $meta) {
            $out[$code] = $meta['label'];
        }

        return $out;
    }

    /**
     * @return array<string, array{code: string, label: string, tag: string, html: string}>
     */
    public static function catalog(): array
    {
        $out = [];
        foreach (self::LOCALES as $code => $meta) {
            $out[$code] = [
                'code' => $code,
                'label' => $meta['label'],
                'tag' => $meta['tag'],
                'html' => $meta['html'],
            ];
        }

        return $out;
    }

    public static function codes(): array
    {
        return array_keys(self::LOCALES);
    }

    public static function current(): string
    {
        $session = session('admin_ui_locale');
        if (is_string($session)) {
            $code = self::normalize($session);
            if ($code !== '') {
                return $code;
            }
        }
        $cookie = request()->cookie('admin_ui_locale');
        if (is_string($cookie)) {
            $code = self::normalize($cookie);
            if ($code !== '') {
                return $code;
            }
        }
        $uid = (int) session('admin_uid', 0);
        if ($uid > 0) {
            try {
                if (Schema::hasColumn('sys_user', 'ui_locale')) {
                    $stored = (string) (SysUserModel::query()->where('id', $uid)->value('ui_locale') ?? '');
                    $code = self::normalize($stored);
                    if ($code !== '') {
                        return $code;
                    }
                }
            } catch (\Throwable) {
            }
        }

        return self::ZH;
    }

    public static function isValid(string $code): bool
    {
        return self::normalize($code) !== '';
    }

    public static function normalize(string $code): string
    {
        $raw = strtolower(str_replace('_', '-', trim($code)));
        if ($raw === '') {
            return '';
        }
        foreach (self::LOCALES as $id => $meta) {
            $candidates = array_merge([$id, $meta['html'], $meta['laravel']], $meta['aliases']);
            foreach ($candidates as $alias) {
                if ($raw === strtolower(str_replace('_', '-', (string) $alias))) {
                    return $id;
                }
            }
        }

        return '';
    }

    public static function apply(): void
    {
        $code = self::current();
        app()->setLocale(self::LOCALES[$code]['laravel'] ?? self::ZH);
    }

    public static function htmlLang(): string
    {
        $code = self::current();

        return self::LOCALES[$code]['html'] ?? 'zh-CN';
    }

    public static function tag(string $code = ''): string
    {
        $code = $code !== '' ? self::normalize($code) : self::current();

        return self::LOCALES[$code]['tag'] ?? 'CN';
    }

    public static function label(string $code = ''): string
    {
        $code = $code !== '' ? self::normalize($code) : self::current();

        return self::LOCALES[$code]['label'] ?? '简体中文';
    }

    public static function isChinese(?string $code = null): bool
    {
        $code = $code === null ? self::current() : self::normalize($code);

        return in_array($code, [self::ZH, 'zh_tw'], true);
    }

    /** @return array{code: string, cookie: \Symfony\Component\HttpFoundation\Cookie} */
    public static function remember(string $code, bool $saveUser = true): array
    {
        $code = self::normalize($code) ?: self::ZH;
        session(['admin_ui_locale' => $code]);
        $uid = (int) session('admin_uid', 0);
        if ($saveUser && $uid > 0) {
            try {
                if (Schema::hasColumn('sys_user', 'ui_locale')) {
                    SysUserModel::query()->where('id', $uid)->update([
                        'ui_locale' => $code,
                        'update_time' => time(),
                    ]);
                }
            } catch (\Throwable) {
            }
        }
        $cookie = cookie('admin_ui_locale', $code, 60 * 24 * 365);

        return ['code' => $code, 'cookie' => $cookie];
    }
}
