<?php

namespace addons\mpt\service;

use app\common\util\AiProvider;

/**
 * 生成提交给 MPT 的 subject / script / terms。
 *
 * 三档由配置项 script_mode 决定：
 *   template（默认）—— 本地模板 + 内置词表，零外部依赖，任何站长都能用；
 *   llm            —— 复用核心 AiProvider（站长已在后台配了 AI key 时）；
 *   remote         —— 只出 subject，script 留空交给 MPT 服务端自己的 LLM。
 * 三档产出同一形状，站长在预览框里都能改了再提交。
 *
 * ★ 句式与标点一律走语言包，不写死在代码里 ★
 * 产出的是要被 TTS 念出来的口播文案，不是界面文案 —— 但它同样是「用户可见文案」，
 * 一个日文站装上插件却生成中文旁白是说不通的。所以开场白、书名号、顿号、
 * 「执导/主演」这类句式、乃至 LLM 的 system prompt 全部拆成 lang key，9 种语言各写一份。
 * 代码只负责「哪些字段有值就拼哪几句」，一个中文字符都不留。
 *
 * ⚠️ 用 sprintf() 拼句式是安全的：格式串只来自语言包（我们自己的资产），
 * 影片名里的 % 是**参数**不是格式串，不会被当占位符解析。
 *
 * ⚠️ 本类只能在语言包已加载的上下文里调用（Api 的 preview/submit）。
 * appInit 那一刻 Lang::range 还没就绪，那时候取 lang() 会拿到裸 key，
 * 直接当旁白送去 TTS 就是一句 "mpt/tpl_opener_1"。理由见 Safety::loadLang()。
 */
class ScriptBuilder
{
    /**
     * @return array ['code'=>1,'data'=>['subject'=>..,'script'=>..,'terms'=>array()]]
     */
    public static function build(array $vod, array $cfg)
    {
        $mode = isset($cfg['script_mode']) ? (string) $cfg['script_mode'] : 'template';

        if ($mode === 'remote') {
            return array('code' => 1, 'msg' => '', 'data' => array(
                'subject' => self::buildSubject($vod),
                'script' => '',
                'terms' => array(),
            ));
        }

        if ($mode === 'llm') {
            $res = self::buildByLlm($vod);
            if ($res['code'] === 1) {
                return $res;
            }
            // LLM 不可用时不让站长卡住，退回模板并把原因带出去
            $fallback = self::buildByTemplate($vod);
            $fallback['msg'] = $res['msg'];

            return $fallback;
        }

        return self::buildByTemplate($vod);
    }

    public static function buildSubject(array $vod)
    {
        $name = self::field($vod, 'vod_name');
        $sub = self::field($vod, 'vod_sub');
        if ($name === '') {
            return '';
        }

        return $sub !== ''
            ? sprintf(lang('mpt/tpl_subject_sub'), $name, $sub)
            : sprintf(lang('mpt/tpl_title'), $name);
    }

    /**
     * 模板档。同一部片结果稳定可复现（按 vod_id 取模选模板），
     * 字段为空的句子整句跳过，不留「主演：」这种空洞。
     */
    public static function buildByTemplate(array $vod)
    {
        $name = self::field($vod, 'vod_name');
        $year = self::field($vod, 'vod_year');
        $area = self::field($vod, 'vod_area');
        $class = self::field($vod, 'vod_class');
        $actor = self::firstOf(self::field($vod, 'vod_actor'), 2);
        $director = self::firstOf(self::field($vod, 'vod_director'), 1);
        $score = self::field($vod, 'vod_score');
        $max = self::maxChars();
        // 简介多半自带结尾标点，套上 tpl_blurb('%s。') 会拼出「……长夜。。」/「…night..」，
        // TTS 会把这一下读成一个额外的停顿。先把尾部标点剥掉，句号由模板统一补。
        $blurb = self::trimTail(self::clip(self::plain(self::field($vod, 'vod_blurb') ?: self::field($vod, 'vod_content')), intval($max / 2)));

        $openers = array(
            lang('mpt/tpl_opener_1'),
            lang('mpt/tpl_opener_2'),
            lang('mpt/tpl_opener_3'),
            lang('mpt/tpl_opener_4'),
            lang('mpt/tpl_opener_5'),
        );
        $idx = abs(intval(isset($vod['vod_id']) ? $vod['vod_id'] : 0)) % count($openers);

        $lines = array();
        $lines[] = $openers[$idx];

        $join = lang('mpt/tpl_join');
        $meta = array();
        if ($year !== '') {
            $meta[] = sprintf(lang('mpt/tpl_year'), $year);
        }
        if ($area !== '') {
            $meta[] = $area;
        }
        if ($class !== '') {
            // 分类字段里的分隔符（半角/全角逗号都常见）换成本语言的顿号
            $meta[] = str_replace(array(',', '，'), $join, $class);
        }
        if ($name !== '') {
            $title = sprintf(lang('mpt/tpl_title'), $name);
            $lines[] = $meta
                ? sprintf(lang('mpt/tpl_line_meta'), $title, implode(lang('mpt/tpl_meta_sep'), $meta))
                : sprintf(lang('mpt/tpl_line_title'), $title);
        }

        if ($director !== '') {
            $lines[] = sprintf(lang('mpt/tpl_director'), $director);
        }
        if ($actor !== '') {
            $lines[] = sprintf(lang('mpt/tpl_actor'), $actor);
        }
        if ($blurb !== '') {
            $lines[] = sprintf(lang('mpt/tpl_blurb'), $blurb);
        }
        if ($score !== '' && floatval($score) > 0) {
            $lines[] = sprintf(lang('mpt/tpl_score'), $score);
        }
        $lines[] = lang('mpt/tpl_closing');

        // 句子之间的连接符：中日韩的句号自带停顿，直接接；西文要补一个空格，
        // 否则会拼成 "...answer.Open it now"，TTS 念出来是连在一起的。
        $script = implode(lang('mpt/tpl_glue'), $lines);
        $script = self::clip($script, $max);

        $terms = Terms::pick(array($class, self::field($vod, 'vod_tag'), $area), 6);

        return array('code' => 1, 'msg' => '', 'data' => array(
            'subject' => self::buildSubject($vod),
            'script' => $script,
            'terms' => $terms,
        ));
    }

