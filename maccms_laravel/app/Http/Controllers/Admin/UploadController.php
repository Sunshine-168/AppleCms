<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        $path = $request->input('path', '');
        $id = $request->input('id', '');
        
        return view('admin.upload.index', compact('path', 'id'));
    }

    public function test()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'Tux');
        if ($tempFile) {
            return response('测试写入成功：' . $tempFile);
        } else {
            return response('测试写入失败：' . sys_get_temp_dir(), 500);
        }
    }

    public function upload(Request $request)
    {
        // This would typically call a service or model method
        // For now, we'll handle basic file upload
        if (!$request->hasFile('file')) {
            return response()->json(['code' => 1001, 'msg' => '没有上传文件']);
        }
        
        $file = $request->file('file');
        $flag = $request->input('flag', 'vod');
        $thumb = $request->input('thumb', '0');
        
        // Validate file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json(['code' => 1002, 'msg' => '不支持的文件格式']);
        }
        
        // Generate filename
        $filename = md5(uniqid()) . '.' . $extension;
        $ymd = date('Ymd');
        $nDir = $ymd;
        
        // Find available directory
        for ($i = 1; $i <= 100; $i++) {
            $nDir = $ymd . '-' . $i;
            $path = public_path("upload/{$flag}/{$nDir}");
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
                break;
            }
            
            $files = glob($path . '/*.*');
            if ($files && count($files) < 999) {
                break;
            }
        }
        
        // Save file
        $savePath = "upload/{$flag}/{$nDir}/{$filename}";
        $file->move(public_path("upload/{$flag}/{$nDir}"), $filename);
        
        // Generate thumb if needed
        $thumbPath = '';
        if ($thumb == '1') {
            // Generate thumbnail logic here
            $thumbPath = $savePath; // Placeholder
        }
        
        return response()->json([
            'code' => 1,
            'msg' => '上传成功',
            'data' => [
                'url' => asset($savePath),
                'path' => $savePath,
                'thumb' => $thumbPath ? asset($thumbPath) : '',
            ]
        ]);
    }
}
