@php
    $titles = [
        'annex' => admin_t('page.tool_annex'),
        'recycle' => admin_t('page.tool_recycle'),
    ];
@endphp
@extends('admin.layouts.inner')
@section('title', $titles[$tool] ?? admin_t('page.tools'))

@section('content')
    @if($tool === 'annex')
        <p class="hint">对照影片/文章/演员封面，找出 <code>/uploads/vod/</code> 里未被引用的文件。</p>
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="btn-scan">扫描</button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-del">删除未引用</button>
        </div>
        <pre id="out" class="out"></pre>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var tool = @json($tool);
    function post(action, extra, cb) {
        U.loading(true);
        U.post('/admin/video/tools/' + tool + '/run', Object.assign({action: action}, extra || {})).then(function (res) {
            U.loading(false);
            if (cb) { cb(res); return; }
            var out = document.getElementById('out');
            if (out) out.textContent = JSON.stringify((res && res.data) || res, null, 2);
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    }
    U.on('#btn-scan', 'click', function () { post('scan'); });
    U.on('#btn-del', 'click', function () {
        if (!U.confirm('确认删除未引用文件？')) return;
        post('delete');
    });
})();
</script>
@endpush
