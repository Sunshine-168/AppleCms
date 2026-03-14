<?php
namespace App\Support\Utils;


class LangHelper
{

    /**
     * 扫描语言目录，获取可用语言列表
     *
     * @param string $langDir 语言包根目录
     * @return array
     */
    public static function getAllowLangList(string $langDir = ''): array
    {
        $langDir = $langDir ?: lang_path();

        $langs   = glob($langDir . '/*', GLOB_ONLYDIR);

        return array_map('basename', $langs);
    }


    /**
     * 扫描语言目录，生成 extend_list 配置
     *
     * @param string $langDir 语言包根目录
     * @return array
     */
    public static function scanLangFiles(string $langDir = ''): array
    {
        $langDir    = $langDir ?: lang_path();
        $result     = [];

        // 扫描每个语言文件夹
        $langs      = glob($langDir . '/*', GLOB_ONLYDIR);

        foreach ($langs as $langPath)
        {
            $langCode           = basename($langPath);
            $files              = glob($langPath . '/*.php');
            $result[$langCode]  = $files;
        }

        return $result;
    }
}
