@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var uploadUrl = '/admin/plugins/upload';
    var noneText = @json(admin_t('plugin.upload_none'));
    var maxBytes = {{ \App\Support\Plugins\PluginInstaller::MAX_ZIP_BYTES }};

    function uninstallUrl(id) {
        return '/admin/plugins/' + encodeURIComponent(id) + '/uninstall';
    }

    function sendZip(file, replace) {
        var fd = new FormData();
        fd.append('file', file);
        if (replace) fd.append('replace', '1');
        return U.post(uploadUrl, fd);
    }

    U.on('#plugin-upload-btn', 'click', function () {
        var tpl = document.getElementById('plugin-upload-tpl');
        if (!tpl) return;
        U.dialog({
            title: @json(admin_t('plugin.upload')),
            okText: @json(admin_t('plugin.upload')),
            content: tpl.innerHTML,
            onOpen: function (body) {
                var input = body.querySelector('input[type=file]');
                var nameEl = body.querySelector('.js-plugin-zip-name');
                var pick = body.querySelector('.js-plugin-pick');
                if (pick && input) {
                    pick.addEventListener('click', function () { input.click(); });
                }
                if (input) {
                    input.addEventListener('change', function () {
                        var file = input.files && input.files[0] ? input.files[0] : null;
                        body._file = file;
                        if (nameEl) nameEl.textContent = file ? file.name : noneText;
                    });
                }
            },
            onSave: function (body) {
                if (body._busy) return false;
                var file = body._file;
                if (!file) { U.toast(@json(admin_t('plugin.err_file')), 'err'); return false; }
                if (!/\.zip$/i.test(file.name || '')) { U.toast(@json(admin_t('plugin.err_zip_only')), 'err'); return false; }
                if (file.size > maxBytes) { U.toast(@json(admin_t('plugin.err_zip_size')), 'err'); return false; }
                body._busy = true;
                U.loading(true);
                return sendZip(file, false).then(function (res) {
                    if (res && res.data && res.data.replaceable) {
                        if (!U.confirm(res.msg)) return false;
                        return sendZip(file, true);
                    }
                    return res;
                }).then(function (res) {
                    if (!res) return false;
                    if (res.code === 0) {
                        U.toast(res.msg || '', 'ok');
                        location.reload();
                        return;
                    }
                    U.toast((res && res.msg) || @json(admin_t('plugin.err_write')), 'err');
                    return false;
                }).finally(function () {
                    body._busy = false;
                    U.loading(false);
                });
            }
        });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-plugin-uninstall');
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        if (!id) return;
        if (!U.confirm(@json(admin_t('plugin.uninstall_confirm')))) return;
        if (btn.disabled) return;
        btn.disabled = true;
        U.post(uninstallUrl(id), {}).then(function (res) {
            if (!res || res.code !== 0) {
                btn.disabled = false;
                U.toast((res && res.msg) || @json(admin_t('plugin.err_write')), 'err');
                return;
            }
            U.toast(res.msg || '', 'ok');
            location.reload();
        });
    });
})();
</script>
@endpush
