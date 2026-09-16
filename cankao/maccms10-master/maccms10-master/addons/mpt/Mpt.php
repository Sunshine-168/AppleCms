<?php

namespace addons\mpt;

use think\Addons;
use addons\mpt\service\Safety;
use addons\mpt\service\PlayerSetup;
use addons\mpt\service\TaskRunner;
use addons\mpt\service\ConfigSchema;
use addons\mpt\service\Schema;

/**
 * AI短视频（MPT）插件入口。
 *
 * 接入 MoneyPrinterTurbo（站长自建实例，或任意第三方实例），
 * 为影片合成宣传短视频并写回播放来源。
 *
 * ★ 异步推进为什么是这个形状 ★
 * 核心 Timming::index() 用 method_exists($this,$file) 派发定时任务，只认核心控制器
 * 上的方法，插件无法注册 cron。而 MPT 出片是十分钟量级，同步等待必撞
 * max_execution_time。本插件在**不改核心**的前提下分三层推进：
 *   L1 任务台/编辑页的 JS 每 5s 打一次 api/poll —— 主路径；
 *   L2 app_init 注册 shutdown 回调，加锁 + 60s 节流、每次最多推 1 条 ——
 *      关掉浏览器后只要站点还有访问流量，任务照样往前走；
 *   L3 设置页给出带 token 的 URL，会用的站长可自行贴进宝塔计划任务。
 * L2 的写法照核心 application/common/behavior/MonitorRequest.php:57，不是新发明。
 */
class Mpt extends Addons
{
    // ⚠️ 这里**不要**声明 `public $info`。
    // 插件元信息的唯一事实来源是 info.ini：Addons::getInfo()
    // （vendor/karsonzhang/fastadmin-addons/src/Addons.php:57-68）只 parse 那个文件，
    // 全库没有任何一处读插件类上的 $info。摆一份在这儿的唯一效果是与 info.ini
    // 对不上时误导读代码的人——尤其 state，info.ini 是 0（随主库分发、默认不启用），
    // 写成 1 会让人以为插件是默认开着的。另外两个插件 aicontent / socialws 也都没有它。

    /** shutdown 兜底的最小间隔（秒） */
    const FALLBACK_INTERVAL = 60;

    /**
     * 本次请求是不是走的「兼容模式」URL（index.php?s=/模块/控制器/方法）。
     *
     * ★ 只能在 appInit 里取 ★
     * Request::pathinfo()（thinkphp/library/think/Request.php:404-407）第一次被调用时
     * 会把 $_GET[var_pathinfo] 搬进 $_SERVER['PATH_INFO'] 并**unset 掉**，之后再想
     * 判断这次请求是哪种形式就没有依据了。appInit 跑在 routeCheck 之前，那一刻还在。
     *
     * 用途见 Safety::addonUrl()：关掉 PATH_INFO 的站点上，硬拼
     * /index.php/addons/mpt/... 的地址一律 404，ajax 与入口按钮都得改成 ?s= 形式。
     *
     * 这里是属性不是方法 —— get_addon_autoload_config() 只把 public **方法**登记成钩子
     * （vendor/karsonzhang/fastadmin-addons/src/common.php:213），属性不会污染 extra/addons.php。
     */
    public static $compatUrl = false;

    // ---------------------------------------------------------------- 生命周期

    /**
     * ★ 为什么 install() 与 enable() 干同样的事 ★
     * 只有「上传 zip / 云市场安装」才会走 Service::install() → install()。
     * 随主库一起躺在 addons/ 里的插件，站长在后台看到的只有「启用」，
     * 走的是 Service::enable() → enable()，**install() 永远不会被调用**。
     * 所以建表、铺资源这些事必须放在两条路都会经过的地方，且全部幂等。
     */
    public function install()
    {
        return $this->setup();
    }

    public function enable()
    {
        return $this->setup();
    }

