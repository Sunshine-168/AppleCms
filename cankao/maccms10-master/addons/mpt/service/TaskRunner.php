<?php

namespace addons\mpt\service;

use think\Db;
use think\Log;
use addons\mpt\model\MptTask;

/**
 * 任务编排：建任务 → 提交 MPT → 轮询推进 → 下载落地 → 写回播放来源。
 *
 * 所有对外方法返回 ['code'=>0|1,'msg'=>'','data'=>[]]，不抛异常。
 * 推进是幂等且可分片的：advance() 每次只取最旧的 N 条 status=2，
 * 任何一条失败都只影响它自己，不会让整轮中断。
 */
class TaskRunner
{
    /**
     * 单条任务允许停留在「生成中」的上限，超过判超时失败（秒）。
     * 计时从 mpt_time_submit（最近一次提交给 MPT 的时刻）起算，不是从入库起算——
     * 否则重试一条旧任务会当场被判超时，见 advanceOne()。
     */
    const TASK_TTL = 7200;

    /**
     * 领取一条任务后的租约时长（秒）。
     * 租约内这条行不会被别的请求再次领走；超过这个时间还没结束就认为
     * 上一个推进者已经死了（进程被 kill / PHP fatal / FPM 回收），放回队列重来。
     * 取值要大于「最慢一次成片下载」——实测 200MB 上限、超时封顶 6×timeout(60s)=360s，
     * 再留一点余量。
     */
    const LEASE_SECONDS = 600;

    /**
     * 一条 STATUS_READY 的任务允许「已入库但还没提交出去」的最长时间（秒）。
     * 超过就由 advance() 重新提交一次，见 resubmitStaleReady()。
     */
    const READY_STALE_SECONDS = 300;

    /**
     * 一次 advance() 允许占用的墙钟上限（秒）。
     *
     * ★ 单条有租约不等于整轮有上限 ★
     * 租约管的是「同一条任务不会被两个请求同时推」，管不了「一轮推多久」。
     * poll_limit 最大 20，而 Safety::downloadToFile() 里 set_time_limit(0)、
     * CURLOPT_TIMEOUT 最高 max(60, 60*6)=360 秒 —— 20 条全赶上慢下载，
     * 一个 api/poll 请求能把一个 PHP-FPM worker 占住两小时。
     * 所以每推完一条就看一次表，超了就把剩下的留给下一轮（任务台 5 秒后就会再来，
     * shutdown 兜底和 cron 也都会再来），没有任何进度会丢。
     */
    const ADVANCE_DEADLINE = 90;

    /**
     * 各语种的默认 TTS 音色。
     *
     * ★ 不放语言包 ★
     * config() 会在 appInit 的兜底路径上被调用，那一刻语言包还没加载，
     * lang() 会返回裸 key —— 把 "mpt/voice_name" 当音色名提交给 MPT，任务必失败。
     * 这里直接按 config('default_lang') 查表，与 Lang 的加载时机完全无关。
     * 站长在设置里填了具体音色就以站长的为准，本表只在留空时兜底。
     */
    public static $defaultVoices = array(
        'zh-cn' => 'zh-CN-YunxiNeural-Male',
        'zh-tw' => 'zh-TW-YunJheNeural-Male',
        'en-us' => 'en-US-GuyNeural-Male',
        'ja-jp' => 'ja-JP-KeitaNeural-Male',
        'ko-kr' => 'ko-KR-InJoonNeural-Male',
        'de-de' => 'de-DE-ConradNeural-Male',
        'fr-fr' => 'fr-FR-HenriNeural-Male',
        'es-es' => 'es-ES-AlvaroNeural-Male',
        'pt-pt' => 'pt-PT-DuarteNeural-Male',
    );

    /** 站点语言对应的默认音色；语种不在表里时回落到英文 */
    public static function defaultVoice()
    {
        $locale = strtolower((string) \think\Config::get('default_lang'));

        return isset(self::$defaultVoices[$locale])
            ? self::$defaultVoices[$locale]
            : self::$defaultVoices['en-us'];
    }

    /**
     * 读配置并 clamp。配置文件是站长可改的，一律不信任其中的数值。
     */
    public static function config()
    {
        $c = function_exists('get_addon_config') ? get_addon_config('mpt') : array();
        if (!is_array($c)) {
            $c = array();
        }
        $get = function ($k, $d) use ($c) {
            return isset($c[$k]) && $c[$k] !== '' ? $c[$k] : $d;
        };
        // bgm_type 的「不加背景音乐」这一项的值就是空串，不能走上面那个
        // 「空串当没配」的 $get，否则站长选了不加、存下来还是 random。
        $getAllowEmpty = function ($k, $d) use ($c) {
            return array_key_exists($k, $c) ? (string) $c[$k] : $d;
        };

        $count = intval($get('video_count', 1));
        $clip = intval($get('video_clip_duration', 4));
        $pollLimit = intval($get('poll_limit', 5));

        return array(
            // 没有代码级默认值：站长没填就是没配，create()/MptClient::isConfigured()
            // 会挡住，任务台也会显示「未配置」提示。理由见 ConfigSchema 里 api_base 那项。
            'api_base' => Safety::normalizeBase($get('api_base', '')),
            // ★ 必须剥掉控制字符，光 trim 不够 ★
            // 这个值最终原样拼进 `x-api-key: {key}` 交给 CURLOPT_HTTPHEADER
            // （MptClient::headers()），而 libcurl 不会替我们做任何清洗。
            // 核心的 Addon::validateAddonConfigRows()（application/admin/controller/
            // Addon.php:830）对 type=string 只卡 65535 长度，值里的 \r\n 会
            // 原样落进 config.php —— 一个有 addon/config 节点的子管理员即可往
            // 出站请求里塞任意请求头。trim() 只管首尾，管不了中间。
            // 与 cron_token 一样在配置读取处收一次口。消费方那一侧（MptClient 的
            // public 构造）自己也收一次，两处都不依赖对方，见 Safety::sanitizeHeaderValue()。
            'api_key' => Safety::sanitizeHeaderValue($get('api_key', '')),
            'timeout' => Safety::clampTimeout($get('timeout', 20)),
            // 默认开。只有站长显式填 0 才关，缺配置一律当开——新装/升级时
            // 不会因为少一个键就悄悄退化成不校验证书。
            'verify_ssl' => (string) $getAllowEmpty('verify_ssl', '1') !== '0',
            'script_mode' => in_array($get('script_mode', 'template'), array('template', 'llm', 'remote'), true)
                ? (string) $get('script_mode', 'template') : 'template',
            'play_from' => PlayerSetup::sanitizeFrom($get('play_from', 'aivideo')),
            'auto_writeback' => (string) $get('auto_writeback', '1') === '1',
            'video_aspect' => in_array($get('video_aspect', '9:16'), array('9:16', '16:9', '1:1'), true)
                ? (string) $get('video_aspect', '9:16') : '9:16',
            'video_source' => in_array($get('video_source', 'pexels'), array('pexels', 'pixabay', 'coverr', 'local'), true)
                ? (string) $get('video_source', 'pexels') : 'pexels',
            'local_materials' => Safety::parseMaterialList($get('local_materials', '')),
            'video_count' => max(1, min(3, $count)),
            'video_clip_duration' => max(2, min(10, $clip)),
            // sequential 时 MPT 按 video_materials 的填写顺序拼接，且每个素材只取
            // 开头 video_clip_duration 秒（app/services/video.py 里那句 break）。
            // 分步骤讲解类的片子要靠它把画面和解说对齐；默认仍是 MPT 自己的 random。
            'video_concat_mode' => in_array($get('video_concat_mode', 'random'), array('random', 'sequential'), true)
                ? (string) $get('video_concat_mode', 'random') : 'random',
            // 留空就按站点语言取默认音色，不写死中文音色 —— 见 defaultVoice()
            'voice_name' => (string) $get('voice_name', self::defaultVoice()),
            'subtitle_enabled' => (string) $get('subtitle_enabled', '1') === '1',
            'bgm_type' => in_array($getAllowEmpty('bgm_type', 'random'), array('random', ''), true)
                ? $getAllowEmpty('bgm_type', 'random') : 'random',
            'poll_limit' => max(1, min(20, $pollLimit)),
            'poll_fallback_enabled' => (string) $get('poll_fallback_enabled', '1') === '1',
            // ★ 必须白名单，不能只 trim ★
            // cron_token 是设置页里一个普通的 type=string 配置项，核心的
            // Addon::validateAddonConfigRows() 对 string 类型只卡 65535 长度、
            // 不做任何 HTML 过滤，值原样落进 config.php。而任务台模板要把它拼进
            // value="..."（TP5 的 {$var} 是裸 echo，本仓库 Template 没有
            // default_filter），带引号的值直接就是一条存储型 XSS —— 有
            // addon/config 节点的子管理员即可打到 admin_id=1。
            // token 本来就只该是随机串（randomToken() 产出 32 位十六进制），
            // 在读取处一次收口，所有消费方（模板、cron 校验）都跟着安全。
            'cron_token' => self::sanitizeCronToken($get('cron_token', '')),
        );
    }

