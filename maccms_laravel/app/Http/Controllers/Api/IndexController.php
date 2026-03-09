<?php

namespace App\Http\Controllers\Api;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->response(1, '获取成功', [
            'site_name' => data_get($this->config, 'site.site_name'),
            'site_url' => url('/'),
            'api_status' => data_get($this->config, 'api.status', 0),
            'version' => config('version.app_version', ''),
        ]);
    }
}
