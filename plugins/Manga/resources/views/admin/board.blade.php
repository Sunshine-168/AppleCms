@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.manga'))

@php
    $desk = in_array((string) ($desk ?? ''), ['pending', 'types', 'chapters', 'pics', 'comments', 'works', 'work', 'stats'], true)
        ? (string) $desk
        : 'works';
    $types = is_array($types ?? null) ? $types : [];
    $works = is_array($works ?? null) ? $works : [];
    $filterMangaId = (int) ($filterMangaId ?? 0);
    $filterMangaTitle = (string) ($filterMangaTitle ?? '');
    $filterTypeId = (int) ($filterTypeId ?? 0);
    $filterTagId = (int) ($filterTagId ?? 0);
    $tags = is_array($tags ?? null) ? $tags : [];
    $filterTag = is_array($filterTag ?? null) ? $filterTag : null;
    $hint = (string) ($hint ?? '');
    $work = is_array($work ?? null) ? $work : null;
    $stats = is_array($stats ?? null) ? $stats : [];
    $worksStat = is_array($stats['works'] ?? null) ? $stats['works'] : ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0];
    $topHits = is_array($stats['top_hits'] ?? null) ? $stats['top_hits'] : [];
    $topFavors = is_array($stats['top_favors'] ?? null) ? $stats['top_favors'] : [];
    $daily = is_array($stats['daily'] ?? null) ? $stats['daily'] : [];
@endphp

