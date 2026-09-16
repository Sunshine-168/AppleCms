<?php

namespace addons\mpt\service;

/**
 * 「分类 / 标签 → 英文素材关键词」词表。
 *
 * ★ 为什么放类常量而不是 application/extra/xxx.php ★
 * 铁律 4 规定 application/extra/ 下经 mac_arr2file() 回写的文件属于站长运行期数据，
 * 升级包不得覆盖，功能依赖它时必须「读→判→只补缺失」。这份词表是插件的内置资产、
 * 没有站长编辑入口，放进 extra/ 只会白白背上那套增量补写的包袱，还会在卸载时留垃圾。
 * 站长想改，改插件文件即可（随插件升级覆盖，语义正确）。
 */
class Terms
{
    /** 兜底关键词：任何分类都没命中时用 */
    public static $fallback = array(
        'cinematic city night',
        'crowd walking street',
        'dramatic clouds timelapse',
    );

    public static $map = array(
        '动作' => array('action fight scene', 'car chase street', 'explosion slow motion'),
        '武侠' => array('bamboo forest wind', 'ancient chinese architecture', 'sword silhouette'),
        '战争' => array('smoke battlefield', 'soldiers marching', 'ruined city war'),
        '犯罪' => array('dark alley night', 'police car lights', 'handcuffs close up'),
        '悬疑' => array('foggy road night', 'abandoned building interior', 'clock ticking close up'),
        '恐怖' => array('dark forest fog', 'flickering light corridor', 'old house door'),
        '惊悚' => array('running through corridor', 'rain window night', 'shadow figure wall'),
        '科幻' => array('futuristic city skyline', 'space stars nebula', 'neon technology abstract'),
        '奇幻' => array('magic particles glow', 'mountain landscape mist', 'castle silhouette sunset'),
        '爱情' => array('romantic couple sunset', 'holding hands close up', 'city cafe window'),
        '喜剧' => array('people laughing together', 'bright colorful street', 'party celebration confetti'),
        '剧情' => array('person looking window', 'quiet street morning', 'rain on glass'),
        '家庭' => array('family dinner table', 'children playing park', 'cozy living room'),
        '青春' => array('students campus walking', 'bicycle road sunset', 'basketball court'),
        '古装' => array('ancient chinese architecture', 'traditional lantern night', 'silk fabric close up'),
        '历史' => array('old map close up', 'ancient ruins', 'candle light manuscript'),
        // 英文分类名统一走 $alias，别在这里再放一份（两处会各自漂移）
        '动画' => array('colorful abstract shapes', 'clouds sky timelapse', 'paper craft motion'),
        '动漫' => array('colorful abstract shapes', 'city street anime style', 'cherry blossom petals'),
        '纪录片' => array('nature landscape aerial', 'wildlife animals', 'documentary interview setup'),
        '综艺' => array('stage lights concert', 'audience clapping', 'studio camera'),
        '音乐' => array('concert stage lights', 'guitar close up', 'recording studio'),
        '体育' => array('stadium crowd', 'running track athlete', 'football match'),
        '灾难' => array('storm clouds lightning', 'flood water street', 'earthquake rubble'),
        '冒险' => array('mountain hiking sunrise', 'jungle river', 'desert dunes'),
        '西部' => array('desert horse riding', 'old west town', 'dust road sunset'),
        '励志' => array('sunrise mountain top', 'person training gym', 'road ahead sunrise'),
        '伦理' => array('quiet room window light', 'city night walking', 'rain street reflection'),
        '短剧' => array('city street people', 'office interior', 'phone screen close up'),
    );