    /**
     * 规范化 cron_token：只允许随机串该有的字符，其余一律当没配。
     *
     * 返回空串时 Api::cron() 会因为 `$expected === ''` 直接拒绝，任务台也不再
     * 渲染那两个输入框 —— 站长看到 cron 区块消失，回设置页重填一个正常 token 即可，
     * 比默默吐一个坏 URL（或一段被注入的 HTML）好。
     */
    protected static function sanitizeCronToken($raw)
    {
        $token = trim((string) $raw);

        return preg_match('/^[A-Za-z0-9_\-]{1,128}$/', $token) ? $token : '';
    }

    protected static function ok($data = array(), $msg = '')
    {
        return array('code' => 1, 'msg' => $msg, 'data' => $data);
    }

    protected static function err($msg)
    {
        return array('code' => 0, 'msg' => (string) $msg, 'data' => array());
    }

    /**
     * 把一次会打到 mpt_task 的调用收口成「永远是一个结果数组」。
     *
     * ★ 这不是防御式编程，是这张表真的可能不存在 ★
     * 库账号没有 CREATE 权限时 Schema::ensure() 补不出表，而它自己特意把建表异常
     * 吞掉了（见 Schema::run() 里 $strict 那段，运行期路径不该吐 500）。紧接着任何
     * 一条打在这张表上的语句照样抛 PDOException，裸着冒出去就是一页 HTML ——
     * 任务台拿到的不是 JSON，只会显示一句 "bad response"，真实原因一个字看不到。
     *
     * advance() / Api::status() / Admin::index() 已经各自兜住了自己那条路径，
     * 这里是**其余全部**入口的同一道收口：preview / create / retry / remove
     * （cleanup 与 searchVod、ping 走控制器自己的 try，因为它们的查询/调用就写在
     * 控制器里）。这份清单是给下一个加动作的人看的——preview 当初正是从这句话里
     * 掉出去才漏了三轮，新增入口时请连它一起改，并跑一遍回归第 10 项。详情进日志，
     * 对外只给一句不含 SQL / 路径 / 堆栈的通用文案。
     *
     * @param string   $tag 日志前缀，用于区分是哪条路径炸的
     * @param \Closure $fn  真正的主体，返回 ok()/err() 形状的数组
     * @return array
     */
    protected static function guarded($tag, \Closure $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::error('mpt ' . $tag . ': ' . $e->getMessage());

            return self::err(lang('mpt/err_internal'));
        }
    }

    /**
     * 还原成纯文本：去标签 → 解实体 → 再去一次标签 → 折叠空白。
     * 见 create() 里关于「不要再 mac_filter_xss」的说明。
     */
    public static function plainText($s)
    {
        $s = strip_tags((string) $s);
        $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
        $s = strip_tags($s);
        $s = preg_replace('/\s+/u', ' ', $s);

        return trim((string) $s);
    }

    public static function loadVod($vodId)
    {
        $vodId = intval($vodId);
        if ($vodId <= 0) {
            return null;
        }
        $row = Db::name('vod')->where('vod_id', $vodId)->find();

        return is_array($row) ? $row : null;
    }

    /**
     * 预览：算出 subject / script / terms 给站长过目，不落库。
     * 异常由 guarded() 统一收口，见那里的说明。
     *
     * ⚠️ 别以为「只读 vod、表必然存在」就不需要这一层。loadVod() 的查询照样会在
     * DB 断连 / 锁等待超时上抛 PDOException，而 script_mode=llm 时还要多走一趟
     * AiProvider。调用方 Api::preview() 只有 out() 没有 catch —— 抛出去就是一页
     * HTML，任务台只显示一句 "bad response"，与本类到处在防的失败模式是同一个。
     */
    public static function preview($vodId)
    {
        return self::guarded('preview', function () use ($vodId) {
            return self::previewGuarded($vodId);
        });
    }

    /** preview() 的主体。异常由 guarded() 统一收口。 */
    protected static function previewGuarded($vodId)
    {
        $vod = self::loadVod($vodId);
        if (!$vod) {
            return self::err(lang('mpt/err_vod_not_found'));
        }
        $cfg = self::config();
        $res = ScriptBuilder::build($vod, $cfg);
        if (intval($res['code']) !== 1) {
            return self::err($res['msg']);
        }
        $res['data']['vod_name'] = (string) $vod['vod_name'];

        return self::ok($res['data'], isset($res['msg']) ? $res['msg'] : '');
    }

    /**
     * 建任务并提交给 MPT。异常由 guarded() 统一收口，见那里的说明。
     */
    public static function create($vodId, $adminId, $subject, $script, $terms)
    {
        return self::guarded('create', function () use ($vodId, $adminId, $subject, $script, $terms) {
            return self::createGuarded($vodId, $adminId, $subject, $script, $terms);
        });
    }

    /** create() 的主体。 */
    protected static function createGuarded($vodId, $adminId, $subject, $script, $terms)
    {
        $cfg = self::config();
        if ($cfg['api_base'] === '' || $cfg['api_key'] === '') {
            return self::err(lang('mpt/err_not_configured'));
        }
        // 播放来源名不合法（站长在设置里填了 `ai-video` 这类带连字符的值，
        // PlayerSetup::sanitizeFrom() 会判空）时**在提交前就拦住**。
        // 不拦的话任务照跑十分钟、成片也照下，只有写回那一步失败，
        // 站长要到任务的 error 列里才看得到原因——而那时算力已经烧掉了。
        // 只在开着自动写回时才管：关掉写回的站长本来就不需要这个值。
        if ($cfg['auto_writeback'] && $cfg['play_from'] === '') {
            return self::err(lang('mpt/err_bad_play_from'));
        }
        $vod = self::loadVod($vodId);
        if (!$vod) {
            return self::err(lang('mpt/err_vod_not_found'));
        }

        // ⚠️ 不要在这里再 mac_filter_xss()。think\addons\Controller 的构造函数
        // 已经对整个 request 挂了 'trim,strip_tags,htmlspecialchars' 过滤器，
        // input() 拿到的就是转义过的；再转一次会得到 &amp;quot; 这种双重实体，
        // 存进 mpt_script 并原样送去 TTS，配音会把实体名念出来。
        // 这里只做「还原成纯文本」，防 XSS 由输出端负责（任务台 JS 全程 esc()）。
        $subject = self::plainText($subject);
        $script = self::plainText($script);
        if ($subject === '') {
            $subject = ScriptBuilder::buildSubject($vod);
        }
        if ($subject === '') {
            return self::err(lang('mpt/err_subject_required'));
        }
        if (mb_strlen($subject, 'UTF-8') > 200) {
            $subject = mb_substr($subject, 0, 200, 'UTF-8');
        }
        if (mb_strlen($script, 'UTF-8') > 1000) {
            $script = mb_substr($script, 0, 1000, 'UTF-8');
        }

        $termList = array();
        if (is_string($terms)) {
            $terms = preg_split('/[,，\n]+/u', $terms);
        }
        if (is_array($terms)) {
            foreach ($terms as $t) {
                // terms 可能是调用方传来的嵌套数组，(string) 转换会抛 notice
                if (!is_string($t) && !is_numeric($t)) {
                    continue;
                }
                $t = self::plainText($t);
                if ($t !== '' && !in_array($t, $termList, true)) {
                    $termList[] = mb_substr($t, 0, 60, 'UTF-8');
                }
                if (count($termList) >= 8) {
                    break;
                }
            }
        }

        // 只放「任务内容」。画幅、音色、素材源这些**站点设置**由 applyConfigParams()
        // 在每次提交前按当前配置铺上去，见那个方法的说明。
        $payload = array('video_subject' => $subject);
        // script_mode=remote 时故意留空 video_script，交给 MPT 服务端自己的 LLM 生成
        if ($script !== '') {
            $payload['video_script'] = $script;
        }
        if ($termList) {
            $payload['video_terms'] = $termList;
        }
        $payload = self::applyConfigParams($payload, $cfg);
        if ($payload === null) {
            // 素材源选了 local 却没填素材名。这里就拦住，不建行——不拦的话任务照跑，
            // 站长要等到轮询才看到「no valid local video materials were found」。
            return self::err(lang('mpt/err_local_materials_required'));
        }

        $now = time();
        $taskId = Db::name('mpt_task')->insertGetId(array(
            'mpt_mid' => 1,
            'mpt_obj_id' => intval($vod['vod_id']),
            'mpt_obj_name' => mb_substr((string) $vod['vod_name'], 0, 200, 'UTF-8'),
            'mpt_subject' => $subject,
            'mpt_script' => $script,
            'mpt_terms' => implode(',', $termList),
            'mpt_params' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'mpt_status' => MptTask::STATUS_READY,
            'mpt_admin_id' => intval($adminId),
            'mpt_time_add' => $now,
            'mpt_time_update' => $now,
        ));
        if (!$taskId) {
            return self::err(lang('mpt/err_task_create'));
        }

        return self::submit(intval($taskId), $cfg);
    }

    /**
     * 把「站点设置」那部分参数按**当前**配置铺到 payload 上。
     *
     * ★ 为什么每次提交都要重铺，而不是沿用入库时的快照 ★
     * mpt_params 里混着两类东西：任务内容（subject / script / terms，站长在预览框里
     * 逐条改过的，必须原样保留）和站点设置（画幅、音色、素材源、条数、单段时长、
     * 字幕、背景音乐，全部来自插件设置页）。retry() 与 resubmitStaleReady() 都会拿
     * 这份快照原样再提交一次 —— 沿用的话，站长把画幅从 9:16 改成 16:9 之后点重试，
     * 出来的还是 9:16，而且没有任何提示，只能怀疑是设置没保存上。
     * 设置是全站当下的意图，不该被一条旧任务的快照冻住；内容才是任务自己的。
     *
     * video_materials 每次都先 unset 再按需重建：素材源从 local 改回 pexels 时，
     * 光覆盖 video_source 会把上一次那串 local 素材留在请求里一起发出去。
     *
     * @return array|null 素材源选了 local 却没填素材名时返回 null，由调用方报错
     */
    protected static function applyConfigParams(array $payload, array $cfg)
    {
        $payload['video_aspect'] = $cfg['video_aspect'];
        $payload['video_source'] = $cfg['video_source'];
        $payload['video_count'] = $cfg['video_count'];
        $payload['video_clip_duration'] = $cfg['video_clip_duration'];
        $payload['video_concat_mode'] = $cfg['video_concat_mode'];
        $payload['voice_name'] = $cfg['voice_name'];
        $payload['subtitle_enabled'] = $cfg['subtitle_enabled'];
        $payload['bgm_type'] = $cfg['bgm_type'];
        unset($payload['video_materials']);

        if ($cfg['video_source'] === 'local') {
            // MPT 的 local 源只认请求体里的 video_materials，不会去扫 storage/local_videos/。
            // 不带这个字段直接提交，任务会在素材阶段死掉。
            if (!$cfg['local_materials']) {
                return null;
            }
            $materials = array();
            foreach ($cfg['local_materials'] as $name) {
                $materials[] = array('provider' => 'local', 'url' => $name, 'duration' => 0);
            }
            $payload['video_materials'] = $materials;
        }

        return $payload;
    }

    /**
     * 把已就绪（或失败重试）的任务提交给 MPT。
     */
    public static function submit($taskId, array $cfg = null)
    {
        if ($cfg === null) {
            $cfg = self::config();
        }
        $task = Db::name('mpt_task')->where('mpt_id', intval($taskId))->find();
        if (!$task) {
            return self::err(lang('mpt/err_task_not_found'));
        }
        $payload = json_decode((string) $task['mpt_params'], true);
        if (!is_array($payload)) {
            return self::err(lang('mpt/err_task_params'));
        }
        // 站点设置按当前配置重铺，任务内容保持快照原样。见 applyConfigParams()。
        $payload = self::applyConfigParams($payload, $cfg);
        if ($payload === null) {
            // 行已经存在了（重试 / 补投卡住的 READY），落成失败让站长在任务台看到原因，
            // 而不是静默什么都不发生。create() 那一侧是提交前拦住、根本不建行。
            $msg = lang('mpt/err_local_materials_required');
            self::markFailed($task['mpt_id'], $msg);

            return self::err($msg);
        }

        $client = new MptClient($cfg);
        $res = $client->submit($payload);
        if (intval($res['code']) !== 1) {
            self::markFailed($task['mpt_id'], $res['msg']);

            return self::err($res['msg']);
        }

        // ★ mpt_time_submit 必须在这里刷新 ★
        // 它是 advanceOne() 那道 TASK_TTL 判据的锚点。锚在 mpt_time_add 上是错的：
        // retry() 与 resubmitStaleReady() 都会让一条**旧**行重新提交一次，而 time_add
        // 停在当初入库那一刻——重试一条两小时前的任务，提交成功后紧接着的第一轮推进
        // 就会 `time() - time_add > TTL` 判它超时，且判死发生在 queryTask() 之前，
        // 远端那条刚提交的任务没人认领，继续烧对方算力。见 advanceOne()。
        $update = array(
            'mpt_remote_id' => $res['data']['remote_id'],
            'mpt_status' => MptTask::STATUS_RUNNING,
            'mpt_progress' => 0,
            'mpt_error' => '',
            'mpt_time_update' => time(),
            // 这一列的语义是「提交给 MPT 的参数快照」，既然 applyConfigParams() 刚按
            // 当前配置重铺过，就得把真正发出去的那份写回来，否则重试之后快照与实际
            // 请求对不上，排查时看到的是一份从没被发送过的参数。
            'mpt_params' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        );
        // ★ 但这一列可能根本不存在，不能无条件写 ★
        // 库账号没有 ALTER 权限时 Schema::ensure() 补不上它。advanceOne() 对那种库
        // 是能降级跑的（判不到这一列就把锚点回落到 mpt_time_add），提交这一侧要是
        // 无条件写，Unknown column 会一路冒到 Api::submit() / Api::retry() ——
        // 那两处只有 out() 没有 catch，站长收到的是一页 500 HTML，前端 JSON.parse
        // 失败后只显示一句 "bad response"，真实原因一个字都看不到。
        // 两处口径必须一致，否则 advanceOne() 那段兜底等于只兑现了一半。
        // $task 是整行 select 回来的，有没有这一列不用再问一次库。
        if (array_key_exists('mpt_time_submit', $task)) {
            $update['mpt_time_submit'] = time();
        }
        Db::name('mpt_task')->where('mpt_id', $task['mpt_id'])->update($update);

        return self::ok(array('task_id' => intval($task['mpt_id'])));
    }

    /**
     * 推进最旧的 N 条「生成中」任务。幂等、可反复调用。
     *
     * ★ 整轮推进全站只能有一个在跑 ★
     * 单条任务的租约（advanceOne 的领取）只保证「同一条任务不会被推两遍」，
     * 挡不住「同时开着好几轮」。而进本方法的口子有三个，谁都可能并发：
     *   - api/poll：任务台 JS 每 5s 一次。JS 里的 polling 标志是**每个标签页**的，
     *     站长开三个任务台就是三条并发长请求；
     *   - api/cron：token 一旦从 access log 泄漏（?token= 形式就会落进去），
     *     或者站长在宝塔里把计划任务配成了每分钟一次而单轮跑得更久，就会叠起来；
     *   - shutdown 兜底：本来就有自己的 runtime/mpt/poll.lock，不受影响。
     * 每一轮都可能占住一个 PHP-FPM worker 到 ADVANCE_DEADLINE(90s) 再加一次
     * 最长 360s 的下载，叠几轮就能把进程池吃光 —— 那正是本类到处在防的事，
     * 却唯独在入口这里没有闸门。
     *
     * 用 LOCK_NB：抢不到**立刻**返回空结果，不排队。排队等于把 worker 占住，
     * 与要防的事情是一回事。调用方 5 秒后自然会再来，没有进度会丢。
     *
     * ★ 整个方法不允许抛异常 ★
     * 唯一的两个调用方是 Api::poll() / Api::cron()，它们只有 out() 没有 catch，
     * 而 out() 之外的任何异常都会走 TP5 的异常处理器吐一页 **HTML**：前端
     * JSON.parse 失败，任务台每 5 秒弹一句 "bad response"，真实原因一个字看不到。
     * 这不是假设——库账号没有 CREATE 权限时 Schema::ensure() 补不出表，它自己
     * 特意把建表异常吞掉了（见 Schema::run() 里 $strict 那段），可紧接着的
     * reclaimExpiredLeases() 一条 UPDATE 打在不存在的表上照样抛，那段吞掉等于白写。
     * 这里收口：详情进日志，对外只给一句不含 SQL/路径/堆栈的通用文案。
     *
     * @return array data: checked / deferred / done / failed / pending / busy
     */
    public static function advance($limit = 0)
    {
        try {
            return self::advanceGuarded($limit);
        } catch (\Throwable $e) {
            Log::error('mpt advance: ' . $e->getMessage());

            return self::err(lang('mpt/err_internal'));
        }
    }

    /** advance() 的主体。异常由 advance() 统一收口，这里只管闸门。 */
    protected static function advanceGuarded($limit = 0)
    {
        $fp = @fopen(Safety::runtimeFile('advance.lock'), 'c');
        if ($fp === false) {
            // 连锁文件都开不出来（runtime 不可写）时不能把功能一并关掉，
            // 退化成没有闸门的旧行为 —— 不理想，但比完全不推进强。
            return self::advanceLocked($limit);
        }
        if (!@flock($fp, LOCK_EX | LOCK_NB)) {
            @fclose($fp);

            return self::ok(array(
                'checked' => 0, 'deferred' => 0, 'done' => 0,
                'failed' => 0, 'pending' => 0, 'busy' => 1,
            ));
        }
        try {
            return self::advanceLocked($limit);
        } finally {
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }

    /**
     * 真正的推进逻辑。调用方已经持有 runtime/mpt/advance.lock。
     */
    protected static function advanceLocked($limit = 0)
    {
        // 在线升级不会重新调 enable()，新版本的列/索引只能靠运行期这条路补上。
        // 带闸门：指纹没变时只是一次 md5_file + 一次小文件读，够便宜；
        // 放在动手推进之前，免得这一轮拿新代码去写老表结构。见 Schema::ensure()。
        Schema::ensure();

        $cfg = self::config();
        $limit = $limit > 0 ? intval($limit) : $cfg['poll_limit'];
        $limit = max(1, min(20, $limit));

        self::reclaimExpiredLeases();
        self::resubmitStaleReady($cfg);

        $rows = Db::name('mpt_task')
            ->where('mpt_status', MptTask::STATUS_RUNNING)
            ->order('mpt_time_update asc')
            ->limit($limit)
            ->select();
        if (!$rows) {
            return self::ok(array(
                'checked' => 0, 'deferred' => 0, 'done' => 0,
                'failed' => 0, 'pending' => 0, 'busy' => 0,
            ));
        }

        $client = new MptClient($cfg);
        $done = 0;
        $failed = 0;
        $pending = 0;
        $skipped = 0;
        $deadline = time() + self::ADVANCE_DEADLINE;

        foreach ($rows as $task) {
            $id = intval($task['mpt_id']);

            // 超时就把剩下的留给下一轮：这一轮已经跑了 ADVANCE_DEADLINE 秒，
            // 再领一条可能又是 360 秒的下载。没有进度会丢 —— 没领的行状态原封不动
            // 还是 RUNNING，任务台 5 秒后的下一次 poll（以及兜底、cron）照样会捞到。
            if (time() >= $deadline) {
                $skipped++;
                $pending++;
                continue;
            }

            // ★ 领取：同一条任务只能有一个推进者，且要一直独占到它做完 ★
            // 任务台 JS 每 5s 打一次 poll，shutdown 兜底和 cron URL 也会调 advance()，
            // 它们分属不同请求（不同会话，PHP 的 session 锁串不起来）。
            // finish() 里的下载可能跑几十秒到几分钟。
            //
            // 只用 mpt_time_update 做乐观锁是**不够**的：那只能挡住「读到同一个
            // 快照的两个并发请求」，挡不住时间上错开的两次轮询——第一个请求领走时
            // 把 time_update 挪到了 T1，5 秒后的第二次轮询读到的就是 T1，
            // `where time_update = T1` 照样能改动，于是同一条任务被下载两遍：
            // upload/mpt/ 下落两个文件、写回两次（后者覆盖前者，前者成孤儿）、
            // 而且每 5 秒就叠一个 set_time_limit(0) 的长进程，FPM 池会被一个任务占满。
            //
            // 所以领取要把行**移出可领取集合**：状态改成 WORKING，advance() 只捞
            // RUNNING。推进者结束时置回 RUNNING / DONE / FAILED；中途死掉的由
            // reclaimExpiredLeases() 在租约过期后放回来。
            $seen = intval($task['mpt_time_update']);
            // 时间戳仍然要挪，让它排到队尾；同一秒内命中时往后挪 1 秒，
            // 避免 MySQL 因「值没变」返回 affected_rows=0 被误判成抢占失败。
            $stamp = (time() === $seen) ? ($seen + 1) : time();
            $claimed = Db::name('mpt_task')
                ->where('mpt_id', $id)
                ->where('mpt_status', MptTask::STATUS_RUNNING)
                ->where('mpt_time_update', $seen)
                ->update(array(
                    'mpt_status' => MptTask::STATUS_WORKING,
                    'mpt_time_update' => $stamp,
                ));
            if (empty($claimed)) {
                $pending++;
                continue;
            }

            // 单条出事只算这一条失败：本类承诺「不抛异常」且「一条失败不中断整轮」，
            // 而 finish() 一路下去会碰 Db::update()，死锁/断连时是抛 PDOException 的。
            // 没有这层 catch，剩下的任务全不检查，异常还会穿透 Api::poll 变成 500。
            try {
                $outcome = self::advanceOne($task, $cfg, $client);
            } catch (\Throwable $e) {
                Log::error('mpt advance task#' . $id . ': ' . $e->getMessage());
                self::markFailed($id, Safety::safeMessage($e->getMessage(), array($cfg['api_key'])));
                $outcome = 'failed';
            }

            if ($outcome === 'done') {
                $done++;
            } elseif ($outcome === 'failed') {
                $failed++;
            } else {
                $pending++;
            }
        }

        if ($skipped > 0) {
            // 静默截断会让「checked=20 全是 pending」看起来像全都卡住了，
            // 实际是这一轮没时间处理。留一行日志，排查时看得见。
            Log::info('mpt advance: deadline reached, ' . $skipped . ' task(s) deferred to next round');
        }

        return self::ok(array(
            'checked' => count($rows) - $skipped,
            'deferred' => $skipped,
            'done' => $done,
            'failed' => $failed,
            'pending' => $pending,
            'busy' => 0,
        ));
    }

    /**
     * 把租约过期的 WORKING 行放回 RUNNING。
     *
     * 推进者中途死掉（进程被 kill、PHP fatal、FPM 回收 worker）时行会一直卡在
     * WORKING，谁也捞不到它，任务表面上停在「生成中」直到 TASK_TTL 才被判超时。
     * 每次 advance() 开头扫一遍，代价是一条走 idx_status 的 update。
     */
    protected static function reclaimExpiredLeases()
    {
        Db::name('mpt_task')
            ->where('mpt_status', MptTask::STATUS_WORKING)
            ->where('mpt_time_update', '<', time() - self::LEASE_SECONDS)
            ->update(array(
                'mpt_status' => MptTask::STATUS_RUNNING,
                'mpt_time_update' => time(),
            ));
    }

    /**
     * 重新提交「入库了但没提交出去」的任务。
     *
     * create() 与 retry() 都是先把行落成 STATUS_READY、再调 submit()。这两步之间
     * 进程要是没了（PHP fatal、被 kill、FPM 回收 worker、站长关掉页面撞上时限），
     * 行就永久停在 READY：advance() 只捞 RUNNING，reclaimExpiredLeases() 只管
     * WORKING，谁都不会再碰它。任务台上它显示成「已就绪」，看不出坏了。
     *
     * 领取方式与 advance() 同理：先用 `where 状态 + where 时间戳` 把时间戳推到现在，
     * 抢到的那个请求才去提交。没有这一步，两个并发推进者会各提交一次，
     * MPT 侧就多出一条谁也不认领的任务，白烧对方的算力。
     */
    protected static function resubmitStaleReady(array $cfg)
    {
        $rows = Db::name('mpt_task')
            ->where('mpt_status', MptTask::STATUS_READY)
            ->where('mpt_time_update', '<', time() - self::READY_STALE_SECONDS)
            // 每轮只救一条：submit() 是一次可能耗到 timeout 上限的对外 POST，
            // 而本方法跑在 advance() 开头，多救几条就把任务台那 5 秒一次的
            // poll 拖成分钟级。卡住的 READY 本来就是罕见路径，一轮一条足够。
            ->order('mpt_time_update asc')
            ->limit(1)
            ->select();
        foreach ((array) $rows as $row) {
            $id = intval($row['mpt_id']);
            $seen = intval($row['mpt_time_update']);
            $claimed = Db::name('mpt_task')
                ->where('mpt_id', $id)
                ->where('mpt_status', MptTask::STATUS_READY)
                ->where('mpt_time_update', $seen)
                ->update(array('mpt_time_update' => time()));
            if (empty($claimed)) {
                continue;
            }
            // submit() 自己已经把失败落成 FAILED 了，这里只兜住它够不着的异常
            // （DB 断连之类）：本方法跑在 advance() 开头，抛出去会让整轮都不检查。
            try {
                self::submit($id, $cfg);
            } catch (\Throwable $e) {
                Log::error('mpt resubmit ready task#' . $id . ': ' . $e->getMessage());
                self::markFailed($id, Safety::safeMessage($e->getMessage(), array($cfg['api_key'])));
            }
        }
    }

    /**
     * 归还租约：任务还没结束，放回 RUNNING 等下一轮。
     * 时间戳同时刷新，让它排到队尾。
     */
    protected static function releaseLease($taskId, $progress = null)
    {
        $update = array(
            'mpt_status' => MptTask::STATUS_RUNNING,
            'mpt_time_update' => time(),
        );
        if ($progress !== null) {
            $update['mpt_progress'] = intval($progress);
        }
        Db::name('mpt_task')->where('mpt_id', intval($taskId))->update($update);
    }

    /**
     * 推进单条任务。调用方已经领取过这一行（状态是 WORKING）。
     * 返回 pending 的每条路径都必须先 releaseLease()，否则这条任务要等
     * LEASE_SECONDS 才会被回收。
     *
     * @return string done | failed | pending
     */
    protected static function advanceOne(array $task, array $cfg, MptClient $client)
    {
        $id = intval($task['mpt_id']);

        if ((string) $task['mpt_remote_id'] === '') {
            self::markFailed($id, lang('mpt/err_no_remote_id'));

            return 'failed';
        }
        // 超时兜底：MPT 挂了的话任务会永远停在生成中。
        //
        // ★ 锚点是「最近一次提交出去的时刻」，不是「入库时刻」★
        // retry() 允许对任意非 busy 状态的任务重来一次，resubmitStaleReady() 也会把
        // 卡住的 READY 行重新提交。这两条路上 mpt_time_add 都停在当初入库那一刻——
        // 拿它当锚点的话，重试一条超过 TASK_TTL 的旧任务，提交成功后紧接着的第一轮
        // 推进就会把它判成「生成超时」，而且是在 queryTask() 之前判的：远端那条刚
        // 提交的任务没人认领，继续烧对方算力，站长看到的原因也与真实情况毫无关系。
        //
        // 存量库刚补上这一列时历史行是 0，回落到 mpt_time_add，避免升级那一刻把
        // 所有在跑的任务一起判超时；列补不上（库账号没有 ALTER 权限）时同理。
        $anchor = isset($task['mpt_time_submit']) ? intval($task['mpt_time_submit']) : 0;
        if ($anchor <= 0) {
            $anchor = intval($task['mpt_time_add']);
        }
        if (time() - $anchor > self::TASK_TTL) {
            self::markFailed($id, lang('mpt/err_timeout'));

            return 'failed';
        }

        $res = $client->queryTask($task['mpt_remote_id']);
        if (intval($res['code']) !== 1) {
            // 单次查询失败不判死，还回队列下一轮再试
            self::releaseLease($id);

            return 'pending';
        }
        $d = $res['data'];

        if (!empty($d['failed'])) {
            self::markFailed($id, $d['error'] !== '' ? $d['error'] : lang('mpt/err_remote_failed'));

            return 'failed';
        }

        if (!empty($d['done'])) {
            $fin = self::finish($task, $d['path'], $cfg, $client);

            return intval($fin['code']) === 1 ? 'done' : 'failed';
        }

        self::releaseLease($id, $d['progress']);

        return 'pending';
    }

    /**
     * 下载成片 → 落盘 → 写回播放来源 → 置完成。
     */
    protected static function finish(array $task, $relPath, array $cfg, MptClient $client)
    {
        $id = intval($task['mpt_id']);
        $save = Safety::allocateSavePath($task['mpt_obj_id'], 'mp4');
        $abs = ROOT_PATH . $save;

        $dl = $client->download($relPath, $abs);
        if (intval($dl['code']) !== 1) {
            self::markFailed($id, $dl['msg']);

            return self::err($dl['msg']);
        }

        // 成片留在本地：播放地址存成 upload/ 开头的相对路径，播放页由
        // application/common/controller/All.php:864 的
        //   if (substr($player_info['url'],0,6) == 'upload') { $url = MAC_PATH . $url; }
        // 自动补上站点前缀。推远端存储会返回 mac: 协议地址，播放器解析不了，
        // 所以这里刻意不调 model('Upload')->api()。
        $update = array(
            'mpt_result_url' => $save,
            'mpt_progress' => 100,
            'mpt_status' => MptTask::STATUS_DONE,
            'mpt_error' => '',
            'mpt_time_update' => time(),
        );
        Db::name('mpt_task')->where('mpt_id', $id)->update($update);

        if ($cfg['auto_writeback']) {
            $wb = self::writeBackPlaySource(intval($task['mpt_obj_id']), $save, $cfg);
            if (intval($wb['code']) !== 1) {
                // 文件已经拿到了，写回失败不回滚任务状态，只把原因记在 error 上让站长看到
                Db::name('mpt_task')->where('mpt_id', $id)->update(array('mpt_error' => $wb['msg']));
            }
        }

        return self::ok(array('url' => $save));
    }

    /**
     * 把成片作为一个独立播放组追加到影片。
     * 四个 vod_play_* 列必须始终等长，否则 mac_play_list() 会错位。
     */
    public static function writeBackPlaySource($vodId, $relUrl, array $cfg)
    {
        $vodId = intval($vodId);
        $vod = self::loadVod($vodId);
        if (!$vod) {
            return self::err(lang('mpt/err_vod_not_found'));
        }
        $from = $cfg['play_from'];
        if ($from === '') {
            return self::err(lang('mpt/err_bad_play_from'));
        }

        $ready = PlayerSetup::ensure($from);
        if (intval($ready['code']) !== 1) {
            return self::err($ready['msg']);
        }

        $split = function ($s) {
            $s = (string) $s;

            return $s === '' ? array() : explode('$$$', $s);
        };
        $froms = $split($vod['vod_play_from']);
        $urls = $split($vod['vod_play_url']);
        $servers = $split($vod['vod_play_server']);
        $notes = $split($vod['vod_play_note']);

        $n = count($froms);

        // ★ 四列不等长时宁可什么都不做，也不能替站长决定丢掉哪一列 ★
        // 下面把 urls/servers/notes 一律按 $n 对齐：少了补空串（正常情况，某个播放组
        // 还没填地址），多了就被 array_slice 截断 —— 而截断掉的是站长核心表里的真实数据。
        // 最典型的一种是 vod_play_from='' 而 vod_play_url 非空：$n 为 0，整列当场清空，
        // 站长的播放地址无声无息就没了，而本方法其余每一处（不动 vod_time、groupIsOurs()
        // 占用检查、删文件前的两道引用确认）都刻意做到了「宁可不写」。口径要一致。
        // 这种数据本来就是坏的（mac_play_list() 按 from 迭代，那些 url 前台已经取不到），
        // 但坏数据该由站长自己去修，报个原因让他在任务台看见，比默默抹掉强。
        if (count($urls) > $n || count($servers) > $n || count($notes) > $n) {
            return self::err(lang('mpt/err_play_columns_mismatch'));
        }

        for ($i = 0; $i < $n; $i++) {
            if (!isset($urls[$i])) {
                $urls[$i] = '';
            }
            if (!isset($servers[$i])) {
                $servers[$i] = '';
            }
            if (!isset($notes[$i])) {
                $notes[$i] = '';
            }
        }
        $urls = array_slice($urls, 0, $n);
        $servers = array_slice($servers, 0, $n);
        $notes = array_slice($notes, 0, $n);

        $replaced = array();
        // ★ 找已有播放组时不能用 array_search($from, $froms, true) ★
        // 严格比对会漏掉站长手写成 "AiVideo" 或前后带了空格的那一组
        // （vod_play_from 是自由文本，采集回来的数据里大小写不统一很常见）。
        // 漏掉的后果不是「少改一处」，而是往 vod_play_from 里**再追加**一个
        // aivideo，同一部片出现两个看起来一样的播放组，前台各显示一次。
        // 比对口径与 sanitizeFrom() 一致：小写 + 去空白。
        $idx = false;
        foreach ($froms as $i => $f) {
            if (strtolower(trim((string) $f)) === $from) {
                $idx = $i;
                break;
            }
        }
        if ($idx === false) {
            $froms[] = $from;
            $urls[] = lang('mpt/play_label') . '$' . $relUrl;
            $servers[] = '';
            $notes[] = '';
        } else {
            // ★ 覆盖前必须确认这一组确实是本插件的产物 ★
            // play_from 是站长可填的自由文本，sanitizeFrom() 只管字符合法性，
            // 填成 dplayer 这种站上真实存在、装满正片的播放组是完全可能的。
            // 直接 $urls[$idx] = $entry 会把那一整组剧集**全部替换成一条宣传片**——
            // 采集回来的几十集就这么没了，而且没有任何提示。
            if (!self::groupIsOurs($urls[$idx])) {
                return self::err(lang('mpt/err_play_from_occupied'));
            }
            // 确认是自己的组：同一部片再次生成时替换掉旧的，不无限堆积。
            // 集数名沿用这一组已有的那个 —— play_label 是随站点语言变的，
            // 站长中途换过语言的话，重新生成会让同一部片的集数名从「宣传片」
            // 突然变成 "Promo"，看着像是换了个东西。首次写入才用当前语言的默认名。
            //
            // 被顶掉的那些地址在这一刻起就没人引用了，记下来，写库成功后删文件。
            $replaced = self::groupUrls($urls[$idx]);
            $urls[$idx] = self::existingEntryLabel($urls[$idx]) . '$' . $relUrl;
        }

        // 刻意**不**动 vod_time：那会把这部片顶到「最近更新」最前面。
        // 生成一条宣传片不是内容更新，不该改变前台的排序。
        // 也不判 update() 的返回值——TP5 返回受影响行数（可能是 0，内容没变时），
        // 真出错是抛异常，由 advanceOne() 外层的 catch 兜住。
        Db::name('vod')->where('vod_id', $vodId)->update(array(
            'vod_play_from' => implode('$$$', $froms),
            'vod_play_url' => implode('$$$', $urls),
            'vod_play_server' => implode('$$$', $servers),
            'vod_play_note' => implode('$$$', $notes),
        ));

        // 写库成功之后才回收被顶掉的旧成片：反过来的话 update 抛异常时文件已经没了，
        // 播放组还指着它，直接变死链。
        self::pruneOrphanResults($replaced, $relUrl);

        Safety::bustVodDetailCache($vodId, isset($vod['vod_en']) ? (string) $vod['vod_en'] : '');
        if (class_exists('\app\common\util\MeilisearchSync')) {
            try {
                \app\common\util\MeilisearchSync::afterVodSave($vodId);
            } catch (\Exception $e) {
                Log::error('mpt meilisearch sync: ' . $e->getMessage());
            }
        }

        return self::ok();
    }

    /**
     * 重试一条任务。
     *
     * ★ 状态检查与状态变更必须是同一条语句 ★
     * 「先 find() 读状态、判断、再无条件 update」是 TOCTOU：读到 FAILED 之后、
     * update 之前，并发的 advance() 完全可能把这一行领走（RUNNING→WORKING）
     * 并开始下载。那样 retry 会把 mpt_remote_id 清空、状态压回 READY，
     * 而推进者仍在跑，最后 finish() 又把它改成 DONE —— 两边互相覆盖，
     * 站长看到的状态取决于谁最后写完。
     * 所以判断条件直接写进 where：抢不到（affected_rows=0）就说明状态已经变了，
     * 报「任务正在处理中」让站长稍后再来。这与 advance() 的领取写法是同一套。
     */
    public static function retry($taskId)
    {
        return self::guarded('retry', function () use ($taskId) {
            return self::retryGuarded($taskId);
        });
    }

    /** retry() 的主体。异常由 guarded() 统一收口。 */
    protected static function retryGuarded($taskId)
    {
        $taskId = intval($taskId);
        $task = Db::name('mpt_task')->where('mpt_id', $taskId)->find();
        if (!$task) {
            return self::err(lang('mpt/err_task_not_found'));
        }
        if (in_array(intval($task['mpt_status']), MptTask::busyStatuses(), true)) {
            return self::err(lang('mpt/err_task_running'));
        }

        // 时间戳同一秒命中时往后挪 1 秒，口径与 advance() 的领取一致：
        // MySQL 的 affected_rows 是**改动**行数不是命中行数（TP5 没开
        // PDO::MYSQL_ATTR_FOUND_ROWS），一条已经是 READY、remote_id/result_url/error
        // 都为空、progress 为 0 的任务在创建的同一秒里被点重试，六个字段一个都没变，
        // 返回 0 会被下面误判成「别人先动了手」，站长看到一句莫名其妙的「任务正在处理中」。
        // 保证时间戳必然变化，affected_rows 才真正只反映「有没有抢到」。
        $seen = intval($task['mpt_time_update']);
        $now = time();
        $stamp = ($now === $seen) ? ($now + 1) : $now;

        // 原子地把它压回 READY：条件里排除 busy 状态，抢不到就是别人先动了手
        $claimed = Db::name('mpt_task')
            ->where('mpt_id', $taskId)
            ->whereNotIn('mpt_status', MptTask::busyStatuses())
            ->update(array(
                'mpt_result_url' => '',
                'mpt_status' => MptTask::STATUS_READY,
                'mpt_remote_id' => '',
                'mpt_progress' => 0,
                'mpt_error' => '',
                'mpt_time_update' => $stamp,
            ));
        if (empty($claimed)) {
            return self::err(lang('mpt/err_task_running'));
        }

        // 抢到之后再动文件。顺序不能反：先删文件再抢锁的话，抢锁失败时文件已经没了。
        //
        // 重试会重新分配落盘路径，上一次的成片再没有任何东西引用它——先删掉，
        // 否则反复重试就是往 upload/mpt/ 里堆几十上百 MB 的孤儿文件。
        //
        // ★ 但先要确认真的没人引用它 ★
        // 本接口只拦 busy 状态，DONE 的任务同样可以重试（「再生成一版」是很自然的操作）。
        // auto_writeback 开着时那条成片已经写进 vod_play_url 了，无条件删掉的话，
        // 从这一刻到新片生成成功之间播放组是死链；重试最终失败的话死链就永久留着。
        // 口径与 remove() 一致：被引用就留着，等新片生成后由 writeBackPlaySource() 覆盖，
        // 覆盖成功的那一刻旧文件由 pruneOrphanResults() 收掉，不会永久堆积。
        $rel = (string) $task['mpt_result_url'];
        if ($rel !== '' && !self::stillReferenced(intval($task['mpt_obj_id']), $rel)) {
            Safety::deleteResultFile($rel);
        }

        return self::submit($taskId);
    }

    /**
     * 删除任务。
     * 成片文件只在「确认没人引用」时才一并删——已写回影片播放来源的删了会让
     * 那个播放组变成死链，判据见 stillReferenced()。
     */
    public static function remove($taskId)
    {
        return self::guarded('remove', function () use ($taskId) {
            return self::removeGuarded($taskId);
        });
    }

    /** remove() 的主体。异常由 guarded() 统一收口。 */
    protected static function removeGuarded($taskId)
    {
        $taskId = intval($taskId);
        $task = Db::name('mpt_task')->where('mpt_id', $taskId)->find();
        if (!$task) {
            return self::err(lang('mpt/err_task_not_found'));
        }
        // 正在被推进（多半是成片正在下载）时不能删：行没了之后 finish() 的 update
        // 打在空行上，下载完的文件就变成 upload/mpt/ 里谁也不认识的孤儿。
        // 租约最多 LEASE_SECONDS 就会释放，让站长稍后再删即可。
        //
        // ★ 条件写进 delete 的 where，不是先 find() 再判 ★
        // 分两步的话，读到「不是 WORKING」之后、delete 之前，并发的 advance()
        // 可以正好把它领走并开始下载 —— 那就是上面这段注释想防的情况原样发生。
        // 判断和删除必须是同一条语句，删不掉就说明它此刻正被推进。
        if (intval($task['mpt_status']) === MptTask::STATUS_WORKING) {
            return self::err(lang('mpt/err_task_running'));
        }

        $deleted = Db::name('mpt_task')
            ->where('mpt_id', $taskId)
            ->where('mpt_status', '<>', MptTask::STATUS_WORKING)
            ->delete();
        if (empty($deleted)) {
            return self::err(lang('mpt/err_task_running'));
        }

        // 行已经删掉了，再动文件：反过来的话删文件成功、删行失败，
        // 任务台上会留一条指向不存在文件的「已完成」记录。
        $rel = (string) $task['mpt_result_url'];
        if ($rel !== '' && !self::stillReferenced(intval($task['mpt_obj_id']), $rel)) {
            // 没有任何影片引用它，删了才不会在 upload/mpt/ 里留孤儿
            Safety::deleteResultFile($rel);
        }

        return self::ok();
    }

    /**
     * 批量删除任务。成片文件的处置口径与 remove() 完全一致
     * （还挂在影片播放来源上、或还被别的任务记录引用的一律留着）。
     *
     * ★ 为什么不是「循环调 remove()」★
     * remove() 每条要发 4 条查询（find + delete + vod + count），cleanup() 一次
     * 最多 500 条就是 2000 条 SQL 打在 vod 与 mpt_task 上，请求跑成分钟级，
     * 而它还挂在一个前台可达的接口上。这里改成整批四条：查回要删的行、一次删、
     * 一次问影片、一次问剩余任务。判据本身仍然只有 filterOrphans() 一处。
     *
     * 正在被推进（WORKING）的行一条都不碰，理由同 remove()。
     *
     * @param array $taskIds
     * @return int 实际删掉的行数
     */
    public static function removeMany(array $taskIds)
    {
        $ids = array();
        foreach ($taskIds as $one) {
            $one = intval($one);
            if ($one > 0) {
                $ids[$one] = $one;
            }
        }
        if (!$ids) {
            return 0;
        }

        // 先取回成片地址与所属影片——行删掉之后就问不到了
        $rows = Db::name('mpt_task')
            ->whereIn('mpt_id', $ids)
            ->where('mpt_status', '<>', MptTask::STATUS_WORKING)
            ->field('mpt_id,mpt_obj_id,mpt_result_url')
            ->select();
        if (!$rows) {
            return 0;
        }

        $delIds = array();
        $files = array();
        foreach ($rows as $row) {
            $delIds[] = intval($row['mpt_id']);
            $rel = trim((string) $row['mpt_result_url']);
            if ($rel === '') {
                continue;
            }
            // ★ 同一个地址被多条任务共享时，每一部相关影片都要收进来 ★
            // 只留第一条的影片是不够的：filterOrphans() 的第 2 道检查只看「还有没有
            // **别的**任务记录在用」，而这两条任务此刻正在同一批里被删掉，检查会
            // 一起放行；第 1 道检查又只查了第一部片。于是第二部片的播放组还挂着这个
            // 地址，文件却被删了，那个播放组当场变死链。单条走的 remove() 没有这个
            // 口子（它只处理一条任务、一部片），批量这一侧要自己把影片凑齐。
            $vodId = intval($row['mpt_obj_id']);
            if ($vodId > 0) {
                $files[$rel][$vodId] = $vodId;
            } elseif (!isset($files[$rel])) {
                $files[$rel] = array();
            }
        }

        // 条件再写一次 WORKING：上面那次 select 与这次 delete 之间，
        // 并发的 advance() 完全可能把其中某条领走并开始下载
        $deleted = Db::name('mpt_task')
            ->whereIn('mpt_id', $delIds)
            ->where('mpt_status', '<>', MptTask::STATUS_WORKING)
            ->delete();
        if (empty($deleted)) {
            return 0;
        }

        // 行已经删掉了再动文件，理由同 remove()
        foreach (self::filterOrphans($files) as $rel => $vodIds) {
            Safety::deleteResultFile($rel);
        }

        return intval($deleted);
    }

    /**
     * 取一个播放组里第一条的集数名，取不到就回落到当前语言的默认名。
     * 组内格式：`名称$地址#名称$地址#...`；只有地址没有名称时没得沿用。
     */
    protected static function existingEntryLabel($groupUrl)
    {
        $first = explode('#', trim((string) $groupUrl));
        $parts = explode('$', $first[0]);
        if (count($parts) > 1 && trim($parts[0]) !== '') {
            return trim($parts[0]);
        }

        return lang('mpt/play_label');
    }

    /**
     * 拆出一个播放组里的所有地址。
     * 组内格式：`名称$地址#名称$地址#...`；只有地址没有名称时整段就是地址。
     */
    protected static function groupUrls($groupUrl)
    {
        $out = array();
        foreach (explode('#', trim((string) $groupUrl)) as $one) {
            if (trim($one) === '') {
                continue;
            }
            $parts = explode('$', $one);
            $url = trim(count($parts) > 1 ? $parts[1] : $parts[0]);
            if ($url !== '') {
                $out[] = $url;
            }
        }

        return $out;
    }

    /**
     * 回收被新成片顶掉的旧文件。
     *
     * ★ 为什么需要它 ★
     * 对一条 DONE 的任务点「重试」时，旧成片还挂在播放组上（retry() 刻意不删，
     * 免得在新片出来之前留一段死链）。新片写回后旧地址就从播放组里消失了，
     * 但那一刻没有任何东西再去删它 —— 反复重试就会在 upload/mpt/ 下持续堆积
     * 几十上百 MB 的孤儿文件，站长完全看不见。
     *
     * 删之前仍然要确认没人引用：
     *   - 不是刚写进去的那一条（$keepUrl）；
     *   - 不是任何一条任务记录的 mpt_result_url（同一个文件可能被多条任务共享，
     *     比如站长手工把地址复制到了另一部片上再建了任务）。
     * deleteResultFile() 里还有一道 upload/mpt/ 前缀校验，站长手工填进播放组的
     * 外站地址或站内其它目录的文件一律动不了。
     */
    protected static function pruneOrphanResults(array $oldUrls, $keepUrl)
    {
        $keepUrl = ltrim((string) $keepUrl, '/');
        foreach (array_unique($oldUrls) as $old) {
            $rel = ltrim(trim((string) $old), '/');
            if ($rel === '' || $rel === $keepUrl) {
                continue;
            }
            if (strncmp($rel, 'upload/mpt/', 11) !== 0) {
                continue;
            }
            $stillUsed = Db::name('mpt_task')->where('mpt_result_url', $rel)->count();
            if ($stillUsed > 0) {
                continue;
            }
            Safety::deleteResultFile($rel);
        }
    }

    /**
     * 一个播放组的内容是不是全部由本插件产出。
     * 判据：每一集的地址都落在 upload/mpt/ 下。空组也算自己的（可以安全写入）。
     *
     * 组内格式：`名称$地址#名称$地址#...`
     */
    public static function groupIsOurs($groupUrl)
    {
        foreach (self::groupUrls($groupUrl) as $url) {
            if (strncmp(ltrim($url, '/'), 'upload/mpt/', 11) !== 0) {
                return false;
            }
        }

        // 空组也算自己的：没有内容可覆盖，写进去是安全的
        return true;
    }

    /**
     * 这个成片文件现在还有人用吗？有就不能删。
     *
     * 两道检查，缺一不可：
     *   1. 任务自己那部影片的播放来源里还挂着它 —— 删了那个播放组直接变死链；
     *   2. **别的任务记录**的 mpt_result_url 还是它。同一个文件可能被多条任务共享
     *      （站长手工把地址复制到另一部片上再建了任务），只看第 1 条的话，删掉
     *      本任务就会把另一条任务的成片一起带走。这条查询走 idx_result 索引
     *      （见 install.sql），不是全表扫。
     *
     * ⚠️ 已知边界：站长把地址手工贴进**另一部影片**的播放来源、又没有对应任务记录时，
     * 两道检查都看不见。要覆盖它只能对 vod 全表做 LIKE '%...%'，那在几十万行的
     * vod 上是一次几秒的全表扫，而 cleanup() 一次要删 500 条 —— 代价与收益不成比例。
     * deleteResultFile() 里的 upload/mpt/ 前缀校验保证受影响范围只在插件自己的产物内。
     *
     * @param int    $vodId  任务关联的影片；<=0 时跳过第 1 道检查
     * @param string $relUrl 站点根相对路径
     */
    protected static function stillReferenced($vodId, $relUrl)
    {
        $relUrl = (string) $relUrl;
        if ($relUrl === '') {
            return false;
        }
        // 判据只写在 filterOrphans() 里一处。单条走这里、批量走那里，
        // 各写一遍必然漂移，而漂移的表现是「某一条路径把还在用的成片删了」。
        $orphans = self::filterOrphans(array($relUrl => array(intval($vodId))));

        return !isset($orphans[$relUrl]);
    }

    /**
     * 从「成片地址 => 相关影片ID列表」里挑出**当前没有任何人引用**、可以安全删除的那些。
     *
     * 这是上面两道检查的批量形态：无论要判 1 个还是 500 个地址，都只发两条查询。
     * cleanup() 一次最多处理 500 条任务，逐条问就是 1000 条查询打在 vod 与
     * mpt_task 上，一个请求跑成分钟级。
     *
     * 调用方必须已经把「本次要删的行」清空 result_url 或整行删掉，
     * 否则第 2 道检查会把任务自己算成引用者。
     *
     * ★ 值是**列表**不是单个 ID ★
     * 同一个地址可能同时挂在好几条任务上，而那几条任务未必属于同一部影片
     * （站长手工复制过地址）。它们在同一批里被删掉时，第 2 道检查会一起放行，
     * 第 1 道检查就成了唯一防线 —— 只带一部影片进来的话，另一部片的播放组会
     * 在文件被删后变成死链。见 removeMany() 里凑影片那段。
     *
     * @param array $files rel => array(vodId,...)（空列表表示跳过第 1 道检查）
     * @return array 可以安全删除的 rel => array(vodId,...)，键名口径与入参一致
     */
    protected static function filterOrphans(array $files)
    {
        $out = array();
        if (!$files) {
            return $out;
        }

        // ① 相关影片的播放来源里还挂着它 —— 删了那个播放组直接变死链
        $vodIds = array();
        foreach ($files as $ids) {
            foreach ((array) $ids as $vodId) {
                $vodId = intval($vodId);
                if ($vodId > 0) {
                    $vodIds[$vodId] = $vodId;
                }
            }
        }
        $playUrls = $vodIds
            ? (array) Db::name('vod')->whereIn('vod_id', $vodIds)->column('vod_play_url', 'vod_id')
            : array();

        // ② 别的任务记录的 mpt_result_url 还是它。同一个文件可能被多条任务共享
        //    （站长手工把地址复制到另一部片上再建了任务），只看 ① 的话，删掉
        //    本任务就会把另一条任务的成片一起带走。这条查询走 idx_result 索引
        //    （见 install.sql），不是全表扫。
        $used = array();
        $rows = Db::name('mpt_task')->whereIn('mpt_result_url', array_keys($files))->column('mpt_result_url');
        foreach ((array) $rows as $one) {
            $used[(string) $one] = true;
        }

        foreach ($files as $rel => $ids) {
            // PHP 会把纯数字的数组键转成 int，统一转回字符串再比
            $rel = (string) $rel;
            if ($rel === '' || isset($used[$rel])) {
                continue;
            }
            // 任意一部相关影片还挂着它就不能删
            $referenced = false;
            foreach ((array) $ids as $vodId) {
                $vodId = intval($vodId);
                if ($vodId > 0 && isset($playUrls[$vodId])
                    && strpos((string) $playUrls[$vodId], $rel) !== false) {
                    $referenced = true;
                    break;
                }
            }
            if ($referenced) {
                continue;
            }
            $out[$rel] = (array) $ids;
        }

        return $out;
    }

    /**
     * 卸载前回收「从未被任何影片引用过」的成片。
     *
     * ★ 为什么 uninstall() 少了这一半就是残留 ★
     * Mpt::uninstall() 交代了「已写回播放来源的成片不删，删了播放组变死链」，
     * 但那只覆盖了写回成功的那些。auto_writeback=0 的站点、以及写回失败（播放组
     * 被占用、四列不等长）的任务，产物从头到尾没有任何影片引用它 —— DROP TABLE
     * 之后连 mpt_result_url 都没了，几十上百 MB 的文件永久留在 upload/mpt/ 下，
     * 站长既看不见也无从追溯是谁留的。
     *
     * ★ 判据一个字不改，仍然只有 filterOrphans() 一处 ★
     * 那两道检查里的第 2 道是「还有**别的**任务记录在用它」，而卸载场景下整张表
     * 都还在，每个地址都会命中自己那一行、于是一个都删不掉。filterOrphans() 的
     * 契约本来就写着「调用方必须已经把本次要删的行清空 result_url 或整行删掉」——
     * 这里照办：先把这一批的 result_url 清空，再交给它判。这样第 1 道检查
     * （影片播放来源里还挂着它吗）仍然全额生效，判据零漂移。
     * 清空是安全的：调用方紧接着就 DROP 这张表。
     *
     * ★ 必须分页 ★
     * 装了几年的站点这张表可能几万行，一次全捞回来就是一个 OOM。
     * 每批 200 条，扫到没有为止；上限兜住「表大到卸载会超时」这种极端情况。
     *
     * ★ 整个方法不允许抛异常 ★
     * 唯一的调用点是 Mpt::uninstall()，而 Service::uninstall() 会把它抛出的异常
     * 原样再抛（Service.php:367-369）—— 为了一个「顺手清垃圾」的动作让整个卸载
     * 失败，代价完全不成比例。口径与 PlayerSetup::fromInUse() 的 catch 一致。
     *
     * @param  int $max 本次最多回收多少个文件（不是行数）。到顶就停，剩下的留在盘上——
     *                  卸载不该因为清垃圾而跑成分钟级。表马上就没了，宁可少删不可超时。
     * @return int 实际删掉的文件数
     */
    public static function purgeOrphanResults($max = 20000)
    {
        $removed = 0;
        $max = max(0, intval($max));

        try {
            while ($removed < $max) {
                $rows = Db::name('mpt_task')
                    ->where('mpt_result_url', '<>', '')
                    ->field('mpt_id,mpt_obj_id,mpt_result_url')
                    ->limit(200)
                    ->select();
                if (!$rows) {
                    break;
                }

                $ids = array();
                $files = array();
                foreach ($rows as $row) {
                    $ids[] = intval($row['mpt_id']);
                    $rel = trim((string) $row['mpt_result_url']);
                    if ($rel === '') {
                        continue;
                    }
                    // 同一个地址可能挂在多条任务上，每一部相关影片都要收进来，
                    // 理由见 removeMany() 里凑影片那段与 filterOrphans() 的说明。
                    $vodId = intval($row['mpt_obj_id']);
                    if ($vodId > 0) {
                        $files[$rel][$vodId] = $vodId;
                    } elseif (!isset($files[$rel])) {
                        $files[$rel] = array();
                    }
                }

                // 先清空这一批，filterOrphans() 的第 2 道检查才不会把它们自己
                // 算成引用者。见上面的说明；下一句就 DROP 这张表。
                Db::name('mpt_task')->whereIn('mpt_id', $ids)->update(array('mpt_result_url' => ''));

                foreach (self::filterOrphans($files) as $rel => $vodIds) {
                    if (Safety::deleteResultFile($rel)) {
                        $removed++;
                    }
                }
            }
        } catch (\Throwable $e) {
            // 表本来就可能不存在（库账号没有 CREATE 权限时压根没建出来）。
            // 清垃圾失败不该让卸载失败，记一行日志即可。
            Log::error('mpt purge orphan results: ' . $e->getMessage());
        }

        return $removed;
    }

    public static function markFailed($taskId, $msg)
    {
        Db::name('mpt_task')->where('mpt_id', intval($taskId))->update(array(
            'mpt_status' => MptTask::STATUS_FAILED,
            'mpt_error' => mb_substr((string) $msg, 0, 200, 'UTF-8'),
            'mpt_time_update' => time(),
        ));
    }

    public static function hasPending()
    {
        // 不写 limit(1)：TP5 的 count() 走的是 COUNT(*) 聚合，limit 对它不生效，
        // 写了只会让人以为这里做了短路优化。三条查询都命中 idx_status。
        if (Db::name('mpt_task')->where('mpt_status', MptTask::STATUS_RUNNING)->count() > 0) {
            return true;
        }

        // ★ 租约已过期的 WORKING 也必须报 true ★
        // 租约**还没过期**的 WORKING 确实已经有推进者，为它挂兜底没意义；
        // 但推进者中途死掉（进程被 kill、PHP fatal、FPM 回收 worker）留下的行
        // 只有 advance() 开头的 reclaimExpiredLeases() 会放回队列，而进 advance()
        // 的前提就是本方法返回 true。漏掉这一条就会死锁在一个自锁的圈里：
        // 站里最后一条任务恰好卡在 WORKING → hasPending() 恒为假 → 不挂回调 →
        // 不进 advance() → 没人 reclaim。任务永久停在「生成中」，连 TASK_TTL
        // 超时判死也够不着（那段在 advanceOne() 里，而它只捞 RUNNING）。
        // 站长关掉任务台之后就再没有任何路径会碰它——正是 L2 存在的理由本身。
        if (Db::name('mpt_task')
                ->where('mpt_status', MptTask::STATUS_WORKING)
                ->where('mpt_time_update', '<', time() - self::LEASE_SECONDS)
                ->count() > 0) {
            return true;
        }

        // 卡在 READY 的行只有 advance() 开头的 resubmitStaleReady() 会救。这里必须
        // 一并报 true，否则站长关掉任务台之后就没有任何路径会再碰它了。
        // 这条查询只在完全没有 RUNNING 任务时才走，而整个兜底路径本身有 60s 节流。
        return Db::name('mpt_task')
            ->where('mpt_status', MptTask::STATUS_READY)
            ->where('mpt_time_update', '<', time() - self::READY_STALE_SECONDS)
            ->count() > 0;
    }

    /** 「还在跑」的任务数，RUNNING + WORKING 一起算，供任务台统计用 */
    public static function busyCount()
    {
        return Db::name('mpt_task')->whereIn('mpt_status', MptTask::busyStatuses())->count();
    }
}
