<?php

namespace addons\mpt\controller;

use think\addons\Controller;
use think\Db;
use addons\mpt\service\Safety;
use addons\mpt\model\MptTask;
use addons\mpt\service\MptClient;
use addons\mpt\service\TaskRunner;

/**
 * ajax 接口，全部返回 JSON。
 * 路由：/index.php/addons/mpt/api/xxx
 *
 * ⚠️ 必须走 index.php 入口：后台入口（manage.php 等）会把非 admin 模块 302 掉，
 * 前端脚本一律用 ROOT_PATH + '/index.php/addons/mpt/...' 拼地址。
 */
class Api extends Controller
{
    // ⚠️ 这里**不要**声明 $noNeedLogin / $noNeedRight。
    // 那两个属性是 FastAdmin 的东西，在本仓库集成的 think\addons\Controller 里
    // 只有声明、没有任何读取点（全库 grep 只命中 Controller.php:29,35 两行定义）。
    // 摆在这儿会让人以为改它就能改鉴权，实际上鉴权全在下面的 _initialize()。

    /** 只有这些动作不要求后台会话（走各自的凭据校验） */
    protected $publicActions = array('cron');

    // 保持 protected（与 think\addons\Controller 的基类同级）：放宽成 public 会让
    // think\addons\Route::execute() 的 is_callable([$instance,$action]) 认下它，
    // /addons/mpt/xxx/_initialize 就成了一个可路由的 action。鉴权仍在、无实际危害，
    // 但那是白白多出来的一块暴露面。
    protected function _initialize()
    {
        parent::_initialize();

        // ★ 必须自己装一次，不能指望父类 ★
        // think\addons\Controller::_initialize() 那句 Lang::load 取的是
        // $request->langset()，而 maccms 的 lang_switch_on 是 false
        // （application/config.php:44），Lang::detect() 从不执行，langset 为空 ——
        // 它拼出来的是 addons/mpt/lang/.php，文件不存在，等于没装。
        // 本类此前能拿到译文，靠的是 parent::_initialize() 里 get_addon_config()
        // 顺带 include config.php → ConfigSchema::hydrate() → items() → loadLang()
        // 这条**隐式**链。而它有一环恰好是本插件自己承认会断的：站长在设置页保存后，
        // 核心 set_addon_fullconfig() 会把 config.php 拍平成 var_export 字面量数组，
        // hydrate() 不再被调用；修回它的 ConfigSchema::restore() 只挂在
        // ENTRANCE==='admin' 与 Admin::_initialize() 上，而本控制器走的是 index 入口。
        // 那之后下面三句鉴权文案就会变成裸 key（实测 lang('mpt/err_bad_token') 返回
        // 'mpt/err_bad_token'）。loadLang() 自带「当前 range 装过就跳过」的探测，
        // 重复调用是廉价的，补在这里就不必再推理那条链。
        Safety::loadLang();

        $action = strtolower($this->request->action());
        if (in_array($action, $this->publicActions, true)) {
            return;
        }

        if (session('admin_auth') !== '1' || empty(session('admin_info'))) {
            $this->out(0, lang('mpt/err_unauthorized_admin'));
        }
        // 登录之外还要过节点鉴权，理由见 Safety::adminAllowed()
        if (!Safety::adminAllowed()) {
            $this->out(0, lang('mpt/err_permission_denied'));
        }

        // ★ token 对 GET 也要校验，不能只校验 POST ★
        // 这里的 GET 动作并不是「只读」：poll 会下载文件、改影片、重写
        // extra/vodplayer.php；preview 在 script_mode=llm 时会烧钱调大模型。
        // 只在 isPost 时校验的话，一个 <img src=".../api/poll"> 就能让带着
        // 登录态的管理员在任意第三方页面上触发这些动作。
        // token 优先走请求头。放 query string 里的话它会原样落进 nginx/apache 的
        // access log —— 与 cron() 那边拒绝把 cron_token 放进 URL 是同一个理由，
        // 两处口径要一致。仍然接受表单字段，供不方便设请求头的调用方使用。
        $token = (string) $this->request->header('x-mpt-csrf', '');
        if ($token === '') {
            $token = self::stringInput('_csrf_token');
        }
        if (!hash_equals(Safety::csrfToken(), $token)) {
            $this->out(0, lang('mpt/err_bad_token'));
        }
    }

    /**
     * 长活儿开跑前先把会话写盘释放锁。
     *
     * PHP 的会话文件是独占锁，一直持有到请求结束。推进任务里的下载可能几十秒到
     * 几分钟，期间这个管理员浏览器的其它请求（包括整个后台）全都会被卡住。
     * 这之后就不能再写 session 了，所以只在鉴权做完之后调。
     */
    protected function releaseSession()
    {
        if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
    }

