<?php

namespace Plugins\PublishPage\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\PublishPage\Models\PublishOption;
use Symfony\Component\HttpFoundation\Cookie;

class PublishService
{
    public const COOKIE = 'lv_publish_entered';

    public const COOKIE_MINUTES = 525600;

    public function ready(): bool
    {
        return Schema::hasTable('plugin_publish_options');
    }

    public function enabled(): bool
    {
        return $this->ready() && (int) $this->get('status', '0') === 1;
    }

    public function get(string $k, string $default = ''): string
    {
        if (! $this->ready()) {
            return $default;
        }
        $row = PublishOption::query()->where('k', $k)->first();

        return $row ? (string) $row->v : $default;
    }

    public function set(string $k, string $v): void
    {
        if (! $this->ready()) {
            return;
        }
        $row = PublishOption::query()->where('k', $k)->first();
        if ($row) {
            $row->v = $v;
            $row->save();

            return;
        }
        PublishOption::query()->create(['k' => $k, 'v' => $v]);
    }

    public function hasEnteredCookie(Request $request): bool
    {
        return (string) $request->cookie(self::COOKIE) === '1';
    }

    public function enteredCookie(Request $request): Cookie
    {
        return cookie(self::COOKIE, '1', self::COOKIE_MINUTES, '/', null, $request->isSecure(), true);
    }

    /** @return list<array{id:string,title:string,hint:string,urls:list<array{name:string,url:string}>}> */
    public function groups(): array
    {
        $raw = json_decode($this->get('groups', '[]'), true);
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            if ($id === '' || $title === '') {
                continue;
            }
            $urls = [];
            foreach (is_array($row['urls'] ?? null) ? $row['urls'] : [] as $u) {
                if (! is_array($u)) {
                    continue;
                }
                $url = self::httpUrl((string) ($u['url'] ?? ''));
                $name = trim((string) ($u['name'] ?? ''));
                if ($url === null) {
                    continue;
                }
                $urls[] = ['name' => $name !== '' ? $name : $url, 'url' => $url];
            }
            $out[] = [
                'id' => $id,
                'title' => $title,
                'hint' => trim((string) ($row['hint'] ?? '')),
                'urls' => $urls,
            ];
        }

        return $out;
    }

    public function group(string $id): ?array
    {
        foreach ($this->groups() as $group) {
            if ($group['id'] === $id) {
                return $group;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function pageData(?array $group = null): array
    {
        $permanentUrl = self::httpUrl($this->get('permanent_url'));

        return [
            'title' => $this->get('title', '地址发布页'),
            'subtitle' => $this->get('subtitle'),
            'bookmark' => $this->get('bookmark'),
            'footer' => $this->get('footer'),
            'permanent_text' => $this->get('permanent_text'),
            'permanent_url' => $permanentUrl,
            'groups' => $this->groups(),
            'group' => $group,
            'enter' => url('/sitehome'),
        ];
    }

    public static function httpUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $url) === 1) {
            return null;
        }
        if (preg_match('#^https?://#i', $url) !== 1) {
            return null;
        }
        $parts = parse_url($url);
        if (! is_array($parts) || trim((string) ($parts['host'] ?? '')) === '') {
            return null;
        }

        return $url;
    }
}
