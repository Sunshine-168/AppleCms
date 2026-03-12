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
        $from = strtolower((string) $request->input('from', ''));

        if ($from !== '') {
            $editor = match ($from) {
                'ueditor' => new \App\Libraries\Editor\Ueditor(),
                'umeditor' => new \App\Libraries\Editor\Umeditor(),
                'kindeditor' => new \App\Libraries\Editor\Kindeditor(),
                'tinymce' => new \App\Libraries\Editor\Tinymce(),
                'ckeditor' => new \App\Libraries\Editor\Ckeditor(),
                default => null,
            };
            if ($editor !== null && method_exists($editor, 'front')) {
                $editor->front($request->all());
            }
        }

        $flag = (string) $request->input('flag', 'vod');
        $flag = preg_replace('/[^a-z0-9_-]/i', '', $flag) ?: 'vod';

        $input = (string) $request->input('input', 'file');
        $input = preg_replace('/[^a-z0-9_-]/i', '', $input) ?: 'file';

        $file = null;
        foreach ([$input, 'file', 'imgdata', 'file1', 'upfile', 'imgFile', 'upload'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                break;
            }
        }

        if (!$file) {
            if ($from !== '') {
                $this->respondEditor($from, '未找到上传的文件', 0, ['file' => '']);
            }
            return response()->json(['code' => 0, 'msg' => '未找到上传的文件']);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowedExtensions = match ($flag) {
            'vod_file' => ['mp4', 'mp3', 'mkv', 'torrent', 'zip', 'txt', 'rar'],
            default => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        };

        if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
            $msg = '非系统允许的上传格式';
            if ($from !== '') {
                $this->respondEditor($from, $msg, 0, ['file' => '']);
            }
            return response()->json(['code' => 0, 'msg' => $msg]);
        }

        $filename = md5(uniqid('', true)) . '.' . $extension;
        $ymd = date('Ymd');
        $nDir = $ymd . '-1';
        $targetDir = null;

        for ($i = 1; $i <= 100; $i++) {
            $nDir = $ymd . '-' . $i;
            $targetDir = public_path('upload/' . $flag . '/' . $nDir);
            if (!is_dir($targetDir)) {
                if (@mkdir($targetDir, 0755, true)) {
                    break;
                }
            } else {
                $files = glob($targetDir . DIRECTORY_SEPARATOR . '*.*');
                if (!$files || count($files) < 999) {
                    break;
                }
            }
        }

        if (!$targetDir || !is_dir($targetDir)) {
            $msg = '上传目录不可写';
            if ($from !== '') {
                $this->respondEditor($from, $msg, 0, ['file' => '']);
            }
            return response()->json(['code' => 0, 'msg' => $msg]);
        }

        $file->move($targetDir, $filename);

        $savePath = 'upload/' . $flag . '/' . $nDir . '/' . $filename;
        $thumb = (string) $request->input('thumb', '0');
        $thumbFile = $thumb === '1' ? $savePath : $savePath;
        $thumbClass = (string) $request->input('thumb_class', '');

        if ($from !== '') {
            $this->respondEditor($from, '上传成功', 1, ['file' => $savePath]);
        }

        return response()->json([
            'code' => 1,
            'msg' => '上传成功',
            'data' => [
                'file' => $savePath,
                'thumb' => [
                    ['file' => $thumbFile],
                ],
                'thumb_class' => $thumbClass,
            ],
        ]);
    }

    private function respondEditor(string $from, string $info, int $status, array $data): void
    {
        $from = strtolower($from);
        $editor = match ($from) {
            'ueditor' => new \App\Libraries\Editor\Ueditor(),
            'umeditor' => new \App\Libraries\Editor\Umeditor(),
            'kindeditor' => new \App\Libraries\Editor\Kindeditor(),
            'tinymce' => new \App\Libraries\Editor\Tinymce(),
            'ckeditor' => new \App\Libraries\Editor\Ckeditor(),
            default => null,
        };
        if ($editor !== null && method_exists($editor, 'back')) {
            $editor->back($info, $status, $data);
        }
        echo json_encode($status ? ['location' => ($data['file'] ?? '')] : ['error' => ($info ?: 'error')], 1);
        exit;
    }
}
