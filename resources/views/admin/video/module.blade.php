@extends('admin.layouts.inner')
@section('title', $title)

@section('content')
    @if(! empty($hint))
        <p class="hint">{{ $hint }}</p>
    @endif
    <div class="toolbar">
        <button type="button" class="btn btn-sm" id="mod-add">新增</button>
        <button type="button" class="btn btn-muted btn-sm" id="mod-refresh">刷新</button>
        @if($module === 'invites')
            <button type="button" class="btn btn-muted btn-sm" id="mod-gen">批量生成</button>
        @endif
        @if($module === 'collect_tasks')
            <button type="button" class="btn btn-muted btn-sm" id="mod-due">执行到期采集</button>
        @endif
        <form class="filter-bar" id="mod-search-form" onsubmit="return false;">
            <input type="search" id="mod-q" name="{{ $search }}" placeholder="{{ $search }}" autocomplete="off">
            <button type="submit" class="btn btn-sm" id="mod-search">查询</button>
        </form>
    </div>
    <div id="mod-table"></div>
    <template id="mod-dialog-tpl">
        <form id="mod-form">
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
    var module = @json($module);
    var cols = @json($cols);
    var fields = @json($fields);
    var searchField = @json($search);
    var tableCols = [];
    tableCols.push({key: 'id', title: 'ID', width: 70});
    cols.forEach(function (c) {
        if (c === 'id') return;
        tableCols.push({key: c, title: c});
    });
    tableCols.push({
        title: '操作',
        cls: 'actions',
        html: function (row) {
            var html = '<a href="#" class="btn-link js-edit">编辑</a>';
            if (module === 'topics') html += '<a href="#" class="btn-link js-bind">绑片</a>';
            if (module === 'collect_tasks') html += '<a href="#" class="btn-link js-run">执行</a>';
            if (module === 'cj') html += '<a href="#" class="btn-link js-try">试跑</a><a href="#" class="btn-link js-import">入库</a>';
            if (module === 'playfails') html += '<a href="#" class="btn-link js-off">下线线路</a>';
            html += '<a href="#" class="btn-link js-del">删除</a>';
            return html;
        }
    });
    var table = U.table({ el: '#mod-table', url: '/admin/video/' + module + '/list', cols: tableCols });

    function open(row) {
        row = row || {};
        U.dialog({
            title: row.id ? '编辑' : '新增',
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
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
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
        var val = U.prompt(isInvite ? '数量,积分,会员ID' : '数量,积分', isInvite ? '10,0,0' : '10,100');
        if (val == null) return;
        var parts = String(val).split(/[,，\s]+/);
        var url = isInvite ? '/admin/video/invites/generate' : '/admin/video/cards/generate';
        var payload = isInvite
            ? {count: parts[0] || 10, points: parts[1] || 0, member_id: parts[2] || 0}
            : {count: parts[0] || 10, points: parts[1] || 100};
        U.post(url, payload).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) table.refresh();
        });
    });
    U.on('#mod-due', 'click', function () {
        U.loading(true);
        U.post('/admin/video/collect-due', {}).then(function (res) {
            U.loading(false);
            table.refresh();
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
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
                var val = U.prompt('影片ID，逗号分隔', ids);
                if (val == null) return;
                U.post('/admin/video/topics/' + row.id + '/videos', {video_ids: val}).then(function (r) {
                    U.toast((r && r.msg) || '完成', r && r.code === 0 ? 'ok' : 'err');
                });
            });
        }
        if (a.classList.contains('js-run')) {
            U.post('/admin/video/collect_tasks/run', {id: row.id}).then(function (r) {
                table.refresh();
                U.toast((r && r.msg) || '完成', r && r.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-try')) {
            U.loading(true);
            U.post('/admin/video/cj/try', {id: row.id}).then(function (r) {
                U.loading(false);
                if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
                var items = (r.data && r.data.items) ? r.data.items : [];
                var lines = items.map(function (it) { return (it.title || '') + ' ' + (it.url || ''); });
                U.dialog({
                    title: '试跑 ' + ((r.data && r.data.total) || items.length) + ' 条',
                    content: '<pre class="out">' + U.escape(lines.join('\n') || '没有匹配') + '</pre>',
                    hideOk: true
                });
            });
        }
        if (a.classList.contains('js-import')) {
            if (!U.confirm('确认按规则入库？已有同名影片会跳过。')) return;
            U.loading(true);
            U.post('/admin/video/cj/run', {id: row.id}).then(function (r) {
                U.loading(false);
                table.refresh();
                U.toast((r && r.msg) || '完成', r && r.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-off')) {
            if (!U.confirm('确认下线该失败记录关联的播放线路？')) return;
            U.post('/admin/video/playfails/offline', {id: row.id}).then(function (r) {
                table.refresh();
                U.toast((r && r.msg) || '完成', r && r.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (res && res.code === 0) { table.refresh(); U.toast('已删除', 'ok'); }
                else U.toast((res && res.msg) || '失败', 'err');
            });
        }
    });
})();
</script>
@endpush
