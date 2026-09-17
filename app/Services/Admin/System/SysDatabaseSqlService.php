<?php

namespace App\Services\Admin\System;

use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 在当前库跑一条 SQL。不是 phpMyAdmin，也不能当备份导入。
 */
class SysDatabaseSqlService
{
    public const CONFIRM_WORD = '执行';

    private const MAX_LIMIT = 200;

    private const MAX_SQL_CHARS = 8000;

    private const CELL_CHARS = 200;

    /** @var list<string> */
    private const QUERY_WORDS = ['select', 'show', 'describe', 'desc', 'explain', 'with'];

    /** @var list<string> */
    private const WRITE_WORDS = ['insert', 'update', 'delete', 'replace'];

    /** @var list<string> */
    private const FORBIDDEN_WORDS = [
        'drop', 'truncate', 'alter', 'create', 'rename', 'grant', 'revoke',
        'attach', 'detach', 'vacuum', 'reindex', 'pragma', 'use', 'load', 'call',
        'begin', 'commit', 'rollback', 'savepoint', 'set', 'lock', 'unlock',
        'handler', 'do', 'prepare', 'execute', 'deallocate', 'analyze',
        'optimize', 'repair', 'flush', 'kill', 'purge', 'copy',
    ];

    /** @var list<string> */
    private const FORBIDDEN_SNIPS = [
        'into outfile', 'into dumpfile', 'load_file', 'load data',
    ];

    /**
     * @return array<string, mixed>
     */
    public function pageBoard(): array
    {
        $driver = $this->connectionDriver();
        $memory = $driver === 'sqlite' && $this->isMemorySqlite($this->sqliteDatabasePath());
        $driverLabel = '未知';
        $driverHint = '';
        if ($driver === 'sqlite' && $memory) {
            $driverLabel = '内存 SQLite';
            $driverHint = '这是内存库。能查，改了也留不住文件。正式环境请把 DB_DATABASE 指到 database/database.sqlite。';
        } elseif ($driver === 'sqlite') {
            $driverLabel = 'SQLite 文件';
            $driverHint = '语法按 SQLite。没有 SHOW TABLES，用下面「有哪些表」。';
        } elseif ($driver === 'mysql') {
            $driverLabel = 'MySQL';
            $driverHint = '语法按 MySQL。一次一条，不要把整段备份 SQL 贴进来。';
        } elseif ($driver === 'mariadb') {
            $driverLabel = 'MariaDB';
            $driverHint = '语法按 MariaDB。一次一条，不要把整段备份 SQL 贴进来。';
        } else {
            $driverLabel = $driver !== '' ? $driver : '未知';
            $driverHint = '现在这种库这里没试过，跑失败会把原文回给你。';
        }

        return [
            'driver' => $driver,
            'driver_label' => $driverLabel,
            'driver_hint' => $driverHint,
            'can_delete' => $this->isFirstAdmin(),
            'examples' => $this->examples($driver),
            'ui' => [
                'title' => '执行 SQL',
                'lead' => '对当前库执行一条 SQL。写入不能撤销，先备份。查询最多 200 行；改库请输入「执行」。',
                'note' => '一次一条。不能建表、删表、改结构。改错字用批量替换。',
                'now' => '当前库',
                'stmt' => '语句',
                'placeholder' => '一条语句。带 FROM 的 SELECT 请写 LIMIT，最多 200 行。',
                'examples' => '示例',
                'run' => '执行',
                'clear' => '清空',
                'result' => '结果',
                'idle' => '还没跑过。',
                'idle_hint' => '写好语句再点执行。查到 0 行会照实显示。',
                'backup' => '备份',
                'restore' => '恢复',
                'replace' => '批量替换',
                'dict' => '字段',
                'confirm_write' => '会改当前库，不能撤销。确定后还要输入「执行」。',
                'type_hint' => '会改当前库。要继续请输入：执行',
                'type_err' => '没输入对，没有改库',
                'word' => self::CONFIRM_WORD,
                'empty_rows' => '查到 0 行。',
                'busy' => '正在执行…',
                'need_sql' => '请先写一条 SQL',
            ],
        ];
    }

