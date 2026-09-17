<?php
namespace app\common\util;

use think\Db;

/**
 * 内容 AI 标注：一次 LLM 调用同时产出 标签 + 摘要 + 建议分类。
 *
 * 为什么三项合一次：三者用的是同一段源文本。拆成三次调用等于同一段文本送三遍，
 * 成本 ×3 而质量不变。这是把这三个需求合并成一个 PR 的全部理由。
 *
 * 为什么摘要不复用 SeoAi 的 description：SeoAi 的 prompt 明确要求
 * 「description 120-160 chars」，那是给搜索引擎 meta 用的；
 * 这里要的是前台展示的剧情简介，语气和长度都不同。
 *
 * 落库只落到 mac_content_ai_annotation（待采纳队列）。写主表是 AnnotationAdopter 的事。
 */
class ContentAnnotator
{
    const SUMMARY_MAX = 1000;
    const TAGS_MAX = 500;

    public static function buildPayload($mid, $row)
    {
        $mid = intval($mid);
        if ($mid === 1) {
            return [
                'mid' => 1,
                'name' => (string)$row['vod_name'],
                'sub' => (string)$row['vod_sub'],
                'blurb' => (string)$row['vod_blurb'],
                'content' => strip_tags((string)$row['vod_content']),
                'class' => (string)$row['vod_class'],
                'tag' => (string)$row['vod_tag'],
                'year' => (string)$row['vod_year'],
                'area' => (string)$row['vod_area'],
                'actor' => (string)$row['vod_actor'],
            ];
        }
        return [
            'mid' => 2,
            'name' => (string)$row['art_name'],
            'sub' => (string)$row['art_sub'],
            'blurb' => (string)$row['art_blurb'],
            'content' => strip_tags(str_replace('$$$', '', (string)$row['art_content'])),
            'class' => (string)$row['art_class'],
            'tag' => (string)$row['art_tag'],
            'year' => '',
            'area' => '',
            'actor' => '',
        ];
    }

    public static function sourceHash($payload)
    {
        return sha1(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 候选分类。必须按 type_mid 过滤 —— 视频的内容不能被建议成文章分类。
     */
    public static function typeCandidates($mid)
    {
        $mid = intval($mid);
        $rows = Db::name('Type')
            ->field('type_id,type_name,type_pid')
            ->where('type_mid', $mid)
            ->where('type_status', 1)
            ->order('type_sort asc')
            ->select();
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'type_id' => intval($r['type_id']),
                'name' => (string)$r['type_name'],
            ];
        }
        return $out;
    }

    public static function systemPrompt()
    {
        return 'You are a media cataloguing assistant. '
            . 'Return STRICT JSON only, with exactly these keys: '
            . 'tags (array of short strings), summary (string), '
            . 'type_id_suggest (integer, MUST be one of the candidate ids given, or 0 if none fits), '
            . 'confidence (number between 0 and 1). '
            . 'Never invent facts that are not present in the input.';
    }

    public static function buildUserPrompt($payload, $candidates)
    {
        $lines = [];
        foreach ($candidates as $c) {
            $lines[] = '- ' . intval($c['type_id']) . ': ' . (string)$c['name'];
        }
        $candidateBlock = empty($lines) ? '(none)' : implode("\n", $lines);
        $targetLang = self::resolveTargetLanguage();

        $kind = intval($payload['mid']) === 1 ? 'video' : 'article';
        return "Annotate this {$kind}.\n"
            . "Output language: {$targetLang}.\n\n"
            . "Name: " . $payload['name'] . "\n"
            . "Subtitle: " . $payload['sub'] . "\n"
            . "Existing category: " . $payload['class'] . "\n"
            . "Existing tags: " . $payload['tag'] . "\n"
            . "Year: " . $payload['year'] . "\n"
            . "Area: " . $payload['area'] . "\n"
            . "Cast: " . self::cut($payload['actor'], 120) . "\n"
            . "Blurb: " . self::cut($payload['blurb'], 220) . "\n"
            . "Content excerpt: " . self::cut($payload['content'], 800) . "\n\n"
            . "Candidate categories (type_id: name):\n" . $candidateBlock . "\n\n"
            . "Rules:\n"
            . "1) tags: 3-8 short descriptive tags. No duplicates. No punctuation.\n"
            . "2) summary: a plot/topic summary for site visitors, 60-200 characters. NOT an SEO meta description.\n"
            . "3) type_id_suggest: pick exactly one id from the candidate list above, or 0 if none fits.\n"
            . "4) confidence: your own confidence in type_id_suggest, 0 to 1.\n"
            . "Return JSON only.";
    }

