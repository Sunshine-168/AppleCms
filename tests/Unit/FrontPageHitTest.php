<?php

namespace Tests\Unit;

use App\Support\FrontPageHit;
use Illuminate\Http\Request;
use Tests\TestCase;

class FrontPageHitTest extends TestCase
{
    public function test_front_pages_count_assets_and_admin_do_not(): void
    {
        $this->assertTrue(FrontPageHit::isDocumentGet(Request::create('/')));
        $this->assertTrue(FrontPageHit::isDocumentGet(Request::create('/type/4')));
        $this->assertTrue(FrontPageHit::isDocumentGet(Request::create('/vod/1')));
        $this->assertTrue(FrontPageHit::isDocumentGet(Request::create('/sitemap.xml')));

        $this->assertFalse(FrontPageHit::isDocumentGet(Request::create('/plugin-assets/code-editor/vendor/search.js')));
        $this->assertFalse(FrontPageHit::isDocumentGet(Request::create('/admin/video/accesslogs')));
        $this->assertFalse(FrontPageHit::isDocumentGet(Request::create('/css/app.css')));
        $this->assertFalse(FrontPageHit::isDocumentGet(Request::create('/js/boot.js')));
        $this->assertFalse(FrontPageHit::isDocumentGet(Request::create('/robots.txt')));
        $this->assertFalse(FrontPageHit::isDocumentGet(Request::create('/vod/1', 'POST')));
    }
}
