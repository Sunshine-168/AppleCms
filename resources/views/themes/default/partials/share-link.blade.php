<button type="button" id="share-link-btn" data-copy="{{ url()->current() }}" data-share="{{ url('/vod/'.$video->id.'/share') }}" data-auth="{{ auth('member')->check() ? '1' : '0' }}">复制链接</button>
<script>
(function () {
    var btn = document.getElementById('share-link-btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-copy') || location.href;
        var authed = btn.getAttribute('data-auth') === '1';
        var share = btn.getAttribute('data-share') || '';
        var copied = function () {
            if (!authed) {
                vodToast('已复制链接', 'ok');
                return;
            }
            fetch(share, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (r) { return r.json().catch(function () { return null; }); }).then(function (res) {
                vodResult(res, '已复制链接');
            }).catch(function () { vodToast('已复制链接', 'ok'); });
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(copied).catch(function () {
                window.prompt('复制链接', url);
                copied();
            });
        } else {
            window.prompt('复制链接', url);
            copied();
        }
    });
})();
</script>
