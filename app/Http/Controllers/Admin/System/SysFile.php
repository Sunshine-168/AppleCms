<?php
namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysFileService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 系统文件管理
 */
class SysFile extends Controller
{
    protected SysFileService $systemFileService;

    public function __construct()
    {
        $this->systemFileService = new SysFileService();
    }
    /**
     * 打开文件
     */
    public function index(): View|Factory
    {
        return view('admin.system.file.index', $this->systemFileService->pageBoard());
    }

    /**
     * 获取文件列表
     */
    public function getLists(Request $request): JsonResponse
    {
        $keyword = (string) $request->input('keyword', '');
        $limit   = (int) $request->input('limit', 10);
        $kind    = (string) $request->input('kind', '');
        $data    = $this->systemFileService->getLists($keyword, $limit, $kind);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 上传文件
     */
    public function upload(Request $request): JsonResponse
    {
        $file = $request->file('file');
        if (!$file instanceof UploadedFile)
        {
            return Ajax::fail('请选择文件');
        }
        $data = $this->systemFileService->upload($file);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除文件
     */
    public function delete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids))
        {
            $ids = explode(',', $ids);
        }
        if (!is_array($ids) || empty($ids))
        {
            return Ajax::fail('请选择要删除的数据');
        }
        $res = $this->systemFileService->delete($ids);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 打开文件
     */
    public function open(Request $request): BinaryFileResponse
    {
        $id = (int)$request->query('id', 0);
        $row = $this->systemFileService->findById($id);
        if (empty($row)) {
            abort(404);
        }

        $path = (string)($row['path'] ?? '');
        $path = preg_replace('#^https?://[^/]+#i', '', $path);
        $path = ltrim((string)$path, '/');
        if ($path === '') {
            abort(404);
        }

        $publicPath = public_path($path);
        if (is_file($publicPath)) {
            return response()->file($publicPath);
        }

        $diskPath = $path;
        if (str_starts_with($diskPath, 'storage/')) {
            $diskPath = substr($diskPath, 8);
        }

        $fullPath = Storage::disk('public')->path($diskPath);
        if (is_file($fullPath)) {
            return response()->file($fullPath);
        }

        abort(404);
    }
}
