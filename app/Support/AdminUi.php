<?php

namespace App\Support;

use App\Models\System\SysUserModel;
use Illuminate\Support\Facades\Schema;

/** 后台界面语言（和前台内容语言分开） */
class AdminUi
{
    public const ZH = 'zh_cn';

    public const EN = 'en';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::ZH => '中文',
            self::EN => 'English',
        ];
    }

    public static function current(): string
    {
        $session = session('admin_ui_locale');
        if (is_string($session) && self::isValid($session)) {
            return $session;
        }
        $cookie = request()->cookie('admin_ui_locale');
        if (is_string($cookie) && self::isValid($cookie)) {
            return $cookie;
        }
        $uid = (int) session('admin_uid', 0);
        if ($uid > 0) {
            try {
                if (Schema::hasColumn('sys_user', 'ui_locale')) {
                    $code = (string) (SysUserModel::query()->where('id', $uid)->value('ui_locale') ?? '');
                    if (self::isValid($code)) {
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
        return array_key_exists($code, self::options());
    }

    public static function apply(): void
    {
        $code = self::current();
        app()->setLocale($code === self::ZH ? 'zh_cn' : 'en');
    }

    public static function htmlLang(): string
    {
        return self::current() === self::EN ? 'en' : 'zh-CN';
    }

    /** @return array{code: string, cookie: \Symfony\Component\HttpFoundation\Cookie} */
    public static function remember(string $code, bool $saveUser = true): array
    {
        $code = self::isValid($code) ? $code : self::ZH;
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
