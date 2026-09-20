@extends('admin.layouts.inner')
@section('title', $title)

@php
    $modJsLang = [
        'fail' => admin_t('ui.fail'),
        'save_ok' => admin_t('ui.save_ok'),
        'finished' => admin_t('ui.finished'),
        'deleted' => admin_t('ui.deleted'),
        'edit' => admin_t('ui.edit'),
        'add' => admin_t('ui.add'),
        'bind_videos' => admin_t('ui.bind_videos'),
        'run_job' => admin_t('ui.run_job'),
        'try_run' => admin_t('ui.try_run'),
        'import_in' => admin_t('ui.import_in'),
        'offline_fail_line' => admin_t('ui.offline_fail_line'),
        'prompt_invite_gen' => admin_t('ui.prompt_invite_gen'),
        'prompt_card_gen' => admin_t('ui.prompt_card_gen'),
        'prompt_video_ids' => admin_t('ui.prompt_video_ids'),
        'try_run_n' => admin_t('ui.try_run_n'),
        'no_match' => admin_t('ui.no_match'),
        'confirm_cj_import' => admin_t('ui.confirm_cj_import'),
        'confirm_offline_fail' => admin_t('ui.confirm_offline_fail'),
        'confirm_del_simple' => admin_t('ui.confirm_del_simple'),
    ];
@endphp

