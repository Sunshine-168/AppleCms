@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑审核规则' : '新增审核规则')

@php
    $rule = is_array($rule ?? null) ? $rule : [];
    $isEdit = (bool) ($isEdit ?? false);
    $scopes = $scopes ?? ['title' => '标题', 'content' => '简介', 'actor' => '演员'];
    $actions = $actions ?? ['skip' => '跳过不入库', 'review' => '入库并下架', 'replace' => '抠词后再入库'];
    $name = (string) ($rule['name'] ?? '');
    $scope = (string) ($rule['scope'] ?? 'title');
    $action = (string) ($rule['action'] ?? 'skip');
    $words = (string) ($rule['words'] ?? '');
    $isRegex = (int) ($rule['is_regex'] ?? 0) === 1;
    $status = (string) ($rule['status'] ?? '1');
    $sort = (int) ($rule['sort'] ?? 0);
    $title = $isEdit ? '编辑审核规则' : '新增审核规则';
@endphp

@section('plain')
<div class="card card-panel audit-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/audits">返回审核规则</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">采集进库时会拿这些词去扫。命中第一条规则就停：可以整部跳过、先下架，或把词抠掉再入库。</p>

        <form class="audit-form" id="audit-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($rule['id'] ?? 0) : '' }}">

            <h3>拦什么</h3>
            <label for="audit-name">名称</label>
            <input id="audit-name" type="text" name="name" value="{{ $name }}" placeholder="可空，默认用第一个词" maxlength="80">
            <label for="audit-scope">看哪里</label>
            <select id="audit-scope" name="scope">
                @foreach($scopes as $val => $lab)
                    <option value="{{ $val }}" @selected($scope === $val)>{{ $lab }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">标题最常用。简介拦广告词，演员拦不想收录的名字。</p>
            <label for="audit-words">关键词</label>
            <textarea id="audit-words" name="words" placeholder="一行一个，也可用逗号分开">{{ $words }}</textarea>
            <p class="muted field-hint">普通匹配不分大小写。勾了正则才按表达式，写错会导致这条不命中。</p>
            <input type="hidden" name="is_regex" value="0">
            <label class="inline">
                <input type="checkbox" name="is_regex" value="1" @checked($isRegex)>
                按正则匹配
            </label>

            <h3>命中之后</h3>
            <label for="audit-action">动作</label>
            <select id="audit-action" name="action">
                @foreach($actions as $val => $lab)
                    <option value="{{ $val }}" @selected($action === $val)>{{ $lab }}</option>
                @endforeach
            </select>
            <p class="muted field-hint" id="audit-action-hint"></p>

            <h3>试一句</h3>
            <label for="audit-sample">样例</label>
            <input id="audit-sample" type="text" name="sample" placeholder="填一句片名或简介，看看会不会命中" autocomplete="off">
            <p class="muted field-hint">只试当前表单，不用先保存。</p>
            <button type="button" class="btn btn-muted btn-sm" id="audit-try-btn">试一下</button>
            <p class="muted" id="audit-try-msg" hidden></p>

            <h3>状态</h3>
            <label for="audit-sort">排序</label>
            <input id="audit-sort" type="number" name="sort" value="{{ $sort }}" min="0">
            <p class="muted field-hint">数字越大越先匹配。</p>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                启用，采集时生效
            </label>
            <p class="muted field-hint">关掉后还留着，只是采集不再用它。</p>

            <div class="form-actions">
                <button type="submit" class="btn" id="audit-save">保存</button>
                <a class="btn btn-muted" href="/admin/video/audits">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('audit-form');
    var action = document.getElementById('audit-action');
    var hint = document.getElementById('audit-action-hint');
    var tryBtn = document.getElementById('audit-try-btn');
    var tryMsg = document.getElementById('audit-try-msg');
    var hints = {
        skip: '整部跳过，采集日志里会写审核拦截。',
        review: '会进库，但状态是下架，要到影片列表里再上架。',
        replace: '把命中的普通词从标题和简介里删掉再入库。正则不会替换，只判断命中。'
    };
    function syncHint() {
        hint.textContent = hints[action.value] || '';
    }
    action.addEventListener('change', syncHint);
    syncHint();

    tryBtn.addEventListener('click', function () {
        var data = U.formData(form);
        if (!String(data.words || '').trim()) {
            U.toast('请先填写关键词', 'err');
            document.getElementById('audit-words').focus();
            return;
        }
        if (!String(data.sample || '').trim()) {
            U.toast('请填一句试试', 'err');
            document.getElementById('audit-sample').focus();
            return;
        }
        U.post('/admin/video/audits/try', data).then(function (res) {
            tryMsg.hidden = false;
            tryMsg.textContent = (res && res.msg) || '没有结果';
            U.toast((res && res.msg) || '没有结果', res && res.code === 0 ? 'ok' : 'err');
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.words || '').trim()) {
            U.toast('请填写关键词', 'err');
            document.getElementById('audit-words').focus();
            return;
        }
        if (!String(form.id.value || '').trim()) delete data.id;
        delete data.sample;
        U.loading(true);
        U.post('/admin/video/audits/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            U.toast('已保存', 'ok');
            location.href = '/admin/video/audits';
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
