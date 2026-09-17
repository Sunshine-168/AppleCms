<?php
namespace App\Support;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ReflectionFunction;
use App\Support\Utils\Syslog;

/**
 * 通用查询 Trait
 */
trait QueryTrait
{
    protected array $afterCommitCallbacks   = [];
    protected array $afterRollbackCallbacks = [];

    // ------------------------------
    // 条件解析
    // ------------------------------
    protected function isSimpleCondition($condition): bool
    {
        return is_array($condition)
            && count($condition) === 3
            && isset($condition[0], $condition[1])
            && is_string($condition[0])
            && is_string($condition[1]);
    }

    protected function parseCondition($query, array $condition): void
    {
        // 兼容单条简单条件
        if ($this->isSimpleCondition($condition)) {
            $condition = [$condition];
        }

        foreach ($condition as $key => $item) {

            if ($key === 'or' && is_array($item)) {
                $query->where(function($q) use ($item) {
                    foreach ($item as $orItem) {
                        if ($this->isSimpleCondition($orItem)) {
                            $q->orWhere(...$orItem);
                        }
                    }
                });
                continue;
            }

            if ($this->isSimpleCondition($item)) {
                $field = $item[0];
                $operator = strtolower((string) $item[1]);
                $value = $item[2];

                if ($operator === 'in' && is_array($value)) {
                    $query->whereIn($field, $value);
                    continue;
                }

                if (($operator === 'not in' || $operator === 'notin') && is_array($value)) {
                    $query->whereNotIn($field, $value);
                    continue;
                }

                if ($operator === 'between' && is_array($value)) {
                    $query->whereBetween($field, $value);
                    continue;
                }

                if (($operator === 'not between' || $operator === 'notbetween') && is_array($value)) {
                    $query->whereNotBetween($field, $value);
                    continue;
                }

                $query->where($field, $item[1], $value);
                continue;
            }

            if (is_array($item)) {
                if (isset($item['in'])) {
                    $query->whereIn($key, $item['in']);
                } elseif (isset($item['between'])) {
                    $query->whereBetween($key, $item['between']);
                } elseif (isset($item['like'])) {
                    $query->where($key, 'like', $item['like']);
                }
            } else {
                if (!is_numeric($key)) {
                    $query->where($key, '=', $item);
                }
            }
        }
    }

    protected function parseOrder($query, array|string $order): void
    {
        if (empty($order)) {
            return;
        }
        if (is_string($order)) {
            $query->orderByRaw($order);
        } elseif (is_array($order)) {
            foreach ($order as $column => $direction) {
                if (is_int($column)) {
                    $query->orderByRaw($direction);
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        }
    }

    // ------------------------------
    // 基础查询方法
    // ------------------------------
    public function findById(int $id, string|array $field='*', bool $master=false, bool $lock=false): array
    {
        try {
            $query = $this/*->master($master)*/->when($lock, function($q){ $q->lockForUpdate(); })->where($this->getKeyName(), $id)->select($field);
            $info = $query->first();
            return $info ? $info->toArray() : [];
        } catch (Exception $e) {
            Syslog::exception('repository', $e,'QueryTrait->findById');
            return [];
        }
    }

    public function findByUid(int $uid, string|array $field='*', array|string $order=[], bool $master=false, bool $lock=false): array
    {
        try {
            $query = $this/*->master($master)*/->when($lock, function($q){ $q->lockForUpdate(); })->where('uid', $uid)->select($field);
            if (!empty($order)) $this->parseOrder($query, $order);
            $info = $query->first();
            return $info ? $info->toArray() : [];
        } catch (Exception $e) {
            Syslog::exception('repository', $e,'QueryTrait->findByUid');
            return [];
        }
    }

    public function findByCondition(array $condition, string|array $field ='*', array|string $order = [], bool $master=false, bool $lock=false): array
    {
        try {
            empty($order) && $order = [$this->getKeyName()=>'desc'];

            $query = $this/*->master($master)*/->when($lock, function($q){ $q->lockForUpdate(); });
            $this->parseCondition($query, $condition);
            $this->parseOrder($query, $order);
            $info = $query->select($field)->first();
            return $info ? $info->toArray() : [];
        } catch (Exception $e) {
            Syslog::exception('repository', $e,'QueryTrait->findByCondition');
            return [];
        }
    }

    public function findByWhereOr(array $groups, string|array $field = '*', array|string $order = [], bool $master = false, bool $lock = false): array
    {
        try {
            empty($order) && $order = [$this->getKeyName() => 'desc'];

            $query = $this/*->master($master)*/->when($lock, function($q){ $q->lockForUpdate(); });
            $query->where(function ($q) use ($groups) {
                foreach ($groups as $group) {
                    if (!is_array($group) || empty($group)) {
                        continue;
                    }

                    $q->orWhere(function ($orQ) use ($group) {
                        $this->parseCondition($orQ, $group);
                    });
                }
            });

            $this->parseOrder($query, $order);
            $info = $query->select($field)->first();
            return $info ? $info->toArray() : [];
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->findByWhereOr');
            return [];
        }
    }

    public function selectByCondition(array $condition = [], string|array $field = '*', array|string $order = [], bool $master = false): array
    {
        try {
            empty($order) && $order = [$this->getKeyName()=>'desc'];
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);
            $this->parseOrder($query, $order);
            $info = $query->select($field)->get();
            return $info->isEmpty() ? [] : $info->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e,'QueryTrait->selectByCondition');
            return [];
        }
    }

