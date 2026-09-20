@extends('admin.layouts.inner')
@section('title', admin_t('page.db_dict'))

@php
    $ui = $ui ?? [];
    $groups = $groups ?? [];
    $tables = $tables ?? [];
    $detail = $detail ?? null;
    $columns = $columns ?? [];
    $current = (string) ($table ?? '');
@endphp

@section('plain')
<div class="card card-panel schema-index db-index" id="schema-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '' }}</span>
    </div>
    <div class="card-body">
        @include('admin.partials.db-tabs', ['tab' => 'dict'])
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}{{ admin_t('ui.dict_dropdowns') }}「<a href="/admin/system/dicts">{{ admin_t('page.dict') }}</a>」。</p>
        <form class="filter-bar schema-find" id="schema-search" autocomplete="off" onsubmit="return false;">
            <input type="search" id="schema-q" placeholder="{{ $ui['find'] ?? '' }}" aria-label="{{ $ui['find'] ?? '' }}">
        </form>
        <div class="queue-chips" id="schema-groups">
            <button type="button" class="chip active" data-group="">{{ $ui['all'] ?? '' }}</button>
            @foreach($groups as $g)
                <button type="button" class="chip" data-group="{{ $g['id'] }}">{{ $g['label'] }}<em>{{ $g['n'] }}</em></button>
            @endforeach
        </div>
        @if($tables === [])
            <div class="list-empty">
                <p>{{ $ui['empty'] ?? '' }}</p>
                <p class="muted">{{ $ui['empty_hint'] ?? '' }}</p>
            </div>
        @else
            <div class="schema-layout">
                <nav class="schema-tables" id="schema-tables">
                    @foreach($tables as $row)
                        <button type="button" class="schema-table{{ $row['name'] === $current ? ' is-on' : '' }}" data-name="{{ $row['name'] }}" data-group="{{ $row['group'] }}" data-search="{{ $row['search'] }}" data-url="{{ $row['url'] }}">
                            <strong>{{ $row['label'] }}</strong>
                            <span>{{ $row['name'] }}</span>
                        </button>
                    @endforeach
                </nav>
                <section class="schema-detail" id="schema-detail">
                    @if(is_array($detail))
                        <div class="schema-detail-head">
                            <div>
                                <h3 id="schema-label">{{ $detail['label'] }}</h3>
                                <p class="muted" id="schema-name">{{ $detail['name'] }} · {{ $detail['group_label'] }}</p>
                            </div>
                            @if(($detail['url'] ?? '') !== '')
                                <a class="btn btn-muted btn-sm" id="schema-go" href="{{ $detail['url'] }}">{{ $ui['look'] ?? '' }}</a>
                            @else
                                <a class="btn btn-muted btn-sm" id="schema-go" href="#" hidden>{{ $ui['look'] ?? '' }}</a>
                            @endif
                        </div>
                        <p class="muted field-hint" id="schema-hint">{{ $detail['hint'] }}</p>
                        <div id="schema-cols">
                            @forelse($columns as $col)
                                <article class="schema-col">
                                    <div class="schema-col-head">
                                        <strong>{{ $col['field'] }}</strong>
                                        <span>{{ $col['type_text'] }}{{ ($col['type'] ?? '') !== '' ? ' · '.$col['type'] : '' }}</span>
                                    </div>
                                    <p>{{ $col['purpose'] }}</p>
                                    <p class="muted">{{ $col['null_text'] }}@if(($col['key_text'] ?? '') !== '') · {{ $col['key_text'] }}@endif @if(($col['default'] ?? '') !== '') · {{ $ui['default'] ?? '' }} {{ $col['default'] }}@endif @if(($col['extra'] ?? '') !== '') · {{ $col['extra'] }}@endif</p>
                                </article>
                            @empty
                                <p class="muted">{{ $ui['no_cols'] ?? '' }}</p>
                            @endforelse
                        </div>
                    @endif
                </section>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var root = document.getElementById('schema-index');
    if (!root || !U) return;
    var UI = @json($ui, JSON_UNESCAPED_UNICODE);
    var group = '';
    var current = @json($current, JSON_UNESCAPED_UNICODE);

    function visible(btn) {
        if (group && btn.getAttribute('data-group') !== group) return false;
        var q = (document.getElementById('schema-q').value || '').trim().toLowerCase();
        if (!q) return true;
        return (btn.getAttribute('data-search') || '').indexOf(q) !== -1;
    }
    function filter() {
        U.qa('#schema-tables .schema-table').forEach(function (btn) {
            btn.hidden = !visible(btn);
        });
    }
    function renderCols(cols) {
        var box = document.getElementById('schema-cols');
        if (!box) return;
        cols = cols || [];
        if (!cols.length) {
            box.innerHTML = '<p class="muted">' + (UI.no_cols || '') + '</p>';
            return;
        }
        var html = '';
        cols.forEach(function (col) {
            var meta = [col.null_text || ''];
            if (col.key_text) meta.push(col.key_text);
            if (col.default) meta.push((UI.default || '') + ' ' + col.default);
            if (col.extra) meta.push(col.extra);
            html += '<article class="schema-col"><div class="schema-col-head"><strong>' + U.escape(col.field || '') + '</strong><span>' + U.escape((col.type_text || '') + (col.type ? ' · ' + col.type : '')) + '</span></div>';
            html += '<p>' + U.escape(col.purpose || '') + '</p><p class="muted">' + U.escape(meta.join(' · ')) + '</p></article>';
        });
        box.innerHTML = html;
    }
    function selectTable(name) {
        if (!name) return;
        current = name;
        U.qa('#schema-tables .schema-table').forEach(function (btn) {
            btn.classList.toggle('is-on', btn.getAttribute('data-name') === name);
        });
        U.get('/admin/system/database/dict/columns', {table: name}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '', 'err'); return; }
            var d = res.data || {};
            var label = document.getElementById('schema-label');
            var hint = document.getElementById('schema-hint');
            var nm = document.getElementById('schema-name');
            var go = document.getElementById('schema-go');
            if (label) label.textContent = d.label || name;
            if (hint) hint.textContent = d.hint || '';
            if (nm) nm.textContent = name + ' · ' + (d.group_label || '');
            if (go) {
                if (d.url) { go.href = d.url; go.hidden = false; }
                else { go.hidden = true; }
            }
            renderCols(d.data || []);
            if (history.replaceState) history.replaceState(null, '', '/admin/system/database/dict?table=' + encodeURIComponent(name));
        });
    }
    U.on('#schema-groups', 'click', function (e) {
        var btn = e.target.closest('.chip');
        if (!btn) return;
        group = btn.getAttribute('data-group') || '';
        U.qa('#schema-groups .chip').forEach(function (el) { el.classList.toggle('active', el === btn); });
        filter();
    });
    U.on('#schema-q', 'input', filter);
    U.on('#schema-tables', 'click', function (e) {
        var btn = e.target.closest('.schema-table');
        if (!btn) return;
        selectTable(btn.getAttribute('data-name') || '');
    });
})();
</script>
@endpush
