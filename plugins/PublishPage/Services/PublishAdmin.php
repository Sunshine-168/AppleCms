<?php

namespace Plugins\PublishPage\Services;

use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;

class PublishAdmin
{
    public function __construct(private readonly PublishService $publish) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $payload['options'] = [
            'status' => (int) $this->publish->get('status', '0') === 1 ? 1 : 0,
            'title' => $this->publish->get('title', '地址发布页'),
            'subtitle' => $this->publish->get('subtitle'),
            'bookmark' => $this->publish->get('bookmark'),
            'footer' => $this->publish->get('footer'),
            'permanent_text' => $this->publish->get('permanent_text'),
            'permanent_url' => $this->publish->get('permanent_url'),
        ];
        $payload['groups'] = $this->publish->groups();

        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! $this->publish->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($params);
        if ($desk === 'config') {
            $opt = $this->boardPayload([])['options'];
            $opt['id'] = 0;

            return Result::success(AdminPage::slice([$opt], $params));
        }
        $rows = [];
        foreach ($this->publish->groups() as $group) {
            $rows[] = [
                'id' => $group['id'],
                'title' => $group['title'],
                'hint' => $group['hint'],
                'url_count' => count($group['urls']),
                'urls_text' => $this->urlsText($group['urls']),
            ];
        }
        $kw = trim((string) ($params['q'] ?? ''));
        if ($kw !== '') {
            $rows = array_values(array_filter($rows, static function (array $row) use ($kw): bool {
                return str_contains((string) $row['title'], $kw) || str_contains((string) $row['id'], $kw);
            }));
        }

        return Result::success(AdminPage::slice($rows, $params));
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        if (! $this->publish->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($data);
        if ($desk === 'config') {
            $this->publish->set('status', (int) ($data['status'] ?? 0) === 1 ? '1' : '0');
            foreach (['title', 'subtitle', 'bookmark', 'footer', 'permanent_text'] as $k) {
                if (array_key_exists($k, $data)) {
                    $this->publish->set($k, trim((string) $data[$k]));
                }
            }
            if (array_key_exists('permanent_url', $data)) {
                $url = trim((string) $data['permanent_url']);
                if ($url !== '' && PublishService::httpUrl($url) === null) {
                    return Result::fail('永久地址只接受 http 或 https');
                }
                $this->publish->set('permanent_url', $url === '' ? '' : (string) PublishService::httpUrl($url));
            }

            return AdminOpLog::ifOk(Result::success(), 'save', '保存发布页参数', ['module' => 'publish_pages']);
        }
        if ($id !== null && $id > 0) {
            $data['id'] = $id;
        }

        return $this->saveGroup($data);
    }

    public function delete(int $id): array
    {
        $desk = $this->desk(request()->all());
        if ($desk !== 'groups') {
            return Result::fail('参数不能删');
        }
        $groups = $this->publish->groups();
        $keep = [];
        $found = false;
        foreach ($groups as $group) {
            if ((int) $group['id'] === $id) {
                $found = true;

                continue;
            }
            $keep[] = $group;
        }
        if (! $found) {
            return Result::fail('数据不存在');
        }
        $this->publish->set('groups', json_encode(array_values($keep), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除发布页线路组', ['module' => 'publish_pages']);
    }

    /** @param  array<string, mixed>  $data */
    private function saveGroup(array $data): array
    {
        $groups = $this->publish->groups();
        $gid = trim((string) ($data['id'] ?? $data['gid'] ?? ''));
        if ($gid === '' || $gid === '0') {
            $gid = $this->nextId($groups);
        }
        $title = mb_substr(trim((string) ($data['title'] ?? '')), 0, 80);
        if ($title === '') {
            return Result::fail('请填写名称');
        }
        $urls = $this->parseUrls((string) ($data['urls_text'] ?? $data['urls'] ?? ''));
        if (is_array($data['urls'] ?? null)) {
            $urls = [];
            foreach ($data['urls'] as $u) {
                if (! is_array($u)) {
                    continue;
                }
                $url = PublishService::httpUrl((string) ($u['url'] ?? ''));
                if ($url === null) {
                    continue;
                }
                $name = trim((string) ($u['name'] ?? ''));
                $urls[] = ['name' => $name !== '' ? $name : $url, 'url' => $url];
            }
        }
        $row = [
            'id' => $gid !== '' ? $gid : $this->nextId($groups),
            'title' => $title,
            'hint' => mb_substr(trim((string) ($data['hint'] ?? '')), 0, 255),
            'urls' => $urls,
        ];
        $replaced = false;
        foreach ($groups as $i => $group) {
            if ((string) $group['id'] === (string) $row['id']) {
                $groups[$i] = $row;
                $replaced = true;
                break;
            }
        }
        if (! $replaced) {
            $groups[] = $row;
        }
        $this->publish->set('groups', json_encode(array_values($groups), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return AdminOpLog::ifOk(Result::success(['id' => $row['id']]), 'save', '保存发布页线路组 '.$title, [
            'module' => 'publish_pages',
        ]);
    }

    /**
     * @param  list<array{name:string,url:string}>  $urls
     */
    private function urlsText(array $urls): string
    {
        $lines = [];
        foreach ($urls as $u) {
            $lines[] = $u['name'].' '.$u['url'];
        }

        return implode("\n", $lines);
    }

    /** @return list<array{name:string,url:string}> */
    private function parseUrls(string $text): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('#^(https?://\S+)\s+(.+)$#i', $line, $m)) {
                $url = PublishService::httpUrl($m[1]);
                $name = trim($m[2]);
            } elseif (preg_match('#^(.+?)\s+(https?://\S+)$#i', $line, $m)) {
                $name = trim($m[1]);
                $url = PublishService::httpUrl($m[2]);
            } else {
                $url = PublishService::httpUrl($line);
                $name = $url ?? '';
            }
            if ($url === null) {
                continue;
            }
            $out[] = ['name' => $name !== '' ? $name : $url, 'url' => $url];
        }

        return $out;
    }

    /** @param  list<array{id:string,title:string,hint:string,urls:list<array{name:string,url:string}>}>  $groups */
    private function nextId(array $groups): string
    {
        $max = 0;
        foreach ($groups as $group) {
            $max = max($max, (int) $group['id']);
        }

        return (string) ($max + 1);
    }

    /** @param  array<string, mixed>  $params */
    private function desk(array $params): string
    {
        $desk = strtolower(trim((string) ($params['desk'] ?? request()->input('desk', ''))));

        return $desk === 'groups' ? 'groups' : 'config';
    }
}
