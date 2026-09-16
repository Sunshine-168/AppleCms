<?php
namespace App\Services\Admin\System;

use App\Models\System\SysFileModel;
use App\Support\Utils\Result;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * 系统文件服务
 */
class SysFileService
{
    public SysFileModel $sysFileModel;

    // 允许上传的扩展名
    protected array $allowExt = [
        'jpg','jpeg','png','gif','webp',
        'mp4','avi','mov',
        'pdf','doc','docx','xls','xlsx',
        'zip','rar'
    ];

    // 最大文件大小 10MB
    protected int $maxSize = 10485760;

    public function __construct()
    {
        $this->sysFileModel = new SysFileModel();
    }

    /**
     * 获取文件列表
     */
    public function getLists(string $keyword, int $limit): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $where = [];

        if ($keyword = trim($keyword)) {
            $where['or'] = [
                ['name', '=', $keyword],
                ['mime', '=', $keyword],
                ['url', '=', $keyword],
            ];
        }

        $data = $this->sysFileModel->paginates($where, '*', $limit, ['id' => 'desc']);

        $rows = $data['data'] ?? [];

        if (!is_array($rows)) {
            $rows = [];
        }

        foreach ($rows as &$item) {

            $item['create_time'] = !empty($item['create_time'])
                ? date('Y-m-d H:i:s', (int)$item['create_time'])
                : '';

            $item['update_time'] = !empty($item['update_time'])
                ? date('Y-m-d H:i:s', (int)$item['update_time'])
                : '';

            $size = (int)($item['size'] ?? 0);

            $item['size_text'] = $size >= 1048576
                ? round($size / 1048576, 2) . ' MB'
                : round($size / 1024, 2) . ' KB';
        }

        $data['data'] = $rows;

        return Result::success($data);
    }

    /**
     * 上传文件
     */
    public function upload(UploadedFile $file): array
    {
        if (!$file->isValid()) {
            return Result::fail('文件无效');
        }

        $size = (int)$file->getSize();

        if ($size > $this->maxSize) {
            return Result::fail('文件超过10MB限制');
        }

        $original = (string)$file->getClientOriginalName();
        $mime     = (string)$file->getClientMimeType();
        $ext      = strtolower((string)$file->getClientOriginalExtension());

        if (!in_array($ext, $this->allowExt)) {
            return Result::fail('不允许上传该文件类型');
        }

        // 文件名生成
        $filename = date('YmdHis') . '_' . mt_rand(1000,9999);

        if ($ext)
        {
            $filename .= '.' . $ext;
        }

        // 年月目录
        $dir = 'uploads/' . date('Y/m');

        $saveDir = public_path($dir);
        if (!is_dir($saveDir) && !@mkdir($saveDir, 0755, true) && !is_dir($saveDir)) {
            return Result::fail('上传目录创建失败');
        }

        try {
            $moved = $file->move($saveDir, $filename);
        } catch (\Throwable $e) {
            return Result::fail($e->getMessage() ?: '文件保存失败');
        }

        if (!$moved) {
            return Result::fail('文件保存失败');
        }

        $stored = $dir . '/' . $filename;
        $fullPath = public_path($stored);

        $md5 = is_file($fullPath) ? md5_file($fullPath) : '';

        $url = '/' . ltrim(str_replace('\\', '/', $stored), '/');

        $type = $this->detectType($ext);

        $now = time();

        $insert = [
            'name'        => $original,
            'path'        => $stored,
            'url'         => $url,
            'size'        => $size,
            'md5'         => $md5,
            'type'        => $type,
            'mime'        => $mime,
            'create_time' => $now,
            'update_time' => $now,
        ];

        $res = $this->sysFileModel->inserts($insert);

        if (!$res) {
            return Result::fail('入库失败');
        }

        return Result::success($insert, '上传成功');
    }
    /**
     * 打开文件
     */
    public function findById(int $id): array
    {
        if ($id <= 0) {
            return [];
        }

        return $this->sysFileModel->findById($id, ['id', 'name', 'path', 'mime', 'url']);
    }

    /**
     * 删除文件
     */
    public function delete(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));

        if (empty($ids)) {
            return Result::fail('参数错误');
        }

        $rows = $this->sysFileModel->selectByCondition(
            [['id','in',$ids]],
            ['id','path']
        );

        foreach ($rows as $row) {
            $path = (string)($row['path'] ?? '');
            $normalized = $this->normalizePath($path);
            if ($normalized !== '') {
                $publicPath = public_path($normalized);
                if (is_file($publicPath)) {
                    @unlink($publicPath);
                }

                $diskPath = $normalized;
                if (str_starts_with($diskPath, 'storage/')) {
                    $diskPath = substr($diskPath, 8);
                }
                Storage::disk('public')->delete($diskPath);
            }
        }

        $ok = $this->sysFileModel->deleteByCondition(
            [['id','in',$ids]]
        );

        if (!$ok) {
            return Result::fail('删除失败');
        }

        return Result::success([], '删除成功');
    }

    protected function normalizePath(string $path): string
    {
        $path = preg_replace('#^https?://[^/]+#i', '', $path);
        $path = ltrim((string)$path, '/');
        return (string)$path;
    }

    /**
     * 判断文件类型
     */
    protected function detectType(string $ext): int
    {
        $image = ['jpg','jpeg','png','gif','webp'];
        $video = ['mp4','avi','mov'];

        if (in_array($ext, $image)) {
            return 1;
        }

        if (in_array($ext, $video)) {
            return 2;
        }

        return 0;
    }
}
