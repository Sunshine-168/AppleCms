<?php

namespace addons\mpt\controller;

use think\addons\Controller;
use think\Db;
use addons\mpt\service\Safety;
use addons\mpt\model\MptTask;
use addons\mpt\service\TaskRunner;
use addons\mpt\service\ConfigSchema;
use addons\mpt\service\Schema;
use addons\mpt\service\Assets;

/**
 * 插件任务台。
 * 路由：/index.php/addons/mpt/admin/index
 *
 * 视图放在插件自己的 view/ 下，运行期也只从这里读——think\addons\Controller
 * 的构造函数把 template.view_path 指回了 ADDON_PATH/mpt/view/。所以不需要往
 * auth.php 加菜单，也不会与后台主题打架。
 *
 * ⚠️ 但**不能**说「完全不碰核心 view_new/」：Service::enable()
 * （vendor/karsonzhang/fastadmin-addons/src/addons/Service.php:414-419）会把
 * addons/mpt/view/ 整个 copydirs 到 application/admin/view_new/mpt/。那份拷贝
 * 运行期没人读，纯属框架的固定动作。disable() 会删掉它，uninstall() 不会——
 * 所以 Mpt::uninstall() 里自己补了一次清理。
 */
class Admin extends Controller
{
    // ⚠️ 不声明 $noNeedLogin / $noNeedRight，理由见 Api 控制器同一位置的说明：
    // 本仓库集成的 think\addons\Controller 从不读这两个属性，鉴权全在 _initialize()。

    // 保持 protected（与 think\addons\Controller 的基类同级）：放宽成 public 会让
    // think\addons\Route::execute() 的 is_callable([$instance,$action]) 认下它，
    // /addons/mpt/xxx/_initialize 就成了一个可路由的 action。鉴权仍在、无实际危害，
    // 但那是白白多出来的一块暴露面。
    protected function _initialize()
    {
        parent::_initialize();

        // ★ 必须自己装一次，不能指望父类 ★
        // 理由与口径完全同 Api::_initialize()（见那里的长注释）：父类那句 Lang::load
        // 取的是 $request->langset()，而 maccms 的 lang_switch_on 是 false，langset
        // 恒空，拼出来的是 addons/mpt/lang/.php，等于没装。本类此前能拿到译文靠的是
        // get_addon_config() 顺带 include config.php → hydrate() → loadLang() 这条
        // **隐式**链，而站长在设置页保存过一次之后 set_addon_fullconfig() 会把
        // config.php 拍平成字面量数组，hydrate() 不再被调用，这条链就断了。
        //
        // 下面三句拒绝文案（未登录 / 无权限 / 超频）全部排在 ConfigSchema::restore()
        // 之前 —— 那是本类里唯一会顺带装语言包的地方。不在这里补的话，链断之后
        // 站长看到的就是 'mpt/err_unauthorized_admin' 这种裸 key。
        // loadLang() 自带「当前 range 装过就跳过」的探测，重复调用是廉价的。
        Safety::loadLang();

        // 走 index.php 入口时核心的 Begin 行为不会强制后台登录，这里自己校验。
        // 无法可靠拼出后台登录地址（admin.php 会被站长改名，运行期只有 JS 全局
        // ADMIN_PATH 知道），所以直接给一句提示而不是跳转。
        if (session('admin_auth') !== '1' || empty(session('admin_info'))) {
            self::deny(lang('mpt/err_unauthorized_admin'));
        }
        // 登录之外还要过节点鉴权，理由见 Safety::adminAllowed()
        if (!Safety::adminAllowed()) {
            self::deny(lang('mpt/err_permission_denied'));
        }

        // ★ 排在下面三道 ensure 之前 ★
        // 这是本插件最后一个没有闸门的入口。它每次都要付一次 md5_file(install.sql)、
        // 一次 Assets 的整目录哈希比对和四条 count()，比任何一个 ajax 动作都贵。
        // 配额按人的操作频率给（正常用法是打开页面看一眼），只挡住按住 F5 不放。
        // 拒绝时给 429 而不是 403：这不是权限问题，反代/浏览器对两者的处置也不同。
        $info = session('admin_info');
        $adminId = is_array($info) && isset($info['admin_id']) ? intval($info['admin_id']) : 0;
        // 取不到管理员 ID 时先在这里挡住，口径同 Api::submit()。
        // 不能直接把 0 交给下面那句：Safety::consumeRateLimit() 对 adminId<=0 是
        // `return false`（无法计数就不放行），落进去会变成一个持续的 429 ——
        // 明明是「会话里没有 admin_id」，却报成「你点太快了」，且再也进不来。
        // 当前不可达（Admin::login 写 session 用的是完整的 mac_admin 行，admin_id
        // 必然存在），但失败模式该是什么就写成什么，别指望它恰好总在。
        if ($adminId <= 0) {
            self::deny(lang('mpt/err_unauthorized_admin'));
        }
        if (!Safety::consumeRateLimit('console', $adminId, 30, 300)) {
            self::deny(lang('mpt/err_rate_limited'), 429);
        }

        // 站长刚在「插件管理 → 设置」里保存过配置的话，核心的 set_addon_fullconfig()
        // 会把 config.php 拍平成字面量、连带冻死文案的语言。这里顺手改回来。
        // 只在任务台页面做（一次小文件读），不放进每 5 秒一次的 ajax 路径。
        //
        // ⚠️ 与 Mpt::appInit() 里那一处不重复，两处都要留：任务台走的是 index.php
        // 入口（ENTRANCE='index'），那道 ENTRANCE==='admin' 的闸门盖不到这里；
        // 反过来，站长保存完设置再也不进任务台时只有那一处能解冻。
        ConfigSchema::restore();

        // 在线升级不会重新调 enable()，新版本的列/索引要靠运行期路径补上。
        // 放在鉴权之后：这一步会发 DDL，不该由一个未登录的请求触发。
        // 指纹没变时只是一次 md5_file + 一次小文件读，见 Schema::ensure()。
        Schema::ensure();

        // 同一个理由的另一半：下面 index() 渲染出的模板加载的是 static/addons/mpt/
        // 下的**拷贝件**，而那份拷贝也只有 install()/enable() 会铺，升级同样补不到。
        // 不补的话升级完浏览器一直拿旧 JS 打新接口，且完全静默。见 Assets::ensure()。
        // 只在这一个页面做：全站只有任务台会加载这些资源，没必要让每个前台请求都比对哈希。
        Assets::ensure();
    }

