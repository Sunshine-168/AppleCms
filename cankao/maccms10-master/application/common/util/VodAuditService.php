<?php

namespace app\common\util;

/**
 * 视频 UGC 自动审核：按规则表命中后自动待审/通过/驳回。
 */
class VodAuditService
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * 人工驳回时校验备注；通过返回空字符串，失败返回错误文案。
     */
    public static function rejectRemarkError($remark)
    {
        if (trim((string)$remark) === '') {
            return lang('admin/vod_audit/remark_required');
        }
        return '';
    }

    /**
     * @param array $vodData 待保存的视频字段
     * @return array|null ['status'=>int,'remark'=>string] 未命中任何规则时返回 null
     */
    public static function evaluate(array $vodData)
    {
        $rules = model('VodAuditRule')->getEnabledRules();
        if (empty($rules)) {
            return null;
        }

        foreach ($rules as $rule) {
            if (!self::matchRule($rule, $vodData)) {
                continue;
            }
            $action = intval($rule['rule_action']);
            $remark = trim((string)($rule['rule_remark'] ?? ''));
            if ($action === self::STATUS_REJECTED) {
                return [
                    'status' => self::STATUS_REJECTED,
                    'remark' => $remark !== '' ? $remark : lang('admin/vod_audit/auto_reject'),
                ];
            }
            if ($action === self::STATUS_APPROVED) {
                return ['status' => self::STATUS_APPROVED, 'remark' => ''];
            }
            return [
                'status' => self::STATUS_PENDING,
                'remark' => $remark,
            ];
        }

        return null;
    }

    /**
     * 保存前写入自动审核结果（已显式设为已审则跳过）。
     *
     * @param array $data 引用传递
     * @param bool  $isNew
     */
    public static function applyOnSave(array &$data, $isNew)
    {
        if (!empty($data['_skip_auto_audit'])) {
            unset($data['_skip_auto_audit']);
            return;
        }
        unset($data['_skip_auto_audit']);

        if (intval($data['vod_status'] ?? 0) === self::STATUS_APPROVED) {
            return;
        }
        if (in_array(intval($data['vod_status'] ?? 0), [VodPublishService::STATUS_DRAFT, VodPublishService::STATUS_SCHEDULED], true)) {
            return;
        }
        // 已驳回且未改回待审：保留人工/历史审核结论，避免编辑时再次被规则覆盖
        if (!$isNew && intval($data['vod_status'] ?? 0) === self::STATUS_REJECTED) {
            return;
        }

        $audit = self::evaluate($data);
        if ($audit !== null) {
            $data['vod_status'] = $audit['status'];
            if ($audit['status'] === self::STATUS_REJECTED) {
                $data['vod_audit_remark'] = mb_substr((string)$audit['remark'], 0, 255);
            } elseif ($audit['status'] === self::STATUS_PENDING && $audit['remark'] !== '') {
                $data['vod_audit_remark'] = mb_substr((string)$audit['remark'], 0, 255);
            }
            return;
        }

        if ($isNew && !isset($data['vod_status'])) {
            $data['vod_status'] = self::STATUS_PENDING;
        }

        if (intval($data['vod_status'] ?? 0) === self::STATUS_APPROVED) {
            $data['vod_audit_remark'] = '';
            $data['vod_publish_time'] = 0;
        }
    }

    protected static function matchRule(array $rule, array $vodData)
    {
        $type = (string)($rule['rule_type'] ?? '');
        switch ($type) {
            case 'title_keyword':
                return self::matchTitleKeyword($rule, $vodData);
            case 'pic_empty':
                return self::isPicEmpty($vodData);
            case 'pic_invalid':
                return self::isPicInvalid($vodData);
            default:
                return false;
        }
    }

    protected static function matchTitleKeyword(array $rule, array $vodData)
    {
        $pattern = trim((string)($rule['rule_pattern'] ?? ''));
        if ($pattern === '') {
            return false;
        }
        $haystack = mb_strtolower(
            (string)($vodData['vod_name'] ?? '') . ' ' . (string)($vodData['vod_sub'] ?? '')
        );
        if ($haystack === '' || $haystack === ' ') {
            return false;
        }
        $keywords = preg_split('/[\r\n|,，;；]+/u', $pattern, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw));
            if ($kw !== '' && mb_strpos($haystack, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    protected static function isPicEmpty(array $vodData)
    {
        $pic = trim((string)($vodData['vod_pic'] ?? ''));
        $thumb = trim((string)($vodData['vod_pic_thumb'] ?? ''));
        return $pic === '' && $thumb === '';
    }

    protected static function isPicInvalid(array $vodData)
    {
        $pic = trim((string)($vodData['vod_pic'] ?? ''));
        if ($pic === '') {
            return false;
        }
        $lower = strtolower($pic);
        if (strpos($lower, 'nopic') !== false || strpos($lower, 'no_pic') !== false) {
            return true;
        }
        if (preg_match('#^(https?://|mac:|/|upload/)#i', $pic)) {
            return false;
        }
        if (preg_match('#\.(jpe?g|png|gif|webp|bmp)(\?.*)?$#i', $pic)) {
            return false;
        }
        return true;
    }

    public static function statusText($status)
    {
        return VodPublishService::statusText($status);
    }

    public static function ruleTypeText($type)
    {
        $map = [
            'title_keyword' => lang('admin/vod_audit/type_title_keyword'),
            'pic_empty' => lang('admin/vod_audit/type_pic_empty'),
            'pic_invalid' => lang('admin/vod_audit/type_pic_invalid'),
        ];
        return $map[$type] ?? $type;
    }

    public static function ruleActionText($action)
    {
        $action = intval($action);
        $map = [
            self::STATUS_PENDING => lang('admin/vod_audit/action_pending'),
            self::STATUS_APPROVED => lang('admin/vod_audit/action_approve'),
            self::STATUS_REJECTED => lang('admin/vod_audit/action_reject'),
        ];
        return $map[$action] ?? lang('unknown');
    }
}
