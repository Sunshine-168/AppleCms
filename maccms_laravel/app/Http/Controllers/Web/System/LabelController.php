<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;
use Illuminate\Http\Request;

class LabelController extends BaseController
{
    public function index(Request $request)
    {
        $file = $request->input('file', 'index');
        
        $file = preg_replace('/[^a-zA-Z0-9_-]/', '', $file);
        
        $templatePath = resource_path("views/label/{$file}.blade.php");
        
        if (!file_exists($templatePath)) {
            abort(404, '模板文件不存在');
        }
        
        return view("label.{$file}");
    }
}
