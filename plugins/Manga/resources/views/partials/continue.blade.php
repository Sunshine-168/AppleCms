@once
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
})();
</script>
@endpush
@endonce
