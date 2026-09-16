<?php

namespace addons\mpt\service;

/**
 * 插件配置项的**声明**（标题、类型、可选值、提示）。
 *
 * ★ 为什么声明不能直接写在 config.php 里 ★
 * 后台保存插件设置走 set_addon_config() → set_addon_fullconfig()
 * （vendor/karsonzhang/fastadmin-addons/src/common.php:451,477），它的写法是
 * 先 `include config.php` 拿到数组、再 `var_export()` 整个写回去。
 * 于是 config.php 里的 `lang('mpt/cfg_api_base')` 会在**保存那一刻**被求值成
 * 当时那一种语言的字面量，然后永久冻在文件里 —— 站点之后换语言，插件设置页
 * 的标题和提示再也不会跟着变，三份语言包等于只有一份生效。
 * 而 application/admin/view_new/addon/config.html:24,112 是直接输出
 * {$item.title} / {$item.tip} 的，不会再过一次 lang()，所以「存 key 等渲染时翻译」
 * 这条路也走不通。
 *
 * 解法：config.php 只留「站长填的值」，声明留在这里，读取时由 hydrate() 套上去。
 * 站长保存设置后 config.php 会被核心重新拍平一次，restore() 负责把它改回来。
 */
class ConfigSchema
{
    /** config.php 处于「只存值」形态的标记，据此判断要不要 restore() */
    const MARK = 'ConfigSchema::hydrate';

