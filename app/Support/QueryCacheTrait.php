<?php
namespace App\Support;

use Exception;
use Illuminate\Support\Facades\Cache;
use App\Support\Utils\Syslog;

/**
 * 缓存仓储层 Trait
 * 集成 CacheTTL 缓存功能与 Repository 查询功能
 *
 * 使用示例：
 * class MemberRepository extends Member {
 *     use CommonTrait, QueryCacheTrait;
 *
 *     protected static string $cachePrefix = 'member';
 *     protected static int $defaultTtl = 600;
 * }
 *
 * // 使用缓存查询
 * $member = MemberRepository::findByIdWithCache(1);
 * $members = MemberRepository::selectByConditionWithCache(['status' => 1]);
 */
trait QueryCacheTrait
{
    // 请求级静态缓存
    protected static array $requestCache = [];

    // 缓存前缀，子类需要定义
    protected static string $cachePrefix = '';

    // 默认TTL，子类可以重写
    protected static int $defaultTtl = 600;

    /************ 缓存 Key 生成 ************/
    protected static function getCachePrefix(): string
    {
        if (!empty(static::$cachePrefix))
        {
            return static::$cachePrefix;
        }

        $class = static::class;
        $short = substr(strrchr($class, '\\'), 1);
        $short = preg_replace('/Repository$/i', '', $short);
        return strtolower($short);
    }

    protected static function buildCacheKey(string $key, int|string $id = null): string
    {
        $prefix = static::getCachePrefix();
        return $id === null ? "{$prefix}:{$key}" : "{$prefix}:{$key}:{$id}";
    }

    protected static function buildConditionKey(array $condition): string
    {
        ksort($condition);
        $str = http_build_query($condition);
        return static::buildCacheKey('cond:' . md5($str));
    }

    /************ 基础缓存操作 ************/
    protected static function cacheSet(string $key, mixed $data, int $ttl = null): bool
    {
        $ttl        = $ttl ?? static::$defaultTtl;
        $cacheKey   = static::buildCacheKey($key);
        $ok         = Cache::set($cacheKey, $data, $ttl);
        self::$requestCache[$cacheKey] = $data;
        return $ok;
    }

    protected static function cacheGet(string $key): mixed
    {
        $cacheKey = static::buildCacheKey($key);

        if (isset(self::$requestCache[$cacheKey]))
        {
            return self::$requestCache[$cacheKey];
        }

        $value = Cache::get($cacheKey);

        if ($value !== null)
        {
            self::$requestCache[$cacheKey] = $value;
        }

        return $value;
    }

    protected static function cacheDel(string $key): bool
    {
        $cacheKey = static::buildCacheKey($key);

        unset(self::$requestCache[$cacheKey]);

        return Cache::delete($cacheKey);
    }

    /************ Hash 缓存操作 ************/
    public static function setHashCache(int|string $id, array $data, int $ttl = null): bool
    {
        if (empty($data)) return false;

        $ttl        = $ttl ?? static::$defaultTtl;
        $cacheKey   = static::buildCacheKey('hash', $id);
        $redis      = Cache::store('redis')->handler();

        $redis->hMSet($cacheKey, $data);

        if ($ttl > 0) $redis->expire($cacheKey, $ttl);

        self::$requestCache[$cacheKey] = array_merge(self::$requestCache[$cacheKey] ?? [], $data);

        return true;
    }

    public static function getHashCache(int|string $id, string $field = null): mixed
    {
        $cacheKey = static::buildCacheKey('hash', $id);

        if (isset(self::$requestCache[$cacheKey]))
        {
            $cacheData = self::$requestCache[$cacheKey];
            return $field === null ? $cacheData : ($cacheData[$field] ?? null);
        }

        $redis = Cache::store('redis')->handler();

        if ($field === null)
        {
            $value = $redis->hGetAll($cacheKey);

            if (empty($value)) $value = null;

            if ($value !== null) self::$requestCache[$cacheKey] = $value;
        }
        else
        {
            $value = $redis->hGet($cacheKey, $field);

            if ($value !== false && $value !== null)
            {
                if (!isset(self::$requestCache[$cacheKey]))
                {
                    self::$requestCache[$cacheKey] = [];
                }

                self::$requestCache[$cacheKey][$field] = $value;
            }
        }

        return $value;
    }

