@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.cj'))

@php
    $desk = in_array((string) ($desk ?? ''), ['rules', 'form', 'logs'], true) ? (string) $desk : 'rules';
    $types = is_array($types ?? null) ? $types : [];
    $collector = is_array($collector ?? null) ? $collector : [];
    $recentLogs = is_array($recent_logs ?? null) ? $recent_logs : [];
    $type = (string) ($collector['type'] ?? 'html');
    if (! in_array($type, ['html', 'rss', 'json'], true)) {
        $type = 'html';
    }
    $into = (string) ($collector['into'] ?? 'vod');
    if (! in_array($into, ['vod', 'art', 'manga'], true)) {
        $into = 'vod';
    }
    $editId = (int) ($collector['id'] ?? 0);
    $vodTypes = is_array($vod_types ?? null) ? $vod_types : $types;
    $artTypes = is_array($art_types ?? null) ? $art_types : [];
    $mangaTypes = is_array($manga_types ?? null) ? $manga_types : [];
    $mangaReady = (bool) ($manga_ready ?? false);
    $catTypes = $into === 'art' ? $artTypes : ($into === 'manga' ? $mangaTypes : $vodTypes);
@endphp

@section('plain')
<div class="card card-panel cj-board desk-board" id="cj-board">
    <div class="card-header">
        <span>
            @if($desk === 'form')
                {{ $editId > 0 ? '编辑网站采集' : '新建网站采集' }}
            @else
                网站采集 <em id="cj-count"></em>
            @endif
        </span>
        <div>
            @if($desk === 'rules')
                <a class="btn btn-sm" href="/admin/video/cj?desk=form">新增</a>
            @elseif($desk === 'form')
                <a class="btn btn-muted btn-sm" href="/admin/video/cj">返回列表</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($desk !== 'form')
            <p class="muted recycle-lead">从网页、RSS 或 JSON 把内容采进影片、文章或漫画。不是资源站接口；日常接苹果 CMS 请用<a href="/admin/video/collects">采集源</a>。先试抓，标题对了再启用。漫画只会建作品条目，不会编造章节和图片。</p>
            <div class="queue-chips">
                <a class="chip{{ $desk === 'rules' ? ' active' : '' }}" href="/admin/video/cj">任务</a>
                <a class="chip{{ $desk === 'logs' ? ' active' : '' }}" href="/admin/video/cj?desk=logs">运行日志</a>
            </div>
        @endif

        @if($desk === 'form')
            <form class="collector-form" id="cj-form">
                <input type="hidden" name="id" value="{{ $editId > 0 ? $editId : '' }}">
                <input type="hidden" name="desk" value="form">

                <label for="cj-name">名称</label>
                <input id="cj-name" type="text" name="name" value="{{ $collector['name'] ?? '' }}" placeholder="如 某站更新列表" required autofocus>
                <p class="muted field-hint">后台列表里看到的名字。</p>

                <label>采集方式</label>
                <div class="collector-types" id="cj-types">
                    @foreach([
                        'html' => ['网站页面', '从列表页抓标题，可翻页、进详情取简介'],
                        'rss' => ['RSS 订阅', '填公开的 RSS / Atom 地址即可'],
                        'json' => ['JSON 接口', '按字段名从接口列表里取标题和链接'],
                    ] as $value => $meta)
                        <label class="collector-type{{ $type === $value ? ' is-on' : '' }}">
                            <input type="radio" name="type" value="{{ $value }}" @checked($type === $value)>
                            <strong>{{ $meta[0] }}</strong>
                            <span>{{ $meta[1] }}</span>
                        </label>
                    @endforeach
                </div>

                <label>写入到</label>
                <div class="collector-types" id="cj-into">
                    @foreach([
                        'vod' => ['影片', '进片库。有 m3u8 / mp4 才能当真播'],
                        'art' => ['文章', '进文章库，列表简介当正文'],
                        'manga' => ['漫画', $mangaReady ? '进漫画库，只建作品，不编造章节' : '漫画插件未启用'],
                    ] as $value => $meta)
                        <label class="collector-type{{ $into === $value ? ' is-on' : '' }}">
                            <input type="radio" name="into" value="{{ $value }}" @checked($into === $value) @disabled($value === 'manga' && ! $mangaReady)>
                            @if($value === 'manga' && ! $mangaReady && $into === 'manga')
                                <input type="hidden" name="into" value="manga">
                            @endif
                            <strong>{{ $meta[0] }}</strong>
                            <span>{{ $meta[1] }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="muted field-hint">三种库分开写。资源站接口请去采集源，不要用这里。</p>

                <label for="cj-url" id="cj-url-label">列表页地址</label>
                <input id="cj-url" type="url" name="source_url" value="{{ $collector['source_url'] ?? '' }}" placeholder="https://" required>
                <p class="muted field-hint" id="cj-url-hint">请只采集你有权使用的公开页面。详情页只跟同网站的链接。内网地址会拒绝。</p>

                <label for="cj-cat" id="cj-cat-label">写入分类</label>
                <select id="cj-cat" name="type_id">
                    <option value="0">不指定分类</option>
                    @foreach($catTypes as $cat)
                        <option value="{{ $cat['id'] }}" @selected((int) ($collector['type_id'] ?? 0) === (int) $cat['id'])>{{ $cat['name'] }}</option>
                    @endforeach
                </select>
                <p class="muted field-hint" id="cj-cat-hint">采进来会进这个分类，可之后再改。</p>

                <div class="collector-inline">
                    <div>
                        <label for="cj-interval">间隔（分钟）</label>
                        <input id="cj-interval" type="number" name="interval_minutes" value="{{ (int) ($collector['interval_minutes'] ?? 60) }}" min="1">
                    </div>
                    <div>
                        <label for="cj-limit">每次最多条数</label>
                        <input id="cj-limit" type="number" name="limit_items" value="{{ (int) ($collector['limit_items'] ?? 10) }}" min="1" max="100">
                    </div>
                </div>

                <label class="inline">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" @checked((int) ($collector['status'] ?? 1) === 1)>
                    启用，到点跟着到期采集跑
                </label>
                <label class="inline" id="cj-publish-wrap">
                    <input type="hidden" name="publish_immediately" value="0">
                    <input type="checkbox" name="publish_immediately" value="1" @checked((int) ($collector['publish_immediately'] ?? 0) === 1)>
                    <span id="cj-publish-label">有播放地址时直接上架（建议先下架核对）</span>
                </label>
                <p class="muted field-hint" id="cj-publish-hint">没有 m3u8 / mp4 / flv 地址的影片始终下架，勾了也不会假装能播。</p>

                <div id="cj-html-opts" class="collector-box" @if($type !== 'html') hidden @endif>
                    <h3>网页怎么取</h3>
                    <p class="muted field-hint">在浏览器里右键「检查」，把列表里每一条的标签填进来。常见写法：<code>article</code>、<code>li.item</code>、<code>h2 a</code>。</p>
                    <label for="item_selector">列表条目</label>
                    <input id="item_selector" type="text" name="item_selector" value="{{ $collector['item_selector'] ?? '' }}" placeholder="article.post">
                    <p class="muted field-hint">圈出「一条内容」的那块，不是整页。</p>

                    <label for="link_selector">标题链接</label>
                    <input id="link_selector" type="text" name="link_selector" value="{{ $collector['link_selector'] ?? 'a' }}" placeholder="h2 a">
                    <p class="muted field-hint">相对上面这一条。默认取第一条链接。</p>

                    <label for="title_selector">标题（可空）</label>
                    <input id="title_selector" type="text" name="title_selector" value="{{ $collector['title_selector'] ?? '' }}" placeholder="h2 a">

                    <label for="summary_selector">简介（可空）</label>
                    <input id="summary_selector" type="text" name="summary_selector" value="{{ $collector['summary_selector'] ?? '' }}" placeholder=".excerpt">

                    <label for="cover_selector">封面图（可空）</label>
                    <input id="cover_selector" type="text" name="cover_selector" value="{{ $collector['cover_selector'] ?? '' }}" placeholder="img">

                    <div class="cj-play-only">
                    <label for="play_selector">播放地址（可空）</label>
                    <input id="play_selector" type="text" name="play_selector" value="{{ $collector['play_selector'] ?? '' }}" placeholder="a.play">
                    <p class="muted field-hint">列表里的 m3u8 / mp4。留空则看标题链接是不是播放地址。文章和漫画用不上。</p>
                    </div>

                    <label for="detail_content_selector">详情简介（可空）</label>
                    <input id="detail_content_selector" type="text" name="detail_content_selector" value="{{ $collector['detail_content_selector'] ?? '' }}" placeholder="article.body">
                    <p class="muted field-hint">填写后会再打开每条链接取简介，只跟同网站，失败时用列表上的简介。</p>

                    <h3>翻页</h3>
                    <label for="page_count">最多翻几页</label>
                    <input id="page_count" type="number" name="page_count" value="{{ (int) ($collector['page_count'] ?? 1) }}" min="1" max="10">
                    <p class="muted field-hint">1 表示只抓当前列表页。最多 10 页，避免把整站爬下来。</p>

                    <label for="page_url">翻页地址（可空）</label>
                    <input id="page_url" type="text" name="page_url" value="{{ $collector['page_url'] ?? '' }}" placeholder="https://example.com/list?page={page}">
                    <p class="muted field-hint">用 <code>{page}</code> 代表页码。留空则在列表地址后加 <code>page=2</code>。必须与列表页同网站。</p>

                    <label for="next_selector">下一页按钮（可空）</label>
                    <input id="next_selector" type="text" name="next_selector" value="{{ $collector['next_selector'] ?? '' }}" placeholder="a.next">
                    <p class="muted field-hint">填写后按「下一页」链接走，不再用上面的页码地址。同样只跟同网站。</p>
                </div>

                <div id="cj-json-opts" class="collector-box" @if($type !== 'json') hidden @endif>
                    <h3>接口字段</h3>
                    <label>列表路径</label>
                    <input type="text" name="list_path" value="{{ $collector['list_path'] ?? 'items' }}">
                    <p class="muted field-hint">数据在哪一层。根数组填 <code>.</code>。</p>
                    <label>标题字段</label>
                    <input type="text" name="title_key" value="{{ $collector['title_key'] ?? 'title' }}">
                    <label>链接字段</label>
                    <input type="text" name="link_key" value="{{ $collector['link_key'] ?? 'url' }}">
                    <label>简介字段</label>
                    <input type="text" name="summary_key" value="{{ $collector['summary_key'] ?? 'summary' }}">
                    <label>正文字段</label>
                    <input type="text" name="content_key" value="{{ $collector['content_key'] ?? 'content' }}">
                    <label>去重字段</label>
                    <input type="text" name="guid_key" value="{{ $collector['guid_key'] ?? 'id' }}">
                    <label>封面字段</label>
                    <input type="text" name="cover_key" value="{{ $collector['cover_key'] ?? 'cover' }}">
                    <div class="cj-play-only">
                    <label>播放地址字段</label>
                    <input type="text" name="play_key" value="{{ $collector['play_key'] ?? 'play' }}">
                    <p class="muted field-hint">播放地址要是 http(s) 的 m3u8 / mp4。对不上就只建下架影片。文章和漫画忽略这项。</p>
                    </div>
                </div>

                <div id="cj-preview" class="collector-preview" hidden>
                    <p class="collector-preview-msg muted"></p>
                    <ol class="collector-preview-list"></ol>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit" id="cj-save-btn">{{ $editId > 0 ? '保存' : '添加' }}</button>
                    <button class="btn btn-muted" type="button" id="cj-preview-btn">试抓</button>
                    <a class="btn btn-muted" href="/admin/video/cj">取消</a>
                </div>
            </form>

            @if($recentLogs !== [])
                <h3 style="margin-top:28px">最近几次</h3>
                <table class="data">
                    <thead><tr><th>时间</th><th>结果</th><th>说明</th></tr></thead>
                    <tbody>
                    @foreach($recentLogs as $log)
                        <tr>
                            <td>{{ $log['created_at'] ?? '' }}</td>
                            <td>
                                @if(($log['status'] ?? '') === 'ok')
                                    <span class="badge badge-ok">成功</span>
                                @elseif(($log['status'] ?? '') === 'fail')
                                    <span class="badge badge-warn">失败</span>
                                @else
                                    <span class="badge">{{ $log['status'] ?? '' }}</span>
                                @endif
                            </td>
                            <td class="muted">{{ $log['message'] ?? '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @else
            <form class="filter-bar" id="cj-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="{{ $desk }}">
                <input type="search" name="q" placeholder="{{ $desk === 'logs' ? '搜说明、规则编号' : '搜名称、地址' }}" autocomplete="off">
                @if($desk === 'rules')
                    <select name="type" aria-label="方式">
                        <option value="">全部方式</option>
                        <option value="html">网站页面</option>
                        <option value="rss">RSS 订阅</option>
                        <option value="json">JSON 接口</option>
                    </select>
                    <select name="status" aria-label="状态">
                        <option value="">全部状态</option>
                        <option value="1">启用</option>
                        <option value="0">停用</option>
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="cj-search-btn">查询</button>
                <button type="reset" class="btn btn-muted btn-sm" id="cj-reset-btn">重置</button>
            </form>
            <div id="cj-table" class="desk-table"></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    if (desk === 'form') {
        var form = document.getElementById('cj-form');
        var types = document.getElementById('cj-types');
        var jsonOpts = document.getElementById('cj-json-opts');
        var htmlOpts = document.getElementById('cj-html-opts');
        var urlLabel = document.getElementById('cj-url-label');
        var urlHint = document.getElementById('cj-url-hint');
        var previewBox = document.getElementById('cj-preview');
        var previewMsg = previewBox ? previewBox.querySelector('.collector-preview-msg') : null;
        var previewList = previewBox ? previewBox.querySelector('.collector-preview-list') : null;
        var previewBtn = document.getElementById('cj-preview-btn');
        var intoBox = document.getElementById('cj-into');
        var catSel = document.getElementById('cj-cat');
        var catHint = document.getElementById('cj-cat-hint');
        var publishLabel = document.getElementById('cj-publish-label');
        var publishHint = document.getElementById('cj-publish-hint');
        var catalogs = {
            vod: @json($vodTypes),
            art: @json($artTypes),
            manga: @json($mangaTypes)
        };
        var intoHints = {
            vod: ['有播放地址时直接上架（建议先下架核对）', '没有 m3u8 / mp4 / flv 地址的影片始终下架，勾了也不会假装能播。', '采进来的片会进这个分类，可在影片里再改。'],
            art: ['直接发布文章（建议先核对正文）', '文章没有播放地址。不勾则保持未发布。', '采进来的文章会进这个分类。'],
            manga: ['直接上架作品（建议先核对）', '只会建漫画作品（标题、封面、简介），不会根据网页编造章节和图片。', '采进来的作品会进这个漫画分类。']
        };
        var selectedInto = function () {
            var on = form && form.querySelector('input[name="into"]:checked');
            return (on && on.value) || 'vod';
        };
        var fillCats = function (into, keepId) {
            if (!catSel) return;
            var rows = catalogs[into] || [];
            var html = '<option value="0">不指定分类</option>';
            rows.forEach(function (row) {
                html += '<option value="' + U.escape(String(row.id)) + '"' + (String(row.id) === String(keepId) ? ' selected' : '') + '>' + U.escape(row.name || '') + '</option>';
            });
            catSel.innerHTML = html;
        };
        var applyInto = function () {
            var into = selectedInto();
            if (intoBox) {
                intoBox.querySelectorAll('.collector-type').forEach(function (el) {
                    var input = el.querySelector('input[type="radio"]');
                    el.classList.toggle('is-on', input && input.value === into);
                });
            }
            document.querySelectorAll('.cj-play-only').forEach(function (el) {
                el.hidden = into !== 'vod';
            });
            var pack = intoHints[into] || intoHints.vod;
            if (publishLabel) publishLabel.textContent = pack[0];
            if (publishHint) publishHint.textContent = pack[1];
            if (catHint) catHint.textContent = pack[2];
            fillCats(into, catSel ? catSel.value : '0');
        };
        var labels = {
            html: ['列表页地址', '请只采集你有权使用的公开页面。详情页只跟同网站的链接。内网地址会拒绝。'],
            rss: ['订阅地址', '公开的 RSS / Atom 地址，例如 https://example.com/feed.xml'],
            json: ['接口地址', '返回列表的 JSON 地址。按下面的字段名取标题和链接。']
        };
        var selectedType = function () {
            return (form && form.querySelector('input[name="type"]:checked') || {}).value || 'html';
        };
        var apply = function () {
            var t = selectedType();
            if (jsonOpts) jsonOpts.hidden = t !== 'json';
            if (htmlOpts) htmlOpts.hidden = t !== 'html';
            if (types) {
                types.querySelectorAll('.collector-type').forEach(function (el) {
                    var input = el.querySelector('input');
                    el.classList.toggle('is-on', input && input.value === t);
                });
            }
            if (urlLabel && labels[t]) urlLabel.textContent = labels[t][0];
            if (urlHint && labels[t]) urlHint.textContent = labels[t][1];
        };
        if (types) types.addEventListener('change', apply);
        if (intoBox) intoBox.addEventListener('change', applyInto);
        apply();
        applyInto();
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var data = U.formData(form);
                data.desk = 'form';
                U.post('/admin/video/cj/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                    U.toast(res.msg || '已保存', 'ok');
                    location.href = '/admin/video/cj';
                });
            });
        }
        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                if (!form || !previewBox) return;
                previewBtn.disabled = true;
                previewBox.hidden = false;
                if (previewMsg) previewMsg.textContent = '正在抓取…';
                if (previewList) previewList.innerHTML = '';
                U.post('/admin/video/cj/try', U.formData(form)).then(function (res) {
                    if (previewMsg) previewMsg.textContent = (res && res.msg) || '试抓失败';
                    var items = (res && res.data && res.data.items) || [];
                    items.forEach(function (item) {
                        var li = document.createElement('li');
                        var title = document.createElement(item.link ? 'a' : 'span');
                        title.textContent = item.title || '无标题';
                        if (item.link) {
                            title.href = item.link;
                            title.target = '_blank';
                            title.rel = 'noopener';
                        }
                        li.appendChild(title);
                        if (item.summary) {
                            var s = document.createElement('div');
                            s.className = 'muted';
                            s.textContent = item.summary;
                            li.appendChild(s);
                        }
                        previewList.appendChild(li);
                    });
                    previewBtn.disabled = false;
                }).catch(function () {
                    if (previewMsg) previewMsg.textContent = '试抓失败，请检查地址和选择器。';
                    previewBtn.disabled = false;
                });
            });
        }
        return;
    }

    var form = document.getElementById('cj-search');
    var countEl = document.getElementById('cj-count');
    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        var data = cleanWhere(U.formData(form));
        data.limit = 20;
        data.desk = desk;
        return data;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'limit' || k === 'desk') return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="cj-empty-reset">清除筛选</button></p></div>';
        }
        if (desk === 'logs') {
            return '<div class="list-empty"><p>还没有运行记录</p><p class="muted">保存任务后点立即采集，这里会记下每次拉了多少、新建了多少。</p></div>';
        }
        return '<div class="list-empty"><p>还没有网站采集</p><p class="muted">从网页、RSS 或接口把内容采进影片、文章或漫画。先试抓，确认标题对了再启用。</p><p><a class="btn btn-sm" href="/admin/video/cj?desk=form">新建网站采集</a></p></div>';
    }
    var cols = [];
    if (desk === 'logs') {
        cols = [
            {title: '采集源', html: function (d) {
                return '<a href="/admin/video/cj?desk=form&id=' + U.escape(String(d.rule_id || '')) + '">' + U.escape(d.rule_name || '未命名') + '</a>';
            }},
            {title: '结果', width: 80, html: function (d) {
                return d.status === 'ok' ? U.status(true, '成功') : U.status(false, d.status_label || '失败');
            }},
            {title: '说明', html: function (d) { return '<span class="muted">' + U.escape(d.message || '') + '</span>'; }},
            {title: '时间', width: 130, html: function (d) { return U.escape(d.created_at || ''); }}
        ];
    } else {
        cols = [
            {title: '任务', html: function (d) {
                var html = '<div><a href="/admin/video/cj?desk=form&id=' + U.escape(String(d.id || '')) + '">' + U.escape(d.name || '未命名') + '</a>';
                html += ' <span class="badge">' + U.escape(d.type_label || '') + '</span>';
                if (String(d.status) !== '1') html += ' <span class="badge badge-off">停用</span>';
                if (String(d.status) === '1' && d.last_status === 'fail') html += ' <span class="badge badge-warn">上次失败</span>';
                html += '</div>';
                html += '<div class="muted">每 ' + U.escape(String(d.interval_minutes || 60)) + ' 分钟 · 每次最多 ' + U.escape(String(d.limit_items || 10)) + ' 条</div>';
                return html;
            }},
            {title: '写入', width: 160, html: function (d) {
                var into = U.escape(d.into_label || '影片');
                var cat = U.escape(d.type_name || '不指定分类');
                return into + ' · ' + cat;
            }},
            {title: '上次', width: 160, html: function (d) {
                var html = U.escape(d.last_run_text || '还没跑过');
                if (d.last_message) html += '<div class="muted">' + U.escape(String(d.last_message).slice(0, 36)) + '</div>';
                return html;
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var on = String(d.status) === '1';
                return '<a href="#" class="btn-link js-run">立即采集</a>'
                    + '<a href="#" class="btn-link js-toggle">' + (on ? '停用' : '启用') + '</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }
    var table = U.table({
        el: '#cj-table',
        url: '/admin/video/cj/list',
        where: queryWhere(),
        cols: cols,
        emptyHtml: emptyHtml,
        onDraw: function (_w, _list, parsed) {
            if (countEl) countEl.textContent = parsed && parsed.total ? ('共 ' + parsed.total + ' 个') : '';
        }
    });
    U.on('#cj-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#cj-reset-btn', 'click', function () {
        if (form) form.reset();
        table.reload(queryWhere());
    });
    U.on('#cj-table', 'click', function (e) {
        if (e.target && e.target.id === 'cj-empty-reset') {
            if (form) form.reset();
            table.reload(queryWhere());
            return;
        }
        var row = U.rowFromClick(e, table);
        if (!row) return;
        var a = e.target.closest && e.target.closest('a');
        if (!a) return;
        if (a.classList.contains('js-run')) {
            e.preventDefault();
            U.post('/admin/video/cj/run', {id: row.id}).then(function (res) {
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
                if (res && res.code === 0) table.refresh();
            });
        }
        if (a.classList.contains('js-toggle')) {
            e.preventDefault();
            U.post('/admin/video/cj/toggle', {id: row.id}).then(function (res) {
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
                if (res && res.code === 0) table.refresh();
            });
        }
        if (a.classList.contains('js-del')) {
            e.preventDefault();
            if (!confirm('确定删除「' + (row.name || '未命名') + '」？已经采进来的内容还在。')) return;
            U.post('/admin/video/cj/delete', {id: row.id, desk: 'rules'}).then(function (res) {
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
                if (res && res.code === 0) table.refresh();
            });
        }
    });
})();
</script>
@endpush
