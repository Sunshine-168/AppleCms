@once
<div class="manga-continue-bar" id="manga-continue-bar" hidden>
    <div class="manga-continue-inner">
        <span class="muted">上次看到</span>
        <a href="#" id="manga-continue-link">继续阅读</a>
        <button type="button" class="btn-link" id="manga-continue-dismiss" aria-label="关闭">关闭</button>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var key = 'manga_history';
    var map = {};
    try { map = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (e) { map = {}; }
    document.querySelectorAll('[data-manga-id]').forEach(function (card) {
        var id = String(card.getAttribute('data-manga-id') || '');
        var row = map[id];
        var link = card.querySelector('.js-continue');
        if (!link || !row || !row.chapter) return;
        link.href = '/manga/' + encodeURIComponent(id) + '/' + encodeURIComponent(row.chapter);
        link.hidden = false;
        if (row.name) link.textContent = '继续 ' + row.name;
    });

    var bar = document.getElementById('manga-continue-bar');
    var barLink = document.getElementById('manga-continue-link');
    var dismiss = document.getElementById('manga-continue-dismiss');
    if (!bar || !barLink) return;
    try {
        if (sessionStorage.getItem('manga_continue_hide') === '1') return;
    } catch (e) {}
    var latest = null;
    Object.keys(map).forEach(function (id) {
        var row = map[id] || {};
        if (!row.chapter) return;
        if (!latest || Number(row.at || 0) > Number(latest.at || 0)) {
            latest = { id: id, chapter: row.chapter, title: row.title || ('漫画 #' + id), name: row.name || '', at: row.at || 0 };
        }
    });
    if (!latest) return;
    if (!location.pathname.match(/^\/manga\/?$/)) return;
    barLink.href = '/manga/' + encodeURIComponent(latest.id) + '/' + encodeURIComponent(latest.chapter);
    barLink.textContent = (latest.title || '继续阅读') + (latest.name ? (' · ' + latest.name) : '');
    bar.hidden = false;
    if (dismiss) {
        dismiss.addEventListener('click', function () {
            bar.hidden = true;
            try { sessionStorage.setItem('manga_continue_hide', '1'); } catch (e) {}
        });
    }
})();
</script>
@endpush
@endonce