    public static function updateHashCache(int|string $id, array $data, int $ttl = null): bool
    {
        if (empty($data)) return false;

        $ttl        = $ttl ?? static::$defaultTtl;
        $cacheKey   = static::buildCacheKey('hash', $id);
        $redis      = Cache::store('redis')->handler();

        $redis->hMSet($cacheKey, $data);

        if ($ttl > 0) $redis->expire($cacheKey, $ttl);

        if (!isset(self::$requestCache[$cacheKey]))
        {
            self::$requestCache[$cacheKey] = [];
        }

        self::$requestCache[$cacheKey] = array_merge(self::$requestCache[$cacheKey], $data);

        return true;
    }

    public static function delHashCache(int|string $id): bool
    {
        $cacheKey   = static::buildCacheKey('hash', $id);
        $redis      = Cache::store('redis')->handler();

        $redis->del($cacheKey);

        unset(self::$requestCache[$cacheKey]);

        return true;
    }

    /************ 带缓存的查询方法 ************/

    /**
     * 根据ID查询单条记录（带缓存）
     * @param int $id
     * @param string $field
     * @param array $order
     * @param bool $master
     * @param bool $lock
     * @param int|null $ttl
     * @return array
     */
    public function findByIdWithCache(int $id, string $field = '*', array $order = [], bool $master = false, bool $lock = false, int $ttl = null): array
    {
        try {
            // 尝试从缓存获取
            $cached = static::getHashCache($id);

            if (!empty($cached))
            {
                if ($field === '*')
                {
                    return $cached;
                }

                // 处理指定字段
                $fields         = explode(',', str_replace(' ', '', $field));
                $result         = [];
                $hasAllFields   = true;

                foreach ($fields as $f)
                {
                    if (isset($cached[$f]))
                    {
                        $result[$f] = $cached[$f];
                    } else
                    {
                        $hasAllFields = false;
                        break;
                    }
                }

                if ($hasAllFields)
                {
                    return $result;
                }
            }

            // 缓存未命中，查询数据库
            $info = $this->master($master)->lock($lock)->where($this->getPk(), '=', $id)->order($order)->field($field)->find();

            if (empty($info))
            {
                return [];
            }

            $data = $info->toArray();

            // 缓存结果
            if ($field === '*')
            {
                static::setHashCache($id, $data, $ttl);
            }

            return $data;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->findByIdWithCache');
            return [];
        }
    }

    /**
     * 根据条件查询单条记录（带缓存）
     * @param array $condition
     * @param string $field
     * @param array $order
     * @param bool $master
     * @param bool $lock
     * @param int|null $ttl
     * @return array
     */
    public function findByConditionWithCache(array $condition, string $field = '*', array $order = [], bool $master = false, bool $lock = false, int $ttl = null): array
    {
        try {
            // 生成条件缓存key
            $conditionKey   = static::buildConditionKey($condition);
            $cached         = static::getCache($conditionKey);

            if (!empty($cached))
            {
                return $cached;
            }

            // 缓存未命中，查询数据库
            $info = $this->master($master)->lock($lock)->where($condition)->order($order)->field($field)->find();

            if (empty($info))
            {
                return [];
            }

            $data = $info->toArray();

            // 缓存结果
            static::setCache($conditionKey, $data, $ttl);

            // 如果查询了完整记录，也缓存到Hash中
            if ($field === '*' && isset($data[$this->getPk()]))
            {
                static::setHashCache($data[$this->getPk()], $data, $ttl);
            }

            return $data;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->findByConditionWithCache');
            return [];
        }
    }

    /**
     * 根据条件查询多条记录（带缓存）
     * @param array $condition
     * @param string $field
     * @param array $order
     * @param bool $master
     * @param int|null $ttl
     * @return array
     */
    public function selectByConditionWithCache(array $condition = [], string $field = '*', array $order = [], bool $master = false, int $ttl = null): array
    {
        try {
            // 生成条件缓存key
            $conditionKey   = static::buildConditionKey(array_merge($condition, ['_select' => true, '_field' => $field, '_order' => $order]));
            $cached         = static::getCache($conditionKey);

            if (!empty($cached))
            {
                return $cached;
            }

            // 缓存未命中，查询数据库
            empty($order) && $order = [$this->getPk() => 'desc'];

            $info = $this->master($master)->where($condition)->field($field)->order($order)->select();

            $data = $info->toArray();

            // 缓存结果
            static::setCache($conditionKey, $data, $ttl);

            // 如果查询了完整记录，也缓存到Hash中
            if ($field === '*')
            {
                foreach ($data as $row)
                {
                    if (isset($row[$this->getPk()]))
                    {
                        static::setHashCache($row[$this->getPk()], $row, $ttl);
                    }
                }
            }

            return $data;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->selectByConditionWithCache');
            return [];
        }
    }

