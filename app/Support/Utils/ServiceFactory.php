<?php
namespace Utils;

use RuntimeException;
use think\facade\Request;

class ServiceFactory
{
    /**
     * 获取 Service 实例
     * @param string|null $service 可传：
     *    - null → 自动根据 Controller 推断
     *    - 模块名 → 自动加命名空间 + Service
     *    - 完整类名 → 直接实例化
     */
    public static function make(string $service = null): object
    {
        // 如果传了完整类名并存在，直接实例化
        if ($service && class_exists($service)) {
            return new $service();
        }

        // 如果传了模块名，不含命名空间，则自动拼接
        $controllerClass = self::getCallerController();
        if ($service && !str_contains($service, '\\')) {
            // 自动获取调用 Controller 的命名空间
            [$namespace] = self::parseController($controllerClass);

            $version = self::getVersion();
            $class   = "{$namespace}\\service\\{$version}\\" . ucfirst($service) . "Service";

            if (!class_exists($class)) {
                throw new RuntimeException("Service class $class not found");
            }
            return new $class();
        }

        // 默认自动根据 Controller 推断模块
        if (!$controllerClass) {
            throw new RuntimeException("Cannot detect calling Controller");
        }

        [$namespace, $module] = self::parseController($controllerClass);
        $version = self::getVersion();
        $class   = "{$namespace}\\service\\{$version}\\" . ucfirst($module) . "Service";

        // if (!class_exists($class)) {
        //     throw new RuntimeException("Service class $class not found");
        // }

        try {
            return new $class();
        } catch (\Throwable $e) {
            throw new RuntimeException("Failed to instantiate service $class: " . $e->getMessage());
        }
    }

    /**
     * 获取调用的 Controller 类名
     */
    protected static function getCallerController(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        foreach ($trace as $t) {
            if (isset($t['class']) && str_contains($t['class'], '\\controller\\')) {
                return $t['class'];
            }
        }
        return '';
    }

    /**
     * 从 Controller 类名解析出命名空间和模块名
     */
    protected static function parseController(string $controllerClass): array
    {
        $parts = explode('\\', $controllerClass);
        if (count($parts) < 3) {
            throw new RuntimeException("Controller class name invalid: $controllerClass");
        }

        $namespace = implode('\\', array_slice($parts, 0, -2));
        $className = end($parts);
        $module    = str_ireplace('Controller', '', $className);

        return [$namespace, $module];
    }

    /**
     * 获取版本逻辑（请求头 > 请求参数 > 默认 v1）
     */
    protected static function getVersion(): string
    {
        $version = Request::header('X-API-Version') ?: Request::param('version', 'v1');
        return strtolower($version);
    }
}