    public function run(string $sql, string $word = ''): array
    {
        $sql = trim($sql);
        if ($sql === '') {
            return Result::fail('请先写一条 SQL');
        }
        if (mb_strlen($sql, 'UTF-8') > self::MAX_SQL_CHARS) {
            return Result::fail('语句太长，最多 '.self::MAX_SQL_CHARS.' 字');
        }

        $parts = preg_split('/;(?=(?:[^\'"]|\'[^\']*\'|"[^"]*")*$)/', $sql) ?: [];
        $statements = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $statements[] = $p;
            }
        }
        if ($statements === []) {
            return Result::fail('请先写一条 SQL');
        }
        if (count($statements) !== 1) {
            return Result::fail('一次只能一条。多条请拆开跑，备份 SQL 请去恢复页盖回去。');
        }

        $statement = $this->stripLeadingComments($statements[0]);
        if ($statement === '') {
            return Result::fail('去掉注释之后没有语句');
        }

        foreach (self::FORBIDDEN_SNIPS as $snip) {
            if (stripos($statement, $snip) !== false) {
                return Result::fail('这类写法不能在这里跑');
            }
        }

        $keyword = $this->firstKeyword($statement);
        if ($keyword === '') {
            return Result::fail('看不出是什么语句');
        }
        if (in_array($keyword, self::FORBIDDEN_WORDS, true)) {
            return Result::fail('不能建表、删表、改结构，也不能 TRUNCATE / VACUUM / PRAGMA。');
        }

        $isQuery = in_array($keyword, self::QUERY_WORDS, true);
        $isWrite = in_array($keyword, self::WRITE_WORDS, true);
        if (! $isQuery && ! $isWrite) {
            return Result::fail('只接受 SELECT / SHOW / 说明表，以及 INSERT / UPDATE / DELETE / REPLACE。');
        }

        if ($keyword === 'delete' && ! $this->isFirstAdmin()) {
            return Result::fail('只有 1 号管理员能跑 DELETE。换文字请去批量替换。');
        }

        if ($isWrite) {
            if (trim($word) !== self::CONFIRM_WORD) {
                return Result::fail('改数据请输入「执行」。没输入对，没有改库。');
            }
        }

        if ($isQuery && in_array($keyword, ['select', 'with'], true)) {
            $needsFrom = preg_match('/\b(from|join)\b/i', $statement) === 1;
            $limit = $this->trailingLimit($statement);
            if ($needsFrom && $limit === null) {
                return Result::fail('带 FROM 的查询请自己写 LIMIT，最多 '.self::MAX_LIMIT.' 行。');
            }
            if ($limit !== null && $limit > self::MAX_LIMIT) {
                return Result::fail('LIMIT 最多 '.self::MAX_LIMIT.'，避免把整张片库拉进浏览器。');
            }
        }

        $started = microtime(true);
        try {
            if ($isQuery) {
                $rows = DB::select($statement);
                $elapsed = $this->elapsedMs($started);
                $list = [];
                foreach ($rows as $row) {
                    $list[] = $this->presentRow((array) $row);
                }
                $columns = [];
                if ($list !== [] && is_array($list[0])) {
                    $columns = array_keys($list[0]);
                }
                $count = count($list);
                $msg = $count === 0
                    ? '查到 0 行，用了 '.$elapsed.' 毫秒'
                    : '查到 '.$count.' 行，用了 '.$elapsed.' 毫秒';

                return Result::success([
                    'type' => 'query',
                    'columns' => $columns,
                    'rows' => $list,
                    'count' => $count,
                    'elapsed_ms' => $elapsed,
                ], $msg);
            }

            $affected = DB::transaction(function () use ($statement) {
                return DB::affectingStatement($statement);
            });
            $elapsed = $this->elapsedMs($started);
            $n = (int) $affected;

            return Result::success([
                'type' => 'affecting',
                'affected' => $n,
                'elapsed_ms' => $elapsed,
            ], '改了 '.$n.' 行，用了 '.$elapsed.' 毫秒');
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '执行失败');
        }
    }

    /**
     * @return list<array{id:string,label:string,sql:string,hint:string}>
     */
    private function examples(string $driver): array
    {
        $out = [];
        try {
            if (Schema::hasTable('videos')) {
                $out[] = [
                    'id' => 'videos',
                    'label' => '最近片子',
                    'sql' => 'SELECT id, title, status FROM videos ORDER BY id DESC LIMIT 10',
                    'hint' => '片名在 videos.title。播放地址不在这张表。',
                ];
            }
            if (Schema::hasTable('video_episodes')) {
                $out[] = [
                    'id' => 'episodes',
                    'label' => '最近剧集',
                    'sql' => 'SELECT id, video_id, name, url FROM video_episodes ORDER BY id DESC LIMIT 10',
                    'hint' => '某一集的播放地址在这里。',
                ];
            }
            if (Schema::hasTable('video_arts')) {
                $out[] = [
                    'id' => 'arts',
                    'label' => '最近文章',
                    'sql' => 'SELECT id, title, status FROM video_arts ORDER BY id DESC LIMIT 10',
                    'hint' => '资讯文章，不是影片简介。',
                ];
            }
        } catch (\Throwable) {
        }

        if ($driver === 'sqlite') {
            $out[] = [
                'id' => 'tables',
                'label' => '有哪些表',
                'sql' => "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name LIMIT 50",
                'hint' => '当前是 SQLite。表干什么去「字段」。',
            ];
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            $out[] = [
                'id' => 'tables',
                'label' => '有哪些表',
                'sql' => 'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name LIMIT 50',
                'hint' => '只列出当前库。表干什么去「字段」。',
            ];
        }

        return $out;
    }

    private function stripLeadingComments(string $sql): string
    {
        $sql = ltrim($sql);
        while ($sql !== '') {
            if (str_starts_with($sql, '--') || str_starts_with($sql, '#')) {
                $nl = strpos($sql, "\n");
                if ($nl === false) {
                    return '';
                }
                $sql = ltrim(substr($sql, $nl + 1));
                continue;
            }
            if (str_starts_with($sql, '/*')) {
                $end = strpos($sql, '*/');
                if ($end === false) {
                    return '';
                }
                $sql = ltrim(substr($sql, $end + 2));
                continue;
            }
            break;
        }

        return $sql;
    }

    private function firstKeyword(string $sql): string
    {
        if (preg_match('/^([A-Za-z]+)/', $sql, $m) !== 1) {
            return '';
        }

        return strtolower($m[1]);
    }

    private function trailingLimit(string $sql): ?int
    {
        $sql = rtrim($sql);
        if (preg_match('/\blimit\s+(\d+)\s*,\s*(\d+)\s*$/i', $sql, $m) === 1) {
            return (int) $m[2];
        }
        if (preg_match('/\blimit\s+(\d+)\s+offset\s+\d+\s*$/i', $sql, $m) === 1) {
            return (int) $m[1];
        }
        if (preg_match('/\blimit\s+(\d+)\s*$/i', $sql, $m) === 1) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>
     */
    private function presentRow(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $out[(string) $key] = $this->presentCell($value);
        }

        return $out;
    }

    private function presentCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (! is_scalar($value)) {
            return '[…]';
        }
        $text = (string) $value;
        if (mb_strlen($text, 'UTF-8') > self::CELL_CHARS) {
            return mb_substr($text, 0, self::CELL_CHARS, 'UTF-8').'…';
        }

        return $text;
    }

    private function elapsedMs(float $started): int
    {
        return (int) max(0, round((microtime(true) - $started) * 1000));
    }

    private function isFirstAdmin(): bool
    {
        return (int) session('admin_uid', 0) === 1;
    }

    private function connectionDriver(): string
    {
        $connection = (string) config('database.default', 'mysql');
        $cfg = (array) config('database.connections.'.$connection, []);

        return (string) ($cfg['driver'] ?? '');
    }

    private function sqliteDatabasePath(): string
    {
        $connection = (string) config('database.default', 'sqlite');
        $cfg = (array) config('database.connections.'.$connection, []);

        return (string) ($cfg['database'] ?? '');
    }

    private function isMemorySqlite(string $path): bool
    {
        $path = strtolower(trim($path));

        return $path === ':memory:' || $path === 'memory' || str_contains($path, ':memory:');
    }
}
