@php
    $titles = [
        'images' => admin_t('page.tool_images'),
        'quality' => admin_t('page.tool_quality'),
        'players' => admin_t('page.tool_players'),
        'annex' => admin_t('page.tool_annex'),
        'recycle' => admin_t('page.tool_recycle'),
        'hub' => admin_t('page.tool_hub'),
    ];
@endphp
@extends('admin.layouts.inner')
@section('title', $titles[$tool] ?? admin_t('page.tools'))

@section('content')
    @if($tool === 'images')
        <p class="hint">扫描影片封面：远程地址、无法访问的坏图。本地化会下载到 <code>/uploads/vod/</code>，并按站点设置写入水印。</p>
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="btn-scan">扫描</button>
            <button type="button" class="btn btn-muted btn-sm" id="btn-local">本地化远程封面</button>
        </div>
        <pre id="out" class="out"></pre>
    @elseif($tool === 'quality')
        <p class="hint">统计无地址、无封面、无简介、无演员、重名、集数不足。</p>
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="btn-scan">开始体检</button>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_url=1">无地址列表</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_pic=1">无封面列表</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?repeat=1">重名列表</a>
        </div>
        <pre id="out" class="out"></pre>
    @elseif($tool === 'players')
        <p class="hint">按线路上的播放器标识批量改名或下线。现有播放器：
            @foreach($players as $p) <span class="status status-off">{{ $p->code }}</span> @endforeach
        </p>
        <label>原标识</label>
        <input type="text" id="from" placeholder="如 dplayer">
        <label>新标识</label>
        <input type="text" id="to" placeholder="下线时可空">
        <div class="form-actions">
            <button type="button" class="btn" id="btn-rename">替换标识</button>
            <button type="button" class="btn btn-danger" id="btn-off">下线该线路</button>
        </div>
        <pre id="out" class="out"></pre>
    @elseif($tool === 'annex')
        <p class="hint">对照影片/文章/演员封面，找出 <code>/uploads/vod/</code> 里未被引用的文件。</p>
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="btn-scan">扫描</button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-del">删除未引用</button>
        </div>
        <pre id="out" class="out"></pre>
    @elseif($tool === 'recycle')
        <p class="hint">删除的影片先进入回收站，可还原或彻底删除。</p>
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="btn-restore">还原选中</button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-purge">彻底删除</button>
        </div>
        <div id="rec-table"></div>
    @else
        <p class="hint">填写苹果 CMS 兼容接口地址，探测分类和样例。也可在「推荐资源」里保存常用源。</p>
        <div class="field-inline">
            <input type="text" id="hub-url" placeholder="https://example.com/api.php/provide/vod/">
            <button type="button" class="btn" id="btn-probe">探测接口</button>
        </div>
        <div class="toolbar" style="margin-top:12px;">
            <a class="btn btn-muted btn-sm" href="/admin/video/unions">推荐资源</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源</a>
        </div>
        @if(count($unions ?? []))
            <p class="hint">已保存：</p>
            <ul>
                @foreach($unions as $u)
                    <li><a href="#" class="hub-fill" data-url="{{ $u->api_url }}">{{ $u->name }}</a> <span class="muted">{{ $u->api_url }}</span></li>
                @endforeach
            </ul>
        @endif
        <pre id="out" class="out"></pre>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var tool = @json($tool);
    var recTable = null;
    function post(action, extra, cb) {
        U.loading(true);
        U.post('/admin/video/tools/' + tool + '/run', Object.assign({action: action}, extra || {})).then(function (res) {
            U.loading(false);
            if (cb) { cb(res); return; }
            var out = document.getElementById('out');
            if (out) out.textContent = JSON.stringify((res && res.data) || res, null, 2);
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    }
    U.on('#btn-scan', 'click', function () { post('scan'); });
    U.on('#btn-local', 'click', function () { post('localize'); });
    U.on('#btn-del', 'click', function () {
        if (!U.confirm('确认删除未引用文件？')) return;
        post('delete');
    });
    U.on('#btn-rename', 'click', function () {
        post('replace', {from: (U.q('#from') || {}).value, to: (U.q('#to') || {}).value, mode: 'rename'});
    });
    U.on('#btn-off', 'click', function () {
        post('replace', {from: (U.q('#from') || {}).value, mode: 'disable'});
    });
    U.on('#btn-probe', 'click', function () { post('probe', {api_url: (U.q('#hub-url') || {}).value}); });
    U.qa('.hub-fill').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            U.q('#hub-url').value = a.getAttribute('data-url') || '';
        });
    });
    if (tool === 'recycle') {
        recTable = U.table({
            el: '#rec-table',
            url: '/admin/video/list',
            where: {trash: 1},
            cols: [
                {check: true, width: 36},
                {key: 'id', title: 'ID', width: 70},
                {key: 'title', title: '标题'},
                {key: 'type_name', title: '分类', width: 120},
                {title: '删除时间', width: 160, html: function (d) {
                    var t = parseInt(d.deleted_at || 0, 10);
                    return t ? new Date(t * 1000).toLocaleString() : '';
                }}
            ]
        });
        U.on('#btn-restore', 'click', function () {
            var ids = recTable.selectedIds();
            if (!ids.length) { U.toast('请选择', 'err'); return; }
            post('restore', {ids: ids.join(',')}, function (res) {
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
                recTable.refresh();
            });
        });
        U.on('#btn-purge', 'click', function () {
            var ids = recTable.selectedIds();
            if (!ids.length) { U.toast('请选择', 'err'); return; }
            if (!U.confirm('彻底删除后无法恢复')) return;
            post('purge', {ids: ids.join(',')}, function (res) {
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
                recTable.refresh();
            });
        });
    }
})();
</script>
@endpush
