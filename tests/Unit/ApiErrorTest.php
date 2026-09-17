<?php

namespace Tests\Unit;

use App\Support\ApiError;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ApiErrorTest extends TestCase
{
    public function test_json_uses_validation_first_message(): void
    {
        $res = ApiError::json(ValidationException::withMessages([
            'email' => ['请填写邮箱'],
        ]));
        $data = $res->getData(true);

        $this->assertSame(422, $res->status());
        $this->assertSame(1, $data['code']);
        $this->assertSame('请填写邮箱', $data['msg']);
        $this->assertSame('请填写邮箱', $data['message']);
        $this->assertFalse($data['ok']);
    }

    public function test_json_uses_exception_message_when_debug(): void
    {
        config(['app.debug' => true]);
        $res = ApiError::json(new \RuntimeException('片库连不上'));
        $data = $res->getData(true);

        $this->assertSame(500, $res->status());
        $this->assertSame('片库连不上', $data['msg']);
    }

    public function test_json_hides_server_detail_when_not_debug(): void
    {
        config(['app.debug' => false]);
        $res = ApiError::json(new \RuntimeException('secret sql'));
        $data = $res->getData(true);

        $this->assertSame('服务器出错了，请稍后再试', $data['msg']);
        $this->assertStringNotContainsString('secret', $data['msg']);
    }

    public function test_expired_page_has_a_readable_prompt(): void
    {
        $res = ApiError::json(new HttpException(419, 'CSRF token mismatch.'));
        $data = $res->getData(true);

        $this->assertSame(419, $res->status());
        $this->assertSame('页面已过期，请刷新后再试', $data['msg']);
    }

    public function test_ajax_and_admin_api_should_render(): void
    {
        $ajax = Request::create('/admin/video/list', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $this->assertTrue(ApiError::shouldRender($ajax));

        $login = Request::create('/api/admin/login', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $this->assertTrue(ApiError::shouldRender($login));
    }
}
