<?php
namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Utils\Syslog;
use Throwable;

trait DualWriteTableTrait
{
    protected static array $tableCache = [];

    /**
     * 双写数据（带调试）
     *
     * @param string $baseTable 基础表名
     * @param array $data 写入数据
     * @param int|null $uid 用户ID
     * @param bool $debug 是否打印调试信息
     */
    protected  function dualInsert(string $baseTable, array $data, ?int $uid = null, bool $debug = false): bool
    {
        $conf   = Config::get("split_table.{$baseTable}");
        $bTable = $conf['b_table'] ?? $baseTable;

        try {
            // 写 b 表总表
            Db::name($bTable)->insert($data);


            // 写 c 表哈希分表（有 UID 才写）
            if ($uid !== null && isset($conf['total']) && $conf['total'] > 0)
            {

                $hashTable = $this->getHashTable($baseTable, $uid, true, $conf);

                Db::name($hashTable)->insert($data);

            }

        } catch (Throwable $e) {
            Syslog::log("repository", "DualInsert error: {$e->getMessage()}");

            return false;
        }

        return true;
    }

    /**
     * 获取哈希分表
     *
     * @param string $baseTable
     * @param int $uid
     * @param bool $autoCreate
     * @param array|null $conf 配置，可传入避免重复读取
     * @return string
     */
    protected  function getHashTable(string $baseTable, int $uid, bool $autoCreate = true, ?array $conf = null): string
    {
        $conf   = $conf ?? Config::get("split_table.{$baseTable}");
        $total  = $conf['total'] ?? 12;
        $prefix = $conf['prefix'] ?? "{$baseTable}_";

        $idx   = $uid % $total;
        $table = "{$prefix}{$idx}";

        if ($autoCreate && !$this->_tableExists($table))
        {
            $this->_createTable($conf['b_table'] ?? $baseTable, $table);
        }

        return $table;
    }

    /**
     * 检查表是否存在
     */
    protected function _tableExists(string $table): bool
    {
        // 1️⃣ 内存缓存
        if (isset(self::$tableCache[$table]))
        {
            return self::$tableCache[$table];
        }

        // 2️⃣ 文件缓存
        $cacheKey = "table_exists_{$table}";
        $exists   = Cache::store('file')->get($cacheKey);

        if ($exists !== null)
        {
            self::$tableCache[$table] = (bool)$exists;
            return (bool)$exists;
        }

        // 3️⃣ 数据库查询
        $res    = Db::query("SHOW TABLES LIKE '{$table}'");
        $exists = !empty($res);

        // 4️⃣ 更新缓存
        self::$tableCache[$table] = $exists;

        Cache::store('file')->set($cacheKey, $exists, 0);

        return $exists;
    }

    /**
     * 创建表
     */
    protected function _createTable(string $baseTable, string $newTable): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$newTable}` LIKE `{$baseTable}`";

        Db::execute($sql);

        // 表创建成功后，更新文件缓存
        Cache::store('file')->set("table_exists_{$newTable}", true, 0);
    }
}