    /**
     * 分页查询（带缓存）
     * @param array $condition
     * @param string $field
     * @param array $order
     * @param int $offset
     * @param int $limit
     * @param bool $master
     * @param int|null $ttl
     * @return array
     */
    public function limitsWithCache(array $condition = [], string $field = '*', array $order = [], int $offset = 0, int $limit = 10, bool $master = false, int $ttl = null): array
    {
        try {
            // 生成条件缓存key
            $conditionKey = static::buildConditionKey(array_merge($condition, [
                '_limits'   => true,
                '_field'    => $field,
                '_order'    => $order,
                '_offset'   => $offset,
                '_limit'    => $limit
            ]));

            $cached = static::getCache($conditionKey);

            if (!empty($cached))
            {
                return $cached;
            }

            // 缓存未命中，查询数据库
            empty($order) && $order = [$this->getPk() => 'desc'];

            $info = $this->master($master)->where($condition)->field($field)->order($order)->limit($offset, $limit)->select();

            if (empty($info))
            {
                return [];
            }

            $data = $info->toArray();

            // 缓存结果
            static::setCache($conditionKey, $data, $ttl);

            // 如果查询了完整记录，也缓存到Hash中
            if ($field === '*')
            {
                foreach ($data as $row)
                {
                    if (isset($row[$this->getPk()]))
                    {
                        static::setHashCache($row[$this->getPk()], $row, $ttl);
                    }
                }
            }

            return $data;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->limitsWithCache');
            return [];
        }
    }

    /************ 数据更新与缓存同步 ************/

    /**
     * 根据ID更新数据（同步缓存）
     * @param int $id
     * @param array $data
     * @param int|null $ttl
     * @return int
     */
    public function updateByIdWithCache(int $id, array $data, int $ttl = null): int
    {
        try {
            // 更新数据库
            $result = $this->updateById($id, $data);

            if ($result > 0)
            {
                // 更新Hash缓存
                static::updateHashCache($id, $data, $ttl);

                // 清除相关条件缓存
                static::clearConditionCache();
            }

            return $result;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->updateByIdWithCache');
            return 0;
        }
    }

    /**
     * 根据条件更新数据（清除缓存）
     * @param array $condition
     * @param array $data
     * @return int
     */
    public function updateByConditionWithCache(array $condition, array $data): int
    {
        try {
            // 更新数据库
            $result = $this->updateByCondition($condition, $data);

            if ($result > 0)
            {
                // 清除所有相关缓存
                static::clearAllCache();
            }

            return $result;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->updateByConditionWithCache');
            return 0;
        }
    }

    /**
     * 删除记录（清除缓存）
     * @param int $id
     * @return int
     */
    public function deleteByIdWithCache(int $id): int
    {
        try {
            // 删除数据库记录
            $result = $this->deleteById($id);

            if ($result > 0)
            {
                // 删除Hash缓存
                static::delHashCache($id);

                // 清除相关条件缓存
                static::clearConditionCache();
            }

            return $result;

        } catch (Exception $exception) {
            Syslog::exception('repository', $exception, 'QueryCacheTrait->deleteByIdWithCache');
            return 0;
        }
    }

    /************ 缓存清理方法 ************/

    /**
     * 清除条件缓存
     */
    protected static function clearConditionCache(): void
    {
        try {
            $redis      = Cache::store('redis')->handler();
            $pattern    = static::getCachePrefix() . ':cond:*';
            $keys       = $redis->keys($pattern);

            if (!empty($keys))
            {
                $redis->del($keys);
            }

        } catch (Exception $e) {
            Syslog::exception('cache', $e, 'QueryCacheTrait->clearConditionCache');
        }
    }

    /**
     * 清除所有缓存
     */
    protected static function clearAllCache(): void
    {
        try {

            $redis      = Cache::store('redis')->handler();
            $pattern    = static::getCachePrefix() . ':*';
            $keys       = $redis->keys($pattern);

            if (!empty($keys))
            {
                $redis->del($keys);
            }

            // 清除请求级缓存
            self::$requestCache = [];

        } catch (Exception $e) {

            Syslog::exception('cache', $e, 'QueryCacheTrait->clearAllCache');
        }
    }

    /**
     * 预热缓存
     * @param array $ids
     * @param string $field
     * @param int|null $ttl
     */
    public function warmupCache(array $ids, string $field = '*', int $ttl = null): void
    {
        try {

            if (empty($ids)) return;

            $data = $this->selectByCondition([$this->getPk() => ['in', $ids]], $field);

            foreach ($data as $row)
            {
                if (isset($row[$this->getPk()]))
                {
                    static::setHashCache($row[$this->getPk()], $row, $ttl);
                }
            }

        } catch (Exception $e) {
            Syslog::exception('cache', $e, 'QueryCacheTrait->warmupCache');
        }
    }


