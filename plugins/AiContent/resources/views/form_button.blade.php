<button type="button" class="btn btn-muted" id="ai-blurb-btn">用 AI 写简介</button>
<script>
(function () {
    var btn = document.getElementById('ai-blurb-btn');
    if (!btn || typeof AdminUi === 'undefined') return;
    btn.addEventListener('click', function () {
        var title = (document.getElementById('video-title') || {}).value || '';
        var desc = document.getElementById('video-desc');
        var idEl = document.querySelector('#video-form input[name=id]');
        var id = idEl ? idEl.value : '';
        if (!String(title).trim()) { AdminUi.toast('请先填标题', 'err'); return; }
        AdminUi.loading(true);
        AdminUi.post('/admin/video/ai/generate', {
            id: id,
            title: title,
            hint: desc ? desc.value : ''
        }).then(function (res) {
            AdminUi.loading(false);
            if (!res || res.code !== 0) { AdminUi.toast((res && res.msg) || '生成失败', 'err'); return; }
            if (desc && res.data && res.data.text) desc.value = res.data.text;
            AdminUi.toast((res && res.msg) || '已生成，请再保存', 'ok');
        }).catch(function () {
            AdminUi.loading(false);
            AdminUi.toast('生成失败', 'err');
        });
    });
})();
</script>
