<?php
namespace app\common\util;

use think\Db;

/**
 * 监控子系统的运行期状态（mac_monitor_state）。
 *
 * 承担三类职责：
 *  1. cron 分钟锁（原子 CAS，防止 crontab 叠跑 / 站长手动重打）
 *  2. due-gate（外层每分钟被调用，内层按各自间隔判断是否到期）
 *  3. counter 基准值与告警 pending 状态的持久化
 *
 * 所有原子操作依赖 UPDATE 的 affected rows 判定，不使用事务。
 * ThinkPHP 5 默认不开启 CLIENT_FOUND_ROWS，affected rows 即真正变更的行数。
 */
class MonitorState
{
    private static function table()
    {
        return Db::getConfig('prefix') . 'monitor_state';
    }

    /**
     * 确保行存在。用 INSERT IGNORE 避免并发下的重复键异常。
     *
     * @param string $key
     * @return void
     */
    private static function ensureRow($key)
    {
        $table = self::table();
        Db::execute(
            "INSERT IGNORE INTO `{$table}` (`state_key`,`state_num`,`state_val`,`updated_at`) VALUES (?,0,'',0)",
            [(string)$key]
        );
    }

    /**
     * @param string $key
     * @param int    $default
     * @return int
     */
    public static function getNum($key, $default = 0)
    {
        $table = self::table();
        $row = Db::query("SELECT `state_num` FROM `{$table}` WHERE `state_key`=? LIMIT 1", [(string)$key]);
        if (empty($row)) {
            return intval($default);
        }
        return intval($row[0]['state_num']);
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function getVal($key, $default = '')
    {
        $table = self::table();
        $row = Db::query("SELECT `state_val` FROM `{$table}` WHERE `state_key`=? LIMIT 1", [(string)$key]);
        if (empty($row)) {
            return (string)$default;
        }
        return (string)$row[0]['state_val'];
    }

    /**
     * @param string $key
     * @param int    $num
     * @param string $val
     * @return void
     */
    public static function set($key, $num, $val = '')
    {
        $table = self::table();
        $now = time();
        Db::execute(
            "INSERT INTO `{$table}` (`state_key`,`state_num`,`state_val`,`updated_at`) VALUES (?,?,?,?)"
            . " ON DUPLICATE KEY UPDATE `state_num`=VALUES(`state_num`),`state_val`=VALUES(`state_val`),`updated_at`=VALUES(`updated_at`)",
            [(string)$key, intval($num), (string)$val, $now]
        );
    }

    /**
     * due-gate：到期返回 true，并把下次到期时间推进 $intervalSec。
     *
     * state_num 存「下次允许执行的时间戳」。
     * UPDATE ... WHERE state_num <= now 的 affected rows 保证：
     * 并发下 MySQL 的行锁会把两个进程串行化，只有一个能拿到 due。
     *
     * ★ 刻意不提供「释放」操作 ★
     * 这是闸门（每 N 秒最多放行一次），不是互斥锁。
     * 如果执行完就把闸门放开，同一分钟内的第二次请求会再次被放行，
     * 「crontab 叠跑 / 站长手动重打只执行一次」的幂等性就没了。
     * 代价是：一次执行崩溃后要等到下一个间隔才会重试 —— 这正是期望行为，
     * 崩溃的任务不该在同一分钟内被疯狂重试。
     *
     * 这是 ExternalSourceRepository::getDueSyncJobs() 的同款范式：
     * 外层被粗粒度调用，内层按各自间隔自判是否到期。
     *
     * @param string $key
     * @param int    $intervalSec
     * @param int    $now
     * @return bool
     */
    public static function due($key, $intervalSec, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $intervalSec = max(1, intval($intervalSec));
        self::ensureRow($key);

        $table = self::table();
        $affected = Db::execute(
            "UPDATE `{$table}` SET `state_num`=?,`updated_at`=? WHERE `state_key`=? AND `state_num` <= ?",
            [$now + $intervalSec, $now, (string)$key, $now]
        );
        return intval($affected) === 1;
    }

    /**
     * 原子扣减预算：成功返回 true，已达上限返回 false。
     *
     * 用于告警的「每小时全局通知预算」：一个根因（比如 MySQL 挂了）会同时点燃
     * 5xx 突增、p95 劣化、连接数异常等一大串规则。没有全局预算的话，
     * 站长的收件箱会在两分钟内被几十条通知淹没，真正有用的第一条反而被埋掉。
     *
     * 用 UPDATE ... WHERE state_num < cap 的 affected rows 判定，
     * MySQL 的行锁保证并发下不会超发。
     *
     * @param string $key 建议带上小时维度，如 notify_budget_2026071409
     * @param int    $cap
     * @param int    $now
     * @return bool
     */
    public static function consumeBudget($key, $cap, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $cap = intval($cap);
        if ($cap <= 0) {
            return false;
        }
        self::ensureRow($key);

        $table = self::table();
        $affected = Db::execute(
            "UPDATE `{$table}` SET `state_num`=`state_num`+1,`updated_at`=? WHERE `state_key`=? AND `state_num` < ?",
            [$now, (string)$key, $cap]
        );
        return intval($affected) === 1;
    }

    /**
     * 只读地判断预算是否已用尽，不扣减。
     *
     * 用于「发送前拦截」：consumeBudget 会真正扣一个额度，若放在渠道循环之前，
     * 那些被熔断/发送失败、实际一条都没发出去的事件也会白扣预算；一个坏渠道的
     * 事件会每分钟重复触发（失败不推进 silence 窗口）从而在几十分钟内耗尽预算，
     * 把健康告警一并饿死。所以循环前用本方法只读判断余额，真正发出至少一条后
     * 才调用 consumeBudget 扣减。cron 由分钟锁串行执行，peek + consume 无并发问题。
     *
     * @param string $key
     * @param int    $cap
     * @return bool 已达上限返回 true
     */
    public static function budgetExceeded($key, $cap)
    {
        $cap = intval($cap);
        if ($cap <= 0) {
            return true;
        }
        $table = self::table();
        $row = Db::query(
            "SELECT `state_num` FROM `{$table}` WHERE `state_key`=? LIMIT 1",
            [(string)$key]
        );
        $used = (!empty($row) && isset($row[0]['state_num'])) ? intval($row[0]['state_num']) : 0;
        return $used >= $cap;
    }

    /**
     * 删除指定前缀的状态行（测试清理与规则删除时用）。
     *
     * @param string $prefix
     * @return int
     */
    public static function purgeByPrefix($prefix)
    {
        $prefix = (string)$prefix;
        if ($prefix === '') {
            return 0;
        }
        $table = self::table();
        $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $prefix) . '%';
        return intval(Db::execute("DELETE FROM `{$table}` WHERE `state_key` LIKE ?", [$like]));
    }

    /**
     * 清理过期的每小时通知预算行（notify_budget_YmdH）。
     *
     * 这些行每小时新增一条且从不被覆盖，长期会无限堆积。按 key 的字典序（YmdH 定长）
     * 删除早于「当前小时」的行 —— 当前小时行仍在计数，必须保留，否则预算会被重置。
     *
     * @param int $now
     * @return int
     */
    public static function purgeBudgetBefore($now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $table = self::table();
        $cur = 'notify_budget_' . date('YmdH', $now);
        return intval(Db::execute(
            "DELETE FROM `{$table}` WHERE `state_key` LIKE 'notify\\_budget\\_%' AND `state_key` < ?",
            [$cur]
        ));
    }
}
