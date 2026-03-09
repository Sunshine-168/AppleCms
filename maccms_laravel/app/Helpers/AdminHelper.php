<?php

/**
 * 后台管理辅助函数
 */

if (!function_exists('admin_url')) {
    /**
     * 生成后台管理URL
     */
    function admin_url($path = '')
    {
        return url('admin/' . ltrim($path, '/'));
    }
}

if (!function_exists('admin_route')) {
    /**
     * 生成后台管理路由
     */
    function admin_route($name, $parameters = [])
    {
        return route('admin.' . $name, $parameters);
    }
}

if (!function_exists('admin_trans')) {
    /**
     * 后台管理翻译函数
     */
    function admin_trans($key, $replace = [], $locale = null)
    {
        return trans('admin.' . $key, $replace, $locale);
    }
}
