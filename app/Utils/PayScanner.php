<?php
namespace App\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class PayScanner
{
    public static function scan(string $basePath = 'app/common/service/pay'): array
    {
        $result = [];
        $dir = base_path() . $basePath;

        $files = self::getPhpFiles($dir);

        foreach ($files as $file)
        {
            $className = self::getClassFullName($file);

            if (!$className || !class_exists($className)) continue;

            $reflection = new ReflectionClass($className);

            if ($reflection->isAbstract()) continue; // 跳过抽象类

            $docComment = $reflection->getDocComment();

            if ($docComment === false) continue;

            $info = self::parseDoc($docComment);
            $info['class'] = class_basename($className);
            $info['namespace'] = $className;

            $result[] = $info;
        }

        return $result;
    }

    protected static function getPhpFiles(string $dir): array
    {
        $rii   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($rii as $file) {
            if (!$file->isDir() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    protected static function getClassFullName(string $file): ?string
    {
        $content = file_get_contents($file);
        if (!preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) return null;
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) return null;
        return $nsMatch[1] . '\\' . $classMatch[1];
    }

    protected static function parseDoc(string $doc): array
    {
        $info = [
            'name' => '',
            'url' => '',
            'desc' => '',
            'time' => '',
            'enabled' => true, // 默认启用
        ];

        if (preg_match('/@name\s+(.*)/', $doc, $m)) $info['name'] = trim($m[1]);
        if (preg_match('/@url\s+(.*)/', $doc, $m))  $info['url'] = trim($m[1]);
        if (preg_match('/@desc\s+(.*)/', $doc, $m)) $info['desc'] = trim($m[1]);
        if (preg_match('/@time\s+(.*)/', $doc, $m)) $info['time'] = trim($m[1]);
        if (preg_match('/@enabled\s+(.*)/', $doc, $m)) {
            $value = strtolower(trim($m[1]));
            $info['enabled'] = in_array($value, ['true', '1', 'yes', 'on']);
        }

        return $info;
    }
}