    // ------------------------------
    // 分页查询（支持可选分表表名）
    // ------------------------------
    public function limits(array $condition = [], string|array $field = '*', array|string $order = [], int $offset = 0, int $limit = 10, bool $master = false, ?string $table = null): array
    {
        try {
            empty($order) && $order = [$this->getKeyName() => 'desc'];

            $query = $this->newQuery()/*->master($master)*/;
            if ($table) $query->from($table);

            $this->parseCondition($query, $condition);
            $this->parseOrder($query, $order);
            $info = $query->select($field)->offset($offset)->limit($limit)->get();

            return $info->isEmpty() ? [] : $info->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->limits');
            return [];
        }
    }

    public function paginates(array $where = [], string|array $field = '*', int $limit = 10, array|string $order = [], bool $master = false, ?string $table = null): array
    {
        try {
            empty($order) && $order = [$this->getKeyName() => 'desc'];

            $query =  $this->newQuery()/*->master($master)*/;
            if ($table) $query->from($table);
            $this->parseCondition($query, $where);
            $this->parseOrder($query, $order);

            $page = max(1, (int) request()->input('page', 1));
            $data = $query->select($field)->paginate($limit, ['*'], 'page', $page);

            return $data->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->paginates');
            return [];
        }
    }

    // ------------------------------
    // 分表分页查询（调用 limits/paginates）
    // ------------------------------
    public function limitsShard(array $condition, int|string $shardKey, int $shardNum, string|array $field = '*', array|string $order = [], int $offset = 0, int $limit = 10, bool $master = false): array
    {
        $tableName = $this->tableShard($shardKey, $shardNum);
        return $this->limits($condition, $field, $order, $offset, $limit, $master, $tableName);
    }

    public function paginatesShard(array $condition, int|string $shardKey, int $shardNum, string|array $field = '*', int $limit = 10, array|string $order = [], bool $master = false): array
    {
        $tableName = $this->tableShard($shardKey, $shardNum);
        return $this->paginates($condition, $field, $limit, $order, $master, $tableName);
    }

    // ------------------------------
    // 插入/更新/删除
    // ------------------------------
    public function inserts(array $data): int
    {
        try {
            $res = $this->insert($data);
            return $res ? 1 : 0;
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->inserts');
            return 0;
        }
    }

    public function insertsGetId(array $data): bool|int
    {
        try {
            $res = $this->insertGetId($data);
            return empty($res) ? false : $res;
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->insertsGetId');
            return false;
        }
    }

    public function insertsAll(array $data): bool
    {
        try {
            $res = $this->insert($data);
            return !empty($res);
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->insertAll');
            return false;
        }
    }

