<?php
namespace app\common\util;

use think\Cache;

/**
 * 语义 embedding 公共服务：向量请求（带缓存 + 超时）、余弦相似度、语义重排。
 *
 * 抽自 AiChatService 的 requestOpenAiEmbeddings / vecCosineSimilarity /
 * applySemanticEmbeddingRerank，供 AiChatService 与 AiSearch 共用，让
 * config('maccms.ai_search') 里的 semantic_* 配置在两处都真正生效。
 *
 * 读取的配置项：
 *   provider / api_key / api_base / embedding_model / timeout
 *   semantic_enabled / semantic_weight / semantic_candidates
 *   embedding_cache_ttl（可选，缺省 self::DEFAULT_CACHE_TTL）
 */
class AiEmbeddingService
{
    /** 单条文本向量的缓存时长（秒）。同一 model+文本 的 embedding 是确定性的，可长缓存。 */
    // 注：不写 private —— 类常量可见性是 PHP 7.1+ 语法，本项目须兼容 PHP 7.0
    const DEFAULT_CACHE_TTL = 86400;

    /** 进程内一级缓存：避免同一请求内对相同文本重复查持久缓存。key => 向量|null。 */
    private static $memo = [];

    /** @var callable|null gate callback returning bool (allowed) for billable embedding HTTP calls */
    private static $llmGate = null;

    /** @var callable|null recorder callback(bool $ok) for embedding HTTP call results */
    private static $llmRecorder = null;

    /**
     * 安装成本防护 gate + recorder：让本类发起的 embedding HTTP 调用与 AiChatService
     * 的 chat 调用共用同一「每请求上限 + 熔断器」账本（主库 commit 8558ab7 的成本防护）。
     * 镜像 AiSearch::setLlmGate。未安装时行为不变（fail-open，不额外限流）。
     *
     * @param callable|null $gate     returns bool (true = allowed)
     * @param callable|null $recorder accepts bool $ok
     */
    public static function setLlmGate($gate, $recorder)
    {
        self::$llmGate = is_callable($gate) ? $gate : null;
        self::$llmRecorder = is_callable($recorder) ? $recorder : null;
    }

    /**
     * semantic_* 是否开启。
     *
     * @param array $cfg 原始 ai_search 配置
     * @return bool
     */
    public static function semanticEnabled(array $cfg)
    {
        return (string)(isset($cfg['semantic_enabled']) ? $cfg['semantic_enabled'] : '0') === '1';
    }

