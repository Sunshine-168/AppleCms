@push('scripts')
<script>
(function () {
    var save = document.getElementById('site-save');
    var form = document.getElementById('site-form');
    if (save && form) {
        save.addEventListener('click', function () {
            AdminUi.post('/admin/video/settings', AdminUi.formData(form)).then(function (res) {
                AdminUi.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            });
        });
    }
    var test = document.getElementById('site-test-mail');
    if (test) {
        test.addEventListener('click', function () {
            var to = (document.getElementById('test-mail-to') || {}).value || '';
            to = to.trim();
            if (!to) { AdminUi.toast('请填写测试邮箱', 'err'); return; }
            AdminUi.loading(true);
            AdminUi.post('/admin/video/settings/test-mail', {to: to}).then(function (res) {
                AdminUi.loading(false);
                AdminUi.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            });
        });
    }
})();
</script>
@endpush
