<?php

namespace addons\socialws\controller;

use think\addons\Controller;

class Index extends Controller
{
    public function index()
    {
        exit('当前插件暂无前台页面');
    }

    /**
     * GET /addons/socialws/index/config
     * Returns whether the plugin is enabled and, if so, the connection info
     * the browser client needs. Front-end treats disabled/missing identically
     * to a 404 — both mean "stay on the existing 4s poll".
     */
    public function config()
    {
        $info = get_addon_info('socialws');
        if (empty($info) || empty($info['state'])) {
            return json(['code' => 1, 'msg' => '', 'data' => ['enabled' => false]]);
        }

        $config = get_addon_config('socialws');
        return json(['code' => 1, 'msg' => '', 'data' => [
            'enabled'   => true,
            'ws_url'    => $config['ws_url'],
            'heartbeat' => (int)$config['heartbeat'],
        ]]);
    }
}
