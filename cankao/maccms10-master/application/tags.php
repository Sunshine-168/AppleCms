<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用行为扩展定义文件
return [
    // 应用初始化
    'app_init'     => [
        'app\\common\\behavior\\SessionSameSite',
        'app\\common\\behavior\\Init',
        'app\\common\\behavior\\RequestSecurity',
        // 请求埋点只在这里注册 shutdown 回调，真正的记录发生在进程结束时。
        // 不能挂在 app_end：404/500 走异常路径，压根到不了 app_end，
        // 那样 http.4xx / http.5xx 会永远是零（详见 MonitorRequest 的类注释）。
        'app\\common\\behavior\\MonitorRequest',
    ],
    // 应用开始
    'app_begin'    => [
        'app\\common\\behavior\\Begin',
        'app\\common\\behavior\\CsrfGuard',
        // IpBlock 必须排在 AntiScrape 之前：已经封掉的 IP 不必再走一遍限流计算。
        // 没有这个行为，blacks.php 的 black_ip_list 只挡评论/弹幕/聊天，
        // 后台的「一键封禁」对刷首页和扫描器完全无效 —— 等于摆设。
        'app\\common\\behavior\\IpBlock',
        'app\\common\\behavior\\AntiScrape',
    ],
    // 模块初始化
    'module_init'  => [

    ],
    // 插件开始
    'addon_begin'  => [

    ],
    // 操作开始执行
    'action_begin' => [],
    // 视图内容过滤
    'view_filter'  => [
        'app\\common\\behavior\\PreviewLinks',
    ],
    // 日志写入
    'log_write'    => [],
    // 应用结束
    'app_end'      => [
        'app\\common\\behavior\\SecurityHeaders',
        'app\\common\\behavior\\CookieSameSite',
        'app\\common\\behavior\\AdminAudit',
    ],
];
