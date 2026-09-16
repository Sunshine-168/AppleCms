<?php

// ⚠️ 本文件由插件自动维护，只保存「站长填的值」。
// 标题与提示文案**不落盘** —— 它们在 ConfigSchema::items() 里按当前站点语言实时生成。
// 把完整声明写在这里的话，后台每次保存设置都会被 set_addon_fullconfig() 用
// var_export() 把当时那一种语言的字符串冻死在文件里，之后换语言再也不会变。
// 详见 addons/mpt/service/ConfigSchema.php 顶部说明。

$values = array(
    'api_base'              => '',
    'api_key'               => '',
    'timeout'               => '20',
    'verify_ssl'            => '1',
    'script_mode'           => 'template',
    'play_from'             => 'aivideo',
    'auto_writeback'        => '1',
    'video_aspect'          => '9:16',
    'video_source'          => 'pexels',
    'local_materials'       => '',
    'video_count'           => '1',
    'video_clip_duration'   => '4',
    'video_concat_mode'     => 'random',
    'voice_name'            => '',
    'subtitle_enabled'      => '1',
    'bgm_type'              => 'random',
    'poll_limit'            => '5',
    'poll_fallback_enabled' => '1',
    'cron_token'            => '',
);

return class_exists('\\addons\\mpt\\service\\ConfigSchema')
    ? \addons\mpt\service\ConfigSchema::hydrate($values)
    : array();