    /**
     * 语义重排：按「基础分 * (1-w) + 余弦相似度 * w * 3」重排，相似度高者靠前。
     * 未开启 / 无 items / 查询为空 / embedding 失败时原样返回 $items（fail-open，绝不拖垮检索）。
     *
     * @param string   $queryText 查询文本（用户输入）
     * @param array    $items     待排序条目（关联数组）列表
     * @param array    $cfg       原始 ai_search 配置
     * @param callable $snippetOf fn(array $item): string —— 取条目用于 embedding 的文本
     * @param array    $opts      [
     *     'base'     => fn(array $item, int $index): float 基础分，缺省按位置衰减
     *     'tie'      => fn(array $a, array $b): int         同分比较器，缺省 0
     *     'scoreKey' => string                             把混合分写回条目的字段名，'' 则不写
     * ]
     * @return array 重排后的 $items
     */
    public static function rerank($queryText, array $items, array $cfg, callable $snippetOf, array $opts = [])
    {
        if (empty($items) || !self::semanticEnabled($cfg)) {
            return $items;
        }
        $queryText = trim((string)$queryText);
        if ($queryText === '') {
            return $items;
        }
        $candidateLimit = max(3, intval(isset($cfg['semantic_candidates']) ? $cfg['semantic_candidates'] : 40));
        $weight = self::clampWeight(isset($cfg['semantic_weight']) ? $cfg['semantic_weight'] : 0.45);
        $items = array_slice($items, 0, $candidateLimit);

        $inputs = [$queryText];
        foreach ($items as $item) {
            $inputs[] = (string)$snippetOf($item);
        }
        $vectors = self::requestEmbeddings($inputs, $cfg);
        if (!is_array($vectors) || count($vectors) !== count($inputs)) {
            return $items;
        }

        $baseOf = (isset($opts['base']) && is_callable($opts['base'])) ? $opts['base'] : null;
        $tie = (isset($opts['tie']) && is_callable($opts['tie'])) ? $opts['tie'] : null;
        $scoreKey = isset($opts['scoreKey']) ? (string)$opts['scoreKey'] : '';

        $qv = $vectors[0];
        $ranked = [];
        foreach ($items as $idx => $item) {
            $sim = self::cosineSimilarity($qv, $vectors[$idx + 1]);
            $base = $baseOf !== null ? floatval($baseOf($item, $idx)) : max(0.35, 1.0 - $idx * 0.06);
            $score = ($base * (1.0 - $weight)) + ($sim * $weight * 3.0);
            if ($scoreKey !== '') {
                $item[$scoreKey] = $score;
            }
            $ranked[] = ['item' => $item, 'score' => $score, 'idx' => $idx];
        }
        usort($ranked, function ($a, $b) use ($tie) {
            if ($a['score'] === $b['score']) {
                if ($tie !== null) {
                    $t = (int)$tie($a['item'], $b['item']);
                    if ($t !== 0) {
                        return $t;
                    }
                }
                return $a['idx'] <=> $b['idx'];
            }
            return ($b['score'] > $a['score']) ? 1 : -1;
        });

        $out = [];
        foreach ($ranked as $r) {
            $out[] = $r['item'];
        }
        return $out;
    }

    /**
     * 请求 embedding 向量：进程内缓存 → 持久缓存 → 命中不足才批量请求，并回填缓存。
     * 返回与 $inputs 顺序一一对应的向量数组；任一环节失败返回 null（调用方据此跳过重排）。
     *
     * @param array $inputs 文本列表
     * @param array $cfg    原始 ai_search 配置
     * @return array|null
     */
    public static function requestEmbeddings(array $inputs, array $cfg)
    {
        $inputs = array_values($inputs);
        if (empty($inputs)) {
            return [];
        }
        $provider = strtolower(trim((string)(isset($cfg['provider']) ? $cfg['provider'] : '')));
        $apiKey = trim((string)(isset($cfg['api_key']) ? $cfg['api_key'] : ''));
        if ($provider !== 'openai' || $apiKey === '') {
            return null;
        }
        $model = trim((string)(isset($cfg['embedding_model']) ? $cfg['embedding_model'] : 'text-embedding-3-small'));
        if ($model === '') {
            $model = 'text-embedding-3-small';
        }
        $ttl = self::cacheTtl($cfg);

        // 先查缓存，收集未命中项一次性批量请求（省 token、省往返）。
        $vectors = array_fill(0, count($inputs), null);
        $missIdx = [];
        $missInputs = [];
        foreach ($inputs as $i => $text) {
            $text = (string)$text;
            $hit = self::cacheGet(self::cacheKey($model, $text));
            if (is_array($hit) && !empty($hit)) {
                $vectors[$i] = $hit;
            } else {
                $missIdx[] = $i;
                $missInputs[] = $text;
            }
        }

        if (!empty($missInputs)) {
            $fetched = self::httpEmbeddings($missInputs, $cfg, $model, $apiKey);
            if (!is_array($fetched) || count($fetched) !== count($missInputs)) {
                return null;
            }
            foreach ($missIdx as $k => $i) {
                $vec = $fetched[$k];
                if (!is_array($vec) || empty($vec)) {
                    return null;
                }
                $vectors[$i] = $vec;
                self::cacheSet(self::cacheKey($model, $missInputs[$k]), $vec, $ttl);
            }
        }

        foreach ($vectors as $vec) {
            if (!is_array($vec) || empty($vec)) {
                return null;
            }
        }
        return $vectors;
    }

