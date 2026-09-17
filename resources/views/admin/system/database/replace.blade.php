@extends('admin.layouts.inner')
@section('title', admin_t('page.db_replace'))

@php
    $targets = $targets ?? [];
@endphp

@section('plain')
<div class="card card-panel replace-index">
    <div class="card-header">
        <span>批量替换</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/system/database/backup">备份</a>
            <a class="btn btn-muted btn-sm" href="/admin/system/database/sql">执行 SQL</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">把片库里一段文字换成另一段。换域名、改错字。改完不能撤销，先「<a href="/admin/system/database/backup">备份</a>」。管理员、会员这些系统表不在这里。更复杂的条件去「<a href="/admin/system/database/sql">执行 SQL</a>」。</p>

        @if($targets === [])
            <div class="list-empty">
                <p>还没有可替换的片库表。</p>
                <p class="muted">装好影片、文章或分类之后再来。</p>
            </div>
        @else
            <p class="replace-label">改哪一类</p>
            <div class="queue-chips" id="replace-targets">
                @foreach($targets as $i => $target)
                    <button type="button" class="chip{{ $i === 0 ? ' active' : '' }}" data-target="{{ $target['id'] }}">{{ $target['label'] }}</button>
                @endforeach
            </div>
            <p class="muted field-hint" id="replace-target-hint">{{ $targets[0]['hint'] ?? '' }}</p>

            <p class="replace-label">勾要改的项</p>
            <div class="replace-fields" id="replace-fields"></div>

            <form id="replace-form" autocomplete="off" onsubmit="return false;">
                <input type="hidden" name="target" id="replace-target" value="{{ $targets[0]['id'] ?? '' }}">
                <label for="replace-from">找这段</label>
                <input type="text" id="replace-from" name="from" placeholder="例如旧域名或错别字" maxlength="500">
                <label for="replace-to">换成</label>
                <input type="text" id="replace-to" name="to" placeholder="可留空，等于删掉这段字" maxlength="500">
                <p class="muted field-hint">只改含这段文字的行。新旧一样不会动库。</p>
            </form>

            <div class="replace-preview" id="replace-preview" hidden>
                <p id="replace-preview-text"></p>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-muted" id="replace-preview-btn">看看会改几条</button>
                <button type="button" class="btn btn-danger" id="replace-run-btn" disabled>确认替换</button>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var TARGETS = @json($targets);
    var form = document.getElementById('replace-form');
    if (!form) return;
    var previewBox = document.getElementById('replace-preview');
    var previewText = document.getElementById('replace-preview-text');
    var runBtn = document.getElementById('replace-run-btn');
    var hint = document.getElementById('replace-target-hint');
    var stamp = '';

    function currentTarget() {
        var id = document.getElementById('replace-target').value;
        for (var i = 0; i < TARGETS.length; i++) if (TARGETS[i].id === id) return TARGETS[i];
        return TARGETS[0] || null;
    }
    function selectedFields() {
        return U.qa('#replace-fields input:checked').map(function (el) { return el.value; });
    }
    function invalidate() {
        stamp = '';
        runBtn.disabled = true;
        if (previewBox) previewBox.hidden = true;
    }
    function renderFields() {
        var t = currentTarget();
        var wrap = document.getElementById('replace-fields');
        if (!t || !wrap) return;
        var html = '';
        (t.fields || []).forEach(function (f, i) {
            html += '<label class="inline"><input type="checkbox" name="fields[]" value="' + U.escape(f.key) + '"' + (i < 2 ? ' checked' : '') + '> ' + U.escape(f.label) + '</label>';
        });
        wrap.innerHTML = html;
        if (hint) hint.textContent = t.hint || '';
        invalidate();
    }
    function payload() {
        return {
            target: document.getElementById('replace-target').value,
            fields: selectedFields(),
            from: document.getElementById('replace-from').value,
            to: document.getElementById('replace-to').value
        };
    }
    function keyOf(p) {
        return [p.target, (p.fields || []).join(','), p.from, p.to].join('\n');
    }

    U.on('#replace-targets', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        document.getElementById('replace-target').value = chip.getAttribute('data-target') || '';
        U.qa('#replace-targets .chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
        renderFields();
    });
    U.on('#replace-fields', 'change', invalidate);
    U.on('#replace-from', 'input', invalidate);
    U.on('#replace-to', 'input', invalidate);

    U.on('#replace-preview-btn', 'click', function () {
        var p = payload();
        if (!p.fields.length) { U.toast('请勾要改的项', 'err'); return; }
        if (!p.from) { U.toast('请填写要找的文字', 'err'); return; }
        U.loading(true);
        U.post('/admin/system/database/replace/preview', p).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能预览', 'err'); return; }
            var d = res.data || {};
            previewText.textContent = d.summary || res.msg || '';
            previewBox.hidden = false;
            var n = parseInt(d.matched, 10) || 0;
            stamp = n > 0 ? keyOf(p) : '';
            runBtn.disabled = n < 1;
            U.toast(res.msg || (n ? '约 ' + n + ' 条' : '没有匹配'), n ? 'ok' : 'err');
        }).catch(function () {
            U.loading(false);
            U.toast('没能预览', 'err');
        });
    });

    U.on('#replace-run-btn', 'click', function () {
        var p = payload();
        if (stamp === '' || stamp !== keyOf(p)) { U.toast('请先看看会改几条', 'err'); return; }
        if (!U.confirm('将按预览替换，改完不能撤销。确定？')) return;
        U.loading(true);
        U.post('/admin/system/database/replace/run', p).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能替换', 'err'); return; }
            U.toast((res && res.msg) || '已替换', 'ok');
            previewText.textContent = (res.data && res.data.summary) || res.msg || '';
            previewBox.hidden = false;
            stamp = '';
            runBtn.disabled = true;
        }).catch(function () {
            U.loading(false);
            U.toast('没能替换', 'err');
        });
    });

    renderFields();
})();
</script>
@endpush
