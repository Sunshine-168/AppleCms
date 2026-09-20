@extends('admin.layouts.inner')
@section('title', admin_t('page.config_email'))

@section('content')
    <form class="admin-form" id="site-form">
        <label>{{ admin_t('ui.smtp_host_label') }}</label>
        <input type="text" name="smtp_host" value="{{ $site['smtp_host'] ?? '' }}">
        <label>{{ admin_t('ui.smtp_port') }}</label>
        <input type="number" name="smtp_port" value="{{ $site['smtp_port'] ?? 465 }}">
        <label>{{ admin_t('ui.smtp_user') }}</label>
        <input type="text" name="smtp_user" value="{{ $site['smtp_user'] ?? '' }}">
        <label>{{ admin_t('ui.smtp_pass') }}</label>
        <input type="password" name="smtp_pass" value="{{ $site['smtp_pass'] ?? '' }}">
        <label>{{ admin_t('ui.mail_from') }}</label>
        <input type="text" name="smtp_from" value="{{ $site['smtp_from'] ?? '' }}" placeholder="noreply@example.com">
        <label>{{ admin_t('ui.test_email') }}</label>
        <div class="field-inline">
            <input type="email" id="test-mail-to" placeholder="{{ admin_t('ui.ph_mail_to') }}">
            <button type="button" class="btn btn-muted" id="site-test-mail">{{ admin_t('ui.send_test_mail') }}</button>
        </div>
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
