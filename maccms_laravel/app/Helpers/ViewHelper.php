<?php

/**
 * 视图辅助函数
 * 用于替换 ThinkPHP 模板中的函数调用
 */

if (!function_exists('mac_filter_xss')) {
    /**
     * XSS 过滤
     */
    function mac_filter_xss($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('mac_format_size')) {
    /**
     * 格式化文件大小
     */
    function mac_format_size($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

if (!function_exists('mac_format_time')) {
    /**
     * 格式化时间
     */
    function mac_format_time($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}

if (!function_exists('mac_format_date')) {
    /**
     * 格式化日期
     */
    function mac_format_date($timestamp)
    {
        return date('Y-m-d', $timestamp);
    }
}