    private function setup()
    {
        // enable()/install() 都跑在一个完整的后台请求里，此时 Lang::range() 已经是
        // 站点语言、default_lang 也已被 Init 行为写好，可以放心加载语言包。
        // （appInit 里**不能**这么做，理由见 Safety::loadLang() 的说明。）
        Safety::loadLang();

        // 强制跑一轮：安装/启用是站长的显式动作，不走「这个版本跑过没有」那道闸门。
        // 运行期（任务台加载、每一轮推进）也会调 Schema::ensure()，那条路径带闸门 ——
        // 在线升级不会重新调 enable()，光靠这里补不到已启用的存量站点。
        Schema::ensure(true);

        // 静态资源不用自己拷：Service::install() 与 Service::enable() 都已经
        // copydirs(addons/mpt/assets → static/addons/mpt/)，disable/uninstall 也会回收。
        $this->ensureCronToken();

        // ensureCronToken() 走的是 set_addon_config()，它会用 var_export() 把
        // config.php 整个拍平（连带把当前语言的文案冻死在里面）。写完立刻改回
        // 「只存值」的形态，理由见 ConfigSchema 顶部说明。
        ConfigSchema::restore();

        // ★ 这里**不**碰 extra/vodplayer.php ★
        // 铺播放来源条目会顺带重写 static/js/playerconfig.js 与 static/player/*.js，
        // 都是站长的运行期数据。启用插件的那一刻站长连 api_base 都还没填，为一个
        // 可能永远不会用起来的功能去改站点播放器配置，代价与收益不成比例。
        // 真正需要它的时刻是第一条成片写回影片时，TaskRunner::writeBackPlaySource()
        // 已经会调 PlayerSetup::ensure()，那里补是幂等的、也一样加锁。

        return true;
    }

    public function disable()
    {
        // 只是停用：数据一律保留，站长重新启用后任务列表还在
        return true;
    }

    /**
     * ⚠️ 卸载会 **DROP 掉任务表**，全部生成记录随之消失，且不可恢复。
     * 只想暂时停用请用「禁用」（disable() 一条数据都不动，重新启用后列表还在）。
     * 已经写回影片播放来源的成片文件留在 upload/mpt/ 下不删——删了那些播放组就成死链；
     * 反过来，从来没被任何影片引用过的成片（关掉自动写回、或写回失败的那些）会在
     * DROP 之前一并回收，否则表一没就再也没人认得出它们。
     * 同理，只要还有影片挂着这个播放来源，vodplayer.php 里的条目与
     * static/player/{from}.js 这个 shim 也一并保留：三样东西缺一样，那些播放组就播不了。
     * 这段说明同时出现在设置页 uninstall 提示（lang: mpt/tip_uninstall）。
     */
    public function uninstall()
    {
        // ★ 必须排在 DROP 之前 ★
        // 「成片不删」只覆盖了已写回播放来源的那些。auto_writeback=0 的站点、
        // 以及写回失败（播放组被占用、四列不等长）的任务，产物从来没有任何影片
        // 引用它 —— 表一 DROP，连 mpt_result_url 都没了，几十上百 MB 永久留在
        // upload/mpt/ 下，站长既看不见也无从追溯。判据仍然只有 filterOrphans()
        // 一处（还挂在播放来源上的一律不动），见 TaskRunner::purgeOrphanResults()。
        // 它自己吞掉全部异常，清垃圾失败不会让卸载失败。
        TaskRunner::purgeOrphanResults();

        \think\Db::execute('DROP TABLE IF EXISTS `' . config('database.prefix') . 'mpt_task`');

        // 表都没了，版本标记留着只会在 runtime/ 里当垃圾
        Schema::forget();

        $cfg = TaskRunner::config();
        // 只回收插件自己铺的播放器 shim，且**只在没有任何影片还挂着这个播放来源时**。
        //
        // ★ 不加这道判断的话，上面那句"成片不删、免得播放组成死链"等于白写 ★
        // vodplayer.php 里的条目本来就不删（站长运行期数据，铁律 4，何况可能已被手工改过），
        // 而前台播放页是按 from 去加载 static/player/{from}.js 的（核心自带的
        // dplayer.js / videojs.js 就是同一角色）。无条件删掉它，成片在、条目在、
        // 播放组也在，唯独播放器起不来 —— 只是把"死链"换成了更难排查的"白板播放器"。
        // 判据见 PlayerSetup::fromInUse()。
        if ($cfg['play_from'] !== '' && !PlayerSetup::fromInUse($cfg['play_from'])) {
            PlayerSetup::removeShim($cfg['play_from']);
        }

        // static/addons/mpt/ 由 Service::uninstall() 自己 rmdirs 掉（Service.php:347-351），
        // 这里不重复处理。
        //
        // 但 Service::enable() 一共铺了**三个**地方（Service.php:398-421），
        // Service::disable() 三个都收（:471-502），Service::uninstall() 只收第一个。
        // 直接卸载一个处于启用态的插件，剩下两份就永久留在核心目录里没人读：
        //   - application/admin/view_new/mpt/   ← addons/mpt/view/ 的拷贝
        //   - static_new/addons/mpt/            ← addons/mpt/assets/ 的另一份拷贝
        // 两份都得自己收，漏掉任何一份都是同一种残留。
        if (function_exists('rmdirs')) {
            $leftovers = array(
                APP_PATH . 'admin' . DS . 'view_new' . DS . 'mpt' . DS,
                ROOT_PATH . 'static_new' . DS . 'addons' . DS . 'mpt' . DS,
                // 本插件的六把锁与两个版本标记，见 Safety::runtimeFile()
                RUNTIME_PATH . 'mpt' . DS,
            );
            foreach ($leftovers as $dir) {
                if (is_dir($dir)) {
                    @rmdirs($dir);
                }
            }
        }

        return true;
    }