    /**
     * 兼容旧代码命名（TP 风格）。
     */
    public function insertAll(array $data): bool
    {
        return $this->insertsAll($data);
    }

    public function store(array $data): bool
    {
        try {
            $this->fill($data);
            return $this->save();
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->store');
            return false;
        }
    }

    public function updateById(int $id, array $data): bool
    {
        try {
            $res = $this->where($this->getKeyName(),$id)->update($data);
            return $res!==false;
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->updateById');
            return false;
        }
    }


    public function deleteById(int|string $id): bool
    {
        try {
            $res = $this->where($this->getKeyName(),$id)->delete();
            return !empty($res);
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->deleteById');
            return false;
        }
    }



    // ------------------------------
    // 值/统计/聚合
    // ------------------------------
    public function valueById(int $id, string|array $field = '*', bool $master = false): mixed
    {
        try {
            return $this/*->master($master)*/->where($this->getKeyName(),$id)->value($field) ?: '';
        } catch (Exception $e) {
            Syslog::exception('repository',$e,'QueryTrait->valueById');
            return '';
        }
    }



    public function updateByCondition(array $condition, array $data): bool
    {
        try {
            $query = $this->newQuery()/*->master(true)*/;
            $this->parseCondition($query, $condition);
            $res = $query->update($data);
            return $res !== false;
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->updateByCondition');
            return false;
        }
    }

    public function deleteByCondition(array $condition): bool
    {
        try {
            $query = $this->newQuery()/*->master(true)*/;
            $this->parseCondition($query, $condition);
            $res = $query->delete();
            return !empty($res);
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->deleteByCondition');
            return false;
        }
    }

