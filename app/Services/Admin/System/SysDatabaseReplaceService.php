<?php

namespace App\Services\Admin\System;

use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 片库文字批量替换
 */
class SysDatabaseReplaceService
{
    /**
     * @return array{targets: list<array<string, mixed>>}
     */
    public function pageBoard(): array
    {
        return [
            'targets' => $this->availableTargets(),
        ];
    }

    /**
     * 先数会改几条
     *
     * @param  list<string>  $fields
     */
    public function preview(string $targetId, array $fields, string $from): array
    {
        $prepared = $this->prepare($targetId, $fields, $from);
        if ((int) ($prepared['code'] ?? 1) !== 0) {
            return $prepared;
        }

        /** @var array<string, mixed> $job */
        $job = $prepared['data'];
        $matched = $this->matchCount((string) $job['table'], $job['columns'], $from);

        return Result::success([
            'matched' => $matched,
            'target' => $job['id'],
            'target_label' => $job['label'],
            'field_labels' => $job['field_labels'],
            'from' => $from,
            'summary' => $this->summary($job, $from, null, $matched),
        ], $matched === 0 ? '没有含这段文字的记录' : '约 '.$matched.' 条含这段文字');
    }

    /**
     * 执行替换。只改白名单里的片库文字，不用任意表和 WHERE。
     *
     * @param  list<string>  $fields
     */
    public function run(string $targetId, array $fields, string $from, string $to): array
    {
        $from = (string) $from;
        $to = (string) $to;
        if (mb_strlen($to) > 500) {
            return Result::fail('替换成的文字太长');
        }

        $prepared = $this->prepare($targetId, $fields, $from);
        if ((int) ($prepared['code'] ?? 1) !== 0) {
            return $prepared;
        }

        /** @var array<string, mixed> $job */
        $job = $prepared['data'];
        if ($from === $to) {
            return Result::fail('新旧一样，不会改任何东西');
        }

        $matched = $this->matchCount((string) $job['table'], $job['columns'], $from);
        if ($matched === 0) {
            return Result::success([
                'matched' => 0,
                'affected' => 0,
                'summary' => $this->summary($job, $from, $to, 0),
            ], '没有含这段文字的记录，库没改');
        }

        try {
            DB::transaction(function () use ($job, $from, $to): void {
                $table = $this->wrapTable((string) $job['table']);
                $like = $this->likePattern($from);
                foreach ($job['columns'] as $col) {
                    $colW = $this->wrap((string) $col);
                    DB::update(
                        'UPDATE '.$table.' SET '.$colW.' = REPLACE('.$colW.', ?, ?) WHERE '.$colW.' LIKE ? ESCAPE ?',
                        [$from, $to, $like, '\\']
                    );
                }
            });
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '没能替换');
        }

        $summary = $this->summary($job, $from, $to, $matched);

