@extends('admin.layouts.inner')
@section('title', admin_t('page.episodes'))

@php
    $episodeJsLang = [
        'add' => admin_t('ui.add_episode'),
        'edit' => admin_t('ui.edit_episode'),
        'need_url' => admin_t('ui.please_fill_play_url'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'confirm_del' => admin_t('ui.confirm_del_episode'),
        'deleted' => admin_t('ui.deleted'),
        'ep' => admin_t('ui.col_ep'),
    ];
@endphp

@section('header_actions')
    <a class="btn btn-muted btn-sm" href="javascript:history.back()">{{ admin_t('ui.back_sources') }}</a>
    <button type="button" class="btn btn-sm" id="episode-add-btn">{{ admin_t('ui.add_episode') }}</button>
    <button type="button" class="btn btn-muted btn-sm" id="episode-refresh-btn">{{ admin_t('ui.refresh') }}</button>
@endsection

@section('content')
    <div id="episode-table"></div>
    <template id="episode-dialog-tpl">
        <form class="admin-form">
            <input type="hidden" name="id">
            <input type="hidden" name="source_id" value="{{ (int)($sourceId ?? 0) }}">
            <label>{{ admin_t('ui.label_episode_num') }}</label>
            <input type="number" name="episode_num" value="1" min="1">
            <p class="muted field-hint">{{ admin_t('ui.hint_episode_num') }}</p>
            <label>{{ admin_t('ui.title_label') }}</label>
            <input type="text" name="episode_name" placeholder="{{ admin_t('ui.ph_episode_name') }}">
            <label>{{ admin_t('ui.label_play_url') }}</label>
            <input type="text" name="url" placeholder="{{ admin_t('ui.ph_play_url') }}" required>
            <p class="muted field-hint">{{ admin_t('ui.hint_play_url') }}</p>
            <div class="admin-dialog-grid">
                <div>
                    <label>{{ admin_t('ui.label_duration_sec') }}</label>
                    <input type="number" name="duration" value="0" min="0">
                </div>
                <div>
                    <label>{{ admin_t('ui.sort') }}</label>
                    <input type="number" name="sort" value="0">
                </div>
            </div>
            <label>{{ admin_t('ui.status') }}</label>
            <select name="status"><option value="1">{{ admin_t('ui.status_available') }}</option><option value="0">{{ admin_t('ui.status_unavailable') }}</option></select>
            <p class="muted field-hint">{{ admin_t('ui.hint_episode_status') }}</p>
        </form>
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($episodeJsLang, JSON_UNESCAPED_UNICODE);
    var sourceId = {{ (int)($sourceId ?? 0) }};
    var table = U.table({
        el: '#episode-table',
        url: '/admin/video/episodes/list',
        where: {source_id: sourceId},
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'episode_num', title: L.ep, width: 70},
            {key: 'episode_name', title: AdminUi.t('title'), width: 140},
            {key: 'url', title: AdminUi.t('address')},
            {key: 'duration', title: AdminUi.t('duration'), width: 70},
            {title: AdminUi.t('status'), width: 80, html: function (d) { return String(d.status) === '1' ? U.status(true, AdminUi.t('available')) : U.status(false, AdminUi.t('unavailable')); }},
            {key: 'sort', title: AdminUi.t('sort'), width: 70},
            {key: 'updated_at_text', title: AdminUi.t('updated'), width: 150},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>'; }}
        ]
    });
    function openEpisodeDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit : L.add,
            wide: true,
            content: document.getElementById('episode-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    source_id: sourceId,
                    episode_num: row.episode_num == null ? 1 : row.episode_num,
                    episode_name: row.episode_name || '',
                    url: row.url || '',
                    duration: row.duration == null ? 0 : row.duration,
                    status: row.status == null ? 1 : row.status,
                    sort: row.sort == null ? 0 : row.sort
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.url) { U.toast(L.need_url, 'err'); return false; }
                return U.post('/admin/video/episodes/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.saved, 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#episode-add-btn', 'click', function () { openEpisodeDialog('add'); });
    U.on('#episode-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#episode-table', 'click', function (e) {
        var a = e.target.closest ? e.target.closest('a') : null; if (!a) return;
        var row = U.rowFromClick(e, table);
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openEpisodeDialog('edit', row);
        if (a.classList.contains('js-del') && U.confirm(L.confirm_del)) {
            U.post('/admin/video/episodes/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh(); U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