    // ---------------------------------------------------------------- 钩子

    /**
     * Hook: app_init
     */
    public function appInit()
    {
        // 这个方法跑在**每一个**请求上，所以只做必需的事。
        // 不在这里探测/补拷静态资源（框架的 install/enable 已经拷过），
        // 也不注册 addons\mpt\service / model 命名空间 —— 插件框架启动时
        // 已经 Loader::addNamespace('addons', ADDON_PATH)，子命名空间自动解析。
        //
        // ★ 这里也**不**加载语言包 ★
        // 插件的 app_init 钩子跑得比核心行为早（见 Safety::loadLang() 的说明），这一刻
        // 装进去的条目会落在一个马上就要被丢掉的 Lang range 里，等于白装。
        // 真正需要文案的两个地方各自按需加载：viewFilter() 与 runFallbackPoll()。

        // maccms 默认 url_route_on=false，fastadmin-addons 的 addons/:addon 路由匹配不到。
        // 只在请求确实指向本插件时才打开路由检查，避免影响全站 URL 解析。
        //
        // 不拿整个 REQUEST_URI 去 strpos：那样 /vod/detail?x=addons/mpt 这种请求也会
        // 被命中。routeMust 是 false，匹配不上会照常走模块分发，没有实际危害，
        // 但没必要为一个无关的 query 参数改变那次请求的 URL 解析方式。
        //
        // ★ 但**必须**认兼容模式的 ?s= ★
        // 关掉 PATH_INFO 的站点，本插件的地址长这样：index.php?s=/addons/mpt/api/poll。
        // 只看 path 的话这类站点永远打不开路由检查，整个任务台 404 —— 而且是「插件装上了
        // 却完全用不了」这种最难排查的坏法。var_pathinfo 的值从配置读（默认 's'）。
        $pathVar = (string) \think\Config::get('var_pathinfo');
        $compatPath = ($pathVar !== '' && isset($_GET[$pathVar]) && is_string($_GET[$pathVar]))
            ? $_GET[$pathVar] : '';
        // 这次请求带了兼容模式参数，就说明整个站点在用这种形式（与是不是本插件无关），
        // 后面生成 ajax 地址与入口按钮时要跟着走。见 Safety::addonUrl()。
        self::$compatUrl = ($compatPath !== '');

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $path = strtok($uri, '?');
        $hit = ($path !== false && strpos($path, 'addons/mpt') !== false)
            || ($compatPath !== '' && strpos($compatPath, 'addons/mpt') !== false);
        if ($hit) {
            \think\App::route(true);
        }

        // 把 config.php 改回「只存值」形态，理由见 ConfigSchema 顶部说明。
        //
        // ★ 为什么这一下必须挂在后台入口上，而不是只放在任务台里 ★
        // 站长在「插件管理 → 设置」点保存，核心的 set_addon_fullconfig() 会用
        // var_export() 把**当时那一种语言**的 title/tip 冻进 config.php。此前唯一
        // 会解冻它的是 Admin::_initialize()，也就是说站长保存完设置、再也不进任务台
        // 的话，设置页的标题与提示会一直停在保存那一刻的语种 —— 站点换过语言就再也
        // 对不上，而这正是 ConfigSchema 整个存在的理由，只补一半等于没补。
        // 挂在这里，任何一次后台请求（包括保存后的那次跳转）都会顺手解冻。
        //
        // 只在后台入口做：ENTRANCE 由入口文件写死（admin.php:25，改名后的副本也带着），
        // 前台每秒几十次的请求不该为它多付一次文件读。绝大多数时候 needsRestore()
        // 只是一次 1KB 的 file_get_contents + strpos 就返回 false。
        // 这一步不依赖语言包，也不碰 MAC_PATH，放在 appInit 这个很早的时点是安全的。
        if (defined('ENTRANCE') && ENTRANCE === 'admin') {
            ConfigSchema::restore();
        }

        $this->maybeScheduleFallbackPoll();
    }