    public function valueByCondition(array $condition, string $field = '', bool $master=false): mixed
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);
            return $query->value($field) ?: '';
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->valueByCondition');
            return '';
        }
    }

    public function countByCondition(array $condition, bool $master=false): int
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);
            return $query->count();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->countByCondition');
            return 0;
        }
    }

    public function sumByCondition(array $condition=[], string $field='', bool $master=false): float
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);
            return $query->sum($field) ?: 0.0;
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->sumByCondition');
            return 0.0;
        }
    }

    public function uniqueColumnByCondition(array $condition, string $field='', bool $master=false): array
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);
            return $query->distinct()->pluck($field)->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->uniqueColumnByCondition');
            return [];
        }
    }


    /**
     * 查询单列或多列数据
     * @param array $condition 查询条件
     * @param string|array $columns 字段名或字段数组
     * @param bool $master 是否走主库
     * @return array
     */
    public function columnByCondition(array $condition, string|array $columns, bool $master = false): array
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);

            if (is_array($columns)) {
                return $query->get($columns)->toArray();
            }
            if (str_contains($columns, ',')) {
                return $query->selectRaw($columns)->get()->toArray();
            }
            return $query->pluck($columns)->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->columnByCondition');
            return [];
        }
    }

    /**
     * 分组聚合查询
     * @param array $condition 查询条件
     * @param string|array $columns 聚合字段 eg: ['status', 'COUNT(id) as total']
     * @param string|array $groupBy 分组字段
     * @param string|array $order 排序
     * @param bool $master 是否走主库
     * @return array
     */
    public function groupByCondition(array $condition, string|array $columns, string|array|null $groupBy = null, array|string $order = [], bool $master = false): array
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            $this->parseCondition($query, $condition);
            // TP8 compatibility: allow 2-arg usage groupByCondition($where, 'uid')
            // and return a flat unique column list.
            if ($groupBy === null) {
                if (is_string($columns) && !str_contains($columns, ',')) {
                    return $query->distinct()->pluck($columns)->toArray();
                }

                $res = $query->select($columns)->distinct()->get();
                return $res->isEmpty() ? [] : $res->toArray();
            }

            $query->select($columns)->groupBy($groupBy);

            $this->parseOrder($query, $order);

            $res = $query->get();
            return $res->isEmpty() ? [] : $res->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->groupByCondition');
            return [];
        }
    }

    // ------------------------------
    // 分表
    // ------------------------------
    public function tableShard(?string $key=null, ?int $shardNum=null): string
    {
        $table = $this->getTable();
        if ($key !== null && $shardNum>1) {
            $hash = crc32((string)$key);
            $table .= '_'.($hash%$shardNum);
        }
        return $table;
    }

    public function getTables(): array
    {
        $pattern = $this->name . '_%';
        $pattern = addslashes($pattern);
        $tables  = DB::select("SHOW TABLES LIKE '{$pattern}'");
        $result = [];
        foreach ($tables as $t) {
            $tableName = array_values((array)$t)[0];
            if (preg_match('/^'.$this->name.'_(\d{6})$/',$tableName,$matches)){
                $result[] = $matches[1];
            }
        }
        return $result;
    }

    protected function parseJoins($query, array $joins)
    {
        foreach ($joins as $join) {
            $type = $join['type'] ?? 'left';
            $table = $join['table'];
            $alias = $join['alias'] ?? null;
            $tableName = $alias ? "{$table} as {$alias}" : $table;
            $on = $join['on'];

            $query->join($tableName, function($joinClause) use ($on) {
                $joinClause->whereRaw($on);
            }, null, null, $type);
        }
    }

    // ------------------------------
    // Join 查询（支持分表）
    // ------------------------------
    public function selectWithJoin(array $joins, array $condition = [], array|string $fields = '*', array|string $order = [], bool $master = false, ?string $table = null): array
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            if ($table) $query->from($table);

            $query->select($fields);

            $this->parseJoins($query, $joins);

            $this->parseCondition($query, $condition);
            $this->parseOrder($query, $order);

            $res = $query->get();
            return $res->isEmpty() ? [] : $res->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->selectWithJoin');
            return [];
        }
    }

    // ------------------------------
    // Join + LIMIT 查询（普通）
    // ------------------------------
    public function joinLimits(array $joins, array $condition = [], array|string $fields = '*', array|string $order = [], int $offset = 0, int $limit = 10, bool $master = false, ?string $table = null): array
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            if ($table) $query->from($table);

            $query->select($fields);

            $this->parseJoins($query, $joins);

            $this->parseCondition($query, $condition);
            $this->parseOrder($query, $order);

            $res = $query->offset($offset)->limit($limit)->get();
            return $res->isEmpty() ? [] : $res->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->joinLimits');
            return [];
        }
    }

    // ------------------------------
    // Join + 分页查询（普通）
    // ------------------------------
    public function joinPaginates(array $joins, array $condition = [], array|string $fields = '*', array|string $order = [], int $limit = 10, bool $master = false, ?string $table = null): array
    {
        try {
            $query = $this->newQuery()/*->master($master)*/;
            if ($table) $query->from($table);

            $query->select($fields);

            $this->parseJoins($query, $joins);

            $this->parseCondition($query, $condition);
            $this->parseOrder($query, $order);

            $data = $query->paginate($limit);
            return $data->toArray();
        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->joinPaginates');
            return [];
        }
    }

    // ------------------------------
    // Join + Shard 查询（普通 select）
    // ------------------------------
    public function selectWithJoinShard(array $joins, int|string $shardKey, int $shardNum, array $condition = [], array|string $fields = '*', array|string $order = [], bool $master = false): array
    {
        $tableName = $this->tableShard($shardKey, $shardNum);
        return $this->selectWithJoin($joins, $condition, $fields, $order, $master, $tableName);
    }

    // ------------------------------
    // Join + Shard + LIMIT 查询
    // ------------------------------
    public function joinLimitsShard(array $joins, int|string $shardKey, int $shardNum, array $condition = [], array|string $fields = '*', array|string $order = [], int $offset = 0, int $limit = 10, bool $master = false): array
    {
        $tableName = $this->tableShard($shardKey, $shardNum);
        return $this->joinLimits($joins, $condition, $fields, $order, $offset, $limit, $master, $tableName);
    }

    // ------------------------------
    // Join + Shard + 分页查询
    // ------------------------------
    public function joinPaginatesShard(array $joins, int|string $shardKey, int $shardNum, array $condition = [], array|string $fields = '*', array|string $order = [], int $limit = 10, bool $master = false): array
    {
        $tableName = $this->tableShard($shardKey, $shardNum);
        return $this->joinPaginates($joins, $condition, $fields, $order, $limit, $master, $tableName);
    }

    // ------------------------------
    // 基础方法
    // ------------------------------
    public function getPk(): string
    {
        return property_exists($this,'pk')?$this->pk:'id';
    }

    public function getTable(): string
    {
        return property_exists($this,'table')?$this->table: parent::getTable();
    }


    // ------------------------------
    // 事务封装
    // ------------------------------
    /**
     * @throws Exception
     */
    public function transaction(callable $callback): mixed
    {
        DB::beginTransaction();
        try {

            $result = $callback();

            DB::commit();

            foreach ($this->afterCommitCallbacks as $cb)
            {
                try {
                    $cb($result);
                } catch (Exception $e)
                {
                    Syslog::exception('transaction', $e, 'afterCommit');
                }
            }

            return $result;
        } catch (Exception $e)
        {

            DB::rollBack();

            foreach ($this->afterRollbackCallbacks as $cb)
            {
                try {

                    $cb($e);

                } catch (Exception $ee)
                {
                    Syslog::exception('transaction', $ee, 'afterRollback');
                }
            }

            throw $e;
        } finally {
            $this->afterCommitCallbacks = [];
            $this->afterRollbackCallbacks = [];
        }
    }

    public function afterCommit(callable $callback): void
    {
        $this->afterCommitCallbacks[] = $callback;
    }

    public function afterRollback(callable $callback): void
    {
        $this->afterRollbackCallbacks[] = $callback;
    }

    // ------------------------------
    // 判断存在
    // ------------------------------
    public function existsBy(array $condition, bool $master=false): bool
    {
        $query = $this->newQuery()/*->master($master)*/;
        $this->parseCondition($query, $condition);
        return $query->limit(1)->first() !== null;
    }



    // ------------------------------
    // 批量 UPSERT
    // ------------------------------
    public function upsertBatch(array $rows, array $updateFields=[], int $batchSize=500): int
    {
        if (empty($rows)) return 0;
        $table = $this->getTable();
        $fields = array_keys($rows[0]);
        if (empty($updateFields)) $updateFields = array_diff($fields, [$this->getKeyName()]);
        if (empty($updateFields)) return 0;

        $total = 0;
        foreach (array_chunk($rows, $batchSize) as $chunk) {
            $columns = implode(',', array_map(fn($f)=>"`$f`",$fields));
            $valuesSql = []; $bind = [];
            foreach ($chunk as $i=>$row) {
                $placeholders = [];
                foreach ($fields as $field) {
                    $key = "{$field}_{$i}";
                    $placeholders[] = ":{$key}";
                    $bind[$key] = $row[$field] ?? null;
                }
                $valuesSql[] = '('.implode(',', $placeholders).')';
            }
            $updateSql = implode(',', array_map(fn($f)=>"`$f`=VALUES(`$f`)",$updateFields));
            $sql = "INSERT INTO `{$table}` ($columns) VALUES ".implode(',', $valuesSql)." ON DUPLICATE KEY UPDATE $updateSql";
            $total += DB::affectingStatement($sql, $bind);
        }
        return $total;
    }

    public function upsert(array $data, array $updateFields=[]): int
    {
        return $this->upsertBatch([$data], $updateFields);
    }


    // ------------------------------
    // 自增/自减
    // ------------------------------
    public function incByCondition(array $condition, string $field, int|float $step=1): bool
    {
        $query = $this->master(true);
        $this->parseCondition($query, $condition);
        return $query->update([$field => Db::raw("{$field}+{$step}")]) !== false;
    }

    public function decByCondition(array $condition, string $field, int|float $step=1): bool
    {
        $query = $this->master(true);
        $this->parseCondition($query, $condition);
        return $query->update([$field => Db::raw("{$field}-{$step}")]) !== false;
    }

    // ------------------------
    // 乐观锁更新 + 重试
    // ------------------------
    /**
     * @throws Exception
     */
    public function updateWithVersionRetry(array $condition, array $data, string $versionField='version', int $maxRetry=3, int $sleepUs=0, callable $onFail=null): bool
    {
        if (!isset($condition[$versionField])) throw new Exception("Missing version field: {$versionField}");

        $attempt = 0;
        while ($attempt < $maxRetry) {
            $attempt++;
            $currentVersion = $condition[$versionField];
            $updateData = $data;
            $updateData[$versionField] = Db::raw("{$versionField}+1");

            $affected = $this->where($condition)->where($versionField, $currentVersion)->update($updateData);
            if ($affected > 0) return true;

            if ($onFail) $onFail($attempt);
            $sleepUs > 0 && usleep($sleepUs);

            $newVersion = $this->where(array_diff_key($condition, [$versionField=>1]))->value($versionField);
            if ($newVersion === null) return false;
            $condition[$versionField] = $newVersion;
        }
        return false;
    }

    /**
     * chunk 处理大数据量
     */
    public function chunks(array $condition, int $size, callable $callback, bool $master=false): mixed
    {
        $query = $this->newQuery()/*->master($master)*/;
        $this->parseCondition($query, $condition);
        return $query->chunk($size, $callback);
    }


    /**
     * 自动缓存查询结果（无需手动传 key 或条件）
     *
     * @param callable $callback 查询回调，返回数组
     * @param int $ttl 缓存时间（秒）
     * @return array
     * @throws ReflectionException
     */
    public function cacheQuery(callable $callback, int $ttl = 60): array
    {
        // 尝试获取回调反射
        $ref    = new ReflectionFunction($callback);

        // 获取闭包文件名 + 起始行 + 结束行，用作key前缀
        $file   = $ref->getFileName();
        $start  = $ref->getStartLine();
        $end    = $ref->getEndLine();
        $prefix = md5($file . $start . $end);

        // 执行回调，获取查询结果
        $data = $callback();

        // 再根据回调返回的条件生成hash key
        $hash = md5(json_encode($data));
        $cacheKey = $this->getTable() . '_query_' . $prefix . '_' . $hash;

        // 如果缓存存在直接返回
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 缓存结果
        Cache::set($cacheKey, $data, $ttl);

        return $data;
    }


    // ---------------------------------
    // 高级聚合统计
    // ---------------------------------
    public function aggregate(
        array $condition = [],
        array $aggregates = [],       // ['amount'=>'sum','id'=>'count']
        array|string $group = [],
        bool $master = false,
        ?string $table = null
    ): array
    {
        if (empty($aggregates)) {
            return [];
        }

        try {
            $query = $this->newQuery()/*->master($master)*/;
            if ($table) $query->table($table);

            $this->parseCondition($query, $condition);

            // 构建聚合字段
            $fields = [];

            foreach ($aggregates as $field => $func) {
                $func = strtolower($func);

                $alias = "{$func}_{$field}";

                switch ($func) {
                    case 'sum':
                    case 'avg':
                        $fields[] = "IFNULL({$func}(`{$field}`),0) as `{$alias}`";
                        break;

                    case 'count':
                        $fields[] = "COUNT(`{$field}`) as `{$alias}`";
                        break;

                    case 'max':
                    case 'min':
                        $fields[] = "{$func}(`{$field}`) as `{$alias}`";
                        break;

                    default:
                        continue 2;
                }
            }

            $query->select($fields);

            if (!empty($group)) {
                $query->group($group);
                $res = $query->select();
                return $res->isEmpty() ? [] : $res->toArray();
            }

            $res = $query->select();

            if ($res->isEmpty()) {
                return [];
            }

            return $res->toArray()[0];

        } catch (Exception $e) {
            Syslog::exception('repository', $e, 'QueryTrait->aggregate');
            return [];
        }
    }

    public function aggregateShard(
        int|string $shardKey,
        int $shardNum,
        array $condition = [],
        array $aggregates = [],
        array|string $group = [],
        bool $master = false
    ): array
    {
        $tableName = $this->tableShard($shardKey, $shardNum);

        return $this->aggregate(
            $condition,
            $aggregates,
            $group,
            $master,
            $tableName
        );
    }
}