    /**
     * LLM 档。复用核心的 AiProvider（resolveConfig / chat 都是 public）。
     */
    public static function buildByLlm(array $vod)
    {
        if (!class_exists('\app\common\util\AiProvider')) {
            return array('code' => 0, 'msg' => lang('mpt/err_llm_unavailable'), 'data' => array());
        }
        $llm = AiProvider::resolveConfig();
        if (empty($llm['api_key'])) {
            return array('code' => 0, 'msg' => lang('mpt/err_llm_no_key'), 'data' => array());
        }

        // system prompt 也在语言包里：它同时承担「用哪种语言写旁白」这条指令，
        // 写死中文的话，en-us 站点拿到的依然是中文文案。
        $sys = lang('mpt/llm_system');

        // 8 个字段标签合在一个 key 里用 | 分隔 —— 拆成 8 个 key 只会让 9 份语言包
        // 多出 64 行几乎没有信息量的条目，而它们永远是一起改的。
        $labels = explode('|', lang('mpt/llm_fields'));
        $keys = array('vod_name', 'vod_sub', 'vod_year', 'vod_area', 'vod_class', 'vod_director', 'vod_actor');
        $sep = lang('mpt/llm_field_sep');

        $parts = array();
        foreach ($keys as $i => $k) {
            $v = self::field($vod, $k);
            if ($v !== '' && isset($labels[$i])) {
                $parts[] = $labels[$i] . $sep . $v;
            }
        }
        $blurb = self::clip(self::plain(self::field($vod, 'vod_blurb') ?: self::field($vod, 'vod_content')), self::maxChars() * 2);
        if ($blurb !== '' && isset($labels[7])) {
            $parts[] = $labels[7] . $sep . $blurb;
        }
        if (!$parts) {
            return array('code' => 0, 'msg' => lang('mpt/err_vod_empty'), 'data' => array());
        }

        $res = AiProvider::chat($llm, $sys, implode("\n", $parts));
        if (!is_array($res) || intval(isset($res['code']) ? $res['code'] : 0) !== 1) {
            $detail = is_array($res) && isset($res['msg']) ? $res['msg'] : '';

            return array(
                'code' => 0,
                'msg' => Safety::safeMessage($detail !== '' ? $detail : lang('mpt/err_llm_failed'),
                    array(isset($llm['api_key']) ? $llm['api_key'] : '')),
                'data' => array(),
            );
        }

        // AiProvider::chat() 的成功形状是 ['code'=>1,'msg'=>'','text'=>...]，正文在 text 不在 data
        $text = isset($res['text']) ? (string) $res['text'] : '';
        $json = self::extractJson($text);
        if (!is_array($json)) {
            return array('code' => 0, 'msg' => lang('mpt/err_llm_bad_json'), 'data' => array());
        }

        // 这三个字段是大模型吐出来的 JSON，给成嵌套对象/数组是常事（尤其 terms 里
        // 混进 {"term":"..."} 这种）。裸 (string) 会吐一句 notice 加一个字面量
        // "Array"，把它当口播稿送去 TTS 就是让配音念一声 "Array"。
        // 见 Safety::scalarText()。
        $subject = isset($json['subject']) ? trim(Safety::scalarText($json['subject'])) : '';
        $script = isset($json['script']) ? trim(Safety::scalarText($json['script'])) : '';
        $terms = array();
        if (isset($json['terms']) && is_array($json['terms'])) {
            foreach ($json['terms'] as $t) {
                $t = trim(Safety::scalarText($t));
                if ($t !== '') {
                    $terms[] = $t;
                }
            }
        }
        if ($subject === '') {
            $subject = self::buildSubject($vod);
        }
        if ($script === '') {
            return array('code' => 0, 'msg' => lang('mpt/err_llm_bad_json'), 'data' => array());
        }
        if (!$terms) {
            $terms = Terms::pick(array(self::field($vod, 'vod_class')), 6);
        }

        return array('code' => 1, 'msg' => '', 'data' => array(
            'subject' => $subject,
            // 比模板档略宽一点：LLM 的句子通常更完整，硬按模板档的上限截会砍在半句上
            'script' => self::clip($script, self::maxChars() + 40),
            'terms' => array_slice($terms, 0, 6),
        ));
    }

