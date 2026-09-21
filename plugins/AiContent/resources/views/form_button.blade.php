<button type="button" class="btn btn-muted" id="ai-blurb-btn">{{ admin_t('ui.ai_blurb_btn') }}</button>
<button type="button" class="btn btn-muted" id="ai-seo-btn">{{ admin_t('ui.ai_seo_btn') }}</button>
<script>
(function () {
    if (typeof AdminUi === 'undefined') return;
    var L = {
        need_title: @json(admin_t('ui.please_fill_title')),
        gen_fail: @json(admin_t('ui.ai_gen_fail')),
        saved_hint: @json(admin_t('ui.ai_filled_save'))
    };
    function ctx() {
        var title = (document.getElementById('video-title') || {}).value || '';
        var desc = document.getElementById('video-desc');
        var idEl = document.querySelector('#video-form input[name=id]');
        var typeEl = document.getElementById('video-type');
        var typeName = '';
        if (typeEl && typeEl.selectedIndex >= 0) typeName = typeEl.options[typeEl.selectedIndex].text || '';
        return {
            id: idEl ? idEl.value : '',
            title: title,
            hint: desc ? desc.value : '',
            type: typeName,
            year: (document.getElementById('video-year') || {}).value || '',
            area: (document.getElementById('video-area') || {}).value || '',
            actors: (document.getElementById('video-actors') || {}).value || ''
        };
    }
    function bind(id, url, fill) {
        var btn = document.getElementById(id);
        if (!btn) return;
        btn.addEventListener('click', function () {
            var data = ctx();
            if (!String(data.title).trim()) { AdminUi.toast(L.need_title, 'err'); return; }
            AdminUi.loading(true);
            AdminUi.post(url, data).then(function (res) {
                AdminUi.loading(false);
                if (!res || res.code !== 0) { AdminUi.toast((res && res.msg) || L.gen_fail, 'err'); return; }
                fill(res.data || {});
                AdminUi.toast((res && res.msg) || L.saved_hint, 'ok');
            }).catch(function () {
                AdminUi.loading(false);
                AdminUi.toast(L.gen_fail, 'err');
            });
        });
    }
    bind('ai-blurb-btn', '/admin/video/ai/generate', function (d) {
        var desc = document.getElementById('video-desc');
        if (desc && d.text) desc.value = d.text;
    });
    bind('ai-seo-btn', '/admin/video/ai/seo', function (d) {
        var box = document.getElementById('video-seo');
        if (box) box.open = true;
        var t = document.getElementById('video-seo-title');
        var k = document.getElementById('video-seo-keywords');
        var s = document.getElementById('video-seo-description');
        if (t && d.title) t.value = d.title;
        if (k && d.keywords) k.value = d.keywords;
        if (s && d.description) s.value = d.description;
    });
})();
</script>
