@extends('admin.layouts.inner')
@section('title', '模板编辑')

@section('plain')
<div class="split-side">
    <div class="card card-panel">
        <div class="card-header"><span>主题文件</span></div>
        <div class="card-body">
            <div class="file-list">
                @foreach($files ?? [] as $f)
                    <a href="#" class="tpl-file" data-path="{{ $f['path'] }}">{{ $f['name'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card card-panel">
        <div class="card-header">
            <span>编辑 <span id="tpl-path"></span></span>
            <div>
                <button type="button" class="btn btn-muted btn-sm" id="tpl-rollback">回滚</button>
                <button type="button" class="btn btn-muted btn-sm" id="tpl-backup">备份</button>
                <button type="button" class="btn btn-sm" id="tpl-save">保存</button>
            </div>
        </div>
        <div class="card-body">
            <textarea id="tpl-content" class="tpl-editor"></textarea>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var current = '';
    document.querySelectorAll('.tpl-file').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.tpl-file').forEach(function (x) { x.classList.remove('active'); });
            a.classList.add('active');
            current = a.getAttribute('data-path');
            document.getElementById('tpl-path').textContent = current;
            U.get('/admin/video/templates/read', {path: current}).then(function (res) {
                if (res && res.code === 0) document.getElementById('tpl-content').value = (res.data && res.data.content) || '';
                else U.toast((res && res.msg) || '读取失败', 'err');
            });
        });
    });
    U.on('#tpl-save', 'click', function () {
        if (!current) { U.toast('请选择文件', 'err'); return; }
        if (!U.confirm('确认保存并覆盖主题文件？保存前会自动备份。')) return;
        U.post('/admin/video/templates/save', {path: current, content: document.getElementById('tpl-content').value}).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
    U.on('#tpl-backup', 'click', function () {
        if (!current) { U.toast('请选择文件', 'err'); return; }
        U.post('/admin/video/templates/backup', {path: current}).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
    U.on('#tpl-rollback', 'click', function () {
        if (!current) { U.toast('请选择文件', 'err'); return; }
        if (!U.confirm('回滚到最近一次备份？')) return;
        U.post('/admin/video/templates/rollback', {path: current}).then(function (res) {
            if (res && res.code === 0) {
                U.get('/admin/video/templates/read', {path: current}).then(function (r) {
                    if (r && r.code === 0) document.getElementById('tpl-content').value = (r.data && r.data.content) || '';
                });
            }
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
})();
</script>
@endpush
