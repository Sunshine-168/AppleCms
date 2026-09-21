<button type="button" class="btn btn-muted btn-sm" id="video-batch-ai-seo">{{ admin_t('ui.ai_seo_batch') }}</button>
<script>
(function () {
    if (typeof AdminUi === 'undefined') return;
    var L = {
        please_select: @json(admin_t('ui.please_select_videos')),
        confirm: @json(admin_t('ui.confirm_ai_seo_batch')),
        fail: @json(admin_t('ui.ai_gen_fail')),
        ok: @json(admin_t('manga.op_ok'))
    };
    var btn = document.getElementById('video-batch-ai-seo');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var table = window.videoIndexTable;
        var ids = table && table.selectedIds ? table.selectedIds() : [];
        if (!ids.length) { AdminUi.toast(L.please_select, 'err'); return; }
        if (!AdminUi.confirm(L.confirm)) return;
        AdminUi.loading(true);
        AdminUi.post('/admin/video/ai/seo/batch', {ids: ids.join(','), overwrite: 0}).then(function (res) {
            AdminUi.loading(false);
            if (!res || res.code !== 0) { AdminUi.toast((res && res.msg) || L.fail, 'err'); return; }
            if (table && table.refresh) table.refresh();
            AdminUi.toast((res && res.msg) || L.ok, 'ok');
        }).catch(function () {
            AdminUi.loading(false);
            AdminUi.toast(L.fail, 'err');
        });
    });
})();
</script>
