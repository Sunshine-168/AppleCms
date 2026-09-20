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
        'jpg','jpeg','png','gif','webp','svg','ico',
        'mp4','avi','mov',
        'pdf','doc','docx','xls','xlsx',
        'zip','rar','css'
    ];

    // 最大文件大小 10MB
    protected int $maxSize = 10485760;

    public function __construct()
    {
        $this->sysFileModel = new SysFileModel();
    }

    /**
     * 附件工作台文案和分类条数
     *
     * @return array{queues: array<string,int>, ui: array<string,string>}
     */
    public function pageBoard(): array
    {
        return [
            'queues' => $this->queueCounts(),
            'ui' => [
                'title' => admin_t('ui.files_title'),
                'lead' => admin_t('ui.files_lead'),
                'note' => admin_t('ui.files_note'),
                'find' => admin_t('ui.ph_search_file'),
                'empty' => admin_t('ui.empty_files'),
                'empty_hint' => admin_t('ui.empty_files_hint'),
                'empty_search' => admin_t('ui.empty_file_search'),
                'empty_kind' => admin_t('ui.empty_file_kind'),
                'preview' => admin_t('ui.preview'),
                'no_preview' => admin_t('ui.no_preview'),
                'templates' => admin_t('nav.templates'),
                'settings' => admin_t('nav.settings'),
                'annex' => admin_t('nav.annex'),
                'upload' => admin_t('ui.upload_file'),
                'click_to_enlarge' => admin_t('ui.click_to_enlarge'),
                'kind_video' => admin_t('ui.kind_video'),
                'kind_file' => admin_t('ui.kind_file'),
                'n_files' => admin_t('ui.n_files'),
                'uploaded' => admin_t('ui.uploaded'),
                'upload_fail' => admin_t('ui.upload_fail'),
                'please_select_files' => admin_t('ui.please_select_files'),
                'confirm_batch_del_files' => admin_t('ui.confirm_batch_del_files'),
                'confirm_del_file' => admin_t('ui.confirm_del_file'),
                'cannot_open_image' => admin_t('ui.cannot_open_image'),
                'no_usable_link' => admin_t('ui.no_usable_link'),
                'deleted' => admin_t('ui.deleted'),
                'fail' => admin_t('ui.fail'),
            ],
        ];
    }

    /**
     * 获取文件列表
     */
    public function getLists(string $keyword, int $limit, string $kind = ''): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $kind = $this->normalizeKind($kind);
        $keyword = trim($keyword);

        try {
            $query = $this->sysFileModel->newQuery();
            $this->applyKind($query, $kind);
            if ($keyword !== '') {
                $like = '%'.$keyword.'%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('mime', 'like', $like)
                        ->orWhere('url', 'like', $like);
                });
            }
            $data = $query->orderByDesc('id')->paginate($limit)->toArray();
        } catch (\Throwable) {
            $data = ['data' => [], 'total' => 0];
        }

        $rows = $data['data'] ?? [];
        if (! is_array($rows)) {
            $rows = [];
        }

        foreach ($rows as $i => $item) {
            $rows[$i] = $this->decorateRow(is_array($item) ? $item : []);
        }

        $data['data'] = $rows;
        $data['queues'] = $this->queueCounts();

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

        try {
            $disk = (string) app(\App\Services\Video\VideoSettingService::class)->get('storage_disk', 'local');
            $cdn = rtrim((string) app(\App\Services\Video\VideoSettingService::class)->get('s3_url', ''), '/');
            if ($disk === 's3' && is_file($fullPath)) {
                \Illuminate\Support\Facades\Storage::disk('vod')->put($stored, (string) file_get_contents($fullPath));
                if ($cdn !== '') {
                    $url = $cdn.'/'.$stored;
                }
            }
        } catch (\Throwable) {
        }

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
                try {
                    if ((string) config('video.storage_disk', 'local') === 's3' && config('filesystems.disks.vod.bucket')) {
                        Storage::disk('vod')->delete($diskPath);
                    }
                } catch (\Throwable) {
                }
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
        $image = ['jpg','jpeg','png','gif','webp','svg','ico'];
        $video = ['mp4','avi','mov'];

        if (in_array($ext, $image, true)) {
            return 1;
        }

        if (in_array($ext, $video, true)) {
            return 2;
        }

        return 0;
    }

    /**
     * @return array<string, int>
     */
    protected function queueCounts(): array
    {
        $all = $this->sysFileModel->newQuery()->count();
        $image = $this->sysFileModel->newQuery();
        $this->applyKind($image, 'image');
        $video = $this->sysFileModel->newQuery();
        $this->applyKind($video, 'video');
        $imageN = $image->count();
        $videoN = $video->count();

        return [
            'all' => $all,
            'image' => $imageN,
            'video' => $videoN,
            'file' => max(0, $all - $imageN - $videoN),
        ];
    }

    protected function applyKind($query, string $kind): void
    {
        if ($kind === 'image') {
            $query->where(function ($q) {
                $q->where('type', 1)
                    ->orWhere('type', '1')
                    ->orWhere('mime', 'like', 'image/%');
            });

            return;
        }
        if ($kind === 'video') {
            $query->where(function ($q) {
                $q->where('type', 2)
                    ->orWhere('type', '2')
                    ->orWhere('mime', 'like', 'video/%');
            })->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->where('type', '!=', 1)->where('type', '!=', '1');
                })->where(function ($inner) {
                    $inner->where('mime', 'not like', 'image/%')
                        ->orWhereNull('mime')
                        ->orWhere('mime', '');
                });
            });

            return;
        }
        if ($kind === 'file') {
            $query->where(function ($q) {
                $q->whereNotIn('type', [1, 2, '1', '2'])
                    ->orWhereNull('type');
            })->where(function ($q) {
                $q->where('mime', 'not like', 'image/%')
                    ->orWhereNull('mime')
                    ->orWhere('mime', '');
            })->where(function ($q) {
                $q->where('mime', 'not like', 'video/%')
                    ->orWhereNull('mime')
                    ->orWhere('mime', '');
            });
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function decorateRow(array $item): array
    {
        $item['create_time'] = ! empty($item['create_time'])
            ? date('Y-m-d H:i:s', (int) $item['create_time'])
            : '';
        $item['update_time'] = ! empty($item['update_time'])
            ? date('Y-m-d H:i:s', (int) $item['update_time'])
            : '';

        $size = (int) ($item['size'] ?? 0);
        $item['size_text'] = $size >= 1048576
            ? round($size / 1048576, 2).' MB'
            : round($size / 1024, 2).' KB';

        $id = (int) ($item['id'] ?? 0);
        $item['is_image'] = $this->isImage($item);
        $item['is_video'] = $this->isVideo($item);
        $item['kind'] = $item['is_image'] ? 'image' : ($item['is_video'] ? 'video' : 'file');
        $item['kind_label'] = match ($item['kind']) {
            'image' => admin_t('ui.pics'),
            'video' => admin_t('ui.kind_video'),
            default => admin_t('ui.kind_file'),
        };
        $item['ext'] = $this->extOf($item);
        $item['open_url'] = $id > 0 ? '/admin/system/attachments/open?id='.$id : $this->publicUrl($item);
        $item['preview_url'] = $item['is_image'] ? $this->previewUrl($item) : '';

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isImage(array $item): bool
    {
        if ((int) ($item['type'] ?? 0) === 1) {
            return true;
        }
        $mime = strtolower((string) ($item['mime'] ?? ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $ext = $this->extOf($item);

        return $ext !== '' && in_array($ext, ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP', 'SVG', 'ICO'], true);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isVideo(array $item): bool
    {
        if ($this->isImage($item)) {
            return false;
        }
        if ((int) ($item['type'] ?? 0) === 2) {
            return true;
        }
        $mime = strtolower((string) ($item['mime'] ?? ''));
        if (str_starts_with($mime, 'video/')) {
            return true;
        }

        return in_array($this->extOf($item), ['MP4', 'AVI', 'MOV'], true);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function extOf(array $item): string
    {
        $blob = (string) ($item['name'] ?? '').' '.(string) ($item['url'] ?? '').' '.(string) ($item['path'] ?? '');
        if (preg_match('/\.([a-z0-9]+)(?:\?|$)/i', $blob, $m)) {
            return strtoupper($m[1]);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function publicUrl(array $item): string
    {
        $url = trim((string) ($item['url'] ?? ''));
        if ($url === '') {
            $url = trim((string) ($item['path'] ?? ''));
        }
        if ($url === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '/')) {
            return $url;
        }

        return '/'.ltrim(str_replace('\\', '/', $url), '/');
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function previewUrl(array $item): string
    {
        $pub = $this->publicUrl($item);
        if ($pub !== '') {
            return $pub;
        }
        $id = (int) ($item['id'] ?? 0);

        return $id > 0 ? '/admin/system/attachments/open?id='.$id : '';
    }

    protected function normalizeKind(string $kind): string
    {
        $kind = strtolower(trim($kind));

        return in_array($kind, ['image', 'video', 'file'], true) ? $kind : '';
    }
}