    /**
     * 别名 → $map 的正式键。
     *
     * ★ 为什么必须有这张表 ★
     * $map 的键全是简体中文分类名。繁体站的分类叫「動作」「愛情」，英文站叫
     * "Action" "Romance" —— 一个都命中不了，`pick()` 每次都退化成 $fallback，
     * 于是整站所有影片的素材关键词都一样（三个通用镜头），宣传片长得一模一样。
     * 这不是"翻译缺失"，是功能对非简中站点完全失效。
     *
     * 英文键一律小写，`pick()` 里对 ASCII 串先 strtolower 再查，
     * 这样 "Action" / "action" / "ACTION" 都能命中。
     */
    public static $alias = array(
        // 繁体。★ 只列**两种写法真的不同**的那些 ★
        // 简繁同形的分类名（科幻、奇幻、犯罪、恐怖、家庭、青春、西部…）不要写进来：
        // collect() 是先查 $map 命中即返回，自映射的 '科幻' => '科幻' 永远走不到，
        // 留着只会让人以为这张表得跟 $map 逐条对齐，下次加分类时白抄一遍。
        '動作' => '动作', '武俠' => '武侠', '戰爭' => '战争', '懸疑' => '悬疑',
        '驚悚' => '惊悚', '愛情' => '爱情', '喜劇' => '喜剧', '劇情' => '剧情',
        '古裝' => '古装', '歷史' => '历史', '動畫' => '动画', '動漫' => '动漫',
        '紀錄片' => '纪录片', '綜藝' => '综艺', '音樂' => '音乐', '體育' => '体育',
        '災難' => '灾难', '冒險' => '冒险', '勵志' => '励志', '倫理' => '伦理',
        '短劇' => '短剧',
        // 英文（含常见同义写法）
        'action' => '动作', 'martial arts' => '武侠', 'wuxia' => '武侠', 'kungfu' => '武侠',
        'war' => '战争', 'crime' => '犯罪', 'mystery' => '悬疑', 'suspense' => '悬疑',
        'horror' => '恐怖', 'thriller' => '惊悚', 'sci-fi' => '科幻', 'scifi' => '科幻',
        'science fiction' => '科幻', 'fantasy' => '奇幻', 'romance' => '爱情',
        'comedy' => '喜剧', 'drama' => '剧情', 'family' => '家庭', 'youth' => '青春',
        'costume' => '古装', 'history' => '历史', 'historical' => '历史',
        'animation' => '动画', 'cartoon' => '动画', 'anime' => '动漫',
        'documentary' => '纪录片', 'variety' => '综艺', 'reality' => '综艺',
        'music' => '音乐', 'musical' => '音乐', 'sport' => '体育', 'sports' => '体育',
        'disaster' => '灾难', 'adventure' => '冒险', 'western' => '西部',
        'inspirational' => '励志', 'biography' => '励志', 'short drama' => '短剧',
        'short' => '短剧',
    );

    /**
     * 取素材关键词。
     * @param array $hints 分类名 / 标签 / 剧情类型，任意顺序
     * @param int   $max
     * @return array 英文关键词，去重、不超过 $max 个
     */
    public static function pick(array $hints, $max = 6)
    {
        $out = array();
        foreach ($hints as $h) {
            $h = trim((string) $h);
            if ($h === '') {
                continue;
            }
            // 整串先试一次：像 "Science Fiction" / "Martial Arts" 这种带空格的分类名
            // 一旦被下面按空白拆开就再也匹配不上了
            self::collect($h, $out);
            // 一个字段里常见 "动作,武侠" 或 "动作 武侠" 这类多值
            $parts = preg_split('/[,，\/\s|]+/u', $h);
            foreach ($parts as $p) {
                self::collect($p, $out);
            }
        }
        if (!$out) {
            $out = self::$fallback;
        }
        $max = max(1, min(12, intval($max)));

        return array_slice($out, 0, $max);
    }

    /**
     * 把一个候选词解析成关键词并去重追加进 $out。
     *
     * strtolower() 只对 ASCII 生效，中文键原样穿过 —— 正是想要的：
     * 英文分类名不区分大小写，中文分类名保持原样匹配。
     */
    protected static function collect($word, array &$out)
    {
        $word = trim((string) $word);
        if ($word === '') {
            return;
        }
        $key = isset(self::$map[$word]) ? $word : null;
        if ($key === null) {
            $lower = strtolower($word);
            foreach (array($word, $lower) as $probe) {
                if (isset(self::$alias[$probe]) && isset(self::$map[self::$alias[$probe]])) {
                    $key = self::$alias[$probe];
                    break;
                }
            }
        }
        if ($key === null) {
            return;
        }
        foreach (self::$map[$key] as $term) {
            if (!in_array($term, $out, true)) {
                $out[] = $term;
            }
        }
    }
}
