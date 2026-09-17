@extends('admin.layouts.inner')
@section('title', admin_t('page.sources'))

@section('header_actions')
    <a class="btn btn-muted btn-sm" href="/admin/video">返回影片</a>
    <button type="button" class="btn btn-sm" id="source-add-btn">新增线路</button>
    <button type="button" class="btn btn-muted btn-sm" id="source-refresh-btn">刷新</button>
@endsection

@section('content')
    <div id="source-table"></div>
    <template id="source-dialog-tpl">
        <form>
            <input type="hidden" name="id">
            <input type="hidden" name="video_id" value="{{ (int)($videoId ?? 0) }}">
            <label>线路名</label>
            <input type="text" name="name">
            <label>类型</label>
            <select name="type">
                <option value="m3u8">m3u8</option>
                <option value="mp4">mp4</option>
                <option value="parse">parse</option>
                <option value="down">下载</option>
            </select>
            <label>播放器标识</label>
            <input type="text" name="player" placeholder="要和「播放器」里的标识一致">
            <label>下载器标识</label>
            <select name="downer">
                <option value="">不指定</option>
                @foreach(($downloaders ?? []) as $d)
                    <option value="{{ $d['code'] ?? '' }}">{{ $d['name'] ?? '' }} ({{ $d['code'] ?? '' }})</option>
                @endforeach
            </select>
            <p class="muted field-hint">下载页用模板替换 {url}/{id}，不是后台任务队列。</p>
            <label>服务器组</label>
            <select name="server_id">
                <option value="0">不拼接前缀</option>
                @foreach(($servers ?? []) as $s)
                    <option value="{{ (int) ($s['id'] ?? 0) }}">{{ $s['name'] ?? '' }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">相对路径会拼上该组的地址前缀。已经是 http 或 // 开头的不会改。停用或前缀空着也原样。</p>
            <label>排序</label>
            <input type="number" name="sort" value="0">
        </form>
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var videoId = {{ (int)($videoId ?? 0) }};
    var openEpisodeOnce = new URLSearchParams(location.search).get('open_episode') === '1';
    var table = U.table({
        el: '#source-table',
        url: '/admin/video/sources/list',
        where: {video_id: videoId},
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '线路名'},
            {key: 'type', title: '类型', width: 80},
            {key: 'player', title: '播放器', width: 90},
            {key: 'downer', title: '下载器', width: 90},
            {key: 'server_id', title: '服务器', width: 80},
            {key: 'episode_total', title: '剧集数', width: 80},
            {key: 'sort', title: '排序', width: 70},
            {key: 'updated_at_text', title: '更新时间', width: 160},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-ep">剧集</a><a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-off">下线</a><a href="#" class="btn-link js-del">删除</a>';
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
            var html = '<table class="data"><thead><tr><th>ID</th><th>线路</th><th></th></tr></thead><tbody>';
            rows.forEach(function (r) {
                html += '<tr><td>' + U.escape(r.id) + '</td><td>' + U.escape(r.name || '') + '</td><td class="actions"><a href="/admin/video/episodes?source_id=' + encodeURIComponent(r.id) + '">选择</a></td></tr>';
            });
            html += '</tbody></table>';
            U.dialog({ title: '选择线路', content: html, hideOk: true });
        }
    });
    function openSourceDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑线路' : '新增线路',
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
                if (!data.name) { U.toast('请输入线路名', 'err'); return false; }
                return U.post('/admin/video/sources/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
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
        if (a.classList.contains('js-off') && U.confirm('确认下线该线路？前台将不再播放。')) {
            U.post('/admin/video/sources/disable', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('已下线', 'ok');
            });
        }
        if (a.classList.contains('js-del') && U.confirm('确定删除该线路吗？')) {
            U.post('/admin/video/sources/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
