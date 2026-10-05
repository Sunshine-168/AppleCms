<?php

namespace Tests\Unit;

use App\Models\Video\VideoPlayerModel;
use Tests\TestCase;

class VideoPlayerEngineTest extends TestCase
{
    public function test_cloud_html_page_gets_hls_sibling(): void
    {
        $html = 'https://play.hongniu.test/play/abc123';
        $this->assertTrue(VideoPlayerModel::looksLikeHtmlPlayPage($html));
        $this->assertSame(
            'https://play.hongniu.test/play/abc123/index.m3u8',
            VideoPlayerModel::preferMediaUrl($html)
        );
        $this->assertTrue(VideoPlayerModel::isDirectMedia(VideoPlayerModel::preferMediaUrl($html)));
    }

    public function test_m3u8_stays_direct_and_is_not_rewritten_to_iframe(): void
    {
        $url = 'https://cdn.example.com/play/abc123/index.m3u8';
        $this->assertFalse(VideoPlayerModel::looksLikeHtmlPlayPage($url));
        $this->assertSame($url, VideoPlayerModel::preferMediaUrl($url));
        $plan = VideoPlayerModel::playPlan(null, $url, $url);
        $this->assertSame('artplayer', $plan['engine']);
        $this->assertSame($url, $plan['media']);
        $this->assertSame('', $plan['fallback']);
    }

    public function test_direct_hls_is_preferred_over_cloud_html(): void
    {
        $this->assertLessThan(
            VideoPlayerModel::sourcePlayPriority('hnyun', 'https://play.hongniu.test/play/abc123'),
            VideoPlayerModel::sourcePlayPriority('hnm3u8', 'https://cdn.example.com/play/abc123/index.m3u8')
        );
    }

    public function test_yun_html_plan_uses_artplayer_with_iframe_fallback(): void
    {
        $html = 'https://play.hongniu.test/play/abc123';
        $plan = VideoPlayerModel::playPlan(null, $html, $html);
        $this->assertSame('artplayer', $plan['engine']);
        $this->assertSame($html.'/index.m3u8', $plan['media']);
        $this->assertSame($html, $plan['fallback']);
    }
}