    /**
     * Hook: view_filter
     * 往后台影片编辑页塞一个「生成宣传片」按钮。
     *
     * 核心的 view_new 模板没有插件挂载点（vod/info.html 里的 AI 按钮全是硬编码），
     * 只能在渲染结果上追加脚本。这里刻意不做 aicontent 那种「正则扫所有 input/textarea」，
     * 只挂在已知的 #btn_ai_seo_generate 旁边，找不到就什么都不做——注入面越小越不易碎。
     */
    public function viewFilter(&$content)
    {
        // 判断从最便宜的排到最贵的：这个钩子挂在 view_filter 上，
        // **每一次**模板渲染都会走一遍。模块/控制器/动作三个判断都是读已解析好的
        // 请求属性，而 strpos() 要扫整份 HTML、get_addon_info() 要读 info.ini，
        // 把后两者排在前面等于为了每秒几十次的无关渲染白干活。
        $req = \think\Request::instance();
        if (strtolower($req->module()) !== 'admin') {
            return;
        }
        $controller = strtolower($req->controller());
        $action = strtolower($req->action());
        if ($controller !== 'vod' || $action !== 'info') {
            return;
        }

        $vodId = intval($req->param('id', 0));
        if ($vodId <= 0) {
            return;
        }

        if (strpos($content, '</body>') === false) {
            return;
        }

        $info = function_exists('get_addon_info') ? get_addon_info('mpt') : array();
        if (empty($info) || intval(isset($info['state']) ? $info['state'] : 0) !== 1) {
            return;
        }

        // ★ 没配好就不要往影片编辑页塞这个按钮 ★
        // api_base / api_key 任缺其一，任务台上什么也做不了（create() 第一件事就是
        // 返回「未配置」）。影片编辑页本来就挤，摆一个点进去只会看到一句提示的按钮
        // 是纯噪音。配好之后它自己会出现；在此之前插件仍然能从「插件管理」进得去。
        // 这一步要付一次 get_addon_config()，但本方法在此之前已经用模块/控制器/动作
        // 三个判断把无关渲染全挡掉了，只有 admin/vod/info 这一个页面会走到这里。
        $cfg = TaskRunner::config();
        if ($cfg['api_base'] === '' || $cfg['api_key'] === '') {
            return;
        }

        // 视图渲染期语言环境已经就绪，这时候加载语言包才落在正确的 range 上
        Safety::loadLang();

        // 这个按钮只负责跳到插件任务台，不打任何接口，所以不需要往页面里注入 token。
        // 地址走 Safety::addonUrl()，兼容模式的站点上才不会跳出一个 404。
        $label = lang('mpt/btn_generate');
        $url = \addons\mpt\service\Safety::addonUrl('admin/index');
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'vod_id=' . $vodId;
        // 转义口径与 Admin::index() 那份 front_config 保持一致：两处都是
        // 「把 PHP 值嵌进 <script>」，JSON_HEX_TAG 保证值里出现 </script>
        // 也不会提前闭合标签。这两个值现在都是自己的资产（语言包 + 站点路径），
        // 风险为零，但两处口径不一样的话，将来谁改了其中一处的来源就会踩空。
        $enc = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $script = '<script>(function(){'
            . 'var anchor=document.getElementById("btn_ai_seo_generate")||document.getElementById("btn_ai_cover_generate");'
            . 'if(!anchor){return;}'
            . 'var b=document.createElement("button");b.type="button";'
            . 'b.className="layui-btn layui-btn-primary";b.style.marginLeft="6px";'
            . 'b.textContent=' . json_encode($label, $enc) . ';'
            . 'b.onclick=function(){window.open(' . json_encode($url, $enc) . ');};'
            . 'anchor.parentNode.insertBefore(b,anchor.nextSibling);'
            . '})();</script>';

        // 只注入**最后一个** </body>。str_replace 会替换掉每一个匹配，页面里出现第二个
        // （转义示例、内嵌片段、注释掉的模板残留）就会插入好几份同样的脚本，
        // 按钮跟着重复出现好几个。上面那道 strpos 只是「有没有」的廉价闸门，
        // 真正定位得用 strrpos。
        $pos = strrpos($content, '</body>');
        if ($pos !== false) {
            $content = substr_replace($content, $script, $pos, 0);
        }
    }

