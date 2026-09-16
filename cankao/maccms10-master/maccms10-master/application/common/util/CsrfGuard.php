<?php
namespace app\common\util;

/**
 * CSRF 校验 trait（API / 前台控制器共用）
 * 合法来源：带正确 __token__ POST 参数 / X-CSRF-Token header，或带 X-Requested-With 的同站 AJAX
 * 过渡：前端全面接入 token 后可移除 X-Requested-With 过渡分支
 */
trait CsrfGuard
{
    protected function checkCsrf()
    {
        $token = input('post.__token__');
        if (empty($token)) {
            $token = request()->header('x-csrf-token');
        }
        $sess = session('csrf_token');
        if (!empty($token) && !empty($sess) && hash_equals((string)$sess, (string)$token)) {
            return null;
        }
        // 过渡：允许带 X-Requested-With 的同站 AJAX（前端尚未加 token 前）
        $xhr = request()->header('x-requested-with') === 'XMLHttpRequest';
        $origin = request()->header('origin');
        // request()->host() 返回 HTTP_HOST，非标准端口时会带端口(如 localhost:8080)，
        // 而 parse_url($origin, PHP_URL_HOST) 只有主机名不含端口，直接比对会在非标准端口部署下
        // 把同源 AJAX 误判为跨站 → 订阅等写接口被拦成 1001 参数错误。这里统一只比对主机名。
        $host = preg_replace('/:\d+$/', '', (string)request()->host());
        $sameOrigin = empty($origin) || parse_url($origin, PHP_URL_HOST) === $host;
        if ($xhr && $sameOrigin) {
            return null;
        }
        return ['code' => 1001, 'msg' => lang('param_err')];
    }
}