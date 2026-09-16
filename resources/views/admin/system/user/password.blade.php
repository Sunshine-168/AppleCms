@extends('admin.layouts.inner')
@section('title', admin_t('page.password'))

@section('content')
    <form id="pwd-form"
          data-need-current="{{ admin_t('page.ph_current') }}"
          data-need-new="{{ admin_t('page.ph_new') }}"
          data-short="{{ admin_t('page.ph_new') }}"
          data-mismatch="{{ admin_t('page.confirm_password') }}"
    >
        <label>{{ admin_t('page.current_password') }}</label>
        <input type="password" name="current_password" placeholder="{{ admin_t('page.ph_current') }}" autocomplete="off">
        <label>{{ admin_t('page.new_password') }}</label>
        <input type="password" name="new_password" placeholder="{{ admin_t('page.ph_new') }}" autocomplete="new-password">
        <label>{{ admin_t('page.confirm_password') }}</label>
        <input type="password" name="confirm_password" placeholder="{{ admin_t('page.ph_confirm') }}" autocomplete="new-password">
        <div class="form-actions">
            <button type="submit" class="btn">{{ admin_t('page.save') }}</button>
            <button type="reset" class="btn btn-muted">{{ admin_t('page.reset') }}</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.getElementById('pwd-form').addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;
    var data = AdminUi.formData(form);
    if (!data.current_password) { AdminUi.toast(form.getAttribute('data-need-current'), 'err'); return; }
    if (!data.new_password) { AdminUi.toast(form.getAttribute('data-need-new'), 'err'); return; }
    if (data.new_password.length < 6) { AdminUi.toast(form.getAttribute('data-short'), 'err'); return; }
    if (data.new_password !== data.confirm_password) { AdminUi.toast(form.getAttribute('data-mismatch'), 'err'); return; }
    AdminUi.post('/admin/set/user/password', data).then(function (res) {
        if (!res || res.code !== 0) { AdminUi.toast((res && res.msg) || form.getAttribute('data-need-new'), 'err'); return; }
        AdminUi.toast('{{ admin_t('page.save') }}', 'ok');
        e.target.reset();
    });
});
</script>
@endpush
