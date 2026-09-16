@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑资源' : '新增资源')

@php
    $union = is_array($union ?? null) ? $union : [];
    $isEdit = (bool) ($isEdit ?? false);
    $name = (string) ($union['name'] ?? '');
    $apiUrl = (string) ($union['api_url'] ?? '');
    $note = (string) ($union['note'] ?? '');
    $status = (string) ($union['status'] ?? '1');
    $adopted = (int) ($union['adopted'] ?? 0) === 1;
    $title = $isEdit ? '编辑资源' : '新增资源';
@endphp

@section('plain')
<div class="card card-panel union-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/unions">返回推荐资源</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($adopted)
                这个接口已经在采集源里了。这里改名称和说明只影响收藏，不会改采集源。
            @else
                这里只记接口，不会采片。保存后可以点「保存并接入采集源」，才会出现在采集源列表。
            @endif
        </p>

        <form class="union-form" id="union-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($union['id'] ?? 0) : '' }}">

            <h3>这个资源站</h3>
            <label for="union-name">名称</label>
            <input id="union-name" type="text" name="name" value="{{ $name }}" placeholder="如 某某资源" required>
            <label for="union-url">接口地址</label>
            <input id="union-url" type="text" name="api_url" value="{{ $apiUrl }}" placeholder="https://xxx/api.php/provide/vod/" required>
            <p class="muted field-hint">苹果 CMS 兼容接口。没写协议会自动加上 https://。</p>
            <label for="union-note">说明</label>
            <input id="union-note" type="text" name="note" value="{{ $note }}" placeholder="可空，给自己看的备注">

            <h3>显示</h3>
            <label for="union-sort">排序</label>
            <input id="union-sort" type="number" name="sort" value="{{ $union['sort'] ?? 0 }}">
            <p class="muted field-hint">数字越大越靠前。</p>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                在列表里显示
            </label>
            <p class="muted field-hint">关掉后只是藏在收藏里，采集源不受影响。</p>

            <div class="form-actions">
                <button type="submit" class="btn" id="union-save">保存</button>
                @if(! $adopted)
                    <button type="button" class="btn btn-muted" id="union-save-adopt">保存并接入采集源</button>
                @endif
                <a class="btn btn-muted" href="/admin/video/unions">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('union-form');
    var isEdit = !!String(form.id.value || '').trim();
    var adoptBtn = document.getElementById('union-save-adopt');

    function save(goAdopt) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast('请填写名称', 'err');
            document.getElementById('union-name').focus();
            return;
        }
        if (!String(data.api_url || '').trim()) {
            U.toast('请填写接口地址', 'err');
            document.getElementById('union-url').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/unions/save', data).then(function (res) {
            if (!res || res.code !== 0) {
                U.loading(false);
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            if (goAdopt && id) {
                return U.post('/admin/video/unions/adopt', {id: id}).then(function (adoptRes) {
                    U.loading(false);
                    if (!adoptRes || adoptRes.code !== 0) {
                        U.toast((adoptRes && adoptRes.msg) || '已保存，但接入失败', 'err');
                        location.href = '/admin/video/unions';
                        return;
                    }
                    U.toast((adoptRes && adoptRes.msg) || '已接入采集源', 'ok');
                    location.href = '/admin/video/collects';
                });
            }
            U.loading(false);
            U.toast('已保存', 'ok');
            location.href = '/admin/video/unions';
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save(false);
    });
    if (adoptBtn) adoptBtn.addEventListener('click', function () { save(true); });
})();
</script>
@endpush
