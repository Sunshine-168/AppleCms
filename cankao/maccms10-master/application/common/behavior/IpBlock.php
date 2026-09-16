<?php
namespace app\common\behavior;

use think\exception\HttpResponseException;

/**
 * 全站 IP 封禁。挂在 app_begin，且必须排在 AntiScrape 之前
 * （已经封掉的 IP 不必再浪费一次限流计算）。
 *
 * ★ 为什么必须有这个行为 ★
 * blacks.php 的 black_ip_list 在此之前只被三个地方消费：
 *   application/index/controller/Comment.php
 *   application/api/controller/Danmaku.php
 *   application/api/controller/Chatroom.php
 * 也就是说它只挡「发评论/弹幕/聊天」，根本不是全站封禁 ——
 * 被「封」掉的扫描器照样能刷首页、打 API、继续扫描。
 * 没有这个行为，后台的「一键封禁」按钮就是个摆设：
 * 站长以为自己把攻击者挡在门外了，其实对方毫发无伤。
 *
 * ★ 后台入口永不封（救生索）★
 * 站长完全可能手滑封掉自己的出口 IP。如果连后台都进不去，
 * 就只能去改数据库或 SSH 改文件才能救回来。
 * 所以 ENTRANCE === 'admin' 时一律放行 —— 封禁的目标是爬虫和攻击者，
 * 不是把站长自己锁在门外。
 */
class IpBlock
{
    public function run(&$dispatch)
    {
        try {
            if (PHP_SAPI === 'cli') {
                return;
            }

            // 救生索：后台入口永远可达
            if (defined('ENTRANCE') && ENTRANCE === 'admin') {
                return;
            }

            $blacks = config('blacks');
            if (!is_array($blacks) || empty($blacks['black_ip_list']) || !is_array($blacks['black_ip_list'])) {
                return;
            }

            $ip = (string)mac_get_client_ip();
            if ($ip === '' || $ip === '0.0.0.0') {
                return;
            }

            // config('blacks') 已被框架缓存，这里零额外 I/O。
            // 列表通常只有几十到几百条，array_flip + isset 让判定与列表长度无关。
            $map = array_flip(array_map('strval', $blacks['black_ip_list']));
            if (!isset($map[$ip])) {
                return;
            }

            throw new HttpResponseException(response('', 403));
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // fail-open：封禁逻辑自身出错，绝不能把正常访客也挡在外面
        }
    }
}