    /**
     * 解析并校验 LLM 输出。
     * type_id 必须落在候选集合内 —— 模型编一个不存在的分类 id 出来，
     * 会把内容归到一个不存在的分类里，这是不可接受的。
     */
    public static function parseResult($text, $candidates)
    {
        $empty = ['tags' => '', 'summary' => '', 'type_id' => 0, 'confidence' => 0.0, 'error' => ''];

        $text = trim((string)$text);
        if ($text === '') {
            $empty['error'] = 'empty ai response';
            return $empty;
        }
        // 模型很爱把 JSON 包在 ```json 围栏里
        $text = preg_replace('/^```[a-zA-Z]*\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $json = json_decode(trim($text), true);
        if (!is_array($json)) {
            $empty['error'] = 'invalid ai response';
            return $empty;
        }

        $tags = [];
        if (isset($json['tags']) && is_array($json['tags'])) {
            foreach ($json['tags'] as $t) {
                if (!is_scalar($t)) {
                    continue;
                }
                $t = trim(strip_tags((string)$t));
                $t = str_replace([',', '，'], '', $t);
                if ($t !== '') {
                    $tags[] = $t;
                }
            }
        }
        $tags = array_values(array_unique($tags));
        $tags = array_slice($tags, 0, 8);

        $summary = trim(preg_replace('/\s+/', ' ', strip_tags((string)(isset($json['summary']) ? $json['summary'] : ''))));

        $allowed = [];
        foreach ($candidates as $c) {
            $allowed[intval($c['type_id'])] = true;
        }
        $typeId = intval(isset($json['type_id_suggest']) ? $json['type_id_suggest'] : 0);
        if (!isset($allowed[$typeId])) {
            $typeId = 0;
        }

        $confidence = floatval(isset($json['confidence']) ? $json['confidence'] : 0);
        if ($confidence < 0) {
            $confidence = 0.0;
        }
        if ($confidence > 1) {
            $confidence = 1.0;
        }

        return [
            'tags' => self::cut(implode(',', $tags), self::TAGS_MAX),
            'summary' => self::cut($summary, self::SUMMARY_MAX),
            'type_id' => $typeId,
            'confidence' => $confidence,
            'error' => '',
        ];
    }

    /**
     * 对一条内容做标注。源文本没变（source_hash 命中）且非强制时直接跳过，不烧 token。
     */
    public static function annotate($mid, $contentId, $force = false)
    {
        $mid = intval($mid);
        $contentId = intval($contentId);
        if ($mid !== 1 && $mid !== 2) {
            return ['code' => 0, 'msg' => 'unsupported mid', 'data' => []];
        }

        $table = $mid === 1 ? 'Vod' : 'Art';
        $pk = $mid === 1 ? 'vod_id' : 'art_id';
        $row = Db::name($table)->where($pk, $contentId)->find();
        if (empty($row)) {
            return ['code' => 0, 'msg' => 'content not found', 'data' => []];
        }

        $payload = self::buildPayload($mid, $row);
        $hash = self::sourceHash($payload);

        $model = model('ContentAiAnnotation');
        $old = $model->getByObject($mid, $contentId);
        if (!$force && !empty($old) && (string)$old['source_hash'] === $hash && intval($old['status']) !== 3) {
            return ['code' => 1, 'msg' => 'skipped: source unchanged', 'data' => []];
        }

        $cfg = AiProvider::resolveConfig();
        $candidates = self::typeCandidates($mid);
        $res = AiProvider::chat($cfg, self::systemPrompt(), self::buildUserPrompt($payload, $candidates));

        if (intval($res['code']) !== 1) {
            $model->saveByObject($mid, $contentId, [
                'ai_tags' => '', 'ai_summary' => '', 'ai_type_id' => 0, 'ai_confidence' => 0,
                'source_hash' => $hash, 'status' => 3,
                'provider' => $cfg['provider'], 'model' => $cfg['model'],
                'error_msg' => mb_substr((string)$res['msg'], 0, 255),
            ]);
            return ['code' => 0, 'msg' => $res['msg'], 'data' => []];
        }

        $parsed = self::parseResult($res['text'], $candidates);
        if ($parsed['error'] !== '') {
            $model->saveByObject($mid, $contentId, [
                'ai_tags' => '', 'ai_summary' => '', 'ai_type_id' => 0, 'ai_confidence' => 0,
                'source_hash' => $hash, 'status' => 3,
                'provider' => $cfg['provider'], 'model' => $cfg['model'],
                'error_msg' => mb_substr($parsed['error'], 0, 255),
            ]);
            return ['code' => 0, 'msg' => $parsed['error'], 'data' => []];
        }

        $data = [
            'ai_tags' => mac_filter_xss($parsed['tags']),
            'ai_summary' => mac_filter_xss($parsed['summary']),
            'ai_type_id' => $parsed['type_id'],
            'ai_confidence' => $parsed['confidence'],
            'source_hash' => $hash,
            'status' => 0,
            'provider' => $cfg['provider'],
            'model' => $cfg['model'],
            'error_msg' => '',
        ];
        $model->saveByObject($mid, $contentId, $data);

        if ($cfg['auto_adopt_empty']) {
            AnnotationAdopter::adoptEmptyOnly($mid, $contentId);
        }
        return ['code' => 1, 'msg' => '', 'data' => $data];
    }

    private static function resolveTargetLanguage()
    {
        $sysLang = strtolower((string)config('maccms.app.lang'));
        if ($sysLang === '') {
            $sysLang = strtolower((string)config('default_lang'));
        }
        $map = [
            'zh-cn' => 'Chinese (Simplified)', 'zh-tw' => 'Chinese (Traditional)',
            'en-us' => 'English', 'ja-jp' => 'Japanese', 'ko-kr' => 'Korean',
            'de-de' => 'German', 'es-es' => 'Spanish', 'fr-fr' => 'French', 'pt-pt' => 'Portuguese',
        ];
        return isset($map[$sysLang]) ? $map[$sysLang] : 'English';
    }

    private static function cut($text, $len)
    {
        $text = (string)$text;
        if (mb_strlen($text, 'UTF-8') <= $len) {
            return $text;
        }
        return mb_substr($text, 0, $len, 'UTF-8');
    }
}
