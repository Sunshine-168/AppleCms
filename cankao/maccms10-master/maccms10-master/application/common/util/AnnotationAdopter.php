<?php
namespace app\common\util;

use think\Db;

/**
 * AI 标注的采纳/拒绝 —— 这是整个 PR 里唯一会写主表（mac_vod / mac_art）的地方。
 *
 * 默认走人工确认：LLM 会幻觉，直接覆盖人工编辑过的内容是不可逆的。
 * auto_adopt_empty 只对「当前为空」的字段生效，永不覆盖已有值。
 *
 * vod_blurb / art_blurb 是 varchar(255)，而 ai_summary 是 varchar(1000)，
 * 回写必须截断 —— 否则 MySQL 非严格模式下会静默截断，严格模式下直接报错。
 */
class AnnotationAdopter
{
    const BLURB_MAX = 255;
    const TAG_MAX = 100;   // mac_vod.vod_tag / mac_art.art_tag 都是 varchar(100)

    private static function tableOf($mid)
    {
        $mid = intval($mid);
        if ($mid === 1) {
            return ['table' => 'Vod', 'pk' => 'vod_id', 'tag' => 'vod_tag', 'blurb' => 'vod_blurb', 'type' => 'type_id'];
        }
        if ($mid === 2) {
            return ['table' => 'Art', 'pk' => 'art_id', 'tag' => 'art_tag', 'blurb' => 'art_blurb', 'type' => 'type_id'];
        }
        return null;
    }

    public static function adopt($mid, $contentId, $fields)
    {
        return self::apply($mid, $contentId, $fields, false);
    }

    public static function adoptEmptyOnly($mid, $contentId)
    {
        return self::apply($mid, $contentId, ['tags', 'summary', 'type_id'], true);
    }

    private static function apply($mid, $contentId, $fields, $emptyOnly)
    {
        $mid = intval($mid);
        $contentId = intval($contentId);
        $map = self::tableOf($mid);
        if ($map === null) {
            return ['code' => 0, 'msg' => 'unsupported mid'];
        }

        $model = model('ContentAiAnnotation');
        $ann = $model->getByObject($mid, $contentId);
        if (empty($ann)) {
            return ['code' => 0, 'msg' => 'annotation not found'];
        }
        if (intval($ann['status']) !== 0) {
            return ['code' => 0, 'msg' => 'annotation not actionable: already processed (status=' . intval($ann['status']) . ')'];
        }

        $row = Db::name($map['table'])->where($map['pk'], $contentId)->find();
        if (empty($row)) {
            return ['code' => 0, 'msg' => 'content not found'];
        }

        $update = [];

        if (in_array('tags', $fields, true)) {
            $aiTags = trim((string)$ann['ai_tags']);
            $cur = trim((string)$row[$map['tag']]);
            if ($aiTags !== '' && (!$emptyOnly || $cur === '')) {
                $update[$map['tag']] = mb_substr($aiTags, 0, self::TAG_MAX, 'UTF-8');
            }
        }
        if (in_array('summary', $fields, true)) {
            $aiSummary = trim((string)$ann['ai_summary']);
            $cur = trim((string)$row[$map['blurb']]);
            if ($aiSummary !== '' && (!$emptyOnly || $cur === '')) {
                $update[$map['blurb']] = mb_substr($aiSummary, 0, self::BLURB_MAX, 'UTF-8');
            }
        }
        if (in_array('type_id', $fields, true)) {
            $aiType = intval($ann['ai_type_id']);
            $cur = intval($row[$map['type']]);
            // type_id 的「空」是 0；已经归好类的内容不做自动改分类
            if ($aiType > 0 && (!$emptyOnly || $cur === 0)) {
                $update[$map['type']] = $aiType;
            }
        }

        try {
            Db::startTrans();
            // 原子认领：仅当仍为「待采纳」(status=0) 时才置为已采纳，
            // 用影响行数判断避免并发下同一标注被重复采纳（TOCTOU）。
            $claimed = Db::name('ContentAiAnnotation')
                ->where('id', intval($ann['id']))
                ->where('status', 0)
                ->update(['status' => 1, 'time_update' => time()]);
            if ($claimed <= 0) {
                Db::rollback();
                return ['code' => 0, 'msg' => 'annotation not actionable: already processed'];
            }
            if (!empty($update)) {
                Db::name($map['table'])->where($map['pk'], $contentId)->update($update);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => 'adopt failed'];
        }

        return ['code' => 1, 'msg' => '', 'data' => ['updated' => array_keys($update)]];
    }

    public static function reject($mid, $contentId)
    {
        $model = model('ContentAiAnnotation');
        $ann = $model->getByObject(intval($mid), intval($contentId));
        if (empty($ann)) {
            return ['code' => 0, 'msg' => 'annotation not found'];
        }
        if (intval($ann['status']) !== 0) {
            return ['code' => 0, 'msg' => 'annotation not actionable: already processed (status=' . intval($ann['status']) . ')'];
        }
        // 原子认领：仅当仍为「待采纳」(status=0) 时才置为已拒绝，与 adopt 对称，
        // 避免并发下 reject 覆盖另一路已写入的「已采纳」(status=1)（内容已落主表却显示已拒绝）。
        $rejected = Db::name('ContentAiAnnotation')
            ->where('id', intval($ann['id']))
            ->where('status', 0)
            ->update(['status' => 2, 'time_update' => time()]);
        if ($rejected <= 0) {
            return ['code' => 0, 'msg' => 'annotation not actionable: already processed'];
        }
        return ['code' => 1, 'msg' => ''];
    }
}
