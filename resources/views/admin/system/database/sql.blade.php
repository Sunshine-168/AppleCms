@extends('admin.layouts.inner')
@section('title', admin_t('page.db_sql'))

@php
    $ui = $ui ?? [];
    $examples = $examples ?? [];
@endphp

@section('plain')
<div class="card card-panel sql-index db-index" id="sql-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '' }}</span>
    </div>
    <div class="card-body">
        @include('admin.partials.db-tabs', ['tab' => 'sql'])
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}</p>
        <p class="sql-note">{{ $ui['note'] ?? '' }}</p>

        <section class="cache-block">
            <div class="cache-block-head">
                <h3>{{ $ui['now'] ?? '' }}</h3>
                <span class="badge">{{ $driver_label ?? '' }}</span>
            </div>
            <p class="muted field-hint">{{ $driver_hint ?? '' }}</p>
            @if(empty($can_delete))
                <p class="muted field-hint">{{ admin_t('ui.sql_no_delete') }}</p>
            @endif
        </section>

        @if($examples !== [])
            <p class="replace-label">{{ $ui['examples'] ?? '' }}</p>
            <div class="queue-chips" id="sql-examples">
                @foreach($examples as $ex)
                    <button type="button" class="chip js-sql-ex" data-sql="{{ $ex['sql'] }}" data-hint="{{ $ex['hint'] ?? '' }}">{{ $ex['label'] }}</button>
                @endforeach
            </div>
            <p class="muted field-hint" id="sql-ex-hint">{{ $examples[0]['hint'] ?? '' }}</p>
        @endif

        <label for="sql-input">{{ $ui['stmt'] ?? '' }}</label>
        <textarea id="sql-input" rows="8" placeholder="{{ $ui['placeholder'] ?? '' }}" spellcheck="false"></textarea>
        <div class="form-actions">
            <button type="button" class="btn" id="sql-run">{{ $ui['run'] ?? '' }}</button>
            <button type="button" class="btn btn-muted" id="sql-clear">{{ $ui['clear'] ?? '' }}</button>
        </div>

        <section class="sql-result" id="sql-result">
            <h3 id="sql-msg">{{ $ui['result'] ?? '' }}</h3>
            <div class="list-empty" id="sql-idle">
                <p>{{ $ui['idle'] ?? '' }}</p>
                <p class="muted">{{ $ui['idle_hint'] ?? '' }}</p>
            </div>
            <div id="sql-table" hidden></div>
            <p class="muted" id="sql-text" hidden></p>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var root = document.getElementById('sql-index');
    if (!root || !U) return;
    var UI = @json($ui, JSON_UNESCAPED_UNICODE);
    var L = {!! json_encode([
        'need_sql' => admin_t('ui.need_sql'),
        'sql_word' => admin_t('ui.sql_word'),
        'sql_typed_wrong' => admin_t('ui.sql_typed_wrong'),
        'sql_run_fail' => admin_t('ui.sql_run_fail'),
    ], JSON_UNESCAPED_UNICODE) !!};
    var input = document.getElementById('sql-input');
    var msg = document.getElementById('sql-msg');
    var idle = document.getElementById('sql-idle');
    var table = document.getElementById('sql-table');
    var text = document.getElementById('sql-text');
    var hint = document.getElementById('sql-ex-hint');

    function setMsg(s) { if (msg) msg.textContent = s || UI.result || ''; }
    function showIdle() {
        if (idle) idle.hidden = false;
        if (table) { table.hidden = true; table.innerHTML = ''; }
        if (text) { text.hidden = true; text.textContent = ''; }
        setMsg(UI.result || '');
    }
    function showText(s) {
        if (idle) idle.hidden = true;
        if (table) { table.hidden = true; table.innerHTML = ''; }
        if (text) { text.hidden = false; text.textContent = s || ''; }
    }
    function showTable(columns, rows, emptyText) {
        if (idle) idle.hidden = true;
        if (text) { text.hidden = true; text.textContent = ''; }
        if (!table) return;
        table.hidden = false;
        columns = Array.isArray(columns) ? columns : [];
        rows = Array.isArray(rows) ? rows : [];
        if (!rows.length) {
            table.innerHTML = '<p class="muted">' + U.escape(emptyText || UI.empty_rows || '') + '</p>';
            return;
        }
        var html = '<div class="ui-table-wrap"><table class="data"><thead><tr>';
        columns.forEach(function (c) { html += '<th>' + U.escape(c) + '</th>'; });
        html += '</tr></thead><tbody>';
        rows.forEach(function (row) {
            html += '<tr>';
            columns.forEach(function (c) { html += '<td>' + U.escape(row[c] == null ? '' : String(row[c])) + '</td>'; });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        table.innerHTML = html;
    }
    function firstKeyword(sql) {
        sql = String(sql || '').replace(/^\s+/, '');
        while (sql.indexOf('--') === 0 || sql.indexOf('#') === 0 || sql.indexOf('/*') === 0) {
            if (sql.indexOf('--') === 0 || sql.indexOf('#') === 0) {
                var nl = sql.indexOf('\n');
                if (nl < 0) return '';
                sql = sql.slice(nl + 1).replace(/^\s+/, '');
                continue;
            }
            var end = sql.indexOf('*/');
            if (end < 0) return '';
            sql = sql.slice(end + 2).replace(/^\s+/, '');
        }
        var m = sql.match(/^([A-Za-z]+)/);
        return m ? m[1].toLowerCase() : '';
    }
    function isWrite(kw) {
        return kw === 'insert' || kw === 'update' || kw === 'delete' || kw === 'replace';
    }

    U.on('#sql-examples', 'click', function (e) {
        var btn = e.target.closest('.js-sql-ex');
        if (!btn || !input) return;
        input.value = btn.getAttribute('data-sql') || '';
        if (hint) hint.textContent = btn.getAttribute('data-hint') || '';
        input.focus();
    });

    U.on('#sql-clear', 'click', function () {
        if (input) input.value = '';
        showIdle();
    });

    U.on('#sql-run', 'click', function () {
        var sql = (input && input.value || '').trim();
        if (!sql) { U.toast(UI.need_sql || L.need_sql, 'err'); return; }
        var body = {sql: sql};
        if (isWrite(firstKeyword(sql))) {
            if (!U.confirm(UI.confirm_write || '')) return;
            var typed = U.prompt(UI.type_hint || '', '');
            if (typed === null) return;
            if (String(typed).trim() !== String(UI.word || L.sql_word)) {
                U.toast(UI.type_err || L.sql_typed_wrong, 'err');
                return;
            }
            body.word = UI.word || L.sql_word;
        }
        U.loading(true);
        U.post('/admin/system/database/sql/run', body).then(function (res) {
            U.loading(false);
            var ok = res && res.code === 0;
            U.toast((res && res.msg) || '', ok ? 'ok' : 'err');
            setMsg((res && res.msg) || '');
            if (!ok) {
                showText((res && res.msg) || '');
                return;
            }
            var data = res.data || {};
            if (data.type === 'query') {
                showTable(data.columns || [], data.rows || [], UI.empty_rows || '');
                return;
            }
            if (data.type === 'affecting') {
                showText(res.msg || '');
                return;
            }
            showText(res.msg || '');
        }).catch(function () {
            U.loading(false);
            U.toast(L.sql_run_fail, 'err');
        });
    });
})();
</script>
@endpush