@section('content')
    @if(! empty($hint))
        <p class="hint">{{ $hint }}</p>
    @endif
    <div class="toolbar">
        <button type="button" class="btn btn-sm" id="mod-add">{{ admin_t('ui.add') }}</button>
        <button type="button" class="btn btn-muted btn-sm" id="mod-refresh">{{ admin_t('ui.refresh') }}</button>
        @if($module === 'invites')
            <button type="button" class="btn btn-muted btn-sm" id="mod-gen">{{ admin_t('ui.batch_gen') }}</button>
        @endif
        @if($module === 'collect_tasks')
            <button type="button" class="btn btn-muted btn-sm" id="mod-due">{{ admin_t('ui.run_due_collect') }}</button>
        @endif
        <form class="filter-bar" id="mod-search-form" onsubmit="return false;">
            <input type="search" id="mod-q" name="{{ $search }}" placeholder="{{ $search }}" autocomplete="off">
            <button type="submit" class="btn btn-sm" id="mod-search">{{ admin_t('ui.search') }}</button>
        </form>
    </div>
    <div id="mod-table"></div>
    <template id="mod-dialog-tpl">
        <form class="admin-form" id="mod-form">
            <input type="hidden" name="id">
            @foreach($fields as $field)
                <label>{{ $field['label'] }}</label>
                @if(($field['type'] ?? 'text') === 'textarea')
                    <textarea name="{{ $field['name'] }}"></textarea>
                @elseif(($field['type'] ?? '') === 'select')
                    <select name="{{ $field['name'] }}">
                        @foreach(($field['options'] ?? []) as $val => $lab)
                            <option value="{{ $val }}">{{ $lab }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="{{ ($field['type'] ?? 'text') === 'number' ? 'number' : 'text' }}" name="{{ $field['name'] }}">
                @endif
            @endforeach
        </form>
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($modJsLang, JSON_UNESCAPED_UNICODE);
    var module = @json($module, JSON_UNESCAPED_UNICODE);
    var cols = @json($cols, JSON_UNESCAPED_UNICODE);
    var fields = @json($fields, JSON_UNESCAPED_UNICODE);
    var searchField = @json($search, JSON_UNESCAPED_UNICODE);
    var tableCols = [];
    tableCols.push({key: 'id', title: 'ID', width: 70});
    cols.forEach(function (c) {
        if (c === 'id') return;
        tableCols.push({key: c, title: c});
    });
    tableCols.push({
        title: AdminUi.t('actions'),
        cls: 'actions',
        html: function (row) {
            var html = '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a>';
            if (module === 'topics') html += '<a href="#" class="btn-link js-bind">' + U.escape(L.bind_videos) + '</a>';
            if (module === 'collect_tasks') html += '<a href="#" class="btn-link js-run">' + U.escape(L.run_job) + '</a>';
            if (module === 'cj') html += '<a href="#" class="btn-link js-try">' + U.escape(L.try_run) + '</a><a href="#" class="btn-link js-import">' + U.escape(L.import_in) + '</a>';
            if (module === 'playfails') html += '<a href="#" class="btn-link js-off">' + U.escape(L.offline_fail_line) + '</a>';
            html += '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            return html;
        }
    });
    var table = U.table({ el: '#mod-table', url: '/admin/video/' + module + '/list', cols: tableCols });

    function open(row) {
        row = row || {};
        U.dialog({
            title: row.id ? L.edit : L.add,
            content: document.getElementById('mod-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var form = body.querySelector('form');
                var val = {id: row.id || ''};
                fields.forEach(function (f) { val[f.name] = row[f.name] == null ? '' : row[f.name]; });
                U.fillForm(form, val);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.save_ok, 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#mod-add', 'click', function () { open({}); });
    U.on('#mod-refresh', 'click', function () { table.refresh(); });
    U.on('#mod-search', 'click', function () {
        var where = {};
        where[searchField] = (U.q('#mod-q').value || '');
        table.reload(where);
    });
    U.on('#mod-gen', 'click', function () {
        var isInvite = module === 'invites';
        var val = U.prompt(isInvite ? L.prompt_invite_gen : L.prompt_card_gen, isInvite ? '10,0,0' : '10,100');
        if (val == null) return;
        var parts = String(val).split(/[,，\s]+/);
        var url = isInvite ? '/admin/video/invites/generate' : '/admin/video/cards/generate';
        var payload = isInvite
            ? {count: parts[0] || 10, points: parts[1] || 0, member_id: parts[2] || 0}
            : {count: parts[0] || 10, points: parts[1] || 100};
        U.post(url, payload).then(function (res) {
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) table.refresh();
        });
    });
    U.on('#mod-due', 'click', function () {
        U.loading(true);
        U.post('/admin/video/collect-due', {}).then(function (res) {
            U.loading(false);
            table.refresh();
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
        });
    });
    U.on('#mod-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) open(row);
        if (a.classList.contains('js-bind')) {
            U.get('/admin/video/topics/' + row.id + '/videos').then(function (res) {
                var ids = (res.data && res.data.video_ids) ? res.data.video_ids : '';
                var val = U.prompt(L.prompt_video_ids, ids);
                if (val == null) return;
                U.post('/admin/video/topics/' + row.id + '/videos', {video_ids: val}).then(function (r) {
                    U.toast((r && r.msg) || L.finished, r && r.code === 0 ? 'ok' : 'err');
                });
            });
        }
        if (a.classList.contains('js-run')) {
            U.post('/admin/video/collect_tasks/run', {id: row.id}).then(function (r) {
                table.refresh();
                U.toast((r && r.msg) || L.finished, r && r.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-try')) {
            U.loading(true);
            U.post('/admin/video/cj/try', {id: row.id}).then(function (r) {
                U.loading(false);
                if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return; }
                var items = (r.data && r.data.items) ? r.data.items : [];
                var lines = items.map(function (it) { return (it.title || '') + ' ' + (it.url || ''); });
                var n = (r.data && r.data.total) || items.length;
                U.dialog({
                    title: (L.try_run_n || '').replace(':n', n),
                    content: '<pre class="out">' + U.escape(lines.join('\n') || L.no_match) + '</pre>',
                    hideOk: true
                });
            });
        }
        if (a.classList.contains('js-import')) {
            if (!U.confirm(L.confirm_cj_import)) return;
            U.loading(true);
            U.post('/admin/video/cj/run', {id: row.id}).then(function (r) {
                U.loading(false);
                table.refresh();
                U.toast((r && r.msg) || L.finished, r && r.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-off')) {
            if (!U.confirm(L.confirm_offline_fail)) return;
            U.post('/admin/video/playfails/offline', {id: row.id}).then(function (r) {
                table.refresh();
                U.toast((r && r.msg) || L.finished, r && r.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_simple)) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (res && res.code === 0) { table.refresh(); U.toast(L.deleted, 'ok'); }
                else U.toast((res && res.msg) || L.fail, 'err');
            });
        }
    });
})();
</script>
@endpush
