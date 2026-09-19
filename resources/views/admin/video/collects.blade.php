@extends('admin.layouts.inner')
@section('title', admin_t('page.collects'))

@php
    $collectJsLang = [
        'sort' => admin_t('ui.sort'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'fail' => admin_t('ui.fail'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'manga' => admin_t('ui.chip_manga'),
        'videos' => admin_t('ui.chip_videos'),
        'unbound' => admin_t('ui.chip_unbound'),
        'bound_n' => admin_t('ui.bound_n', ['n' => '__N__']),
        'badge_fail' => admin_t('ui.badge_fail'),
        'badge_break' => admin_t('ui.badge_break', ['n' => '__N__']),
        'never_collected' => admin_t('ui.never_collected'),
        'col_collect' => admin_t('ui.col_collect'),
        'col_run' => admin_t('ui.col_run'),
        'run_today' => admin_t('ui.run_today'),
        'run_week' => admin_t('ui.run_week'),
        'run_all' => admin_t('ui.run_all'),
        'run_resume' => admin_t('ui.run_resume'),
        'run_retry' => admin_t('ui.run_retry'),
        'bind' => admin_t('ui.bind'),
        'logs' => admin_t('ui.logs'),
        'temps' => admin_t('ui.temps'),
        'schedule' => admin_t('ui.schedule'),
        'add_collect' => admin_t('ui.add_collect'),
        'edit_collect' => admin_t('ui.edit_collect'),
        'empty_collects' => admin_t('ui.empty_collects'),
        'empty_collects_hint' => admin_t('ui.empty_collects_hint'),
        'no_match_collects' => admin_t('ui.no_match_collects'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_fill_api' => admin_t('ui.please_fill_api'),
        'please_enable_collect' => admin_t('ui.please_enable_collect'),
        'collect_done' => admin_t('ui.collect_done'),
        'resume_done' => admin_t('ui.resume_done'),
        'retry_done' => admin_t('ui.retry_done'),
        'confirm_collect_all' => admin_t('ui.confirm_collect_all'),
        'confirm_del_collect' => admin_t('ui.confirm_del_collect'),
        'bind_fail_fetch' => admin_t('ui.bind_fail_fetch'),
        'bind_no_types' => admin_t('ui.bind_no_types'),
        'bind_hint_vod' => admin_t('ui.bind_hint_vod'),
        'bind_hint_manga' => admin_t('ui.bind_hint_manga'),
        'bind_auto' => admin_t('ui.bind_auto'),
        'bind_auto_ok' => admin_t('ui.bind_auto_ok'),
        'bind_remote' => admin_t('ui.bind_remote'),
        'bind_local' => admin_t('ui.bind_local'),
        'bind_skip' => admin_t('ui.bind_skip'),
        'bind_title' => admin_t('ui.bind_title', ['name' => '__NAME__']),
        'bind_save' => admin_t('ui.bind_save'),
        'bind_saved' => admin_t('ui.bind_saved'),
        'save_ok' => admin_t('ui.save_ok'),
        'delete_ok' => admin_t('ui.delete_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel collect-index desk-board">
    <div class="card-header">
        <span>{{ admin_t('ui.collects') }}</span>
        <div>
            <button type="button" class="btn btn-sm" id="collect-source-add-btn">{{ admin_t('ui.add_collect') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="collect-source-search" onsubmit="return false;">
            <input type="hidden" name="empty_bind">
            <input type="hidden" name="has_error">
            <input type="hidden" name="status">
            <input type="hidden" name="mid">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_collect') }}" autocomplete="off">
            <button type="button" class="btn btn-sm" id="collect-source-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="collect-source-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="collect-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.chip_enabled') }}</button>
            <button type="button" class="chip" data-queue="mid" data-value="1">{{ admin_t('ui.chip_videos') }}</button>
            @if($mangaReady ?? false)
                <button type="button" class="chip" data-queue="mid" data-value="2">{{ admin_t('ui.chip_manga') }}</button>
            @endif
            <button type="button" class="chip" data-queue="empty_bind" data-value="1">{{ admin_t('ui.chip_unbound') }}</button>
            <button type="button" class="chip" data-queue="has_error" data-value="1">{{ admin_t('ui.chip_has_error') }}</button>
        </div>
        <p class="muted recycle-lead">
            {{ admin_t('ui.collects_lead_before') }}
            <code>api.php/provide/vod/</code>{{ admin_t('ui.collects_lead_mid') }}
            <code>api.php/provide/manga/</code>{{ admin_t('ui.collects_lead_after') }}
            「<a href="/admin/video/tools/hub">{{ admin_t('ui.try_api_link') }}</a>」{{ admin_t('ui.collects_lead_end') }}
            「<a href="/admin/video/unions">{{ admin_t('ui.unions_link') }}</a>」{{ admin_t('ui.collects_lead_cj') }}
            「<a href="/admin/video/cj">{{ admin_t('ui.cj_link') }}</a>」{{ admin_t('ui.collects_lead_tail') }}
        </p>
        <div id="collect-source-table" class="desk-table"></div>
    </div>
</div>
<template id="collect-source-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <h3>{{ admin_t('ui.section_basic') }}</h3>
        <label>{{ admin_t('ui.label_name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_collect_name') }}" required autofocus>
        <label>{{ admin_t('ui.label_api_url') }}</label>
        <input type="text" name="api_url" placeholder="https://xxx/api.php/provide/vod/" required>
        <p class="muted field-hint">{{ admin_t('ui.hint_collect_api') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.label_format') }}</label>
                <select name="api_type">
                    <option value="auto">{{ admin_t('ui.api_auto') }}</option>
                    <option value="json">JSON</option>
                    <option value="xml">XML</option>
                </select>
            </div>
            <div>
                <label>{{ admin_t('ui.label_write_to') }}</label>
                <select name="mid">
                    <option value="1">{{ admin_t('ui.chip_videos') }}</option>
                    @if($mangaReady ?? false)
                        <option value="2">{{ admin_t('ui.chip_manga') }}</option>
                    @endif
                </select>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.hint_manga_plugin') }}</p>
        <label>{{ admin_t('ui.label_param') }}</label>
        <input type="text" name="param" placeholder="{{ admin_t('ui.ph_collect_param') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_collect_param') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status"><option value="1">{{ admin_t('ui.enabled') }}</option><option value="0">{{ admin_t('ui.disabled') }}</option></select>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.hint_bind_after_save') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($collectJsLang);
    var QUEUE_KEYS = ['empty_bind', 'has_error', 'mid'];
    var form = document.getElementById('collect-source-search');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        U.qa('#collect-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '') {
                on = status === '' && QUEUE_KEYS.every(function (k) { return !form[k] || form[k].value === ''; });
            } else if (key === 'status') {
                on = status === val && QUEUE_KEYS.every(function (k) { return !form[k] || form[k].value === ''; });
            } else if (form[key]) {
                on = form[key].value === val && status === '';
            }
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') {
            form.status.value = value || '';
        } else if (key && form[key]) {
            form[key].value = value || '';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(cleanWhere(U.formData(form)));
        markChips();
    }
    function sourceHtml(d) {
        var badges = [];
        if (parseInt(d.mid, 10) === 2) badges.push('<span class="badge badge-search">' + L.manga + '</span>');
        else badges.push('<span class="badge">' + L.videos + '</span>');
        if (!(parseInt(d.bind_count, 10) > 0)) badges.push('<span class="badge badge-warn">' + L.unbound + '</span>');
        else badges.push('<span class="badge badge-ok">' + String(L.bound_n || '').replace('__N__', String(d.bind_count)) + '</span>');
        if (d.has_error) badges.push('<span class="badge badge-off">' + L.badge_fail + '</span>');
        if (d.has_break && parseInt(d.last_page, 10) > 1) badges.push('<span class="badge badge-search">' + String(L.badge_break || '').replace('__N__', String(d.last_page)) + '</span>');
        var meta = U.escape(d.api_host || d.api_url || '');
        if (d.api_type && d.api_type !== 'auto') meta += ' · ' + U.escape(d.api_type);
        if (d.last_collect_at_text) meta += ' · ' + U.escape(d.last_collect_at_text);
        else meta += ' · ' + L.never_collected;
        if (d.last_error) meta += ' · ' + U.escape(d.last_error);
        return '<div class="collect-cell"><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted collect-url" title="' + U.escape(d.api_url || '') + '">' + meta + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }
    function collectHtml(d) {
        var html = '<a href="#" class="btn-link js-today">' + L.run_today + '</a><a href="#" class="btn-link js-week">' + L.run_week + '</a><a href="#" class="btn-link js-all">' + L.run_all + '</a>';
        if (d.has_break) html += '<a href="#" class="btn-link js-resume">' + L.run_resume + '</a>';
        if (d.has_error) html += '<a href="#" class="btn-link js-retry">' + L.run_retry + '</a>';
        return html;
    }

    var table = U.table({
        el: '#collect-source-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/collects/list',
        where: cleanWhere(U.formData(form)),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_collects + '</p><p><button type="button" class="btn btn-muted btn-sm" id="collect-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_collects + '</p><p class="muted">' + L.empty_collects_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="collect-empty-add">' + L.add_collect + '</button></p></div>';
        },
        onDraw: function () {
            var add = document.getElementById('collect-empty-add');
            var reset = document.getElementById('collect-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; }); runSearch(); });
        },
        cols: [
            {title: L.col_collect, html: sourceHtml},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.col_run, cls: 'actions collect-ops', html: collectHtml},
            {title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="#" class="btn-link js-bind">' + L.bind + '</a>'
                    + '<a class="btn-link js-logs" href="/admin/video/collect_logs?collect_source_id=' + encodeURIComponent(d.id || '') + '">' + L.logs + '</a>'
                    + '<a class="btn-link" href="/admin/video/collect_temps?collect_source_id=' + encodeURIComponent(d.id || '') + '">' + L.temps + '</a>'
                    + '<a class="btn-link js-task" href="/admin/video/collect_tasks/create?collect_source_id=' + encodeURIComponent(d.id || '') + '">' + L.schedule + '</a>'
                    + '<a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_collect : L.add_collect,
            content: document.getElementById('collect-source-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    name: row.name || '',
                    api_url: row.api_url || '',
                    api_type: row.api_type || 'auto',
                    mid: row.mid == null ? '1' : String(row.mid),
                    param: row.param || '',
                    status: row.status == null ? '1' : String(row.status),
                    sort: row.sort == null ? 0 : row.sort
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (!data.api_url) { U.toast(L.please_fill_api, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/collects/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.save_ok, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function runCollect(row, hours, pages, confirmText) {
        if (String(row.status) === '0') { U.toast(L.please_enable_collect, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.loading(true);
        U.post('/admin/video/collects/run', {id: row.id, page: 1, pages: pages || 999, hours: hours || 0}).then(function (res) {
            U.loading(false);
            table.refresh();
            U.toast((res && res.msg) || L.collect_done, res && res.code === 0 ? 'ok' : 'err');
        });
    }

    function openBind(row) {
        U.loading(true);
        U.get('/admin/video/collects/classes', {id: row.id}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.bind_fail_fetch, 'err'); return; }
            var data = res.data || {};
            var types = data.types || [];
            var locals = data.local_types || [];
            if (!types.length) { U.toast(L.bind_no_types, 'err'); return; }
            var html = parseInt(row.mid, 10) === 2
                ? '<p class="hint">' + L.bind_hint_manga + '</p>'
                : '<p class="hint">' + L.bind_hint_vod + '</p>';
            html += '<p><button type="button" class="btn btn-muted btn-sm" id="collect-suggest-btn">' + L.bind_auto + '</button></p>';
            html += '<table class="data"><thead><tr><th>' + L.bind_remote + '</th><th>' + L.bind_local + '</th></tr></thead><tbody>';
            types.forEach(function (t) {
                html += '<tr><td>' + U.escape(t.name) + ' <span class="muted">#' + U.escape(t.remote_id) + '</span></td><td><select data-remote="' + U.escape(t.remote_id) + '"><option value="0">' + L.bind_skip + '</option>';
                locals.forEach(function (l) {
                    var pad = parseInt(l.parent_id, 10) > 0 ? '└ ' : '';
                    html += '<option value="' + U.escape(l.id) + '"' + (String(l.id) === String(t.local_id) ? ' selected' : '') + '>' + pad + U.escape(l.name) + '</option>';
                });
                html += '</select></td></tr>';
            });
            html += '</tbody></table>';
            U.dialog({
                title: String(L.bind_title || '').replace('__NAME__', row.name || ''),
                wide: true,
                okText: L.bind_save,
                content: html,
                onOpen: function (body) {
                    var btn = body.querySelector('#collect-suggest-btn');
                    if (!btn) return;
                    btn.addEventListener('click', function () {
                        U.loading(true);
                        U.post('/admin/video/collects/suggest', {id: row.id}).then(function (r) {
                            U.loading(false);
                            U.toast((r && r.msg) || L.bind_auto_ok, r && r.code === 0 ? 'ok' : 'err');
                            if (r && r.code === 0) {
                                body.closest('.ui-mask').remove();
                                openBind(row);
                            }
                        });
                    });
                },
                onSave: function (body) {
                    var bind = {};
                    U.qa('select[data-remote]', body).forEach(function (sel) {
                        bind[sel.getAttribute('data-remote')] = sel.value;
                    });
                    return U.post('/admin/video/collects/bind', {id: row.id, bind: JSON.stringify(bind)}).then(function (r) {
                        if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return false; }
                        U.toast(L.bind_saved, 'ok');
                        table.refresh();
                    });
                }
            });
        });
    }

    U.on('#collect-source-search-btn', 'click', runSearch);
    U.on('#collect-source-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    U.on('#collect-source-add-btn', 'click', function () { openDialog('add', {}); });
    document.getElementById('collect-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#collect-source-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.classList.contains('js-logs') || a.classList.contains('js-task')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-bind')) openBind(row);
        if (a.classList.contains('js-today')) runCollect(row, 24, 999);
        if (a.classList.contains('js-week')) runCollect(row, 168, 999);
        if (a.classList.contains('js-all')) runCollect(row, 0, 999, L.confirm_collect_all);
        if (a.classList.contains('js-resume')) {
            U.loading(true);
            U.post('/admin/video/collects/resume', {id: row.id, pages: 999, hours: 0}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || L.resume_done, res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-retry')) {
            U.loading(true);
            U.post('/admin/video/collects/retry', {id: row.id}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || L.retry_done, res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-del') && U.confirm(L.confirm_del_collect)) {
            U.post('/admin/video/collects/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.delete_ok, 'ok');
            });
        }
    });
})();
</script>
@endpush
