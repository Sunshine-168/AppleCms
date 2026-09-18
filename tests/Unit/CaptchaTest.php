<?php

namespace Tests\Unit;

use App\Support\Captcha;
use Tests\TestCase;

class CaptchaTest extends TestCase
{
    public function test_generate_stores_session_answer_and_check_passes(): void
    {
        $cap = Captcha::generate();

        $this->assertArrayHasKey('question', $cap);
        $this->assertArrayHasKey('a', $cap);
        $this->assertArrayHasKey('b', $cap);
        $this->assertGreaterThanOrEqual(1, $cap['a']);
        $this->assertLessThanOrEqual(9, $cap['a']);

        $answer = session('captcha');
        $this->assertNotNull($answer);
        $this->assertContains($answer, [$cap['a'] + $cap['b'], $cap['a'] - $cap['b']]);
        $this->assertTrue(Captcha::check((string) $answer));
        $this->assertNull(session('captcha'));
    }

    public function test_check_rejects_wrong_answer_and_forgets_session(): void
    {
        Captcha::generate();
        $this->assertNotNull(session('captcha'));
        $this->assertFalse(Captcha::check('999'));
        $this->assertNull(session('captcha'));
    }

    public function test_response_is_image_not_json(): void
    {
        $response = Captcha::response();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $ctype = (string) $response->headers->get('Content-Type');
        $this->assertTrue(
            str_contains($ctype, 'image/png') || str_contains($ctype, 'image/svg+xml'),
            $ctype
        );

        $body = (string) $response->getContent();
        $this->assertNotSame('', $body);
        $this->assertFalse(str_starts_with(ltrim($body), '{'));
        $this->assertStringNotContainsString('"question"', $body);

        if (str_contains($ctype, 'image/svg+xml')) {
            $this->assertStringNotContainsString('<text', $body);
        }
    }

    public function test_svg_fallback_has_no_text_nodes(): void
    {
        $svg = (new \ReflectionMethod(Captcha::class, 'svg'))->invoke(null, '8 - 4 = ?');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringNotContainsString('<text', $svg);
        $this->assertStringNotContainsString('8 - 4 = ?', $svg);
    }
}
