@extends('admin.layouts.inner')
@section('title', '修改密码')

@section('content')
    <form id="pwd-form">
        <label>当前密码</label>
        <input type="password" name="current_password" placeholder="请输入当前密码" autocomplete="off">
        <label>新密码</label>
        <input type="password" name="new_password" placeholder="请输入新密码" autocomplete="new-password">
        <label>确认新密码</label>
        <input type="password" name="confirm_password" placeholder="请再次输入新密码" autocomplete="new-password">
        <div class="form-actions">
            <button type="submit" class="btn">保存</button>
            <button type="reset" class="btn btn-muted">重置</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.getElementById('pwd-form').addEventListener('submit', function (e) {
    e.preventDefault();
    var data = AdminUi.formData(this);
    if (!data.current_password) { AdminUi.toast('请输入当前密码', 'err'); return; }
    if (!data.new_password) { AdminUi.toast('请输入新密码', 'err'); return; }
    if (data.new_password.length < 6) { AdminUi.toast('新密码至少6位', 'err'); return; }
    if (data.new_password !== data.confirm_password) { AdminUi.toast('两次新密码不一致', 'err'); return; }
    AdminUi.post('/admin/set/user/password', data).then(function (res) {
        if (!res || res.code !== 0) { AdminUi.toast((res && res.msg) || '修改失败', 'err'); return; }
        AdminUi.toast('修改成功', 'ok');
        e.target.reset();
    });
});
</script>
@endpush