    // ---------------------------------------------------------------- L2 兜底推进

    /**
     * 判断这次请求要不要挂一个 shutdown 推进回调。
     * 判断本身必须极轻——它跑在每一个前台请求上。
     */
    private function maybeScheduleFallbackPoll()
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        // ★ 没有 fastcgi_finish_request() 就不挂这个回调 ★
        // shutdown 里的 advance() 会下载成片，Safety::downloadToFile() 还把
        // set_time_limit 解成了 0。在 PHP-FPM 下响应已经先冲出去了，访客无感；
        // 但 Apache mod_php / CGI 下没有这个函数，访客的请求会被一次几十 MB 的
        // 下载卡住好几分钟且没有上限——拿前台访客的体验去换后台任务进度，不划算。
        // 这类站点走 L1（任务台开着）或 L3（cron URL）即可。
        if (!function_exists('fastcgi_finish_request')) {
            return;
        }
        // ★ 节流排在最前面，比 get_addon_info() 还前 ★
        // 这个方法跑在每一个前台请求上，而 get_addon_info() 第一次调用会
        // parse_ini_file() 读 info.ini（Addons::getInfo() 只有请求内缓存，
        // vendor/karsonzhang/fastadmin-addons/src/Addons.php:59-68）。
        // 一次 filemtime() 比一次 ini 解析便宜得多，先用它把 99% 的请求挡掉。
        $lock = self::lockFile();
        if (is_file($lock) && (time() - (int) @filemtime($lock)) < self::FALLBACK_INTERVAL) {
            return;
        }
        $info = function_exists('get_addon_info') ? get_addon_info('mpt') : array();
        if (empty($info) || intval(isset($info['state']) ? $info['state'] : 0) !== 1) {
            self::backoffLock($lock);

            return;
        }
        $cfg = TaskRunner::config();
        if (!$cfg['poll_fallback_enabled']) {
            self::backoffLock($lock);

            return;
        }