    protected function out($code, $msg = '', $data = array())
    {
        // 这里绕开了 TP5 的 Response，所以响应头要自己给——不给的话浏览器按
        // text/html 收，前端只能靠自己 JSON.parse 兜着，调试时也看不出是 JSON。
        // poll 之类的动作可能在 releaseSession() 之后才输出，用 headers_sent()
        // 兜一下，避免在已经有输出的情况下抛 warning。
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(array('code' => intval($code), 'msg' => (string) $msg, 'data' => $data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 取一个必须是字符串的输入项。
     * 直接 (string) input() 的话，调用方传成数组（`_csrf_token[]=x`）会触发
     * 「Array to string conversion」的 notice —— display_errors 开着时那行 warning
     * 会先于 JSON 打出去，把响应体弄成解析不了的东西。
     */
    protected static function stringInput($key)
    {
        $raw = input($key, '');

        return is_string($raw) ? $raw : '';
    }

    protected function adminId()
    {
        $info = session('admin_info');

        return is_array($info) && isset($info['admin_id']) ? intval($info['admin_id']) : 0;
    }

    // ------------------------------------------------------------------ 动作

    /** 按名称/ID 找影片，供任务台选片 */
    public function searchVod()
    {
        // 关键词走 LIKE '%kw%'，前导通配符用不上任何索引，几十万行的 vod 表上
        // 一次就是一趟全表扫。这是本类里唯一一个会打到核心大表的动作，
        // 不能是唯一没有闸门的那个。配额给得宽（正常用法是敲一次搜一次），
        // 只挡住「按住不放刷接口」这种把库拖垮的用法。
        if (!Safety::consumeRateLimit('search', $this->adminId(), 30, 300)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // 上面那段说的全表扫在几十万行的 vod 上是秒级的，而这段时间里会话文件锁
        // 一直被占着 —— 同一个管理员的任务台轮询和其它标签页全部排在后面，
        // 表现成「搜一次片，整个后台卡住几秒」。口径与 preview/submit/poll 一致：
        // 慢动作一律先把会话写盘释放锁。必须排在限流之后，adminId() 要读会话。
        $this->releaseSession();
        // 不再 mac_filter_xss()：addons Controller 已经对整个 request 做过
        // trim,strip_tags,htmlspecialchars，再转一次会把片名里的 & 变成 &amp;amp;，
        // LIKE 永远匹配不上。这里只把实体还原成原文再去查。
        $wd = TaskRunner::plainText(self::stringInput('wd'));
        if ($wd === '') {
            $this->out(0, lang('mpt/err_keyword_required'));
        }
        // ★ 与 status()/cleanup() 同一个理由：查询就写在控制器里，兜底也得写在这里 ★
        // 别因为「vod 是核心表、必然存在」就省掉这一层：本动作是全类唯一一个在
        // 几十万行的 vod 上做前导通配符 LIKE 的查询，锁等待超时 / DB 断连抛出的
        // 概率比其余各格都高。out() 之外的任何异常都会走 TP5 的异常处理器吐一页
        // HTML，前端 JSON.parse 失败后只显示一句 "bad response" —— 真实原因一个字
        // 都看不到。详情进日志，对外只给通用文案，不带 SQL。
        try {
            $q = Db::name('vod')->field('vod_id,vod_name,vod_year,vod_class');
            if (ctype_digit($wd)) {
                $q->where('vod_id', intval($wd));
            } else {
                // 转义 LIKE 的通配符，否则站长搜 "100%" 会变成匹配一切
                $esc = str_replace(array('\\', '%', '_'), array('\\\\', '\%', '\_'), $wd);
                $q->where('vod_name', 'like', '%' . $esc . '%');
            }
            $list = $q->limit(20)->order('vod_id desc')->select();
        } catch (\Throwable $e) {
            \think\Log::error('mpt api/searchVod: ' . $e->getMessage());
            // out() 自己 exit，这里不需要 return
            $this->out(0, lang('mpt/err_internal'));
        }

        $this->out(1, '', array('list' => $list ? $list : array()));
    }

    /** 预览脚本，不落库 */
    public function preview()
    {
        // script_mode=llm 时每次预览都是一次真金白银的大模型调用，必须限流
        if (!Safety::consumeRateLimit('preview', $this->adminId(), 20, 200)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // llm 档要等一次大模型返回，而 AiProvider 的 timeout 只有下限没有上限
        // （application/common/util/AiProvider.php:28 是 max(5,...)）。不放会话锁的话，
        // 这段时间里同一个管理员的所有后台请求都排在会话文件锁后面，界面像死了一样。
        $this->releaseSession();
        $vodId = intval(input('vod_id', 0));
        $res = TaskRunner::preview($vodId);
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    /** 建任务并提交 */
    public function submit()
    {
        if (!$this->request->isPost()) {
            $this->out(0, lang('mpt/err_post_required'));
        }
        $adminId = $this->adminId();
        if ($adminId <= 0) {
            $this->out(0, lang('mpt/err_unauthorized_admin'));
        }
        if (!Safety::consumeRateLimit('submit', $adminId, 5, 30)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // create() 会同步往 MPT 打一次 POST（最长 clampTimeout 60 秒）。
        // 不放会话锁的话，站长点完提交，任务台自己 5 秒一次的 poll 与整个后台
        // 都被堵在会话文件锁上，表现成界面假死。理由同 releaseSession() 的说明。
        $this->releaseSession();

        $res = TaskRunner::create(
            intval(input('vod_id', 0)),
            $adminId,
            self::stringInput('subject'),
            self::stringInput('script'),
            // terms 允许是数组（前端也可能提交 terms[]），create() 两种都收
            input('terms', '')
        );
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    /** 每页任务数 */
    const PAGE_SIZE = 20;

    /**
     * 任务台自动轮询那两个动作（poll / status）的配额。
     *
     * ★ 为什么不能照搬 submit 那种紧配额 ★
     * 这两个是 mpt.js 的定时器**自己**打的：tick() 每 5 秒一轮，一轮一次 poll
     * 加一次 status，也就是每个开着的任务台标签页 12 次/分钟、720 次/小时。
     * 按人的操作频率去设阈值，站长开两三个标签页就会被自己的页面刷到限流 ——
     * 而 refreshList() 拿到 code!=1 是直接 return 的，表现成"任务列表不动了"，
     * 比不加限流糟得多。
     *
     * 所以阈值按**客户端自己的节奏**来定，留一个数量级的余量：
     * 120/分钟 ≈ 10 个任务台同时开着，3600/小时 ≈ 5 个连开一小时。
     * 正常用法碰不到，而一个丢掉 5 秒间隔的死循环（每分钟成百上千次）一定撞上。
     * 挡的就是后者：这两个动作是本类里最后两个没有闸门的，其余七个都有。
     */
    const POLL_RATE_PER_MIN = 120;
    const POLL_RATE_PER_HOUR = 3600;

    /**
     * 任务列表 / 单条状态。
     *
     * ★ 必须分页 ★
     * 早先是固定 limit(50) 且前端无翻页：mpt_task 只增不减，装久了第 51 条之后的
     * 任务在后台既看不到也删不掉，只能进数据库手删——等于功能有个天花板。
     * 这里给出 total/page，前端据此翻页，配合 cleanup() 就有完整的收口。
     */
    public function status()
    {
        // 两条走索引的查询，本身很轻；配额在这里的作用只是别让它成为唯一一个
        // 可以无限刷的动作。阈值见 POLL_RATE_PER_MIN 的说明。
        if (!Safety::consumeRateLimit('status', $this->adminId(), self::POLL_RATE_PER_MIN, self::POLL_RATE_PER_HOUR)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        $taskId = intval(input('task_id', 0));
        $vodId = intval(input('vod_id', 0));
        $page = max(1, intval(input('page', 1)));

        // 条件用闭包铺，count 与 select 各建一次查询：Query 对象是有状态的，
        // 同一个实例先 count() 再 select() 会把聚合的痕迹带进第二次查询。
        $filter = function ($q) use ($taskId, $vodId) {
            if ($taskId > 0) {
                $q->where('mpt_id', $taskId);
            } elseif ($vodId > 0) {
                $q->where('mpt_obj_id', $vodId)->where('mpt_mid', 1);
            }

            return $q;
        };

        // ★ 与 poll 同一个理由：这个动作也是 5 秒一次，也必须只吐 JSON ★
        // 表不存在（库账号没有 CREATE 权限，Schema::ensure() 补不出来）时这两条
        // 查询会抛 PDOException，没有 catch 的话冒出去就是一页 HTML，任务台每
        // 5 秒弹一句 "bad response"。详情进日志，对外只给通用文案，不带 SQL。
        try {
            $total = $filter(Db::name('mpt_task'))->count();
            $pageCount = max(1, (int) ceil($total / self::PAGE_SIZE));
            // 站长停在第 5 页时把该页任务删光了，页码要收回来，不然看到的是空表
            if ($page > $pageCount) {
                $page = $pageCount;
            }

            $rows = $filter(Db::name('mpt_task'))
                ->order('mpt_id desc')
                ->limit(($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE)
                ->select();
        } catch (\Throwable $e) {
            \think\Log::error('mpt api/status: ' . $e->getMessage());
            // out() 自己 exit，这里不需要 return
            $this->out(0, lang('mpt/err_internal'));
        }

        $list = array();
        foreach ((array) $rows as $r) {
            $list[] = $this->rowForApi($r);
        }
        $this->out(1, '', array(
            'list' => $list,
            'total' => intval($total),
            'page' => $page,
            'page_count' => $pageCount,
        ));
    }

    /**
     * 清理历史任务记录。
     *
     * 只清「已完成 / 已失败」且早于 N 天的记录，正在跑的一条都不碰。
     * 成片文件的处置口径与 remove() 完全一致（还挂在影片播放来源上的就留着，
     * 免得把播放组变成死链），所以直接复用 TaskRunner::removeMany()，不另写一套。
     */
    public function cleanup()
    {
        if (!$this->request->isPost()) {
            $this->out(0, lang('mpt/err_post_required'));
        }
        if (!Safety::consumeRateLimit('cleanup', $this->adminId(), 2, 10)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        $days = intval(input('days', 30));
        $days = max(1, min(365, $days));
        $this->releaseSession();

        // ★ 与 status() 同一个理由：表可能根本不存在，异常裸着冒出去就是一页 HTML ★
        // 这里的查询在控制器里、removeMany() 又是另一处触点，两段一起兜住。
        // 详情进日志，对外只给通用文案，不带 SQL。
        try {
            $ids = Db::name('mpt_task')
                ->whereIn('mpt_status', array(MptTask::STATUS_DONE, MptTask::STATUS_FAILED))
                ->where('mpt_time_update', '<', time() - $days * 86400)
                ->order('mpt_id asc')
                // 单次设上限：一次点击要删几万行的话，请求会超时且删到一半，
                // 站长再点一次继续即可（返回值里带 removed，看得出还有没有）
                ->limit(500)
                ->column('mpt_id');

            // 整批四条查询，不是逐条 remove()（那是 500×4 条 SQL），见 removeMany() 的说明
            $removed = TaskRunner::removeMany((array) $ids);
        } catch (\Throwable $e) {
            \think\Log::error('mpt api/cleanup: ' . $e->getMessage());
            // out() 自己 exit，这里不需要 return
            $this->out(0, lang('mpt/err_internal'));
        }

        $this->out(1, sprintf(lang('mpt/tip_cleanup_done'), $removed), array('removed' => $removed));
    }

    /**
     * L1 主路径：页面开着时由 JS 每 5s 打一次，推进任务。
     */
    public function poll()
    {
        if (!$this->request->isPost()) {
            $this->out(0, lang('mpt/err_post_required'));
        }
        // 限流要排在 releaseSession() 之前：adminId() 读的是会话。
        // 真正的重活儿另有 advance.lock 那道 LOCK_NB 闸门兜着（抢不到立刻返回 busy），
        // 所以这里挡的不是"并发跑太多轮"，而是"请求本身被无限放大"。
        // 阈值见 POLL_RATE_PER_MIN 的说明 —— 必须容得下多个任务台同时开着。
        if (!Safety::consumeRateLimit('poll', $this->adminId(), self::POLL_RATE_PER_MIN, self::POLL_RATE_PER_HOUR)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        $this->releaseSession();
        $res = TaskRunner::advance(intval(input('limit', 0)));
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    public function retry()
    {
        if (!$this->request->isPost()) {
            $this->out(0, lang('mpt/err_post_required'));
        }
        // 重试同样会真的往 MPT 提交一次任务，配额口径与 submit 一致，
        // 否则限流可以直接绕过：建一条任务再狂点重试就行。
        if (!Safety::consumeRateLimit('submit', $this->adminId(), 5, 30)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // retry() 同样会同步 POST 一次，口径与 submit 一致
        $this->releaseSession();
        $res = TaskRunner::retry(intval(input('task_id', 0)));
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    public function remove()
    {
        if (!$this->request->isPost()) {
            $this->out(0, lang('mpt/err_post_required'));
        }
        // 删除同样要限流：它每次都会做一次 vod 查询并可能 unlink 一个成片文件，
        // 配额给得比 submit 宽（正常操作就是一条条点），但不能是唯一没有闸门的写接口。
        if (!Safety::consumeRateLimit('remove', $this->adminId(), 30, 300)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // 成片文件可能有几十上百 MB，网络存储上的 unlink 不是瞬时的；期间占着会话
        // 文件锁的话，任务台每 5 秒一次的轮询和整个后台都排在后面。口径与其余
        // 有副作用的动作一致，见 releaseSession() 的说明。必须排在限流之后。
        $this->releaseSession();
        $res = TaskRunner::remove(intval(input('task_id', 0)));
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    /** 服务探活 */
    public function ping()
    {
        // 每次点击都是一次对外 curl，配额给得宽松但不能没有
        if (!Safety::consumeRateLimit('ping', $this->adminId(), 10, 60)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // 服务不通时这一次 curl 会一直等到 timeout 上限，同样别占着会话锁
        $this->releaseSession();

        // 同一列上的第三格：这里的 config() 与客户端调用都写在控制器里，没有
        // TaskRunner 那层 guarded() 兜着。config() 会 include 一次 config.php，
        // 那个文件被写坏时抛的是 ParseError（\Error 不是 \Exception，
        // ConfigSchema::restore() 里已经为同一个理由 catch 过 \Throwable）。
        try {
            $cfg = TaskRunner::config();
            $client = new MptClient($cfg);
            $res = $client->ping();
        } catch (\Throwable $e) {
            \think\Log::error('mpt api/ping: ' . $e->getMessage());
            // out() 自己 exit，这里不需要 return
            $this->out(0, lang('mpt/err_internal'));
        }
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    /**
     * L3：给会用的站长贴进宝塔计划任务的地址。
     * 不要求后台会话，改用安装时生成的 cron_token 校验。
     *
     * token 优先从 x-mpt-token 请求头取。放 query string 里的话它会原样落进
     * nginx/apache 的 access log，日志被打包发出去或被其它人看到就等于泄漏，
     * 而这个 token 就是推进接口的唯一凭据。设置页给出的是 curl -H 的形式。
     * 仍然接受 ?token= 是为了兼容只能填一个 URL 的计划任务面板。
     */
    public function cron()
    {
        // ★ 限流排在 token 校验之前 ★
        // 这是本类唯一不要求后台会话的动作，任何人都能打。校验 token 之前就要先付
        // 一次 get_addon_info()（parse_ini_file）+ include config.php + 载入 144 条
        // 语言条目，不设闸门的话一个循环就能把这些开销放大成 CPU 占用。
        // 桶按 IP 分（consumeIpRateLimit），与站长那套按管理员的配额互不干扰——
        // 计划任务从本机打过来是 127.0.0.1，不会被外部刷子挤掉配额。
        // 额度给得远高于任何正常的计划任务频率（最密也就一分钟一次）。
        if (!Safety::consumeIpRateLimit('cron', 30, 300)) {
            $this->out(0, lang('mpt/err_rate_limited'));
        }
        // 同一列上的第四格。这里的 config() 排在 token 校验**之前**，而它会
        // include 一次 config.php —— 那个文件被写坏时抛的是 ParseError
        // （\Error 不是 \Exception）。本动作是全类唯一免登录的端点，异常裸着
        // 冒出去就是一页 HTML 直接发给任何人，还会带上 TP5 的调试信息。
        try {
            $cfg = TaskRunner::config();
        } catch (\Throwable $e) {
            \think\Log::error('mpt api/cron config: ' . $e->getMessage());
            // out() 自己 exit，这里不需要 return
            $this->out(0, lang('mpt/err_internal'));
        }
        $expected = (string) $cfg['cron_token'];
        $given = (string) $this->request->header('x-mpt-token', '');
        if ($given === '') {
            $given = self::stringInput('token');
        }
        if ($expected === '' || strlen($expected) < 16 || !hash_equals($expected, $given)) {
            $this->out(0, lang('mpt/err_bad_token'));
        }
        $this->releaseSession();
        $res = TaskRunner::advance(intval(input('limit', 0)));
        $this->out($res['code'], $res['msg'], $res['data']);
    }

    protected function rowForApi(array $r)
    {
        $url = (string) $r['mpt_result_url'];

        return array(
            'task_id' => intval($r['mpt_id']),
            'vod_id' => intval($r['mpt_obj_id']),
            'vod_name' => (string) $r['mpt_obj_name'],
            'subject' => (string) $r['mpt_subject'],
            'status' => intval($r['mpt_status']),
            'status_text' => MptTask::statusText($r['mpt_status']),
            'progress' => intval($r['mpt_progress']),
            'error' => (string) $r['mpt_error'],
            'url' => $url === '' ? '' : (MAC_PATH . $url),
            'time_add' => date('Y-m-d H:i', intval($r['mpt_time_add'])),
        );
    }
}