@section('plain')
<div class="card card-panel manga-board desk-board" id="manga-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? '漫画统计' : ($desk === 'work' ? '作品工作台' : '漫画') }} <em id="manga-count"></em></span>
        <div>
            @if(in_array($desk, ['works', 'pending'], true))
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-tags">标签</a>
            @endif
            @if(! in_array($desk, ['stats'], true))
                <button type="button" class="btn btn-sm" id="manga-add-btn">新增</button>
            @endif
            @if($desk === 'work' && $work)
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas">返回作品</a>
                <a class="btn btn-muted btn-sm" href="{{ $work['front_url'] }}" target="_blank" rel="noopener">前台预览</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($desk === 'types')
                这是漫画自己的分类，不是影片分类。下级会缩进。点「添加下级」挂到这一栏下面；有作品时请先移走再删。关掉插件后前台 /manga 一起消失。
            @else
                独立漫画库，不是影片分类。点作品「管理」进工作台管章节和图片。关掉插件后前台 /manga 一起消失。采集待审等见「参数」。资源接口：<code>/api/provide/manga</code>。
            @endif
        </p>

        @if($desk === 'stats')
            <p class="muted recycle-lead">阅读次数来自会员阅读历史；章节更新来自章节创建时间。人气为作品 hits。</p>
            <div class="stat-grid dash" style="margin:12px 0 20px">
                <div class="stat-card">
                    <em>今日阅读</em>
                    <strong>{{ (int) ($stats['today_reads'] ?? 0) }}</strong>
                    <span class="muted">会员续看记录</span>
                </div>
                <div class="stat-card">
                    <em>近 7 日阅读</em>
                    <strong>{{ (int) ($stats['week_reads'] ?? 0) }}</strong>
                    <span class="muted">近 30 日 {{ (int) ($stats['month_reads'] ?? 0) }}</span>
                </div>
                <div class="stat-card">
                    <em>近 7 日新章</em>
                    <strong>{{ (int) ($stats['week_chapters'] ?? 0) }}</strong>
                    <span class="muted">近 30 日 {{ (int) ($stats['month_chapters'] ?? 0) }}</span>
                </div>
                <div class="stat-card">
                    <em>上架作品</em>
                    <strong>{{ (int) ($worksStat['show'] ?? 0) }}</strong>
                    <span class="muted">共 {{ (int) ($worksStat['all'] ?? 0) }} · 待审 {{ (int) ($worksStat['pending'] ?? 0) }}</span>
                </div>
            </div>
            <div class="stat-grid dash" style="margin:0 0 20px">
                <div class="stat-card">
                    <em>待审</em>
                    <strong>{{ (int) ($worksStat['pending'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>下架</em>
                    <strong>{{ (int) ($worksStat['off'] ?? 0) }}</strong>
                </div>
            </div>
            <div class="flink-stats-split" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:8px">
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">人气 TOP</h3>
                    @if($topHits === [])
                        <p class="muted">还没有作品。</p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>作品</th><th>人气</th></tr></thead>
                                <tbody>
                                @foreach($topHits as $row)
                                    <tr>
                                        <td><a href="/admin/video/mangas?desk=work&manga_id={{ (int) $row['id'] }}">{{ $row['title'] }}</a></td>
                                        <td>{{ (int) $row['hits'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">收藏 TOP</h3>
                    @if($topFavors === [])
                        <p class="muted">还没有书架收藏。</p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>作品</th><th>收藏</th></tr></thead>
                                <tbody>
                                @foreach($topFavors as $row)
                                    <tr>
                                        <td><a href="/admin/video/mangas?desk=work&manga_id={{ (int) $row['id'] }}">{{ $row['title'] }}</a></td>
                                        <td>{{ (int) $row['favors'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <h3 style="font-size:15px;margin:20px 0 10px">近 14 日趋势</h3>
            @if($daily === [])
                <p class="muted">暂无数据。</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>日期</th><th>阅读</th><th>新章</th></tr></thead>
                        <tbody>
                        @foreach(array_reverse($daily) as $row)
                            <tr>
                                <td>{{ $row['day'] }}</td>
                                <td>{{ (int) $row['reads'] }}</td>
                                <td>{{ (int) $row['chapters'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            @if($desk === 'work' && $work)
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:12px;align-items:center">
                    <strong>{{ $work['title'] }}</strong>
                    <span class="muted">#{{ $work['id'] }}</span>
                    <span>{{ $work['serialize_label'] }}</span>
                    <span class="muted">{{ (int) $work['chapter_count'] }} 话 · 人气 {{ (int) $work['hits'] }}</span>
                    @if(! empty($work['author']))
                        <span class="muted">作者 {{ $work['author'] }}</span>
                    @endif
                    <button type="button" class="btn btn-sm" id="manga-edit-work">编辑作品</button>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=pics&manga_id={{ $work['id'] }}">图片明细</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=comments&manga_id={{ $work['id'] }}">评论</a>
                </div>
                <p class="muted" style="margin:0 0 12px">下方管理本章节。新增时可粘贴多行图片地址；保存后写入图片表。</p>
            @endif
            @if($filterMangaId > 0 && in_array($desk, ['chapters', 'pics', 'comments'], true))
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                    <span>正在看作品</span>
                    <strong>{{ $filterMangaTitle !== '' ? $filterMangaTitle : ('#'.$filterMangaId) }}</strong>
                    <a class="btn btn-sm" href="/admin/video/mangas?desk=work&manga_id={{ $filterMangaId }}">作品工作台</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas">返回作品</a>
                    <a class="btn btn-muted btn-sm" href="/manga/{{ $filterMangaId }}" target="_blank" rel="noopener">前台预览</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk={{ $desk }}">清除作品筛选</a>
                </div>
            @endif
            @if($desk !== 'stats')
        <form class="filter-bar" id="manga-search" onsubmit="return false;">
            <input type="hidden" name="yid" value="{{ $desk === 'pending' ? '1' : ($desk === 'works' ? '0' : '') }}">
            <input type="hidden" name="manga_id" value="{{ ($desk === 'work' || $filterMangaId > 0) ? ($desk === 'work' && $work ? (int) $work['id'] : $filterMangaId) : '' }}">
            <input type="search" name="q" placeholder="{{ $desk === 'types' ? '搜分类名' : (in_array($desk, ['chapters', 'work'], true) ? '搜章节' : ($desk === 'pics' ? '搜图片地址' : ($desk === 'comments' ? '搜评论、作品' : '搜名称、作者、标签'))) }}" autocomplete="off">
            @if(in_array($desk, ['works', 'pending'], true) && $types !== [])
                <select name="type_id" aria-label="分类">
                    <option value="">全部分类</option>
                    @foreach($types as $type)
                        <option value="{{ $type['id'] }}" @selected($filterTypeId === (int) $type['id'])>{{ $type['label'] ?? $type['name'] }}</option>
                    @endforeach
                </select>
            @endif
            @if(in_array($desk, ['works', 'pending'], true) && $tags !== [])
                <select name="tag_id" aria-label="标签">
                    <option value="">全部标签</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag['id'] }}" @selected($filterTagId === (int) $tag['id'])>{{ $tag['name'] }}</option>
                    @endforeach
                </select>
            @endif
            @if(in_array($desk, ['works', 'pending'], true))
                <select name="serialize" aria-label="连载">
                    <option value="">全部状态</option>
                    <option value="0">连载</option>
                    <option value="1">完结</option>
                </select>
                <select name="recommend" aria-label="推荐">
                    <option value="">全部</option>
                    <option value="1">推荐</option>
                </select>
            @endif
            @if($desk === 'comments')
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="1">显示</option>
                    <option value="0">待审</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="manga-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-reset-btn">重置</button>
        </form>
        @if($filterTag && in_array($desk, ['works', 'pending'], true))
            <div class="queue-chips" style="margin:8px 0 0">
                <a class="chip active" href="/admin/video/mangas{{ $desk === 'pending' ? '?desk=pending' : '' }}">标签 {{ $filterTag['name'] }} ×</a>
            </div>
        @endif
        @if(in_array($desk, ['works', 'pending', 'comments', 'chapters', 'pics', 'work', 'types'], true))
            <div class="batch-bar" id="manga-batch" hidden>
                <strong id="manga-batch-count">已选 0 条</strong>
                @if(in_array($desk, ['works', 'pending'], true))
                    <button type="button" class="btn btn-sm" id="manga-batch-on">上架</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">下架</button>
                    <button type="button" class="btn btn-sm" id="manga-batch-pass">通过审核</button>
                    <button type="button" class="btn btn-sm" id="manga-batch-rec">推荐</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-unrec">取消推荐</button>
                @endif
                @if($desk === 'comments')
                    <button type="button" class="btn btn-sm" id="manga-batch-on">通过</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">隐藏</button>
                @endif
                @if($desk === 'types')
                    <button type="button" class="btn btn-sm" id="manga-batch-on">启用</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">禁用</button>
                    <select id="manga-batch-parent" class="batch-select"><option value="">改到上级</option></select>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-move">移动</button>
                @endif
                <button type="button" class="btn btn-danger btn-sm" id="manga-batch-del">删除</button>
                <button type="button" class="btn btn-muted btn-sm" id="manga-batch-clear">取消选择</button>
            </div>
        @endif
        <div id="manga-table"></div>
            @endif
        @endif
    </div>
</div>

<template id="manga-work-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="title" required>
        <label>分类</label>
        <select name="type_id">
            <option value="0">未分类</option>
            @foreach($types as $type)
                <option value="{{ $type['id'] }}">{{ $type['label'] ?? $type['name'] }}</option>
            @endforeach
        </select>
        <label>作者</label>
        <input type="text" name="author">
        <label>封面</label>
        <div class="field-inline">
            <input type="text" name="cover" placeholder="图片地址">
            <button type="button" class="btn btn-sm js-cover-pick">上传</button>
        </div>
        <img class="img-preview js-cover-preview" alt="">
        <label>连载</label>
        <select name="serialize">
            <option value="0">连载</option>
            <option value="1">完结</option>
        </select>
        <label>标签</label>
        <input type="text" name="tags" placeholder="多个用逗号分隔">
        <label>推荐</label>
        <select name="recommend">
            <option value="0">否</option>
            <option value="1">是</option>
        </select>
        <label>审核</label>
        <select name="yid">
            <option value="0">已审</option>
            <option value="1">待审</option>
        </select>
        <label>状态</label>
        <select name="status">
            <option value="1">上架</option>
            <option value="0">下架</option>
        </select>
        <label>备注</label>
        <input type="text" name="remarks">
        <label>简介</label>
        <textarea name="content"></textarea>
        <label>人气</label>
        <input type="number" name="hits" value="0">
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>

<template id="manga-type-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" required>
        <label>上级</label>
        <select name="parent_id">
            <option value="0">顶级</option>
        </select>
        <p class="muted field-hint">选已有分类作上级。下级会缩进显示。不要选自己或自己的下级。</p>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">禁用</option>
        </select>
    </form>
</template>

<template id="manga-chapter-tpl">
    <form>
        <input type="hidden" name="id">
        <label>作品</label>
        <select name="manga_id" required>
            <option value="">选择作品</option>
            @foreach($works as $work)
                <option value="{{ $work['id'] }}">{{ $work['title'] }} (#{{ $work['id'] }})</option>
            @endforeach
        </select>
        <label>章节名</label>
        <input type="text" name="name" required>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>VIP 锁章</label>
        <select name="vip">
            <option value="0">免费</option>
            <option value="1">VIP 可读</option>
        </select>
        <p class="muted field-hint">VIP 章节按站点设置 <code>manga_vip_group_ids</code> / 试看页数 <code>manga_trysee_pages</code> 控制。</p>
        <label>图片地址</label>
        <textarea name="pics" rows="8" placeholder="每行一条，http(s) 或 / 开头的站内路径"></textarea>
        <div class="field-inline" style="margin-top:8px">
            <button type="button" class="btn btn-sm js-pics-upload">上传并追加</button>
        </div>
        <img class="img-preview js-pics-preview" alt="">
        <p class="muted field-hint">图片地址每行一条。可上传追加。保存后会写入图片表。javascript: 不会收录。</p>
    </form>
</template>

<template id="manga-pic-tpl">
    <form>
        <input type="hidden" name="id">
        <label>作品</label>
        <select name="manga_id" required>
            <option value="">选择作品</option>
            @foreach($works as $work)
                <option value="{{ $work['id'] }}">{{ $work['title'] }} (#{{ $work['id'] }})</option>
            @endforeach
        </select>
        <label>章节ID</label>
        <input type="number" name="chapter_id" required>
        <label>图片地址</label>
        <div class="field-inline">
            <input type="text" name="url" required placeholder="http(s) 或 / 开头">
            <button type="button" class="btn btn-sm js-pic-pick">上传</button>
        </div>
        <img class="img-preview js-pic-preview" alt="">
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>

<template id="manga-comment-tpl">
    <form>
        <input type="hidden" name="id">
        <label>作品</label>
        <select name="manga_id" required>
            <option value="">选择作品</option>
            @foreach($works as $work)
                <option value="{{ $work['id'] }}">{{ $work['title'] }} (#{{ $work['id'] }})</option>
            @endforeach
        </select>
        <label>昵称</label>
        <input type="text" name="author_name">
        <label>内容</label>
        <textarea name="content" rows="4" required></textarea>
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">待审 / 隐藏</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    var filterMangaId = @json($filterMangaId);
    var workPayload = @json($work);
    if (desk === 'stats') {
        return;
    }
    var form = document.getElementById('manga-search');
    var countEl = document.getElementById('manga-count');
    var addBtn = document.getElementById('manga-add-btn');
    if (desk === 'work' && workPayload && workPayload.id) {
        filterMangaId = Number(workPayload.id) || filterMangaId;
    }

    var modules = {
        works: 'mangas',
        pending: 'mangas',
        types: 'manga_types',
        chapters: 'manga_chapters',
        work: 'manga_chapters',
        pics: 'manga_pics',
        comments: 'manga_comments'
    };
    var module = modules[desk] || 'mangas';
    var addLabels = {
        works: '新增作品',
        pending: '新增作品',
        types: '新增分类',
        chapters: '新增章节',
        work: '新增章节',
        pics: '新增图片',
        comments: '新增评论'
    };
    if (addBtn) addBtn.textContent = addLabels[desk] || '新增';

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        var data = cleanWhere(U.formData(form));
        data.limit = desk === 'types' ? 500 : 20;
        if (desk === 'pending') data.yid = 1;
        if (desk === 'works') data.yid = 0;
        if (desk === 'work' && filterMangaId) data.manga_id = filterMangaId;
        return data;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'limit' || k === 'yid' || (desk === 'work' && k === 'manga_id')) return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            var miss = desk === 'types' ? '没有符合名称的分类' : '没有符合条件的记录';
            return '<div class="list-empty"><p>' + miss + '</p><p><button type="button" class="btn btn-muted btn-sm" id="manga-empty-reset">清除筛选</button></p></div>';
        }
        if (desk === 'types') {
            return '<div class="list-empty"><p>还没有分类。</p><p class="muted">分类是漫画的目录。先建一级，再在它下面「添加下级」做多级。</p><p><button type="button" class="btn btn-primary btn-sm" id="manga-empty-add">新增分类</button></p></div>';
        }
        var copy = {
            works: ['还没有漫画作品', '新增作品'],
            pending: ['没有待审作品', '新增作品'],
            chapters: ['还没有章节', '新增章节'],
            work: ['这部还没有章节', '新增章节'],
            pics: ['还没有图片', '新增图片'],
            comments: ['还没有评论', '新增评论']
        }[desk] || ['还没有记录', '新增'];
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="manga-empty-add">' + copy[1] + '</button></p></div>';
    }

    function fillParentSelect(sel, excludeId, selected, placeholder) {
        if (!sel) return;
        excludeId = parseInt(excludeId, 10) || 0;
        var skip = {};
        if (excludeId) skip[excludeId] = true;
        var rows = table.rows() || [];
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            var pid = parseInt(r.parent_id, 10) || 0;
            if (skip[pid]) skip[id] = true;
        });
        var html = placeholder ? '<option value="">' + U.escape(placeholder) + '</option>' : '';
        html += '<option value="0">顶级</option>';
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            if (skip[id]) return;
            var pad = '';
            var d = parseInt(r.depth, 10) || 0;
            while (d-- > 0) pad += '└ ';
            html += '<option value="' + U.escape(r.id) + '">' + pad + U.escape(r.name || '') + '</option>';
        });
        sel.innerHTML = html;
        if (placeholder && (selected === '' || selected == null)) {
            sel.value = '';
            return;
        }
        sel.value = selected == null || selected === '' ? '0' : String(selected);
    }
    function fillBatchParent() {
        fillParentSelect(document.getElementById('manga-batch-parent'), 0, '', '改到上级');
    }
    function typeNameHtml(d) {
        var depth = parseInt(d.depth, 10) || 0;
        var branch = depth > 0 ? '<span class="cat-branch">└</span>' : '';
        var n = parseInt(d.manga_count, 10) || 0;
        var meta = '#' + U.escape(d.id);
        if (n > 0) {
            meta += ' · <a href="/admin/video/mangas?type_id=' + encodeURIComponent(d.id) + '">' + U.escape(String(n)) + ' 部</a>';
        } else {
            meta += ' · 0 部';
        }
        if (parseInt(d.child_count, 10) > 0) meta += ' · ' + U.escape(d.child_count) + ' 个子类';
        return '<div class="cat-cell" style="padding-left:' + (depth * 22) + 'px">' + branch
            + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    function workStatus(d) {
        var st = String(d.status) === '1' ? '上架' : '下架';
        var yid = d.yid_label || (String(d.yid) === '1' ? '待审' : '已审');
        return U.escape(st + ' / ' + yid);
    }

    var cols = [];
    if (desk === 'works' || desk === 'pending') {
        cols = [
            {check: true, width: 36},
            {title: '名称', html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.title || '未填写') + '</a>';
            }},
            {title: '分类', html: function (d) { return U.escape(d.type_name || '未分类'); }},
            {title: '作者', html: function (d) { return U.escape(d.author || ''); }},
            {title: '连载', width: 72, html: function (d) { return U.escape(d.serialize_label || ''); }},
            {title: '状态/待审', width: 110, html: workStatus},
            {title: '章节数', width: 72, html: function (d) { return U.escape(String(d.chapter_count == null ? 0 : d.chapter_count)); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id || '');
                return '<a href="/admin/video/mangas?desk=work&manga_id=' + id + '" class="btn-link">管理</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="' + U.escape(d.front_url || ('/manga/' + id)) + '" class="btn-link" target="_blank" rel="noopener">前台</a>';
            }}
        ];
    } else if (desk === 'comments') {
        cols = [
            {check: true, width: 36},
            {title: '内容', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.content || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '昵称', width: 100, html: function (d) { return U.escape(d.author_name || ''); }},
            {title: '状态', width: 90, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '待审');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'types') {
        cols = [
            {check: true, width: 36},
            {title: '分类', html: typeNameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id || '');
                return '<a href="#" class="btn-link js-child">添加下级</a>'
                    + '<a href="/admin/video/mangas?type_id=' + id + '" class="btn-link">作品</a>'
                    + (String(d.status) === '1' ? '<a href="/manga?type=' + id + '" class="btn-link" target="_blank" rel="noopener">前台</a>' : '')
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'chapters' || desk === 'work') {
        cols = [
            {check: true, width: 36},
            {title: '章节', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: 'VIP', width: 64, html: function (d) { return String(d.vip) === '1' ? U.status(true, 'VIP') : U.status(false, '免费'); }},
            {title: '图片数', width: 72, html: function (d) { return U.escape(String(d.pic_count == null ? 0 : d.pic_count)); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || filterMangaId || '');
                var cid = encodeURIComponent(d.id || '');
                return '<a href="#" class="btn-link js-edit">编辑</a>'
                    + (mid && cid ? '<a href="/manga/' + mid + '/' + cid + '" class="btn-link" target="_blank" rel="noopener">阅读</a>' : '')
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'pics') {
        cols = [
            {check: true, width: 36},
            {title: '图片', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.url || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '章节', html: function (d) { return U.escape(d.chapter_name || ('#' + (d.chapter_id || ''))); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#manga-table',
        url: '/admin/video/' + module + '/list',
        where: queryWhere(),
        pager: desk !== 'types',
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            if (desk === 'types') fillBatchParent();
            var add = document.getElementById('manga-empty-add');
            var reset = document.getElementById('manga-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            var bar = document.getElementById('manga-batch');
            var count = document.getElementById('manga-batch-count');
            if (!bar) return;
            bar.hidden = !ids.length;
            if (count) count.textContent = '已选 ' + ids.length + (desk === 'types' ? ' 个' : ' 条');
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function tplId() {
        if (desk === 'types') return 'manga-type-tpl';
        if (desk === 'chapters' || desk === 'work') return 'manga-chapter-tpl';
        if (desk === 'pics') return 'manga-pic-tpl';
        if (desk === 'comments') return 'manga-comment-tpl';
        return 'manga-work-tpl';
    }
    function titles(mode) {
        var map = {
            works: ['新增作品', '编辑作品'],
            pending: ['新增作品', '编辑作品'],
            types: ['新增分类', '编辑分类'],
            chapters: ['新增章节', '编辑章节'],
            work: ['新增章节', '编辑章节'],
            pics: ['新增图片', '编辑图片'],
            comments: ['新增评论', '编辑评论']
        }[desk] || ['新增', '编辑'];
        return mode === 'edit' ? map[1] : map[0];
    }
    function fill(mode, row) {
        row = row || {};
        if (desk === 'types') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                name: row.name || '',
                parent_id: row.parent_id == null ? 0 : row.parent_id,
                sort: row.sort == null ? 0 : row.sort,
                status: row.status == null ? '1' : String(row.status)
            };
        }
        if (desk === 'chapters' || desk === 'work') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                manga_id: row.manga_id || (filterMangaId || ''),
                name: row.name || '',
                sort: row.sort == null ? 0 : row.sort,
                vip: row.vip == null ? '0' : String(row.vip),
                pics: row.pics || ''
            };
        }
        if (desk === 'pics') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                manga_id: row.manga_id || (filterMangaId || ''),
                chapter_id: row.chapter_id || '',
                url: row.url || '',
                sort: row.sort == null ? 0 : row.sort
            };
        }
        if (desk === 'comments') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                manga_id: row.manga_id || (filterMangaId || ''),
                author_name: row.author_name || '',
                content: row.content || '',
                status: row.status == null ? '1' : String(row.status)
            };
        }
        return {
            id: mode === 'edit' ? (row.id || '') : '',
            title: row.title || '',
            type_id: row.type_id == null ? 0 : row.type_id,
            author: row.author || '',
            cover: row.cover || '',
            serialize: row.serialize == null ? '0' : String(row.serialize),
            tags: row.tags || '',
            recommend: row.recommend == null ? '0' : String(row.recommend),
            yid: desk === 'pending' && mode !== 'edit' ? '1' : (row.yid == null ? '0' : String(row.yid)),
            status: row.status == null ? '1' : String(row.status),
            remarks: row.remarks || '',
            content: row.content || '',
            hits: row.hits == null ? 0 : row.hits,
            sort: row.sort == null ? 0 : row.sort
        };
    }
    function bindImageFields(body) {
        U.bindImageField(body, {
            input: '[name=cover]',
            btn: '.js-cover-pick',
            preview: '.js-cover-preview'
        });
        U.bindImageField(body, {
            input: '[name=url]',
            btn: '.js-pic-pick',
            preview: '.js-pic-preview'
        });
        var pics = body.querySelector('textarea[name=pics]');
        var picsBtn = body.querySelector('.js-pics-upload');
        var picsPreview = body.querySelector('.js-pics-preview');
        if (!pics || !picsBtn) return;
        function lastPicUrl() {
            var lines = String(pics.value || '').split(/\r?\n/);
            for (var i = lines.length - 1; i >= 0; i--) {
                var line = String(lines[i] || '').trim();
                if (line) return line;
            }
            return '';
        }
        function syncPicsPreview() {
            var url = lastPicUrl();
            if (!picsPreview) return;
            if (url) {
                picsPreview.src = url;
                picsPreview.style.display = 'block';
            } else {
                picsPreview.removeAttribute('src');
                picsPreview.style.display = 'none';
            }
        }
        syncPicsPreview();
        pics.addEventListener('input', syncPicsPreview);
        picsBtn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        var cur = String(pics.value || '').replace(/\s+$/, '');
                        pics.value = cur ? (cur + '\n' + res.data.url) : res.data.url;
                        syncPicsPreview();
                        U.toast('已追加', 'ok');
                    } else {
                        U.toast((res && res.msg) || '上传失败', 'err');
                    }
                }).catch(function () { U.loading(false); });
            });
        });
    }
    function openDialog(mode, row, forceModule) {
        row = row || {};
        var saveModule = forceModule || module;
        var useWorkTpl = forceModule === 'mangas';
        U.dialog({
            title: useWorkTpl ? (mode === 'edit' ? '编辑作品' : '新增作品') : titles(mode),
            content: document.getElementById(useWorkTpl ? 'manga-work-tpl' : tplId()).innerHTML,
            onOpen: function (body) {
                var data = useWorkTpl ? {
                    id: mode === 'edit' ? (row.id || '') : '',
                    title: row.title || '',
                    type_id: row.type_id == null ? 0 : row.type_id,
                    author: row.author || '',
                    cover: row.cover || '',
                    serialize: row.serialize == null ? '0' : String(row.serialize),
                    tags: row.tags || '',
                    recommend: row.recommend == null ? '0' : String(row.recommend),
                    yid: row.yid == null ? '0' : String(row.yid),
                    status: row.status == null ? '1' : String(row.status),
                    remarks: row.remarks || '',
                    content: row.content || '',
                    hits: row.hits == null ? 0 : row.hits,
                    sort: row.sort == null ? 0 : row.sort
                } : fill(mode, row);
                if (desk === 'types' && !useWorkTpl) {
                    var parentSel = body.querySelector('select[name=parent_id]');
                    fillParentSelect(parentSel, mode === 'edit' ? (row.id || 0) : 0, data.parent_id);
                }
                U.fillForm(body.querySelector('form'), data);
                bindImageFields(body);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (useWorkTpl && !data.title) { U.toast('请填写名称', 'err'); return false; }
                if (desk === 'types' && !data.name) { U.toast('请填写名称', 'err'); return false; }
                if ((desk === 'works' || desk === 'pending') && !data.title) { U.toast('请填写名称', 'err'); return false; }
                if ((desk === 'chapters' || desk === 'work') && !useWorkTpl && !data.name) { U.toast('请填写章节名', 'err'); return false; }
                if (desk === 'pics' && !data.url) { U.toast('请填写图片地址', 'err'); return false; }
                if (desk === 'comments' && !data.content) { U.toast('请填写评论', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + saveModule + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    if (useWorkTpl && desk === 'work') {
                        location.reload();
                        return;
                    }
                    table.refresh();
                });
            }
        });
    }

    function batch(action, value, confirmText) {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(desk === 'types' ? '请先勾选分类' : '请先勾选记录', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/' + module + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    U.on('#manga-search-btn', 'click', runSearch);
    U.on('#manga-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#manga-add-btn', 'click', function () { openDialog('add'); });
    U.on('#manga-edit-work', 'click', function () {
        if (!workPayload) return;
        openDialog('edit', workPayload, 'mangas');
    });
    U.on('#manga-batch-on', 'click', function () { batch('status', 1); });
    U.on('#manga-batch-off', 'click', function () { batch('status', 0); });
    U.on('#manga-batch-pass', 'click', function () { batch('yid', 0); });
    U.on('#manga-batch-rec', 'click', function () { batch('recommend', 1); });
    U.on('#manga-batch-unrec', 'click', function () { batch('recommend', 0); });
    U.on('#manga-batch-move', 'click', function () {
        var val = document.getElementById('manga-batch-parent').value;
        if (val === '') { U.toast('请选择目标上级', 'err'); return; }
        batch('parent', val);
    });
    U.on('#manga-batch-del', 'click', function () {
        batch('delete', '', desk === 'types'
            ? '确认删除选中分类？有下级或作品的会跳过。'
            : '确认删除选中记录？');
    });
    U.on('#manga-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#manga-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('target') === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video/mangas?desk=work') === 0) {
            return;
        }
        if ((a.getAttribute('href') || '').indexOf('/admin/video/mangas?type_id=') === 0) {
            return;
        }
        if ((a.getAttribute('href') || '').indexOf('/manga/') === 0 && a.getAttribute('target') === '_blank') {
            return;
        }
        if ((a.getAttribute('href') || '').indexOf('/manga?type=') === 0) {
            return;
        }
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (a.classList.contains('js-edit') || a.classList.contains('js-del') || a.classList.contains('js-child')) {
            e.preventDefault();
        }
        if (a.classList.contains('js-child')) {
            openDialog('add', {parent_id: row.id, status: 1, sort: 0});
            return;
        }
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            var tip = desk === 'types'
                ? ('删除「' + (row.name || '') + '」？有下级或作品时无法删除。')
                : '确认删除？';
            if (!U.confirm(tip)) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
