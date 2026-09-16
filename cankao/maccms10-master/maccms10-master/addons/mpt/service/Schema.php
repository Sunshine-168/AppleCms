<?php

namespace addons\mpt\service;

use think\Db;
use think\Log;

/**
 * 建表 + 补齐后续版本新增的列与索引。
 *
 * ★ 为什么不能只有 CREATE TABLE IF NOT EXISTS ★
 * 本插件随主库分发，站长在后台只看得到「启用」，走的是 enable() → setup()。
 * 表已经存在时 `CREATE TABLE IF NOT EXISTS` 是一句空操作 —— 也就是说插件一旦
 * 发过一个版本，之后任何表结构变更对**存量站点永远不会生效**，而且没有任何
 * 报错，只会在运行期表现成「某个查询突然很慢」或「插入报 Unknown column」。
 * 这不是假设：本插件 1.0 开发期就已经补过一次 `idx_result`
 * （TaskRunner::pruneOrphanResults() 依赖它，没有就是全表扫），
 * 早于那次改动启用过插件的库到今天都还没有这个索引。
 *
 * 核心侧对应的规矩是铁律 1 的 application/data/update/database.php：
 * 判 $col_list 里有没有，缺了才 ALTER。插件没有那条升级管道，只能自己带一条。
 *
 * ★ 光挂在 install()/enable() 上是不够的 ★
 * 那两个入口只在站长「安装 / 启用」时被调用。而本插件随主库分发，站长走后台
 * 在线升级（Update.php 下载官方包覆盖文件）时插件一直是**启用态**，enable()
 * 不会再被调用 —— 新版本的列与索引对已启用的站点仍然永远不生效，也就是上面
 * 那段说明想解决的问题原封不动地留着。所以 ensure() 还要能被运行期路径调用，
 * 并且自带一道「这个版本跑过没有」的闸门，免得每个请求都去 SHOW COLUMNS。
 *
 * ★ 声明只写在 install.sql 里，这里解析它 ★
 * 再手写一份「列 → ALTER 语句」的映射就是第二处事实来源，两处必然漂移 ——
 * 而漂移的表现恰恰是「新版本忘了往升级表里加一条」，也就是本类要防的事。
 * 所以直接从 install.sql 的建表语句里把列定义和索引定义抠出来，
 * 以后改表只需要改 install.sql 一个文件。
 *
 * ★ 为什么是一个 service 类而不是插件主类上的方法 ★
 * get_addon_autoload_config()（vendor/karsonzhang/fastadmin-addons/src/common.php:213）
 * 会把插件主类上**每一个** public 方法登记成同名钩子写进 application/extra/addons.php。
 * 而本类要能被 Admin 控制器与 TaskRunner 调到，只能是 public —— 放主类上就会凭空
 * 多出一个 ensure 钩子。与 Safety::loadLang() 挪出来是同一个理由。
 */
class Schema
{
    /** 表名（不含前缀） */
    const TABLE = 'mpt_task';

    /**
     * 补表失败后的重试间隔（秒）。见 ensure() 里 failedRecently() 那段说明。
     */
    const RETRY_AFTER = 600;

