<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait MonthTableTrait
{
    /**
     * 按月分表
     * @param string|int $time
     * @param bool $autoCreate 是否自动创建分表
     * @return static
     */
    public static function monthTable(string|int $time, bool $autoCreate = false): static
    {
        $instance       = new static();
        $month          = is_numeric($time) ? date('Ym', $time) : date('Ym', strtotime($time));

        $baseTable      = $instance->getOriginTableName();
        $instance->name = "{$baseTable}_{$month}";

        // 自动创建分表
        if ($autoCreate && !self::tableExists($instance->name))
        {
            self::createTable($baseTable, $instance->name);
        }

        return $instance;
    }

    /**
     * 获取模型基础表名（不带月份后缀）
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
     * 跨月查询多个分表并合并结果
     * @param int|string $start 开始时间（日期字符串或时间戳）
     * @param int|string $end   结束时间（日期字符串或时间戳）
     * @param array $where 查询条件
     * @param string $order 排序字段
     * @param int|null $limit 限制数量（可选）
     * @return array
     */
    public static function rangeQuery(int|string $start, int|string $end, array $where = [], string $order = 'id desc', ?int $limit = null): array
    {
        $startTime = is_numeric($start) ? $start : strtotime($start);
        $endTime   = is_numeric($end) ? $end : strtotime($end);

        // 计算时间范围内所有月份
        $months   = [];
        $current  = strtotime(date('Y-m-01', $startTime));
        $endMonth = strtotime(date('Y-m-01', $endTime));

        while ($current <= $endMonth)
        {
            $months[] = date('Ym', $current);
            $current  = strtotime('+1 month', $current);
        }

        $allData = [];

        // 获取模型原表名
        $instance  = new static();
        $baseTable = $instance->getOriginTableName();

        foreach ($months as $month)
        {
            $table = "{$baseTable}_{$month}";

            if (!self::tableExists($table))
            {
                continue; // 跳过不存在的分表
            }

            $query = Db::name($table)->where($where)->order($order);

            if ($limit)
            {
                $query->limit($limit);
            }

            $data    = $query->select()->toArray();
            $allData = array_merge($allData, $data);
        }

        // 统一排序（防止跨表时间顺序错乱）
        usort($allData, fn($a, $b) => $b['create_time'] <=> $a['create_time']);

        if ($limit)
        {
            $allData = array_slice($allData, 0, $limit);
        }

        return $allData;
    }

    /**
     * 跨月查询多个分表（UNION ALL，限制最多3张表）
     */
    public static function rangeQueryUnion(int|string $start, int|string $end, array $where = [], string $order = 'id desc', ?int $limit = null): array
    {
        $startTime = is_numeric($start) ? $start : strtotime($start);
        $endTime   = is_numeric($end) ? $end : strtotime($end);

        // 计算月份
        $months   = [];
        $current  = strtotime(date('Y-m-01', $startTime));
        $endMonth = strtotime(date('Y-m-01', $endTime));

        while ($current <= $endMonth)
        {
            $months[] = date('Ym', $current);
            $current  = strtotime('+1 month', $current);
        }

        // 限制最多 3 张表
        $months     = array_slice($months, 0, 3);

        $instance   = new static();
        $baseTable  = $instance->getOriginTableName();

        $sqlParts   = [];

        foreach ($months as $month)
        {
            $table = "{$baseTable}_{$month}";

            if (!self::tableExists($table))
            {
                continue;
            }

            $whereSql = [];

            foreach ($where as $k => $v)
            {
                $val            = is_numeric($v) ? $v : "'{$v}'";
                $whereSql[]     = "`{$k}` = {$val}";
            }

            $whereClause = $whereSql ? 'WHERE ' . implode(' AND ', $whereSql) : '';
            $sqlParts[]  = "SELECT * FROM `{$table}` {$whereClause}";
        }

        if (empty($sqlParts))
        {
            return [];
        }

        // 使用 UNION ALL 合并
        $sql = implode(' UNION ALL ', $sqlParts) . " ORDER BY {$order}";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $data = Db::query($sql);
        return $data ?: [];
    }
}