        return AdminOpLog::ifOk(Result::success([
            'matched' => $matched,
            'affected' => $matched,
            'summary' => $summary,
        ], '已替换，约 '.$matched.' 条含这段文字'), 'replace', '批量替换了'.$job['label'].'约 '.$matched.' 条', [
            'module' => '数据',
            'target_type' => 'replace',
            'payload' => [
                'target' => $job['id'],
                'fields' => $job['columns'],
                'matched' => $matched,
            ],
        ]);
    }

    public function targetIdFromTable(string $table): string
    {
        $table = trim($table);
        foreach ($this->catalog() as $item) {
            if ($item['table'] === $table) {
                return $item['id'];
            }
        }

        return '';
    }

    /**
     * @param  list<string>  $fields
     * @return array{code: int, msg: string, data?: array<string, mixed>}
     */
    private function prepare(string $targetId, array $fields, string $from): array
    {
        $from = (string) $from;
        if ($from === '') {
            return Result::fail('请填写要找的文字');
        }
        if (mb_strlen($from) > 500) {
            return Result::fail('要找的文字太长');
        }

        $target = $this->findTarget($targetId);
        if ($target === null) {
            return Result::fail('只改片库内容，不能改系统表');
        }

        $allowed = [];
        foreach ($target['fields'] as $field) {
            $allowed[$field['key']] = $field['label'];
        }

        $columns = [];
        $labels = [];
        foreach ($fields as $key) {
            $key = trim((string) $key);
            if ($key === '' || ! isset($allowed[$key])) {
                continue;
            }
            if (! Schema::hasColumn($target['table'], $key)) {
                continue;
            }
            if (! in_array($key, $columns, true)) {
                $columns[] = $key;
                $labels[] = $allowed[$key];
            }
        }

        if ($columns === []) {
            return Result::fail('请勾要改的项');
        }

        return Result::success([
            'id' => $target['id'],
            'label' => $target['label'],
            'table' => $target['table'],
            'columns' => $columns,
            'field_labels' => $labels,
        ]);
    }

    /**
     * @param  list<string>  $columns
     */
    private function matchCount(string $table, array $columns, string $from): int
    {
        $like = $this->likePattern($from);
        $query = DB::table($table);
        $query->where(function ($outer) use ($columns, $like): void {
            foreach ($columns as $i => $col) {
                $sql = $this->wrap((string) $col).' LIKE ? ESCAPE ?';
                if ($i === 0) {
                    $outer->whereRaw($sql, [$like, '\\']);
                } else {
                    $outer->orWhereRaw($sql, [$like, '\\']);
                }
            }
        });

        return (int) $query->count();
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function summary(array $job, string $from, ?string $to, int $matched): string
    {
        $fields = implode('、', $job['field_labels'] ?? []);
        $line = ($job['label'] ?? '').' · '.$fields.' 里约 '.$matched.' 条含「'.$from.'」';
        if ($to === null) {
            return $line;
        }
        if ($to === '') {
            return $line.'，将删掉这段字';
        }

        return $line.'，将换成「'.$to.'」';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findTarget(string $id): ?array
    {
        $id = trim($id);
        foreach ($this->availableTargets() as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function availableTargets(): array
    {
        $out = [];
        foreach ($this->catalog() as $item) {
            if (! Schema::hasTable($item['table'])) {
                continue;
            }
            $fields = [];
            foreach ($item['fields'] as $field) {
                if (Schema::hasColumn($item['table'], $field['key'])) {
                    $fields[] = $field;
                }
            }
            if ($fields === []) {
                continue;
            }
            $item['fields'] = $fields;
            $out[] = $item;
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'id' => 'videos',
                'label' => '影片',
                'table' => 'videos',
                'hint' => '片名、简介、封面地址',
                'fields' => [
                    ['key' => 'title', 'label' => '片名'],
                    ['key' => 'description', 'label' => '简介'],
                    ['key' => 'subtitle', 'label' => '副标题'],
                    ['key' => 'remarks', 'label' => '备注'],
                    ['key' => 'director', 'label' => '导演'],
                    ['key' => 'writer', 'label' => '编剧'],
                    ['key' => 'cover', 'label' => '封面地址'],
                    ['key' => 'banner', 'label' => '横幅地址'],
                ],
            ],
            [
                'id' => 'episodes',
                'label' => '播放地址',
                'table' => 'video_episodes',
                'hint' => '每集播放链接，换域名用这个',
                'fields' => [
                    ['key' => 'url', 'label' => '播放地址'],
                    ['key' => 'episode_name', 'label' => '集名'],
                ],
            ],
            [
                'id' => 'arts',
                'label' => '文章',
                'table' => 'video_arts',
                'hint' => '文章标题和正文',
                'fields' => [
                    ['key' => 'title', 'label' => '标题'],
                    ['key' => 'content', 'label' => '正文'],
                    ['key' => 'cover', 'label' => '封面地址'],
                ],
            ],
            [
                'id' => 'types',
                'label' => '分类',
                'table' => 'video_types',
                'hint' => '分类名和 SEO',
                'fields' => [
                    ['key' => 'name', 'label' => '名称'],
                    ['key' => 'seo_title', 'label' => 'SEO 标题'],
                    ['key' => 'seo_keywords', 'label' => 'SEO 关键词'],
                    ['key' => 'seo_description', 'label' => 'SEO 描述'],
                ],
            ],
            [
                'id' => 'topics',
                'label' => '专题',
                'table' => 'video_topics',
                'hint' => '专题名和介绍',
                'fields' => [
                    ['key' => 'name', 'label' => '名称'],
                    ['key' => 'blurb', 'label' => '简介'],
                    ['key' => 'content', 'label' => '介绍'],
                    ['key' => 'cover', 'label' => '封面地址'],
                ],
            ],
            [
                'id' => 'actors',
                'label' => '演员',
                'table' => 'actors',
                'hint' => '演员名和介绍',
                'fields' => [
                    ['key' => 'name', 'label' => '姓名'],
                    ['key' => 'content', 'label' => '介绍'],
                    ['key' => 'avatar', 'label' => '头像地址'],
                ],
            ],
            [
                'id' => 'plots',
                'label' => '剧情',
                'table' => 'video_plots',
                'hint' => '按集写的剧情简介',
                'fields' => [
                    ['key' => 'title', 'label' => '标题'],
                    ['key' => 'content', 'label' => '内容'],
                ],
            ],
        ];
    }

    private function likePattern(string $from): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $from).'%';
    }

    private function wrap(string $name): string
    {
        return DB::connection()->getQueryGrammar()->wrap($name);
    }

    private function wrapTable(string $table): string
    {
        return DB::connection()->getQueryGrammar()->wrapTable($table);
    }
}
