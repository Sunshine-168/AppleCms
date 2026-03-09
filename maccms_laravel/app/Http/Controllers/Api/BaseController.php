<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = config('maccms');
        
        // Check if site is closed
        if ($this->config['site']['site_status'] == 0) {
            // Site is closed - could return error or allow API access
        }
    }

    protected function checkConfig()
    {
        $apiConfig = $this->config['api'] ?? [];
        
        // Check if API is enabled
        if (empty($apiConfig) || ($apiConfig['status'] ?? 0) == 0) {
            return response()->json([
                'code' => 1001,
                'msg' => 'API功能未开启'
            ], 403);
        }
        
        return null;
    }

    protected function formatSqlString($str)
    {
        return addslashes(strip_tags($str));
    }

    protected function response($code = 1, $msg = 'success', $data = [])
    {
        return response()->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }
}
