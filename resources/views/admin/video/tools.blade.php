@php
    $titles = [
        'annex' => admin_t('page.tool_annex'),
        'recycle' => admin_t('page.tool_recycle'),
    ];
    $jsLang = [
        'finished' => admin_t('ui.finished'),
        'confirm_del_unref' => admin_t('ui.confirm_del_unref'),
    ];
@endphp
@extends('admin.layouts.inner')
@section('title', $titles[$tool] ?? admin_t('page.tools'))

@section('content')
    @if($tool === 'annex')
        <p class="hint">{!! admin_t('ui.annex_hint') !!}</p>
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="btn-scan">{{ admin_t('ui.scan_btn') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-del">{{ admin_t('ui.del_unref') }}</button>
        </div>
        <pre id="out" class="out"></pre>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var tool = @json($tool, JSON_UNESCAPED_UNICODE);
    function post(action, extra, cb) {
        U.loading(true);
        U.post('/admin/video/tools/' + tool + '/run', Object.assign({action: action}, extra || {})).then(function (res) {
            U.loading(false);
            if (cb) { cb(res); return; }
            var out = document.getElementById('out');
            if (out) out.textContent = JSON.stringify((res && res.data) || res, null, 2);
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
        });
    }
    U.on('#btn-scan', 'click', function () { post('scan'); });
    U.on('#btn-del', 'click', function () {
        if (!U.confirm(L.confirm_del_unref)) return;
        post('delete');
    });
})();
</script>
@endpush
