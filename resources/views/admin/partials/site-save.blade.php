@php
    $siteSaveJsLang = [
        'finished' => admin_t('ui.finished'),
        'need_test_mail' => admin_t('ui.need_test_mail'),
    ];
@endphp
@push('scripts')
<script>
(function () {
    var L = @json($siteSaveJsLang, JSON_UNESCAPED_UNICODE);
    var save = document.getElementById('site-save');
    var form = document.getElementById('site-form');
    if (save && form) {
        var doSave = function () {
            AdminUi.post('/admin/video/settings', AdminUi.formData(form)).then(function (res) {
                AdminUi.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
            });
        };
        save.addEventListener('click', doSave);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            doSave();
        });
    }
    var test = document.getElementById('site-test-mail');
    if (test) {
        test.addEventListener('click', function () {
            var to = (document.getElementById('test-mail-to') || {}).value || '';
            to = to.trim();
            if (!to) { AdminUi.toast(L.need_test_mail, 'err'); return; }
            AdminUi.loading(true);
            AdminUi.post('/admin/video/settings/test-mail', {to: to}).then(function (res) {
                AdminUi.loading(false);
                AdminUi.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
            });
        });
    }
})();
</script>
@endpush