        // 用闭包而不是 array(__CLASS__,'runFallbackPoll')：
        // get_addon_autoload_config() 会把插件类上**每一个** public 方法都登记成钩子，
        // 所以内部方法一律保持 private，避免往 extra/addons.php 里塞垃圾钩子。
        register_shutdown_function(function () {
            self::runFallbackPoll();
        });
    }

    /**
     * 决定不挂回调时，也把节流文件的时间戳推到现在。
     *
     * ★ 不补这一下，上面那道 filemtime 短路在「关掉兜底」这条分支上永远不生效 ★
     * runtime/mpt/poll.lock 只有 runFallbackPoll() 会创建。站长把 poll_fallback_enabled
     * 设成 0 之后就再没有任何路径去碰它，于是 maybeScheduleFallbackPoll() 里那句
     * 「99% 的请求靠一次 filemtime 挡掉」恒不命中 —— **每一个**前台请求都要白付
     * 一次 parse_ini_file(info.ini) + include config.php + Lang::load(144 条语言条目)
     * + 40 次 lang()。关掉一个功能反而比开着它更贵，显然不是本意。
     *
     * 只 touch、不写内容：runFallbackPoll() 的节流判据是
     * 「mtime 在 60s 内 **且** filesize > 0」，空文件不会把真正的推进误挡掉
     * （何况走到这里的分支根本不会挂回调）。站长把兜底改回开启后，
     * 最迟 FALLBACK_INTERVAL 秒就会重新开始推进。
     */
    private static function backoffLock($lock)
    {
        @touch($lock);
    }

    /**
     * 在响应发出之后推进 1 条任务。
     * 三重保险：文件锁（并发只有一个在跑）、时间戳节流、单次只推 1 条。
     */
    private static function runFallbackPoll()
    {
        $lock = self::lockFile();
        $fp = @fopen($lock, 'c');
        if ($fp === false) {
            return;
        }
        if (!@flock($fp, LOCK_EX | LOCK_NB)) {
            @fclose($fp);

            return;
        }
        clearstatcache(true, $lock);
        if ((time() - (int) @filemtime($lock)) < self::FALLBACK_INTERVAL && @filesize($lock) > 0) {
            @flock($fp, LOCK_UN);
            @fclose($fp);

            return;
        }
        @ftruncate($fp, 0);
        @fwrite($fp, (string) time());
        @fflush($fp);
        @touch($lock);

        // 响应已经生成完毕，先冲出去，别让访客等这一下。
        // maybeScheduleFallbackPoll() 已经保证只有存在这个函数时才会挂回调，
        // 这里再判一次纯属防御（回调可能被别处直接调用）。
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }

        try {
            if (TaskRunner::hasPending()) {
                // 推进路径会把 lang('mpt/err_*') 写进 mpt_task.mpt_error 落库，
                // 语言包没装的话站长在任务台看到的就是一行裸 key。appInit 那次
                // 加载是无效的（见 Safety::loadLang() 说明），这里补一次真正生效的。
                Safety::loadLang();
                TaskRunner::advance(1);
            }
        } catch (\Throwable $e) {
            // 这段跑在 shutdown 里，响应已经发出去了；无论出什么都只记日志，
            // 不能让它影响别的 shutdown 回调（\Exception 接不住 \Error）
            \think\Log::error('mpt fallback poll: ' . $e->getMessage());
        }

        @flock($fp, LOCK_UN);
        @fclose($fp);

        // ★ 这一层的日志必须自己冲盘，否则一行都写不出去 ★
        // Log::error()/info() 走的是 Log::record()，只把消息塞进内存缓冲区
        // （thinkphp/library/think/Log.php:100-106，只有 IS_CLI 才顺手 save()）。
        // 真正把缓冲区写出去的 Log::save() 挂在 Error::appShutdown() 上
        // （thinkphp/library/think/Error.php:83-94），而那个 shutdown 回调是
        // thinkphp/base.php:62 在启动最早期注册的 —— shutdown 回调按注册顺序执行，
        // 它**排在本回调前面**，等我们跑到这里时它早已 save() 完并清空了缓冲区。
        // 不自己补一次的话，上面那个 catch 以及 TaskRunner 推进路径里的
        // Log::error/info 全部无声消失。而 L2 恰恰是唯一无人值守的一层，
        // 出了问题只能靠日志回溯，没有日志等于这层坏了也没人知道。
        try {
            \think\Log::save();
        } catch (\Throwable $e) {
            // 日志都落不了盘就没什么可补救的了，但绝不能让它掀翻别的 shutdown 回调
        }
    }

    private static function lockFile()
    {
        return Safety::runtimeFile('poll.lock');
    }

    // ---------------------------------------------------------------- 杂项

    /**
     * L3 用的 cron token：安装时生成一次，之后不动。
     */
    private function ensureCronToken()
    {
        if (!function_exists('get_addon_config') || !function_exists('set_addon_config')) {
            return;
        }
        $cfg = get_addon_config('mpt');
        if (is_array($cfg) && !empty($cfg['cron_token'])) {
            return;
        }
        if (!is_array($cfg)) {
            $cfg = array();
        }
        // CSPRNG，理由见 Safety::randomToken()：这是 api/cron 的唯一凭据
        $cfg['cron_token'] = \addons\mpt\service\Safety::randomToken();
        try {
            set_addon_config('mpt', $cfg);
        } catch (\Exception $e) {
            // token 生成失败不阻断安装，站长可在设置页手填
        }
    }
}
