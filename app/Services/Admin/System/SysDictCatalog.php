<?php

namespace App\Services\Admin\System;

/**
 * 字典类型：只收真正写在 sys_dict 里的选项。
 * 影片分类、播放器、会员组不在这张表，也不在这里跳转。
 */
class SysDictCatalog
{
    /**
     * @return array<string, string>
     */
    public function families(): array
    {
        return [
            'filter' => '筛选',
            'other' => '自建',
        ];
    }

    /**
     * @return array<string, array{family:string,label:string,hint:string,url:string,kind:string,setting_key:string}>
     */
    public function groups(): array
    {
        return [
            'filter_area' => [
                'family' => 'filter',
                'label' => '地区',
                'hint' => '前台地区筛选。',
                'url' => '',
                'kind' => 'options',
                'setting_key' => 'filter_area',
            ],
            'filter_lang' => [
                'family' => 'filter',
                'label' => '语言',
                'hint' => '前台语言筛选。',
                'url' => '',
                'kind' => 'options',
                'setting_key' => 'filter_lang',
            ],
            'filter_year' => [
                'family' => 'filter',
                'label' => '年份',
                'hint' => '前台年份筛选。',
                'url' => '',
                'kind' => 'options',
                'setting_key' => 'filter_year',
            ],
            'filter_weekday' => [
                'family' => 'filter',
                'label' => '周期',
                'hint' => '周几更新。',
                'url' => '',
                'kind' => 'options',
                'setting_key' => '',
            ],
            'filter_serial' => [
                'family' => 'filter',
                'label' => '连载',
                'hint' => '连载状态，如更新至、全集。',
                'url' => '',
                'kind' => 'options',
                'setting_key' => '',
            ],
        ];
    }

    /**
     * @return array{family:string,label:string,hint:string,url:string,kind:string,setting_key:string}|null
     */
    public function group(string $id): ?array
    {
        $id = trim($id);
        $all = $this->groups();

        return $all[$id] ?? null;
    }

    public function isAway(string $id): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function filterTypeMap(): array
    {
        return [
            'area' => 'filter_area',
            'lang' => 'filter_lang',
            'year' => 'filter_year',
            'weekday' => 'filter_weekday',
            'serial' => 'filter_serial',
        ];
    }
}