    /**
     * 对着 install.sql 把表结构补齐。
     *
     * @param bool $force true=无视版本闸门强制跑一遍（install/enable 用）
     * @return bool 是否真的跑了一轮
     */
    public static function ensure($force = false)
    {
        $sqlFile = self::sqlFile();
        if (!is_file($sqlFile)) {
            return false;
        }
        // 指纹取 install.sql 的内容哈希：改表必然改这个文件，改了就该再跑一轮。
        // 用内容而不是 filemtime —— 升级包解包后的 mtime 是不可靠的。
        $want = (string) @md5_file($sqlFile);
        if ($want === '') {
            return false;
        }
        if (!$force && self::stamp() === $want) {
            return false;
        }
        // ★ 失败也必须退避 ★
        // 库账号没有 CREATE / ALTER 权限时 run() 会一直失败、一直不写版本指纹，
        // 于是每一次 advance()（任务台开着就是 5 秒一次）都要重跑
        // SHOW COLUMNS + SHOW INDEX + 一条必然失败的 ALTER，并各写一行错误日志。
        // 日志会一直涨，而重试一万次也不会长出权限来。这里记下「这个指纹刚失败过」，
        // RETRY_AFTER 秒内不再试；站长补好权限后最迟这么久会自动补上，
        // 换了新版本（指纹变了）则立刻重试，不受上一版的失败拖累。
        //
        // $force 不受这道闸门约束：安装/启用是站长的显式动作，要当场看到结果。
        if (!$force && self::failedRecently($want)) {
            return false;
        }

        // ★ 串行 ★
        // 版本刚升上去的那一刻，标记文件还是旧的，并发请求会一起冲进来跑同一批
        // ALTER，后到的那些拿到「Duplicate column name」。加一把锁，只让一个进程做。
        // 非强制路径用 LOCK_NB：抢不到说明已经有人在补了，直接走人，不占着 worker 排队。
        $lock = @fopen(Safety::runtimeFile('schema.lock'), 'c');
        if ($lock === false) {
            // runtime 不可写：退化成没有闸门的旧行为，不理想但比完全不建表强
            return self::run($sqlFile, $want, false, $force);
        }
        if (!@flock($lock, $force ? LOCK_EX : (LOCK_EX | LOCK_NB))) {
            @fclose($lock);

            return false;
        }
        try {
            // 拿到锁后重判：等锁期间别人可能已经补完了
            if (!$force && self::stamp() === $want) {
                return false;
            }

            return self::run($sqlFile, $want, true, $force);
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }

    /** 卸载时抹掉版本标记与失败标记，免得在 runtime/ 留下没人认领的文件 */
    public static function forget()
    {
        @unlink(self::stampFile());
        @unlink(self::failFile());
    }

    /**
     * 真正执行建表与补列。调用方已经处理好闸门与锁。
     *
     * @param string $sqlFile install.sql 路径
     * @param string $want    本次要落下的版本指纹
     * @param bool   $locked  是否持有 runtime/mpt/schema.lock（没拿到锁就不写标记，
     *                        免得把「其实没串行过」的这一轮记成已完成）
     * @param bool   $strict  建表失败时抛出而不是吞掉。安装/启用（ensure(true)）传 true，
     *                        运行期路径传 false，理由见下面 catch 块里的说明。
     * @return bool
     */
    protected static function run($sqlFile, $want, $locked, $strict = false)
    {
        // 表前缀不能写死 mac_：站长装库时可以改前缀，写死会让整个安装在非默认前缀的站上失败
        $prefix = (string) config('database.prefix');
        $sql = str_replace('__PREFIX__', $prefix, file_get_contents($sqlFile));
        // 先剥掉整行的 `-- 注释` 再按分号切：注释里出现一个半角分号就会把
        // CREATE TABLE 从中间劈成两条残句，安装当场失败且报错完全看不出原因。
        // 只匹配行首（允许缩进）的 --，不会碰到 COMMENT='...' 里的内容。
        $sql = preg_replace('/^[ \t]*--[^\r\n]*$/m', '', $sql);

        try {
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
                if ($statement !== '') {
                    Db::execute($statement);
                }
            }
        } catch (\Exception $e) {
            Log::error('mpt ensureSchema create: ' . $e->getMessage());
            if ($locked) {
                self::rememberFailure($want);
            }
            // ★ 建表失败在两条路上要有两种表现 ★
            // 安装/启用（$strict）：必须抛出去。Db::execute() 抛的是
            // think\exception\PDOException，继承链最终落在 think\Exception 上，
            // 正好被 Service::enable() 的 catch 接住并把消息呈给站长
            // （vendor/karsonzhang/fastadmin-addons/src/addons/Service.php:437-439），
            // 站长当场知道「库账号没有 CREATE 权限」；吞掉的话插件会「启用成功」，
            // 然后每一个动作都报表不存在，反而查不出根因。
            // 运行期（poll / cron / 打开任务台）：必须吞掉。裸着的话异常会一路穿过
            // advanceLocked() → advance()（那里只有 finally 没有 catch）冒到
            // Api::poll()，前端收到一页 500 HTML 而不是 JSON，任务台每 5 秒弹一句
            // "bad response" —— 真实原因一个字都看不到，反而更难排查。
            if ($strict) {
                throw $e;
            }

            return false;
        }

        // 建表跑完了，接着把存量库缺的列/索引补上。
        // 整段包一层 catch：站长的库账号可能没有 ALTER 权限，那种情况下插件
        // 本身仍然是可用的（缺的无非是个索引），不该因此让「启用」整个失败。
        try {
            self::syncTable($prefix . self::TABLE, $sql);
        } catch (\Exception $e) {
            Log::error('mpt ensureSchema: ' . $e->getMessage());
            if ($locked) {
                self::rememberFailure($want);
            }

            return false;
        }

        if ($locked) {
            @file_put_contents(self::stampFile(), $want, LOCK_EX);
            // 这一轮成功了，上一次的失败记录就没有意义了；留着只会让下一个
            // 版本的失败判断读到一条陈旧记录（指纹不同虽然不会误挡，但那是垃圾）。
            @unlink(self::failFile());
        }

        return true;
    }

