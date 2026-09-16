<?php

namespace App\Support\Utils;

/**
 * 文件
 */
class File
{

    /**
     * 创建多级目录
     * @param string $dirname 要创建的目录路径
     * @param int $mode 目录权限，默认为 0777
     * @param bool $recursive 是否递归创建，默认为 true
     * @return bool 创建成功返回 true，失败返回 false
     */
    public static function mkdir(string $dirname, int $mode = 0777, bool $recursive = true): bool
    {
        if (is_dir($dirname))
        {
            return true;
        }

       if (!self::mkdir(dirname($dirname), $mode, $recursive))
       {
           return false;
       }

       return mkdir($dirname, $mode, $recursive);
    }


    /**
     * 目录
     * @param string $directory
     * @return array
     */
    public  static function listFiles(string $directory = './uploads'): array
    {
        // 获取目录中的所有文件和子目录
        $files = scandir($directory);

        // 过滤掉 '.' 和 '..'
        $files = array_diff($files, ['.', '..']);

        // 初始化一个数组来存储文件信息
        $fileInfo = [];

        foreach ($files as $file)
        {
            $filePath   = $directory  . $file;

            $fileNameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);

            $fileInfo[] = [
                'name'      => $fileNameWithoutExtension,
                'file_name' => $file,
                'path'      => $filePath,
                'isDir'     => is_dir($filePath)
            ];
        }

        return $fileInfo;
    }

}
