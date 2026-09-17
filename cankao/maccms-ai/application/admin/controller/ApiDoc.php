<?php
namespace app\admin\controller;

use think\Request;
use app\common\util\OpenApiSpec;

/**
 * API 文档控制器（新版后台）
 *
 * 将《说明文档/API接口说明V2.txt》转换为 OpenAPI 3.0 规范，并内嵌 Swagger UI 供在线浏览与调试。
 * - index()   渲染内嵌 Swagger UI 的文档页面
 * - openapi() 输出 OpenAPI 3.0 JSON（servers[].url 注入当前站点 api.php 入口）
 */
class ApiDoc extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->view->config('view_path', APP_PATH . 'admin/view_new/');
    }

    /**
     * API 文档首页（Swagger UI）
     */
    public function index()
    {
        $this->assign('title', lang('admin/apidoc/title'));
        $this->assign('api_base', $this->apiBaseUrl());
        return $this->fetch('apidoc/index');
    }

    /**
     * 输出 OpenAPI 3.0 规范（JSON）
     */
    public function openapi()
    {
        $base_url = $this->apiBaseUrl();
        // spec 由静态 API 定义生成、仅随入口地址变化，缓存避免每次请求重建整份规范
        $cache_key = 'apidoc_openapi_' . md5($base_url);
        $spec = \think\Cache::get($cache_key);
        if (empty($spec) || !is_array($spec)) {
            $builder = new OpenApiSpec();
            $spec = $builder->build($base_url);
            \think\Cache::set($cache_key, $spec, 3600);
        }

        return json($spec, 200, [], [
            'json_encode_param' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ]);
    }

    /**
     * 计算当前站点 api.php 入口地址（含域名），用于 OpenAPI servers 及 Swagger UI「Try it out」
     * @return string
     */
    private function apiBaseUrl()
    {
        $request = Request::instance();
        $base = $request->root();
        // 入口文件（admin.php / index.php）所在目录
        if (strpos($base, '.') !== false) {
            $dir = rtrim(dirname($base), '/\\');
        } else {
            $dir = rtrim($base, '/');
        }
        if ($dir === '.' || $dir === '\\' || $dir === '/') {
            $dir = '';
        }
        $domain = $request->domain();
        if (empty($domain)) {
            return ($dir === '' ? '' : $dir) . '/api.php';
        }
        return $domain . $dir . '/api.php';
    }
}