    /**
     * 完整声明。title / tip 每次调用都按**当前**语言重新求值。
     *
     * ★ 必须自己先把语言包装上 ★
     * 本方法最主要的消费方是后台插件设置页，它的调用链是
     * application/admin/controller/Addon.php:41 → get_addon_fullconfig()
     * → Addons::getFullConfig()（vendor/.../src/Addons.php:145-149，一句裸
     * `include config.php`）→ config.php → hydrate() → items()。
     * 这条链上**没有任何一环**会加载插件语言包：核心 Addon 控制器不认识插件的
     * lang/，think\addons\Controller::_initialize() 那次 Lang::load 只在请求
     * 打到插件自己的控制器时才跑，而 Mpt::viewFilter() 在
     * `$controller !== 'vod'` 处就早退了。
     * 于是 18 个配置项的 title/tip 全部拿到 Lang::get() 的兜底返回值 ——
     * 也就是 'mpt/cfg_api_base' 这样的裸 key，而 view_new/addon/config.html:24
     * 又是直出 {$item.title}。站长打开设置页看到的就是一屏 key。
     * 这恰好是本类顶部那段说明想解决的问题本身，只补一半等于没补。
     *
     * Safety::loadLang() 自带「当前 range 已装过就跳过」的探测，重复调用是廉价的。
     */
    public static function items()
    {
        Safety::loadLang();

        return array(
            array(
                'name'    => 'api_base',
                'title'   => lang('mpt/cfg_api_base'),
                'type'    => 'string',
                'content' => array(),
                // 默认留空，由站长自己填。tip 里只给一个第三方实例作为格式示例，
                // 并写明它不由本项目运营：插件随主库分发，预填一个非主库控制的
                // 服务地址，等于让站长在完全没做选择的情况下就把 api_key 指向了
                // 别人的服务器。填了才生效，比较妥当。
                'value'   => '',
                'rule'    => 'required',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_api_base_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'api_key',
                'title'   => lang('mpt/cfg_api_key'),
                'type'    => 'string',
                'content' => array(),
                'value'   => '',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_api_key_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'timeout',
                'title'   => lang('mpt/cfg_timeout'),
                'type'    => 'string',
                'content' => array(),
                'value'   => '20',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_timeout_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'verify_ssl',
                'title'   => lang('mpt/cfg_verify_ssl'),
                'type'    => 'select',
                // 默认 1，且 TaskRunner::config() 里「缺这个键也当开」——
                // 关掉证书校验必须是站长的一次显式动作，不能因为配置缺项就悄悄退化。
                'content' => array('1' => lang('mpt/yes'), '0' => lang('mpt/no')),
                'value'   => '1',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_verify_ssl_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'script_mode',
                'title'   => lang('mpt/cfg_script_mode'),
                'type'    => 'select',
                'content' => array(
                    'template' => lang('mpt/cfg_script_mode_template'),
                    'llm'      => lang('mpt/cfg_script_mode_llm'),
                    'remote'   => lang('mpt/cfg_script_mode_remote'),
                ),
                'value'   => 'template',
                'rule'    => 'required',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_script_mode_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'play_from',
                'title'   => lang('mpt/cfg_play_from'),
                'type'    => 'string',
                'content' => array(),
                'value'   => 'aivideo',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_play_from_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'auto_writeback',
                'title'   => lang('mpt/cfg_auto_writeback'),
                'type'    => 'select',
                'content' => array('1' => lang('mpt/yes'), '0' => lang('mpt/no')),
                'value'   => '1',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_auto_writeback_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'video_aspect',
                'title'   => lang('mpt/cfg_video_aspect'),
                'type'    => 'select',
                'content' => array('9:16' => '9:16', '16:9' => '16:9', '1:1' => '1:1'),
                'value'   => '9:16',
                'rule'    => '',
                'msg'     => '',
                'tip'     => '',
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'video_source',
                'title'   => lang('mpt/cfg_video_source'),
                'type'    => 'select',
                // local 可用，但**必须**同时填下面的 local_materials：
                // MPT 的 video_source=local 只认请求体里的 video_materials
                // （app/services/task.py:587，preprocess_video(materials=params.video_materials)），
                // 它不会去扫 storage/local_videos/。只选 local 而不给素材名，
                // 任务会停在「no valid local video materials were found」。
                'content' => array('pexels' => 'pexels', 'pixabay' => 'pixabay', 'coverr' => 'coverr', 'local' => 'local'),
                'value'   => 'pexels',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_video_source_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'local_materials',
                'title'   => lang('mpt/cfg_local_materials'),
                'type'    => 'text',
                'content' => array(),
                'value'   => '',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_local_materials_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'video_count',
                'title'   => lang('mpt/cfg_video_count'),
                'type'    => 'select',
                'content' => array('1' => '1', '2' => '2', '3' => '3'),
                'value'   => '1',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_video_count_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'video_clip_duration',
                'title'   => lang('mpt/cfg_clip_duration'),
                'type'    => 'string',
                'content' => array(),
                'value'   => '4',
                'rule'    => '',
                'msg'     => '',
                'tip'     => '',
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'video_concat_mode',
                'title'   => lang('mpt/cfg_video_concat_mode'),
                'type'    => 'select',
                'content' => array(
                    'random'     => lang('mpt/cfg_video_concat_mode_random'),
                    'sequential' => lang('mpt/cfg_video_concat_mode_sequential'),
                ),
                'value'   => 'random',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_video_concat_mode_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'voice_name',
                'title'   => lang('mpt/cfg_voice'),
                'type'    => 'string',
                'content' => array(),
                // 默认留空 = 按站点语言自动选（TaskRunner::defaultVoice()）。
                // 写死 zh-CN-YunxiNeural-Male 的话，日文站装上插件生成的是中文旁白，
                // 而站长多半不知道该改哪里。填了具体音色就以站长的为准。
                'value'   => '',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_voice_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'subtitle_enabled',
                'title'   => lang('mpt/cfg_subtitle'),
                'type'    => 'select',
                'content' => array('1' => lang('mpt/yes'), '0' => lang('mpt/no')),
                'value'   => '1',
                'rule'    => '',
                'msg'     => '',
                'tip'     => '',
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'bgm_type',
                'title'   => lang('mpt/cfg_bgm'),
                'type'    => 'select',
                'content' => array('random' => 'random', '' => lang('mpt/cfg_bgm_none')),
                'value'   => 'random',
                'rule'    => '',
                'msg'     => '',
                'tip'     => '',
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'poll_limit',
                'title'   => lang('mpt/cfg_poll_limit'),
                'type'    => 'string',
                'content' => array(),
                'value'   => '5',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_poll_limit_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'poll_fallback_enabled',
                'title'   => lang('mpt/cfg_poll_fallback'),
                'type'    => 'select',
                'content' => array('1' => lang('mpt/yes'), '0' => lang('mpt/no')),
                'value'   => '1',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_poll_fallback_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
            array(
                'name'    => 'cron_token',
                'title'   => lang('mpt/cfg_cron_token'),
                'type'    => 'string',
                'content' => array(),
                'value'   => '',
                'rule'    => '',
                'msg'     => '',
                'tip'     => lang('mpt/cfg_cron_token_tip'),
                'ok'      => '',
                'extend'  => '',
            ),
        );
    }

    /**
     * 把「只存值」的数组套上声明，还原成框架要的 fullconfig 形状。
     * config.php 最后一行调的就是它。
     *
     * @param array $values name => value
     */
    public static function hydrate(array $values)
    {
        $items = self::items();
        foreach ($items as $i => $item) {
            if (array_key_exists($item['name'], $values)) {
                $items[$i]['value'] = $values[$item['name']];
            }
        }

        return $items;
    }

    /** config.php 是不是已经被核心拍平成字面量了 */
    public static function needsRestore()
    {
        $file = self::file();
        if (!is_file($file)) {
            return false;
        }
        $fc = @file_get_contents($file);

        return is_string($fc) && strpos($fc, self::MARK) === false;
    }

    /**
     * 把 config.php 改回「只存值」形态，站长填的值一个不动。
     *
     * 幂等：已经是这个形态时 include 出来的仍是完整数组，取值逻辑不变。
     * 写不动（目录只读）时静默返回 false —— 那只会让文案停在某一种语言，
     * 功能本身不受影响，不值得为它中断安装或报错。
     *
     * ★ 判断与读取都必须在锁里 ★
     * 早先是先 needsRestore() + include 算出 $values，再去拿锁写回。那样有个
     * 很窄但后果很实的窗口：站长在「插件管理 → 设置」里点保存的同一瞬间，
     * 另一个请求的 Admin::_initialize() 正好走到这里，它读到的是保存**之前**的值，
     * 拿到锁之后再把这份旧值原样写回去 —— 站长刚填的 api_key / api_base 就这么
     * 无声无息地退回去了，页面上还显示保存成功。
     * 这与 PlayerSetup::ensure()「拿到锁后必须重读文件」是同一条规矩，两处口径要一致。
     */
    public static function restore()
    {
        // 锁外先做一次廉价预检：绝大多数请求 config.php 已经是目标形态，
        // 不该为它们付一次 fopen+flock。真要动手时锁内还会再判一次。
        if (!self::needsRestore()) {
            return true;
        }
        $file = self::file();
        if (!is_writable($file)) {
            return false;
        }

        // mac_arr2file() 用不上（这不是纯数组文件），自己加锁写，避免与并发的
        // 设置保存互相截断 —— config.php 写坏等于插件配置全没。
        $lock = @fopen(Safety::runtimeFile('config.lock'), 'c');
        if ($lock === false || !@flock($lock, LOCK_EX)) {
            if ($lock !== false) {
                @fclose($lock);
            }

            return false;
        }

        try {
            // 拿到锁后重读、重判：等锁期间别人可能已经写过了
            if (!self::needsRestore()) {
                return true;
            }
            $cur = Safety::includeFresh($file);
            if (!is_array($cur)) {
                return false;
            }
            $values = array();
            foreach ($cur as $item) {
                if (is_array($item) && isset($item['name'])) {
                    $values[(string) $item['name']] = isset($item['value']) ? $item['value'] : '';
                }
            }
            if (!$values) {
                return false;
            }

            return self::write($file, $values);
        } catch (\Throwable $e) {
            // config.php 被写坏时 include 会抛 ParseError（是 \Error 不是 \Exception）。
            // 文案语言本来就是锦上添花，为它把整个任务台打成 500 不值当，记一行日志即可。
            \think\Log::error('mpt config restore: ' . $e->getMessage());

            return false;
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }

    /**
     * 写出「只存值」形态的 config.php。调用方必须持有 runtime/mpt/config.lock。
     */
    protected static function write($file, array $values)
    {
        $php = "<?php\n\n"
            . "// ⚠️ 本文件由插件自动维护，只保存「站长填的值」。\n"
            . "// 标题与提示文案**不落盘** —— 它们在 ConfigSchema::items() 里按当前站点语言实时生成。\n"
            . "// 把完整声明写在这里的话，后台每次保存设置都会被 set_addon_fullconfig() 用\n"
            . "// var_export() 把当时那一种语言的字符串冻死在文件里，之后换语言再也不会变。\n"
            . "// 详见 addons/mpt/service/ConfigSchema.php 顶部说明。\n\n"
            . '$values = ' . var_export($values, true) . ";\n\n"
            . "return class_exists('\\\\addons\\\\mpt\\\\service\\\\ConfigSchema')\n"
            . "    ? \\addons\\mpt\\service\\ConfigSchema::hydrate(\$values)\n"
            . "    : array();\n";

        // ★ 必须原子写 ★
        // LOCK_EX 只排斥同样用锁的写者，挡不住并发请求对这个文件的 include ——
        // 核心 Addons::getConfig() 那句 include 没有任何保护，读到写了一半的
        // config.php 就是当场 ParseError，插件全废。见 Safety::putFileAtomic()。
        $ok = Safety::putFileAtomic($file, $php);
        if ($ok) {
            // 本次请求稍后还会 include 这个文件（get_addon_config 等），
            // 不失效的话拿到的仍是刚被替换掉的旧 opcode。
            Safety::invalidateFile($file);
        }

        return $ok;
    }

    protected static function file()
    {
        return ADDON_PATH . 'mpt' . DS . 'config.php';
    }
}
