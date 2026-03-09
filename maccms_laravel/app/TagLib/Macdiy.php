<?php

namespace App\TagLib;

/**
 * 自定义标签库示例
 * 
 * 这是一个示例标签库，用于演示如何创建自定义标签
 * 原文件：maccms10/application/common/taglib/Macdiy.php
 * 
 * 注意：在 Laravel 中，标签功能已通过 Blade 指令实现
 * 如果需要自定义标签，可以在 AppServiceProvider 中注册 Blade 指令
 */
class Macdiy
{
    /**
     * 测试标签（示例）
     * 
     * 原 ThinkPHP 语法：
     * {macdiy:test order="desc" by="time" num="10"}
     * {$vo.name}
     * {/macdiy:test}
     * 
     * Laravel Blade 语法（需要注册指令）：
     * @macdiytest(['order' => 'desc', 'by' => 'time', 'num' => 10])
     * @foreach($__TAG_LIST__ as $key => $vo)
     * {{ $vo['name'] }}
     * @endforeach
     * @endmacdiytest
     */
    public function tagTest($params = [])
    {
        // 示例：返回测试数据
        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $num = intval($params['num'] ?? 10);
        
        // 这里可以添加实际的业务逻辑
        return [
            ['name' => '测试项1'],
            ['name' => '测试项2'],
        ];
    }
}
