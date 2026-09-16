<?php
namespace app\common\model;

use think\Db;

/**
 * 内容级多语言：mac_content_lang 的读写入口 + 当前请求语言的存取。
 */
class ContentLang extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'content_lang';
    protected $primaryId = 'id';

    /** @var string|null 当前请求解析出的内容语言，index 模块入口设置一次 */
    private static $currentLang = null;

    public static function setCurrent($lang)
    {
        self::$currentLang = $lang ?: null;
    }

    public static function getCurrent()
    {
        return self::$currentLang ?: mac_content_lang_default();
    }

    /**
     * 读取某内容在某语言下的可翻译字段。
     * @return array 未找到时返回空数组（调用方应回退到默认语言原文）
     */
    public function getFields($contentType, $contentId, $langCode)
    {
        if (empty($contentId) || empty($langCode)) {
            return [];
        }
        try {
            $row = $this->where([
                'content_type' => $contentType,
                'content_id'   => (int)$contentId,
                'lang_code'    => $langCode,
            ])->find();
        } catch (\Exception $e) {
            // 存量站点升级窗口期 mac_content_lang 可能还没建表：静默回退默认语言原文，不白屏
            return [];
        }
        if (empty($row) || empty($row['data'])) {
            return [];
        }
        $data = json_decode($row['data'], true);
        return is_array($data) ? $data : [];
    }

    /**
     * 批量读取某内容类型在某语言下所有（或指定若干）记录的可翻译字段。
     * 一条 SQL 取回，供 Type 分类缓存这类"整表 overlay"场景用，避免逐行查询。
     * @return array [content_id => [field => value, ...]]，无记录返回空数组
     */
    public function getFieldsMap($contentType, $langCode, array $contentIds = [])
    {
        if (empty($langCode)) {
            return [];
        }
        $where = ['content_type' => $contentType, 'lang_code' => $langCode];
        if (!empty($contentIds)) {
            $where['content_id'] = ['in', array_map('intval', $contentIds)];
        }
        try {
            $rows = $this->where($where)->column('data', 'content_id');
        } catch (\Exception $e) {
            return [];
        }
        $map = [];
        foreach ($rows as $cid => $data) {
            $d = json_decode($data, true);
            if (is_array($d)) {
                $map[(int)$cid] = $d;
            }
        }
        return $map;
    }

    /**
     * 写入/更新某内容在某语言下的可翻译字段（按 content_type+content_id+lang_code upsert）。
     */
    public function saveFields($contentType, $contentId, $langCode, array $fields, $source = 'manual', $status = 0)
    {
        $contentId = (int)$contentId;
        if (empty($contentId) || empty($langCode)) {
            return false;
        }

        // 只保留该内容类型真正允许翻译覆盖的字段：前端多传的（如 *_en slug、*_letter 首字母）
        // 一律丢弃，避免写进 JSON 成为永不回读的死数据。type_extend 这类结构字段在名单内会保留。
        $allow = mac_content_lang_fields($contentType);
        if (!empty($allow)) {
            $fields = array_intersect_key($fields, array_flip($allow));
        }
        if (empty($fields)) {
            return false;
        }

        // 默认语言字段在各模型 saveData() 里已过 mac_filter_xss()，其它语言字段走这条
        // 侧信道写入时必须套用同一份过滤名单，否则等于开了一个绕过 XSS 过滤的通道。
        $xssFields = mac_content_lang_xss_fields($contentType);
        foreach ($xssFields as $f) {
            if (isset($fields[$f]) && is_scalar($fields[$f])) {
                $fields[$f] = mac_filter_xss($fields[$f]);
            }
        }

        $row = [
            'content_type' => $contentType,
            'content_id'   => $contentId,
            'lang_code'    => $langCode,
            'data'         => json_encode($fields, JSON_UNESCAPED_UNICODE),
            'source'       => $source,
            'status'       => (int)$status,
            'update_time'  => time(),
        ];

        // 复用 Base::upsertByUnique()：一条 INSERT ... ON DUPLICATE KEY UPDATE，
        // 规避并发/双击保存时 find-then-insert 撞 uniq_item_lang 唯一键。
        // 存量站点升级窗口期 mac_content_lang 可能还没建表：与 getFields()/deleteByContent()
        // 一样静默失败，避免「主内容 saveData() 已成功、再写译文」这一步把整个后台保存动作带崩。
        try {
            return $this->upsertByUnique($row) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除某内容类型下若干内容的全部译文（各模型 delData() 里内容删除后调用）。
     * 不清理会留下永不回读的孤儿行，且 content_id 一旦被复用会把旧译文串给新内容。
     * @param array $contentIds 内容主键列表
     */
    public static function deleteByContent($contentType, array $contentIds)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $contentIds))));
        if (empty($contentType) || empty($ids)) {
            return;
        }
        try {
            Db::name('content_lang')->where([
                'content_type' => $contentType,
                'content_id'   => ['in', $ids],
            ])->delete();
        } catch (\Exception $e) {
            // 存量站点升级窗口期可能还没建表：静默跳过，不阻断内容删除
        }
    }
}
