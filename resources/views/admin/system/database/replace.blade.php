@extends('admin.layouts.inner')
@section('title', admin_t('page.db_replace'))

@php
    $targets = $targets ?? [];
    $jsLang = [
        'need_replace_fields' => admin_t('ui.need_replace_fields'),
        'need_replace_from' => admin_t('ui.need_replace_from'),
        'preview_fail' => admin_t('ui.preview_fail'),
        'match_n' => admin_t('ui.match_n'),
        'no_match_text' => admin_t('ui.no_match_text'),
        'need_preview_first' => admin_t('ui.need_preview_first'),
        'replace_confirm' => admin_t('ui.replace_confirm'),
        'replace_fail' => admin_t('ui.replace_fail'),
        'replaced_ok' => admin_t('ui.replaced_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel replace-index db-index">
    <div class="card-header">
        <span>{{ admin_t('page.db_replace') }}</span>
    </div>
    <div class="card-body">
        @include('admin.partials.db-tabs', ['tab' => 'replace'])
        <p class="muted recycle-lead">{{ admin_t('ui.replace_lead') }}</p>

        @if($targets === [])
            <div class="list-empty">
                <p>{{ admin_t('ui.empty_replace') }}</p>
                <p class="muted">{{ admin_t('ui.empty_replace_hint') }}</p>
            </div>
        @else
            <p class="replace-label">{{ admin_t('ui.replace_which') }}</p>
            <div class="queue-chips" id="replace-targets">
                @foreach($targets as $i => $target)
                    <button type="button" class="chip{{ $i === 0 ? ' active' : '' }}" data-target="{{ $target['id'] }}">{{ $target['label'] }}</button>
                @endforeach
            </div>
            <p class="muted field-hint" id="replace-target-hint">{{ $targets[0]['hint'] ?? '' }}</p>

            <p class="replace-label">{{ admin_t('ui.replace_tick') }}</p>
            <div class="replace-fields" id="replace-fields"></div>

            <form id="replace-form" autocomplete="off" onsubmit="return false;">
                <input type="hidden" name="target" id="replace-target" value="{{ $targets[0]['id'] ?? '' }}">
                <label for="replace-from">{{ admin_t('ui.replace_find') }}</label>
                <input type="text" id="replace-from" name="from" placeholder="{{ admin_t('ui.ph_replace_from') }}" maxlength="500">
                <label for="replace-to">{{ admin_t('ui.replace_to') }}</label>
                <input type="text" id="replace-to" name="to" placeholder="{{ admin_t('ui.ph_replace_to') }}" maxlength="500">
                <p class="muted field-hint">{{ admin_t('ui.replace_hint') }}</p>
            </form>

            <div class="replace-preview" id="replace-preview" hidden>
                <p id="replace-preview-text"></p>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-muted" id="replace-preview-btn">{{ admin_t('ui.replace_preview_btn') }}</button>
                <button type="button" class="btn btn-danger" id="replace-run-btn" disabled>{{ admin_t('ui.replace_run') }}</button>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var TARGETS = @json($targets, JSON_UNESCAPED_UNICODE);
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
        if (!p.fields.length) { U.toast(L.need_replace_fields, 'err'); return; }
        if (!p.from) { U.toast(L.need_replace_from, 'err'); return; }
        U.loading(true);
        U.post('/admin/system/database/replace/preview', p).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.preview_fail, 'err'); return; }
            var d = res.data || {};
            previewText.textContent = d.summary || res.msg || '';
            previewBox.hidden = false;
            var n = parseInt(d.matched, 10) || 0;
            stamp = n > 0 ? keyOf(p) : '';
            runBtn.disabled = n < 1;
            U.toast(res.msg || (n ? String(L.match_n || '').replace(':n', String(n)) : L.no_match_text), n ? 'ok' : 'err');
        }).catch(function () {
            U.loading(false);
            U.toast(L.preview_fail, 'err');
        });
    });

    U.on('#replace-run-btn', 'click', function () {
        var p = payload();
        if (stamp === '' || stamp !== keyOf(p)) { U.toast(L.need_preview_first, 'err'); return; }
        if (!U.confirm(L.replace_confirm)) return;
        U.loading(true);
        U.post('/admin/system/database/replace/run', p).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.replace_fail, 'err'); return; }
            U.toast((res && res.msg) || L.replaced_ok, 'ok');
            previewText.textContent = (res.data && res.data.summary) || res.msg || '';
            previewBox.hidden = false;
            stamp = '';
            runBtn.disabled = true;
        }).catch(function () {
            U.loading(false);
            U.toast(L.replace_fail, 'err');
        });
    });

    renderFields();
})();
</script>
@endpush
