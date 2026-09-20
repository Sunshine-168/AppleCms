@extends('admin.layouts.inner')
@section('title', admin_t('page.sources'))

@php
    $sourceJsLang = [
        'add_title' => admin_t('ui.add_source'),
        'edit_title' => admin_t('ui.edit_source'),
        'need_name' => admin_t('ui.please_fill_source_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'confirm_off' => admin_t('ui.confirm_offline_source'),
        'offlined' => admin_t('ui.offlined'),
        'confirm_del' => admin_t('ui.confirm_del_source'),
        'deleted' => admin_t('ui.deleted'),
        'episodes' => admin_t('page.episodes'),
        'pick' => admin_t('ui.pick'),
        'select' => admin_t('ui.select_source'),
        'line' => admin_t('ui.line'),
        'off' => admin_t('ui.offline_line'),
        'source_name' => admin_t('ui.col_source_name'),
        'player' => admin_t('ui.col_player'),
        'downer' => admin_t('ui.col_downer'),
        'server' => admin_t('ui.col_server'),
        'ep_count' => admin_t('ui.col_episode_count'),
        'type' => admin_t('ui.col_type'),
        'sort' => admin_t('ui.sort'),
        'updated' => admin_t('ui.col_updated'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
    ];
@endphp

@section('header_actions')
    <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.back_videos') }}</a>
    <button type="button" class="btn btn-sm" id="source-add-btn">{{ admin_t('ui.add_source') }}</button>
    <button type="button" class="btn btn-muted btn-sm" id="source-refresh-btn">{{ admin_t('ui.refresh') }}</button>
@endsection

@section('content')
    <div id="source-table"></div>
    <template id="source-dialog-tpl">
        <form class="admin-form">
            <input type="hidden" name="id">
            <input type="hidden" name="video_id" value="{{ (int)($videoId ?? 0) }}">
            <label>{{ admin_t('ui.label_source_name') }}</label>
            <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_source_name') }}" autofocus>
            <p class="muted field-hint">{{ admin_t('ui.hint_source_name') }}</p>
            <label>{{ admin_t('ui.label_source_type') }}</label>
            <select name="type">
                <option value="m3u8">m3u8</option>
                <option value="mp4">mp4</option>
                <option value="parse">parse</option>
                <option value="down">{{ admin_t('ui.type_down') }}</option>
            </select>
            <p class="muted field-hint">{{ admin_t('ui.hint_source_type') }}</p>
            <label>{{ admin_t('ui.label_player_code') }}</label>
            <input type="text" name="player" placeholder="{{ admin_t('ui.ph_player_code_match') }}">
            <p class="muted field-hint">{{ admin_t('ui.hint_player_code_source') }}</p>
            <label>{{ admin_t('ui.label_downer_code') }}</label>
            <select name="downer">
                <option value="">{{ admin_t('ui.not_specified') }}</option>
                @foreach(($downloaders ?? []) as $d)
                    <option value="{{ $d['code'] ?? '' }}">{{ $d['name'] ?? '' }} ({{ $d['code'] ?? '' }})</option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.hint_downer') }}</p>
            <label>{{ admin_t('ui.label_server_group') }}</label>
            <select name="server_id">
                <option value="0">{{ admin_t('ui.no_url_prefix') }}</option>
                @foreach(($servers ?? []) as $s)
                    <option value="{{ (int) ($s['id'] ?? 0) }}">{{ $s['name'] ?? '' }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.hint_server_group') }}</p>
            <label>{{ admin_t('ui.sort') }}</label>
            <input type="number" name="sort" value="0">
            <p class="muted field-hint">{{ admin_t('ui.hint_source_sort') }}</p>
        </form>
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($sourceJsLang, JSON_UNESCAPED_UNICODE);
    var videoId = {{ (int)($videoId ?? 0) }};
    var openEpisodeOnce = new URLSearchParams(location.search).get('open_episode') === '1';
    var table = U.table({
        el: '#source-table',
        url: '/admin/video/sources/list',
        where: {video_id: videoId},
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: L.source_name},
            {key: 'type', title: L.type, width: 80},
            {key: 'player', title: L.player, width: 90},
            {key: 'downer', title: L.downer, width: 90},
            {key: 'server_id', title: L.server, width: 80},
            {key: 'episode_total', title: L.ep_count, width: 80},
            {key: 'sort', title: L.sort, width: 70},
            {key: 'updated_at_text', title: L.updated, width: 160},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-ep">' + U.escape(L.episodes) + '</a><a href="#" class="btn-link js-edit">' + U.escape(L.edit) + '</a><a href="#" class="btn-link js-off">' + U.escape(L.off) + '</a><a href="#" class="btn-link js-del">' + U.escape(L.delete) + '</a>';
            }}
        ],
        onDraw: function (wrap, rows) {
            if (!openEpisodeOnce) return;
            openEpisodeOnce = false;
            if (!rows.length) return;
            if (rows.length === 1) {
                location.href = '/admin/video/episodes?source_id=' + encodeURIComponent(rows[0].id);
                return;
            }
            var html = '<table class="data"><thead><tr><th>ID</th><th>' + U.escape(L.line) + '</th><th></th></tr></thead><tbody>';
            rows.forEach(function (r) {
                html += '<tr><td>' + U.escape(r.id) + '</td><td>' + U.escape(r.name || '') + '</td><td class="actions"><a href="/admin/video/episodes?source_id=' + encodeURIComponent(r.id) + '">' + U.escape(L.pick) + '</a></td></tr>';
            });
            html += '</tbody></table>';
            U.dialog({ title: L.select, content: html, hideOk: true });
        }
    });
    function openSourceDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_title : L.add_title,
            wide: true,
            content: document.getElementById('source-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    video_id: videoId,
                    name: row.name || '',
                    type: row.type || 'm3u8',
                    player: row.player || '',
                    downer: row.downer || '',
                    server_id: row.server_id || 0,
                    sort: row.sort == null ? 0 : row.sort
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.need_name, 'err'); return false; }
                return U.post('/admin/video/sources/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.saved, 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#source-add-btn', 'click', function () { openSourceDialog('add'); });
    U.on('#source-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#source-table', 'click', function (e) {
        var a = e.target.closest ? e.target.closest('a') : null; if (!a) return;
        var row = U.rowFromClick(e, table);
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openSourceDialog('edit', row);
        if (a.classList.contains('js-ep')) location.href = '/admin/video/episodes?source_id=' + encodeURIComponent(row.id);
        if (a.classList.contains('js-off') && U.confirm(L.confirm_off)) {
            U.post('/admin/video/sources/disable', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh(); U.toast(L.offlined, 'ok');
            });
        }
        if (a.classList.contains('js-del') && U.confirm(L.confirm_del)) {
            U.post('/admin/video/sources/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh(); U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