    /************ 查询单条或多条 ************/

    /**
     * 查询单条 by ID，优先缓存
     * @param int $id
     * @param int $ttl
     * @return array
     */
    public function findByIdCache(int $id, int $ttl = 600): array
    {
        return $this->findByIdWithCache($id, '*', [], false, false, $ttl);
    }

    /**
     * 查询多条 by 条件，优先条件缓存
     * @param array $condition
     * @param array $fields
     * @param array $order
     * @param int $ttl
     * @return array
     */
    public function selectByConditionCache(array $condition = [], array $fields = ['*'], array $order = [], int $ttl = 600): array
    {
        $fieldStr = is_array($fields) ? implode(',', $fields) : $fields;
        return $this->selectByConditionWithCache($condition, $fieldStr, $order, false, $ttl);
    }

    /************ 更新 / 删除 ************/

    /**
     * 根据ID更新数据（同步缓存）
     * @param int $id
     * @param array $data
     * @param int $ttl
     * @return bool
     */
    public function updateByIdCache(int $id, array $data, int $ttl = 600): bool
    {
        $result = $this->updateByIdWithCache($id, $data, $ttl);
        return $result > 0;
    }

    /**
     * 根据ID删除数据（清除缓存）
     * @param int $id
     * @return bool
     */
    public function deleteByIdCache(int $id): bool
    {
        $result = $this->deleteByIdWithCache($id);
        return $result > 0;
    }

    /************ 字段操作 ************/

    /**
     * 获取指定字段值
     * @param int $id
     * @param string $field
     * @return mixed
     */
    public function getField(int $id, string $field): mixed
    {
        return static::getHashCache($id, $field);
    }

    /**
     * 更新指定字段
     * @param int $id
     * @param array $fields
     * @param int $ttl
     * @return bool
     */
    public function updateFields(int $id, array $fields, int $ttl = 600): bool
    {
        // 先更新数据库
        $result = $this->updateByIdWithCache($id, $fields, $ttl);
        return $result > 0;
    }

    /**
     * 字段自增
     * @param int $id
     * @param string $field
     * @param int $step
     * @param int $ttl
     * @return int
     */
    public function incrementField(int $id, string $field, int $step = 1, int $ttl = 600): int
    {
        // 获取当前值
        $currentValue = (int)$this->getField($id, $field);
        $newValue = $currentValue + $step;

        // 更新数据库和缓存
        $this->updateFields($id, [$field => $newValue], $ttl);

        return $newValue;
    }

    /**
     * 字段自减
     * @param int $id
     * @param string $field
     * @param int $step
     * @param int $ttl
     * @return int
     */
    public function decrementField(int $id, string $field, int $step = 1, int $ttl = 600): int
    {
        // 获取当前值
        $currentValue = (int)$this->getField($id, $field);
        $newValue = $currentValue - $step;

        // 更新数据库和缓存
        $this->updateFields($id, [$field => $newValue], $ttl);

        return $newValue;
    }

    /************ 批量操作 ************/

    /**
     * 批量获取多个记录
     * @param array $ids
     * @return array
     */
    public function getMultiple(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $data = static::getHashCache($id);
            if (!empty($data)) {
                $result[$id] = $data;
            }
        }

        // 如果有未命中的ID，从数据库查询
        $missedIds = array_diff($ids, array_keys($result));
        if (!empty($missedIds)) {
            $dbData = $this->selectByConditionWithCache([$this->getPk() => ['in', $missedIds]]);
            foreach ($dbData as $row) {
                $id = $row[$this->getPk()];
                $result[$id] = $row;
            }
        }

        return $result;
    }

    /**
     * 批量更新字段
     * @param array $ids
     * @param array $fields
     * @param int $ttl
     * @return bool
     */
    public function updateMultipleFields(array $ids, array $fields, int $ttl = 600): bool
    {
        foreach ($ids as $id) {
            $this->updateFields($id, $fields, $ttl);
        }
        return true;
    }

    /************ 分页查询 ************/

    /**
     * 分页查询（带缓存）
     * @param array $condition
     * @param int $page
     * @param int $limit
     * @param array $order
     * @param array $fields
     * @param int $ttl
     * @return array
     */
    public function paginateByConditionCache(array $condition = [], int $page = 1, int $limit = 10, array $order = [], array $fields = ['*'], int $ttl = 600): array
    {
        $offset = ($page - 1) * $limit;
        $fieldStr = is_array($fields) ? implode(',', $fields) : $fields;

        return $this->limitsWithCache($condition, $fieldStr, $order, $offset, $limit, false, $ttl);
    }
}