    /**
     * 鉴权失败的出口。
     *
     * 必须带错误状态码：默认的 200 会让这个页面对反代/CDN/搜索引擎看起来是一张正常
     * 页面，有可能被缓存或收录，之后真正有权限的管理员反而拿到缓存里的拒绝页。
     * 鉴权失败用默认的 403，限流用 429（见 _initialize() 里的说明）。
     * 这里绕开了 TP5 的 Response（要立刻 exit），所以状态码和响应头都得自己给。
     */
    protected static function deny($msg, $status = 403)
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            http_response_code(intval($status));
        }
        // $msg 只来自语言包（我们自己的资产），仍然转义一次：这是直出 HTML 的地方，
        // 将来谁往这里传一个别处来的字符串就不用再想一遍安全问题。
        echo '<meta charset="utf-8">' . htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8');
        exit;
    }

    public function index()
    {
        $cfg = TaskRunner::config();
        $vodId = intval($this->request->param('vod_id', 0));

        $vod = null;
        if ($vodId > 0) {
            $vod = Db::name('vod')->field('vod_id,vod_name,vod_year')->where('vod_id', $vodId)->find();
        }

        // ★ 任务表可能根本不存在 ★
        // 库账号没有 CREATE 权限时 Schema::ensure() 补不出表，它自己特意把建表
        // 异常吞掉了（见 Schema::run() 里 $strict 那段，运行期路径不该吐 500）。
        // 但紧接着这四条 count() 打在不存在的表上照样抛，任务台变成一页 500 ——
        // 那段吞掉等于白写。这里兜住，页面照常渲染，并明确告诉站长是数据层坏了，
        // 免得他对着一屏零和一个不动的列表猜原因。详情只进日志，不带 SQL 外泄。
        $dbError = 0;
        $stats = array('total' => 0, 'running' => 0, 'done' => 0, 'failed' => 0);
        try {
            $stats = array(
                'total' => Db::name('mpt_task')->count(),
                // RUNNING + WORKING 一起算：WORKING 是内部的「已被领取」状态，
                // 对站长而言与「生成中」是同一件事，分开显示只会让人以为任务丢了
                'running' => TaskRunner::busyCount(),
                'done' => Db::name('mpt_task')->where('mpt_status', MptTask::STATUS_DONE)->count(),
                'failed' => Db::name('mpt_task')->where('mpt_status', MptTask::STATUS_FAILED)->count(),
            );
        } catch (\Throwable $e) {
            \think\Log::error('mpt admin/index stats: ' . $e->getMessage());
            $dbError = 1;
        }

        // 推荐 curl -H：token 走请求头不会落进 web server 的 access log。
        // 带 ?token= 的裸 URL 仍然给出，供只能填一个地址的计划任务面板使用。
        $cronUrl = '';
        $cronCmd = '';
        if ($cfg['cron_token'] !== '') {
            // 地址形式跟着站点走（PATH_INFO / 兼容模式 ?s=），见 Safety::addonUrl()
            $endpoint = Safety::addonUrl('api/cron');
            // token 已在 TaskRunner::config() 过白名单（只剩 [A-Za-z0-9_-]），
            // 这里仍然 rawurlencode 一次：它是要拼进 query string 的，编码是这个
            // 位置本来就该做的事，不该依赖上游恰好把危险字符都挡掉了。
            $cronCmd = 'curl -s -H "x-mpt-token: ' . $cfg['cron_token'] . '" "' . $endpoint . '"';
            // 兼容模式下 $endpoint 里已经有 ?s=... 了，再拼一个 ? 会把 token 弄丢
            $cronUrl = $endpoint
                . (strpos($endpoint, '?') === false ? '?' : '&')
                . 'token=' . rawurlencode($cfg['cron_token']);
        }

        // ★ 前端配置整块在 PHP 侧 json_encode，不在模板里逐个拼字符串字面量 ★
        // 模板里写 `token: "{$csrf_token}"` / `retry: "{:lang('mpt/btn_retry')}"` 的话，
        // 任何一个值里出现半角双引号都会当场把这段 <script> 语法打断，整个任务台变白板。
        // 现在 9 份语言包里恰好没有带双引号的条目，但那是巧合不是保证——
        // 将来某次翻译（尤其是英文/德文的引号用法）就会踩上。
        // JSON_HEX_TAG 顺带把 < > 转义掉，值里出现 </script> 也不会提前闭合标签。
        $front = json_encode(array(
            // 兼容模式下这会是 .../index.php?s=/addons/mpt/api/ —— 前端拼 query
            // 时不能无脑加 '?'，见 mpt.js 的 sep()。
            'base' => Safety::addonUrl('api/'),
            'token' => Safety::csrfToken(),
            'presetVodId' => $vod ? intval($vod['vod_id']) : 0,
            'lang' => array(
                'confirmDelete' => lang('mpt/confirm_delete'),
                'confirmCleanup' => lang('mpt/confirm_cleanup'),
                'retry' => lang('mpt/btn_retry'),
                'del' => lang('mpt/btn_delete'),
                'view' => lang('mpt/btn_view'),
                'empty' => lang('mpt/tip_no_task'),
                'pick' => lang('mpt/btn_pick'),
                'working' => lang('mpt/tip_working'),
                'page' => lang('mpt/label_page'),
            ),
        ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        // 某个语言包被存成非 UTF-8 时 json_encode() 返回 false，直接输出会拼出
        // `window.MPT = ;` —— 整个页面的脚本全废。宁可退成空对象，页面还能打开。
        if (!is_string($front)) {
            $front = '{}';
        }

        return $this->fetch('admin/index', array(
            'front_config' => $front,
            'configured' => ($cfg['api_base'] !== '' && $cfg['api_key'] !== '') ? 1 : 0,
            'db_error' => $dbError,
            'script_mode' => $cfg['script_mode'],
            'play_from' => $cfg['play_from'],
            'preset_vod' => $vod,
            'stats' => $stats,
            'cron_cmd' => $cronCmd,
            'cron_url' => $cronUrl,
            'root_path' => rtrim(MAC_PATH, '/'),
            // 资源地址上的内容版本号。上面 Assets::ensure() 只换了磁盘上的文件，
            // 不带这个的话浏览器仍会从缓存里取旧 mpt.js —— 见 Assets::version()。
            'asset_ver' => Assets::version(),
        ));
    }
}