    /**
     * 这个指纹刚失败过吗？失败记录形如 `<指纹>|<时间戳>`。
     *
     * 指纹对不上一律返回 false —— 换了新版本就该立刻重试，
     * 不能让上一版的失败把新版本的补列一起拖住。
     */
    protected static function failedRecently($want)
    {
        $file = self::failFile();
        if (!is_file($file)) {
            return false;
        }
        $parts = explode('|', trim((string) @file_get_contents($file)), 2);
        if (count($parts) !== 2 || $parts[0] !== $want) {
            return false;
        }

        return (time() - intval($parts[1])) < self::RETRY_AFTER;
    }

    /** 记下「这个指纹在这一刻失败了」。调用方持有 runtime/mpt/schema.lock。 */
    protected static function rememberFailure($want)
    {
        @file_put_contents(self::failFile(), $want . '|' . time(), LOCK_EX);
    }

    /**
     * 对着 install.sql 里的建表语句补齐一张表缺失的列与索引。
     *
     * @param string $table 已带前缀的表名
     * @param string $sql   已去注释、已替换前缀的 install.sql 内容
     */
    protected static function syncTable($table, $sql)
    {
        // 从 `CREATE TABLE ... (` 之后开始扫表体
        if (!preg_match('/CREATE\s+TABLE[^(]*\(/i', $sql, $m, PREG_OFFSET_CAPTURE)) {
            return;
        }
        $start = $m[0][1] + strlen($m[0][0]);

        $cols = array();
        $keys = array();
        foreach (self::splitDefs($sql, $start) as $def) {
            // 列定义：以 `列名` 开头
            if (preg_match('/^`([A-Za-z0-9_]+)`\s+(.+)$/s', $def, $mm)) {
                $cols[$mm[1]] = $mm[2];
                continue;
            }
            // 索引定义。PRIMARY KEY 不补：主键只能建表时定，事后加等于重建整张表，
            // 而一张连主键都没有的 mpt_task 说明库已经被人手工动过，不该由插件擅自动手。
            if (preg_match('/^(UNIQUE\s+KEY|KEY|INDEX)\s+`([A-Za-z0-9_]+)`\s*(\(.+\))$/is', $def, $mm)) {
                $keys[$mm[2]] = array(
                    'unique' => stripos($mm[1], 'UNIQUE') !== false,
                    'cols' => $mm[3],
                );
            }
        }
        if (!$cols) {
            return;
        }

        // 库里现有的列与索引。表刚被上面那句 CREATE 建出来时这两条查询也照跑，
        // 结果自然是「一个都不缺」，多一次 SHOW 而已，换来的是不用分支判断。
        $have = array();
        foreach ((array) Db::query('SHOW COLUMNS FROM `' . $table . '`') as $row) {
            if (isset($row['Field'])) {
                $have[$row['Field']] = true;
            }
        }
        if (!$have) {
            return;
        }
        $haveKeys = array();
        foreach ((array) Db::query('SHOW INDEX FROM `' . $table . '`') as $row) {
            if (isset($row['Key_name'])) {
                $haveKeys[$row['Key_name']] = true;
            }
        }

        foreach ($cols as $name => $def) {
            if (!isset($have[$name])) {
                Db::execute('ALTER TABLE `' . $table . '` ADD `' . $name . '` ' . rtrim(trim($def), ','));
            }
        }
        foreach ($keys as $name => $key) {
            if (!isset($haveKeys[$name])) {
                Db::execute('ALTER TABLE `' . $table . '` ADD '
                    . ($key['unique'] ? 'UNIQUE KEY' : 'KEY')
                    . ' `' . $name . '` ' . $key['cols']);
            }
        }
    }

