<?php

namespace Tests\Feature;

use App\Models\System\SysFileModel;
use App\Services\Admin\System\SysFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachmentIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_attachment_index_shows_image_preview_not_a_dump_table(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/attachments')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('file-index', $html);
        $this->assertStringContainsString('附件', $html);
        $this->assertStringContainsString('图片可以看缩略图', $html);
        $this->assertStringContainsString('点开会放大', $html);
        $this->assertStringContainsString('没有缩略图', $html);
        $this->assertStringContainsString('未入库的不会出现', $html);
        $this->assertStringContainsString('还没有附件', $html);
        $this->assertStringContainsString('file-thumb', $html);
        $this->assertStringContainsString('file-lightbox', $html);
        $this->assertStringContainsString("title: ui.preview || '预览'", $html);
        $this->assertStringContainsString('不能预览', $html);
        $this->assertStringContainsString('/admin/video/templates', $html);
        $this->assertStringContainsString('/admin/video/settings', $html);
        $this->assertStringContainsString('/admin/video/tools/annex', $html);
        $this->assertStringContainsString('data-kind="image"', $html);
        $this->assertStringNotContainsString('>刷新<', $html);
        $this->assertStringNotContainsString('file-refresh-btn', $html);
        $this->assertStringNotContainsString('暂无数据', $html);
        $this->assertStringNotContainsString('关键字（名称/类型/URL）', $html);
        $this->assertStringNotContainsString('数据初始化', $html);
        $this->assertStringNotContainsString('文件夹模式', $html);
    }

    public function test_list_marks_images_for_preview_and_keeps_other_files_honest(): void
    {
        $now = time();
        SysFileModel::query()->insert([
            [
                'name' => '屏幕截图.png',
                'path' => 'uploads/2026/09/shot.png',
                'url' => '/uploads/2026/09/shot.png',
                'size' => 7516,
                'md5' => '',
                'type' => 1,
                'mime' => 'image/png',
                'create_time' => $now,
                'update_time' => $now,
            ],
            [
                'name' => '说明.pdf',
                'path' => 'uploads/2026/09/doc.pdf',
                'url' => '/uploads/2026/09/doc.pdf',
                'size' => 12000,
                'md5' => '',
                'type' => 0,
                'mime' => 'application/pdf',
                'create_time' => $now - 10,
                'update_time' => $now - 10,
            ],
            [
                'name' => '预告.mp4',
                'path' => 'uploads/2026/09/clip.mp4',
                'url' => '/uploads/2026/09/clip.mp4',
                'size' => 2048000,
                'md5' => '',
                'type' => 2,
                'mime' => 'video/mp4',
                'create_time' => $now - 20,
                'update_time' => $now - 20,
            ],
            [
                'name' => '旧海报.jpg',
                'path' => 'uploads/2026/09/old.jpg',
                'url' => '/uploads/2026/09/old.jpg',
                'size' => 4096,
                'md5' => '',
                'type' => 0,
                'mime' => 'image/jpeg',
                'create_time' => $now - 30,
                'update_time' => $now - 30,
            ],
        ]);

        $svc = app(SysFileService::class);
        $all = $svc->getLists('', 20);
        $this->assertSame(0, $all['code']);
        $rows = $all['data']['data'] ?? [];
        $this->assertCount(4, $rows);

        $byName = [];
        foreach ($rows as $row) {
            $byName[(string) ($row['name'] ?? '')] = $row;
        }

        $png = $byName['屏幕截图.png'] ?? [];
        $this->assertTrue((bool) ($png['is_image'] ?? false));
        $this->assertFalse((bool) ($png['is_video'] ?? true));
        $this->assertSame('image', $png['kind'] ?? '');
        $this->assertSame('图片', $png['kind_label'] ?? '');
        $this->assertSame('/uploads/2026/09/shot.png', $png['preview_url'] ?? '');
        $this->assertStringContainsString('/admin/system/attachments/open?id=', (string) ($png['open_url'] ?? ''));

        $legacy = $byName['旧海报.jpg'] ?? [];
        $this->assertTrue((bool) ($legacy['is_image'] ?? false), 'mime 是图片即使 type 记成 0 也要能预览');
        $this->assertSame('/uploads/2026/09/old.jpg', $legacy['preview_url'] ?? '');

        $pdf = $byName['说明.pdf'] ?? [];
        $this->assertFalse((bool) ($pdf['is_image'] ?? true));
        $this->assertSame('file', $pdf['kind'] ?? '');
        $this->assertSame('', $pdf['preview_url'] ?? 'x');
        $this->assertSame('PDF', $pdf['ext'] ?? '');

        $mp4 = $byName['预告.mp4'] ?? [];
        $this->assertFalse((bool) ($mp4['is_image'] ?? true));
        $this->assertTrue((bool) ($mp4['is_video'] ?? false));
        $this->assertSame('', $mp4['preview_url'] ?? 'x');
        $this->assertSame('视频', $mp4['kind_label'] ?? '');

        $images = $svc->getLists('', 20, 'image');
        $imageNames = array_column($images['data']['data'] ?? [], 'name');
        $this->assertContains('屏幕截图.png', $imageNames);
        $this->assertContains('旧海报.jpg', $imageNames);
        $this->assertNotContains('说明.pdf', $imageNames);
        $this->assertNotContains('预告.mp4', $imageNames);

        $files = $svc->getLists('', 20, 'file');
        $fileNames = array_column($files['data']['data'] ?? [], 'name');
        $this->assertContains('说明.pdf', $fileNames);
        $this->assertNotContains('旧海报.jpg', $fileNames);
        $this->assertNotContains('屏幕截图.png', $fileNames);
        $this->assertNotContains('预告.mp4', $fileNames);

        $json = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/system/attachments/list?keyword=截图')
            ->assertOk()
            ->json();
        $this->assertSame(0, $json['code'] ?? 1);
        $found = $json['data']['data'] ?? [];
        $this->assertCount(1, $found);
        $this->assertTrue((bool) ($found[0]['is_image'] ?? false));
        $this->assertNotSame('', (string) ($found[0]['preview_url'] ?? ''));
    }
}
