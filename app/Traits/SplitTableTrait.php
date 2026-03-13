<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * 通用分表 Trait
 * 支持：按用户哈希分表（如128张），可扩展
 */
trait SplitTableTrait
{
    /**
     * 获取模型基础表名（不带后缀）
     */
    protected function getOriginTableName(): string
    {
        return $this->name;
    }

    /**
     * 检查表是否存在
     */
    protected static function tableExists(string $table): bool
    {
        $result = Db::query("SHOW TABLES LIKE '{$table}'");
        return !empty($result);
    }

    /**
     * 自动创建分表（结构与原表一致）
     * @param string $baseTable 原表名
     * @param string $newTable  新表名
     */
    protected static function createTable(string $baseTable, string $newTable): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$newTable}` LIKE `{$baseTable}`";
        Db::execute($sql);
    }

    /**
     * 按哈希分表（例如128张表）
     * @param int $id 用户ID（或其他分片ID）
     * @param int $mod 分表数量（默认128）
     * @param bool $autoCreate 是否自动创建表
     * @return static
     */
    public static function hashTable(int $id, int $mod = 128, bool $autoCreate = false): static
    {
        $instance       = new static();
        $baseTable      = $instance->getOriginTableName();
        $suffix         = $id % $mod;
        $instance->name = "{$baseTable}_{$suffix}";

        if ($autoCreate && !self::tableExists($instance->name))
        {
            self::createTable($baseTable, $instance->name);
        }

        return $instance;
    }

    /**
     * 按哈希规则批量查询所有分表（例如跨128张）
     * @param array $where 查询条件
     * @param string $order 排序字段
     * @param int $mod 分表数量
     * @param int|null $limit 限制数量
     * @return array
     */
    public static function hashRangeQuery(array $where = [], string $order = 'id desc', int $mod = 128, ?int $limit = null): array
    {
        $instance  = new static();
        $baseTable = $instance->getOriginTableName();
        $allData   = [];

        for ($i = 0; $i < $mod; $i++)
        {
            $table = "{$baseTable}_{$i}";

            if (!self::tableExists($table))
            {
                continue;
            }

            $query = Db::name($table)->where($where)->order($order);

            if ($limit)
            {
                $query->limit($limit);
            }

            $data    = $query->select()->toArray();
            $allData = array_merge($allData, $data);
        }

        // 跨表整体排序
        usort($allData, fn($a, $b) => $b['create_time'] <=> $a['create_time']);

        if ($limit)
        {
            $allData = array_slice($allData, 0, $limit);
        }

        return $allData;
    }
}
