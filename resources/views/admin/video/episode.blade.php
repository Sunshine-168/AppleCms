@extends('admin.layouts.inner')
@section('title', admin_t('page.episodes'))

@section('header_actions')
    <a class="btn btn-muted btn-sm" href="javascript:history.back()">返回线路</a>
    <button type="button" class="btn btn-sm" id="episode-add-btn">新增剧集</button>
    <button type="button" class="btn btn-muted btn-sm" id="episode-refresh-btn">刷新</button>
@endsection

@section('content')
    <div id="episode-table"></div>
    <template id="episode-dialog-tpl">
        <form>
            <input type="hidden" name="id">
            <input type="hidden" name="source_id" value="{{ (int)($sourceId ?? 0) }}">
            <label>集序号</label>
            <input type="number" name="episode_num" value="1" min="1">
            <p class="muted field-hint">播放页按集序号排序。同一线路内不要重复。</p>
            <label>标题</label>
            <input type="text" name="episode_name" placeholder="如 第1集 / 正片">
            <label>播放地址</label>
            <input type="text" name="url" placeholder="https://…m3u8 或直链" required>
            <p class="muted field-hint">填完整播放 URL。相对地址会拼服务器组前缀（在线路里配置）。</p>
            <div class="admin-dialog-grid">
                <div>
                    <label>时长（秒）</label>
                    <input type="number" name="duration" value="0" min="0">
                </div>
                <div>
                    <label>排序</label>
                    <input type="number" name="sort" value="0">
                </div>
            </div>
            <label>状态</label>
            <select name="status"><option value="1">可用</option><option value="0">不可用</option></select>
            <p class="muted field-hint">不可用时前台不显示这一集，线路本身仍保留。</p>
        </form>
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var sourceId = {{ (int)($sourceId ?? 0) }};
    var table = U.table({
        el: '#episode-table',
        url: '/admin/video/episodes/list',
        where: {source_id: sourceId},
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'episode_num', title: '集', width: 70},
            {key: 'episode_name', title: '标题', width: 140},
            {key: 'url', title: '地址'},
            {key: 'duration', title: '时长', width: 70},
            {title: '状态', width: 80, html: function (d) { return String(d.status) === '1' ? U.status(true, '可用') : U.status(false, '不可用'); }},
            {key: 'sort', title: '排序', width: 70},
            {key: 'updated_at_text', title: '更新时间', width: 150},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    function openEpisodeDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑剧集' : '新增剧集',
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
                if (!data.url) { U.toast('请输入播放地址', 'err'); return false; }
                return U.post('/admin/video/episodes/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
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
        if (a.classList.contains('js-del') && U.confirm('确定删除该剧集吗？')) {
            U.post('/admin/video/episodes/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
