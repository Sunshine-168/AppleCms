<?php

namespace Utils;

use Illuminate\Support\Facades\DB;

class Database
{
    private $fp = null;
    private array $file;
    private int $size = 0;
    private array $config;

    public function __construct(array $file, array $config)
    {
        $this->file = $file;
        $this->config = $config;
    }

    public function create(): bool
    {
        $connection = config('database.connections.mysql', []);
        $sql = "-- -----------------------------\n";
        $sql .= "-- Laravel MySQL Data Transfer\n";
        $sql .= "-- Host     : " . ($connection['host'] ?? '127.0.0.1') . "\n";
        $sql .= "-- Port     : " . ($connection['port'] ?? '3306') . "\n";
        $sql .= "-- Database : " . ($connection['database'] ?? '') . "\n";
        $sql .= "-- Part     : #{$this->file['part']}\n";
        $sql .= "-- Date     : " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        return $this->write($sql);
    }

    public function backup(string $table, int $start)
    {
        if ($start === 0) {
            $result = DB::select("SHOW CREATE TABLE `{$table}`");
            $row = (array) ($result[0] ?? []);
            $createSql = $row['Create Table'] ?? array_values($row)[1] ?? null;
            if (!$createSql) {
                return false;
            }

            $sql = "\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= trim((string) $createSql) . ";\n\n";

            if ($this->write($sql) === false) {
                return false;
            }
        }

        $countRow = (array) (DB::select("SELECT COUNT(*) AS count FROM `{$table}`")[0] ?? []);
        $count = (int) ($countRow['count'] ?? 0);
        if ($count < 1) {
            return 0;
        }

        if ($start === 0) {
            $this->write("-- -----------------------------\n-- Records of `{$table}`\n-- -----------------------------\n");
        }

        $rows = DB::select("SELECT * FROM `{$table}` LIMIT {$start}, 1000");
        foreach ($rows as $row) {
            $values = [];
            foreach ((array) $row as $value) {
                $values[] = $this->quoteValue($value);
            }
            $sql = "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
            if ($this->write($sql) === false) {
                return false;
            }
        }

        if ($count > $start + 1000) {
            return [$start + 1000, $count];
        }

        return 0;
    }

    public function import(int $start)
    {
        $filename = $this->file[1];
        $compressed = (bool) ($this->config['compress'] ?? false);
        $handle = $compressed ? gzopen($filename, 'r') : fopen($filename, 'r');
        if (!$handle) {
            return false;
        }

        $size = $compressed ? 0 : (filesize($filename) ?: 0);
        $sql = '';

        if ($start > 0) {
            $compressed ? gzseek($handle, $start) : fseek($handle, $start);
        }

        for ($i = 0; $i < 1000; $i++) {
            $line = $compressed ? gzgets($handle) : fgets($handle);
            if ($line === false) {
                return 0;
            }

            $sql .= $line;
            if (preg_match('/.*;\s*$/', trim($sql))) {
                try {
                    DB::unprepared($sql);
                    $start += strlen($sql);
                } catch (\Throwable $e) {
                    return false;
                }
                $sql = '';
            } elseif ($compressed ? gzeof($handle) : feof($handle)) {
                return 0;
            }
        }

        return [$start, $size];
    }

    public function __destruct()
    {
        if (is_resource($this->fp)) {
            if ((bool) ($this->config['compress'] ?? false)) {
                @gzclose($this->fp);
            } else {
                @fclose($this->fp);
            }
        }
    }

    private function open(float $size): void
    {
        if ($this->fp) {
            $this->size += (int) $size;
            if ($this->size > (int) $this->config['part']) {
                (bool) $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
                $this->fp = null;
                $this->file['part']++;
                $this->create();
            }
            return;
        }

        $filename = rtrim($this->config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$this->file['name']}-{$this->file['part']}.sql";
        if ((bool) $this->config['compress']) {
            $filename .= '.gz';
            $this->fp = @gzopen($filename, 'a' . (int) $this->config['level']);
        } else {
            $this->fp = @fopen($filename, 'a');
        }

        $this->size = (file_exists($filename) ? (int) filesize($filename) : 0) + (int) $size;
    }

    private function write(string $sql): bool
    {
        $size = strlen($sql);
        if ((bool) $this->config['compress']) {
            $size = (int) ceil($size / 2);
        }

        $this->open($size);
        if (!$this->fp) {
            return false;
        }

        return ((bool) $this->config['compress'])
            ? @gzwrite($this->fp, $sql) !== false
            : @fwrite($this->fp, $sql) !== false;
    }

    private function quoteValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $value = str_replace(["\\", "'", "\r", "\n"], ["\\\\", "\\'", '', ''], (string) $value);

        return "'" . $value . "'";
    }
}
