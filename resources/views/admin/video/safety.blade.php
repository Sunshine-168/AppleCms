@extends('admin.layouts.inner')
@section('title', '挂马扫描')

@section('content')
    <p class="hint">扫描 <code>app/</code> 与 <code>public/</code> 下 PHP 文件中的危险函数调用，只报告文件与行号。</p>
    <div class="toolbar">
        <button type="button" class="btn btn-danger" id="safety-scan">开始扫描</button>
    </div>
    <pre id="safety-result" class="out"></pre>
@endsection

@push('scripts')
<script>
AdminUi.on('#safety-scan', 'click', function () {
    AdminUi.loading(true);
    AdminUi.post('/admin/video/safety/scan', {}).then(function (res) {
        AdminUi.loading(false);
        var rows = (res && res.data && res.data.hits) ? res.data.hits : [];
        document.getElementById('safety-result').textContent = rows.length ? rows.join('\n') : ((res && res.msg) || '未发现可疑调用');
        AdminUi.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
    });
});
</script>
@endpush