    /**
     * 余弦相似度；维度取两者最小值，任一模长为 0 返回 0。
     *
     * @param array $a
     * @param array $b
     * @return float
     */
    public static function cosineSimilarity(array $a, array $b)
    {
        $n = min(count($a), count($b));
        if ($n < 1) {
            return 0.0;
        }
        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $va = floatval($a[$i]);
            $vb = floatval($b[$i]);
            $dot += $va * $vb;
            $na += $va * $va;
            $nb += $vb * $vb;
        }
        if ($na <= 0.0 || $nb <= 0.0) {
            return 0.0;
        }
        return $dot / (sqrt($na) * sqrt($nb));
    }

    private static function clampWeight($w)
    {
        $w = floatval($w);
        if ($w < 0) {
            return 0.0;
        }
        if ($w > 1) {
            return 1.0;
        }
        return $w;
    }

    private static function cacheTtl(array $cfg)
    {
        $ttl = intval(isset($cfg['embedding_cache_ttl']) ? $cfg['embedding_cache_ttl'] : self::DEFAULT_CACHE_TTL);
        if ($ttl < 60) {
            $ttl = self::DEFAULT_CACHE_TTL;
        }
        return $ttl;
    }

    private static function cacheKey($model, $text)
    {
        return 'ai_embed:' . md5($model . "\x1e" . $text);
    }

    private static function cacheGet($key)
    {
        if (array_key_exists($key, self::$memo)) {
            return self::$memo[$key];
        }
        $val = null;
        try {
            if (class_exists('\think\Cache', false)) {
                $val = Cache::get($key);
            }
        } catch (\Throwable $e) {
            $val = null;
        }
        self::$memo[$key] = is_array($val) ? $val : null;
        return self::$memo[$key];
    }

    private static function cacheSet($key, array $vec, $ttl)
    {
        self::$memo[$key] = $vec;
        try {
            if (class_exists('\think\Cache', false)) {
                Cache::set($key, $vec, $ttl);
            }
        } catch (\Throwable $e) {
            // 缓存写入失败不影响主流程
        }
    }

    /**
     * 实际发起 OpenAI /embeddings 请求，返回按 index 升序排列的向量数组或 null。
     *
     * @return array|null
     */
    private static function httpEmbeddings(array $inputs, array $cfg, $model, $apiKey)
    {
        $apiBase = rtrim((string)(isset($cfg['api_base']) ? $cfg['api_base'] : ''), '/');
        if ($apiBase === '') {
            $apiBase = 'https://api.openai.com/v1';
        }
        $timeout = max(3, intval(isset($cfg['timeout']) ? $cfg['timeout'] : 12));
        $post = ['model' => $model, 'input' => array_values($inputs)];
        $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey];
        // 成本防护：先过每请求上限（gate），命中上限直接放弃本次 embedding（fail-open 由调用方跳过重排）。
        if (self::$llmGate !== null && !call_user_func(self::$llmGate)) {
            return null;
        }
        $respBody = HttpClient::curlPostWithTimeout(
            $apiBase . '/embeddings',
            json_encode($post, JSON_UNESCAPED_UNICODE),
            $headers,
            $timeout
        );
        // 把这次计费 HTTP 调用的成败记入熔断器（与 AiChatService::guardedLlmCall 同口径：非空即成功）。
        if (self::$llmRecorder !== null) {
            call_user_func(self::$llmRecorder, $respBody !== false && $respBody !== '');
        }
        if ($respBody === false || $respBody === '') {
            return null;
        }
        $json = json_decode((string)$respBody, true);
        if (!is_array($json) || empty($json['data']) || !is_array($json['data'])) {
            return null;
        }
        usort($json['data'], function ($a, $b) {
            return intval(isset($a['index']) ? $a['index'] : 0) <=> intval(isset($b['index']) ? $b['index'] : 0);
        });
        $vectors = [];
        foreach ($json['data'] as $item) {
            if (empty($item['embedding']) || !is_array($item['embedding'])) {
                return null;
            }
            $vectors[] = $item['embedding'];
        }
        return $vectors;
    }
}
