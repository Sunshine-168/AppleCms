<?php

namespace addons\mpt\model;

/**
 * mac_mpt_task 的状态词表。
 * 状态口径与《三方服务接入插件通用规范》一致：
 * 0 待提交 / 1 已就绪 / 2 生成中 / 3 完成 / 4 失败。
 *
 * 5 是本插件多出来的一个**内部**状态：任务已被某个请求领取、正在推进中。
 * 它不是新的业务状态，对站长而言与「生成中」是同一件事（statusText 也回同一句），
 * 存在的唯一目的是把「正在被处理」的行移出可领取集合，见 TaskRunner::advance()。
 *
 * ★ 刻意不继承 think\Model ★
 * 全库对这张表的读写都走 Db::name('mpt_task') —— 领取任务、归还租约、批量删除
 * 全靠「条件写进 where、看 affected_rows」这套原子更新，ORM 的模型层在这里
 * 一点忙都帮不上（see TaskRunner::advanceLocked()）。挂一个从没被实例化过的
 * Model 子类只会让人以为存在另一条数据访问路径。
 */
class MptTask
{
    const STATUS_NEW = 0;
    const STATUS_READY = 1;
    const STATUS_RUNNING = 2;
    const STATUS_DONE = 3;
    const STATUS_FAILED = 4;
    /** 内部状态：已被领取、推进中（租约期内不可再被领取） */
    const STATUS_WORKING = 5;

    /** 对站长而言「还在跑」的两个状态 */
    public static function busyStatuses()
    {
        return array(self::STATUS_RUNNING, self::STATUS_WORKING);
    }

    public static function statusText($status)
    {
        switch (intval($status)) {
            case self::STATUS_READY:
                return lang('mpt/status_ready');
            case self::STATUS_RUNNING:
            case self::STATUS_WORKING:
                return lang('mpt/status_running');
            case self::STATUS_DONE:
                return lang('mpt/status_done');
            case self::STATUS_FAILED:
                return lang('mpt/status_failed');
            default:
                return lang('mpt/status_new');
        }
    }
}