    protected static function extractJson($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }
        // 容忍 ```json ... ``` 包裹
        $s = strpos($text, '{');
        $e = strrpos($text, '}');
        if ($s === false || $e === false || $e <= $s) {
            return null;
        }
        $out = json_decode(substr($text, $s, $e - $s + 1), true);

        return is_array($out) ? $out : null;
    }

    protected static function field(array $vod, $k)
    {
        return isset($vod[$k]) ? trim((string) $vod[$k]) : '';
    }

    /**
     * 剥掉尾部的句读标点（全角半角都剥），供「模板自己会补句号」的字段使用。
     * 省略号整体剥掉，不要只剥掉最后一个点留下「…‥」这种半截。
     */
    protected static function trimTail($s)
    {
        $s = trim((string) $s);
        if ($s === '') {
            return '';
        }

        return trim(preg_replace('/[\x{3002}\x{FF01}\x{FF1F}\x{FF1B}\x{FF0C}\x{2026}.!?;,\s]+$/u', '', $s));
    }

    /**
     * 口播文案的字数上限（字符数，不是字节数）。
     *
     * 不能所有语言共用一个数：MPT 侧对中文的建议是 120-160 字，而同样时长的英文
     * 大约要 2.5 倍的字符数。写死 180 的话，en-us 站点生成的宣传片只有十来秒。
     * 值放在语言包里，各语种自己给合适的数；取不到（语言包没加载）时回落到中文口径。
     */
    protected static function maxChars()
    {
        $n = intval(lang('mpt/tpl_max_chars'));

        return ($n >= 60 && $n <= 600) ? $n : 180;
    }

    /** 取逗号分隔字段的前 N 个，用本语言的顿号连接 */
    protected static function firstOf($raw, $n)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }
        $parts = preg_split('/[,，\/|]+/u', $raw);
        $out = array();
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $out[] = $p;
            }
            if (count($out) >= $n) {
                break;
            }
        }

        return implode(lang('mpt/tpl_join'), $out);
    }

    /**
     * 把简介压成可以直接念出来的纯文本。
     *
     * 两处曾经写错、这里说明为什么是现在这样：
     *  - 空白**折叠**成一个空格，不是删掉。删掉对中文看不出问题，
     *    但英文简介会变成 "Aquietstoryabouttwobrothers"，配音直接废掉。
     *  - 这里**不再**调 mac_filter_xss()。产物是要送去 TTS 念的口播文本，
     *    再 htmlspecialchars 一遍只会得到 &quot; 这种被念出来的实体。
     *    解实体 + 去标签就够了，防 XSS 由输出端负责（任务台 JS 全程 esc()）。
     */
    protected static function plain($s)
    {
        $s = strip_tags((string) $s);
        $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
        // 解一次实体后可能又冒出标签（如 &lt;script&gt;），再去一次
        $s = strip_tags($s);
        $s = preg_replace('/\s+/u', ' ', $s);

        return trim((string) $s);
    }

    /**
     * 按句读边界截断，照 VodAiCover::clip() 的思路（该方法是 private，无法复用）。
     *
     * 全角与半角标点都要找：只找全角的话，英文/德文简介永远命中不了任何边界，
     * 每次都退化成硬切，把最后一个单词劈成两半送去 TTS。
     */
    protected static function clip($s, $max)
    {
        $s = (string) $s;
        $max = intval($max);
        if ($s === '' || $max <= 0) {
            return '';
        }
        $len = mb_strlen($s, 'UTF-8');
        if ($len <= $max) {
            return $s;
        }
        $cut = mb_substr($s, 0, $max, 'UTF-8');
        foreach (array('。', '！', '？', '；', '，', '.', '!', '?', ';', ',') as $p) {
            $pos = mb_strrpos($cut, $p, 0, 'UTF-8');
            if ($pos !== false && $pos > $max * 0.6) {
                return mb_substr($cut, 0, $pos + 1, 'UTF-8');
            }
        }

        return $cut;
    }
}
