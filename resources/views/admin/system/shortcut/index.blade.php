@extends('admin.layouts.inner')
@section('title', '系统快捷')

@section('header_actions')
    <span class="muted" id="shortcut-updated"></span>
    <button type="button" class="btn btn-muted btn-sm" id="shortcut-refresh-btn">刷新</button>
@endsection

@section('content')
    <p class="hint">常用系统功能入口。</p>
    <div class="tool-grid" id="shortcut-grid"></div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function setUpdated() {
        var d = new Date();
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };
        document.getElementById('shortcut-updated').textContent = '更新于 ' + d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function render(list) {
        var grid = document.getElementById('shortcut-grid');
        grid.innerHTML = '';
        (list || []).forEach(function (it) {
            var a = document.createElement('a');
            a.href = it.url || '#';
            a.textContent = (it.title || '') + (it.desc ? ' · ' + it.desc : '');
            grid.appendChild(a);
        });
    }
    function loadList() {
        U.get('/admin/system/shortcut/list').then(function (res) {
            var list = res && res.data && res.data.data ? res.data.data : [];
            render(Array.isArray(list) ? list : []);
            setUpdated();
        });
    }
    U.on('#shortcut-refresh-btn', 'click', loadList);
    loadList();
})();
</script>
@endpush
