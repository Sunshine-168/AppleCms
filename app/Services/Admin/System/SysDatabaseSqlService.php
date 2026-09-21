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
        $word = admin_t('ui.sql_word');
        $driverLabel = admin_t('ui.unknown');
        $driverHint = '';
        if ($driver === 'sqlite' && $memory) {
            $driverLabel = admin_t('ui.driver_sqlite_mem');
            $driverHint = admin_t('ui.sql_hint_mem');
        } elseif ($driver === 'sqlite') {
            $driverLabel = admin_t('ui.driver_sqlite_file');
            $driverHint = admin_t('ui.sql_hint_sqlite');
        } elseif ($driver === 'mysql') {
            $driverLabel = 'MySQL';
            $driverHint = admin_t('ui.sql_hint_mysql');
        } elseif ($driver === 'mariadb') {
            $driverLabel = 'MariaDB';
            $driverHint = admin_t('ui.sql_hint_maria');
        } else {
            $driverLabel = $driver !== '' ? $driver : admin_t('ui.unknown');
            $driverHint = admin_t('ui.sql_hint_unknown');
        }

        return [
            'driver' => $driver,
            'driver_label' => $driverLabel,
            'driver_hint' => $driverHint,
            'can_delete' => $this->isFirstAdmin(),
            'examples' => $this->examples($driver),
            'ui' => [
                'title' => admin_t('page.db_sql'),
                'lead' => admin_t('ui.sql_lead', ['word' => $word]),
                'note' => admin_t('ui.sql_note'),
                'now' => admin_t('ui.bak_restore_now'),
                'stmt' => admin_t('ui.sql_stmt'),
                'placeholder' => admin_t('ui.sql_ph'),
                'examples' => admin_t('ui.sql_examples'),
                'run' => $word,
                'clear' => admin_t('ui.clear'),
                'result' => admin_t('ui.result'),
                'idle' => admin_t('ui.sql_idle'),
                'idle_hint' => admin_t('ui.sql_idle_hint'),
                'backup' => admin_t('ui.tab_backup'),
                'restore' => admin_t('ui.restore'),
                'replace' => admin_t('page.db_replace'),
                'dict' => admin_t('page.db_dict'),
                'confirm_write' => admin_t('ui.sql_confirm_write', ['word' => $word]),
                'type_hint' => admin_t('ui.sql_type_hint', ['word' => $word]),
                'type_err' => admin_t('ui.sql_type_err'),
                'word' => $word,
                'empty_rows' => admin_t('ui.sql_empty_rows'),
                'busy' => admin_t('ui.sql_busy'),
                'need_sql' => admin_t('ui.sql_need_stmt'),
            ],
        ];
    }

    public function run(string $sql, string $word = ''): array
    {
        $sql = trim($sql);
        if ($sql === '') {
            return Result::fail(admin_t('ui.sql_need_stmt'));
        }
        if (mb_strlen($sql, 'UTF-8') > self::MAX_SQL_CHARS) {
            return Result::fail(admin_t('ui.sql_too_long', ['n' => self::MAX_SQL_CHARS]));
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
            return Result::fail(admin_t('ui.sql_need_stmt'));
        }
        if (count($statements) !== 1) {
            return Result::fail(admin_t('ui.sql_one_only'));
        }

        $statement = $this->stripLeadingComments($statements[0]);
        if ($statement === '') {
            return Result::fail(admin_t('ui.sql_no_stmt'));
        }

        foreach (self::FORBIDDEN_SNIPS as $snip) {
            if (stripos($statement, $snip) !== false) {
                return Result::fail(admin_t('ui.sql_forbidden_snip'));
            }
        }

        $keyword = $this->firstKeyword($statement);
        if ($keyword === '') {
            return Result::fail(admin_t('ui.sql_unknown_stmt'));
        }
        if (in_array($keyword, self::FORBIDDEN_WORDS, true)) {
            return Result::fail(admin_t('ui.sql_no_ddl'));
        }

        $isQuery = in_array($keyword, self::QUERY_WORDS, true);
        $isWrite = in_array($keyword, self::WRITE_WORDS, true);
        if (! $isQuery && ! $isWrite) {
            return Result::fail(admin_t('ui.sql_only_dml'));
        }

        if ($keyword === 'delete' && ! $this->isFirstAdmin()) {
            return Result::fail(admin_t('ui.sql_delete_admin'));
        }

        if ($isWrite) {
            if (trim($word) !== admin_t('ui.sql_word')) {
                return Result::fail(admin_t('ui.sql_typed_wrong', ['word' => admin_t('ui.sql_word')]));
            }
        }

        if ($isQuery && in_array($keyword, ['select', 'with'], true)) {
            $needsFrom = preg_match('/\b(from|join)\b/i', $statement) === 1;
            $limit = $this->trailingLimit($statement);
            if ($needsFrom && $limit === null) {
                return Result::fail(admin_t('ui.sql_need_limit', ['n' => self::MAX_LIMIT]));
            }
            if ($limit !== null && $limit > self::MAX_LIMIT) {
                return Result::fail(admin_t('ui.sql_limit_max', ['n' => self::MAX_LIMIT]));
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
                    ? admin_t('ui.sql_query_0', ['ms' => $elapsed])
                    : admin_t('ui.sql_query_n', ['n' => $count, 'ms' => $elapsed]);

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
            ], admin_t('ui.sql_affect', ['n' => $n, 'ms' => $elapsed]));
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : admin_t('ui.sql_run_fail'));
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
                    'label' => admin_t('ui.sql_ex_videos'),
                    'sql' => 'SELECT id, title, status FROM videos ORDER BY id DESC LIMIT 10',
                    'hint' => admin_t('ui.sql_ex_videos_hint'),
                ];
            }
            if (Schema::hasTable('video_episodes')) {
                $out[] = [
                    'id' => 'episodes',
                    'label' => admin_t('ui.sql_ex_episodes'),
                    'sql' => 'SELECT id, video_id, name, url FROM video_episodes ORDER BY id DESC LIMIT 10',
                    'hint' => admin_t('ui.sql_ex_episodes_hint'),
                ];
            }
            if (Schema::hasTable('video_arts')) {
                $out[] = [
                    'id' => 'arts',
                    'label' => admin_t('ui.sql_ex_arts'),
                    'sql' => 'SELECT id, title, status FROM video_arts ORDER BY id DESC LIMIT 10',
                    'hint' => admin_t('ui.sql_ex_arts_hint'),
                ];
            }
        } catch (\Throwable) {
        }

        if ($driver === 'sqlite') {
            $out[] = [
                'id' => 'tables',
                'label' => admin_t('ui.sql_ex_tables'),
                'sql' => "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name LIMIT 50",
                'hint' => admin_t('ui.sql_ex_tables_hint_sqlite'),
            ];
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            $out[] = [
                'id' => 'tables',
                'label' => admin_t('ui.sql_ex_tables'),
                'sql' => 'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name LIMIT 50',
                'hint' => admin_t('ui.sql_ex_tables_hint_mysql'),
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
