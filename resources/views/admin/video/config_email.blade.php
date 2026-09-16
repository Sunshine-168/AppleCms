@extends('admin.layouts.inner')
@section('title', '邮件设置')

@section('content')
    <form id="site-form">
        <label>SMTP 主机</label>
        <input type="text" name="smtp_host" value="{{ $site['smtp_host'] ?? '' }}">
        <label>SMTP 端口</label>
        <input type="number" name="smtp_port" value="{{ $site['smtp_port'] ?? 465 }}">
        <label>SMTP 账号</label>
        <input type="text" name="smtp_user" value="{{ $site['smtp_user'] ?? '' }}">
        <label>SMTP 密码</label>
        <input type="password" name="smtp_pass" value="{{ $site['smtp_pass'] ?? '' }}">
        <label>发件人</label>
        <input type="text" name="smtp_from" value="{{ $site['smtp_from'] ?? '' }}" placeholder="noreply@example.com">
        <label>测试邮箱</label>
        <div class="field-inline">
            <input type="email" id="test-mail-to" placeholder="收件邮箱">
            <button type="button" class="btn btn-muted" id="site-test-mail">发送测试邮件</button>
        </div>
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">保存</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