    /**
     * 从建表语句左括号之后开始扫，把表体按顶层逗号切成一条条定义，
     * 扫到与那个左括号配对的右括号为止。
     *
     * ★ 三件事都不能少，缺一条就会静默漏掉某条定义 ★
     *
     * 1）不能 explode(',')：`idx_obj` (`mpt_mid`,`mpt_obj_id`) 这类多列索引的逗号
     *    在括号里，切开只会得到两条谁也认不出来的残句 —— 所以要跟括号深度。
     *
     * 2）表体的结尾必须靠深度扫出来，**不能用 strrpos($sql, ')')**。
     *    本表最后一行是 `) ENGINE=InnoDB ... COMMENT='AI短视频(MPT)生成任务';`，
     *    最后一个 `)` 落在 COMMENT 字符串里的「(MPT)」上，比真正的表体结尾还靠右。
     *    那样表体会多吞一截 `) ENGINE=... COMMENT='AI短视频(MPT`，末尾定义的正则
     *    因为收不了尾而匹配失败 —— 实测的表现就是 `idx_result` 被悄悄丢掉，
     *    而那恰好是本机制唯一要补的东西。
     *
     * 3）引号里的内容一律原样吞掉，不参与深度与逗号判断。COMMENT 文案里写一个
     *    半角逗号（`COMMENT '进度,百分比'`）就会在顶层被误切成两条。
     *    本表现有的 COMMENT 里括号恰好是配对的，但那是巧合不是保证。
     */
    protected static function splitDefs($sql, $pos)
    {
        $out = array();
        $buf = '';
        $depth = 0;
        $quote = '';
        $len = strlen($sql);
        for ($i = $pos; $i < $len; $i++) {
            $ch = $sql[$i];

            if ($quote !== '') {
                $buf .= $ch;
                // 反斜杠转义只在单引号串里有意义
                if ($ch === '\\' && $quote === "'" && $i + 1 < $len) {
                    $buf .= $sql[$i + 1];
                    $i++;
                    continue;
                }
                if ($ch === $quote) {
                    // 连着两个同样的引号（'' 或 ``）是转义，不是收尾
                    if ($i + 1 < $len && $sql[$i + 1] === $quote) {
                        $buf .= $sql[$i + 1];
                        $i++;
                        continue;
                    }
                    $quote = '';
                }
                continue;
            }

            if ($ch === "'" || $ch === '`') {
                $quote = $ch;
                $buf .= $ch;
                continue;
            }

            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                if ($depth === 0) {
                    break; // 与 CREATE TABLE 那个左括号配对，表体到此为止
                }
                $depth--;
            } elseif ($ch === ',' && $depth === 0) {
                $out[] = trim($buf);
                $buf = '';
                continue;
            }
            $buf .= $ch;
        }
        $out[] = trim($buf);

        return array_filter($out, function ($one) {
            return $one !== '';
        });
    }

    /** 已经补到哪个版本了 */
    protected static function stamp()
    {
        $fp = self::stampFile();

        return is_file($fp) ? trim((string) @file_get_contents($fp)) : '';
    }

    protected static function stampFile()
    {
        return Safety::runtimeFile('schema.ver');
    }

    protected static function failFile()
    {
        return Safety::runtimeFile('schema.fail');
    }

    protected static function sqlFile()
    {
        // 路径口径与 ConfigSchema::file() / Safety::loadLang() 一致
        return ADDON_PATH . 'mpt' . DS . 'install.sql';
    }
}
