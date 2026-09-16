<?php

namespace addons\mpt\service;

/**
 * 把 addons/mpt/assets/ 同步到 static/addons/mpt/。
 *
 * ★ 为什么必须有这条运行期通道 ★
 * 任务台模板加载的是**拷贝件**（view/admin/index.html 里的
 * static/addons/mpt/css/mpt.css 与 .../js/mpt.js），而这份拷贝只有框架的
 * Service::install()（vendor/karsonzhang/fastadmin-addons/src/addons/Service.php:292-295）
 * 与 Service::enable()（:396-425）会铺。站长走后台在线升级
 * （application/admin/controller/Update.php 下载官方包覆盖文件）时插件一直是
 * **启用态**，这两个入口都不会再被调用 —— Update.php 全文没有任何 addon 处理，
 * Service::refresh()（Service.php:207）也只重生成 static/js/addons.js。
 * 于是升级包换掉了 addons/mpt/assets/js/mpt.js，浏览器拿到的仍然是旧的那一份：
 * 新接口配旧 JS，而且完全静默，站长只会觉得「升级完某个按钮不好使了」。
 *
 * 这与 Schema::ensure() 要解决的是同一件事（enable() 不会因为升级重跑），
 * 只是那边补的是表结构，这边补的是前端资源。
 *
 * ★ 判据用内容哈希，不用 mtime ★
 * 升级包解包后的 mtime 不可靠 —— Schema::ensure() 里已经为同一个理由放弃过
 * filemtime，改用 md5_file。源文件的 mtime 完全可能比目标还旧，靠「源比目标新
 * 才拷」会把该更新的资源整个漏掉。本插件的 assets 一共三个小文件，两边各
 * md5_file 一次的代价可以忽略，换来的是连「同一个版本号里热修了 js」也认得出来。
 *
 * ★ 先写临时文件再 rename ★
 * 同一时刻可能有两个管理员各自打开任务台。直接 copy() 到目标路径的话，另一个
 * 请求的浏览器可能正好读到写了一半的 mpt.js —— 那是一个语法错误的脚本，整个
 * 任务台变白板，刷新一次又好了，最难查的那种。rename() 在同一文件系统上是原子的，
 * 读到的要么是旧的完整文件、要么是新的完整文件。有了它这里就不需要再加锁。
 *
 * ★ 为什么是一个 service 类 ★
 * get_addon_autoload_config()（vendor/karsonzhang/fastadmin-addons/src/common.php:213）
 * 会把插件主类上每一个 public 方法登记成同名钩子写进 application/extra/addons.php。
 * 与 Schema / ConfigSchema / Safety::loadLang() 挪出来是同一个理由。
 */
class Assets
{
    /**
     * 对着 addons/mpt/assets/ 把 static/addons/mpt/ 补齐。
     *
     * 调用时机见 Admin::_initialize()：只有任务台那一个页面会加载这些资源，
     * 在它渲染之前同步一次即可，不该放进 appInit 让每个前台请求都去比对哈希。
     *
     * @return bool 全部到位返回 true；任何一个文件没搞定返回 false（只影响样式/脚本，
     *              不阻断页面，所以调用方可以忽略返回值）
     */
    public static function ensure()
    {
        $src = ADDON_PATH . 'mpt' . DS . 'assets' . DS;
        if (!is_dir($src)) {
            return false;
        }
        // 目标目录口径与框架的 Service::getDestAssetsDir()（Service.php:645-652）
        // 以及模板里的 {$root_path}/static/addons/mpt/ 保持一致，三处必须同一个地方。
        $dst = ROOT_PATH . 'static' . DS . 'addons' . DS . 'mpt' . DS;

        return self::syncDir($src, $dst);
    }

    /**
     * 任务台引用的那两个资源的内容版本，形如 `?v=1a2b3c4d5e`。
     *
     * ★ 光有 ensure() 是不够的 ★
     * ensure() 只把**磁盘上**的文件换成了新的，浏览器那一侧没有任何变化。
     * 而 nginx / 宝塔对 static/ 普遍配着几天到一年的 expires —— 站长升级完打开
     * 任务台，跑的仍然是缓存里的旧 mpt.js，新接口配旧 JS，且完全静默。
     * 那正是本类顶部那段说明想解决的失效模式，只补磁盘这一半等于没补。
     *
     * 版本取内容哈希而不是插件版本号，理由与 ensure() 用 md5_file 而不用 filemtime
     * 一样：同一个版本号里热修了 js 也认得出来。口径与核心一致 ——
     * application/admin/view_new/public/head.html:9-12 每个静态资源都带 `?{$MAC_VERSION}`。
     *
     * 只在任务台那一个页面调用，两次 md5_file 的代价可以忽略。
     *
     * @return string 形如 '?v=xxxxxxxxxx'；任一文件读不出来时返回空串，
     *                调用方原样拼进 URL 即可（退化成没有版本号，不会拼出坏地址）
     */
    public static function version()
    {
        $src = ADDON_PATH . 'mpt' . DS . 'assets' . DS;
        $sig = '';
        foreach (array('css' . DS . 'mpt.css', 'js' . DS . 'mpt.js') as $one) {
            $one = @md5_file($src . $one);
            if ($one === false) {
                return '';
            }
            $sig .= $one;
        }

        return '?v=' . substr(md5($sig), 0, 10);
    }

    /**
     * 递归同步一层目录。只处理常规文件与子目录，其余（软链、特殊文件）一律跳过。
     */
    protected static function syncDir($src, $dst)
    {
        $items = @scandir($src);
        if ($items === false) {
            return false;
        }
        $ok = true;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $from = $src . $item;
            $to = $dst . $item;

            if (is_dir($from)) {
                $ok = self::syncDir($from . DS, $to . DS) && $ok;
                continue;
            }
            if (!is_file($from)) {
                continue;
            }

            $want = @md5_file($from);
            // 源读不出来就没什么可同步的，也别据此去动目标文件 ——
            // md5_file() 失败返回 false，不先挡住的话下面 `=== $want` 会在
            // 目标同样读不出来时变成 false === false，把「两边都坏了」当成一致。
            if ($want === false) {
                $ok = false;
                continue;
            }
            if (is_file($to) && @md5_file($to) === $want) {
                continue;
            }
            $ok = self::copyAtomic($from, $to) && $ok;
        }

        return $ok;
    }

    /**
     * 原子地把一个文件放到位：先写同目录下的临时文件，再 rename 覆盖。
     *
     * 临时文件名带随机后缀，两个并发请求同步同一个文件时不会写进同一个临时文件
     * （那样 rename 出去的就是两份内容交错的残片）。
     */
    protected static function copyAtomic($from, $to)
    {
        if (!Safety::ensureDir(dirname($to))) {
            return false;
        }
        // 临时文件必须与目标同目录：rename() 只在同一文件系统上是原子的，
        // 跨挂载点会退化成「拷贝+删除」，那就白设计了。
        $tmp = $to . '.' . uniqid('', true) . '.mpt-tmp';
        if (!@copy($from, $tmp)) {
            @unlink($tmp);

            return false;
        }
        if (!@rename($tmp, $to)) {
            @unlink($tmp);

            return false;
        }

        return true;
    }
}
