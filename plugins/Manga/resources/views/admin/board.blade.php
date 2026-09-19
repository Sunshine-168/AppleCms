@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.manga'))

@php
    $desk = in_array((string) ($desk ?? ''), ['pending', 'types', 'chapters', 'pics', 'comments', 'favors', 'works', 'work', 'stats'], true)
        ? (string) $desk
        : 'works';
    $types = is_array($types ?? null) ? $types : [];
    $works = is_array($works ?? null) ? $works : [];
    $filterMangaId = (int) ($filterMangaId ?? 0);
    $filterMangaTitle = (string) ($filterMangaTitle ?? '');
    $filterMemberId = (int) ($filterMemberId ?? 0);
    $filterMemberName = (string) ($filterMemberName ?? '');
    $filterTypeId = (int) ($filterTypeId ?? 0);
    $filterTagId = (int) ($filterTagId ?? 0);
    $filterAuthorId = (int) ($filterAuthorId ?? 0);
    $tags = is_array($tags ?? null) ? $tags : [];
    $tagsReady = (bool) ($tagsReady ?? true);
    $authors = is_array($authors ?? null) ? $authors : [];
    $authorsReady = (bool) ($authorsReady ?? true);
    $filterTag = is_array($filterTag ?? null) ? $filterTag : null;
    $filterAuthor = is_array($filterAuthor ?? null) ? $filterAuthor : null;
    $filterQ = (string) ($filterQ ?? '');
    $filterSerialize = (string) ($filterSerialize ?? '');
    $filterRecommend = (string) ($filterRecommend ?? '');
    $hint = (string) ($hint ?? '');
    $work = is_array($work ?? null) ? $work : null;
    $stats = is_array($stats ?? null) ? $stats : [];
    $worksStat = is_array($stats['works'] ?? null) ? $stats['works'] : ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0];
    $topHits = is_array($stats['top_hits'] ?? null) ? $stats['top_hits'] : [];
    $topFavors = is_array($stats['top_favors'] ?? null) ? $stats['top_favors'] : [];
    $daily = is_array($stats['daily'] ?? null) ? $stats['daily'] : [];
    $favorQueues = is_array($favorQueues ?? null) ? $favorQueues : ['all' => 0, 'today' => 0, 'missing' => 0];
    $fq = fn (string $k) => (int) ($favorQueues[$k] ?? 0);
    $commentQueues = is_array($commentQueues ?? null) ? $commentQueues : ['all' => 0, 'pending' => 0, 'pass' => 0];
    $cq = fn (string $k) => (int) ($commentQueues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel manga-board desk-board" id="manga-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? '漫画统计' : ($desk === 'work' ? '作品工作台' : ($desk === 'chapters' ? '章节' : ($desk === 'pics' ? '图片' : ($desk === 'comments' ? '评论' : ($desk === 'favors' ? '书架' : '漫画'))))) }} <em id="manga-count"></em></span>
        <div>
            @if(in_array($desk, ['works', 'pending'], true))
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-tags">标签</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-authors">作者</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=chapters">章节</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=pics">图片</a>
                <span class="btn-split" role="group" aria-label="添加作品">
                    <a class="btn btn-sm" href="#manga-work-compose-box">新增作品</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
                </span>
            @elseif(in_array($desk, ['chapters', 'work'], true))
                <span class="btn-split" role="group" aria-label="添加章节">
                    <a class="btn btn-sm" href="#manga-chapter-compose-box">新增章节</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/manga-chapters/create{{ ($desk === 'work' && $work) ? '?manga_id='.(int) $work['id'] : ($filterMangaId > 0 ? '?manga_id='.$filterMangaId : '') }}">完整表单</a>
                </span>
            @elseif($desk === 'pics')
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-pics/create{{ $filterMangaId > 0 ? '?manga_id='.$filterMangaId : '' }}">完整表单</a>
            @elseif($desk === 'comments')
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-comments/create{{ $filterMangaId > 0 ? '?manga_id='.$filterMangaId : '' }}">完整表单</a>
            @elseif($desk === 'favors')
                <a class="btn btn-muted btn-sm" href="/manga/shelf" target="_blank" rel="noopener">前台书架</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/favorites">影片收藏</a>
            @endif
            @if($desk === 'work' && $work)
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas/{{ (int) $work['id'] }}/edit">编辑作品</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas">返回作品</a>
                <a class="btn btn-muted btn-sm" href="{{ $work['front_url'] }}" target="_blank" rel="noopener">前台预览</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($desk !== 'stats')
        <p class="muted recycle-lead">
            @if($desk === 'work')
                下方管本章节。快捷填话名即可添加；贴图、VIP 请点「完整表单」。
            @elseif(in_array($desk, ['chapters', 'pics', 'comments'], true))
                列表页只做快捷添加与浏览。复杂字段进「完整表单」。
            @elseif($desk === 'favors')
                会员在漫画页点「加入书架」后出现。后台不能代收藏。删除只取消此人的书架，不删作品。
            @elseif(in_array($desk, ['works', 'pending'], true))
                独立漫画库，不是影片分类。快捷填名称即可添加；分类、标签、封面等进「完整表单」。点「管理」进工作台管章节。
            @else
                独立漫画库，不是影片分类。
            @endif
        </p>
        @endif

        @if($desk === 'stats')
            <p class="muted recycle-lead manga-stats-lead">独立漫画库 · 阅读来自会员历史，新章来自章节创建，人气为 hits，收藏来自书架。</p>
            <div class="stat-grid dash manga-stats-grid">
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
                    <span class="muted">共 {{ (int) ($worksStat['all'] ?? 0) }} · 待审 {{ (int) ($worksStat['pending'] ?? 0) }} · 下架 {{ (int) ($worksStat['off'] ?? 0) }}</span>
                </div>
                <div class="stat-card">
                    <em>书架收藏</em>
                    <strong>{{ (int) ($stats['favor_total'] ?? 0) }}</strong>
                    <span class="muted"><a href="/admin/video/mangas?desk=favors">打开书架台</a></span>
                </div>
            </div>
            <div class="flink-stats-split manga-stats-split">
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">人气 TOP</h3>
                    @if($topHits === [])
                        <p class="muted">还没有作品。</p>
                        <p><a class="btn btn-muted btn-sm" href="/admin/video/mangas">去作品台</a></p>
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
                        <p class="muted">还没有书架收藏。会员前台点「加入书架」后会出现在这里。</p>
                        <p><a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=favors">打开书架台</a></p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>作品</th><th>收藏</th></tr></thead>
                                <tbody>
                                @foreach($topFavors as $row)
                                    <tr>
                                        <td><a href="/admin/video/mangas?desk=favors&manga_id={{ (int) $row['id'] }}">{{ $row['title'] }}</a></td>
                                        <td>{{ (int) ($row['favors'] ?? $row['favor_count'] ?? 0) }}</td>
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
            @if(in_array($desk, ['works', 'pending'], true) && (! $tagsReady || ! $authorsReady))
                <div class="flash" style="margin:0 0 12px">
                    @if(! $tagsReady)
                        <p class="muted" style="margin:0">标签表还没建，完整表单里暂不能挂标签。<a href="/admin/video/manga-tags">打开标签台</a>查看迁移说明。</p>
                    @endif
                    @if(! $authorsReady)
                        <p class="muted" style="margin:{{ $tagsReady ? '0' : '6px 0 0' }}">作者表还没建，完整表单里暂不能挂作者库。<a href="/admin/video/manga-authors">打开作者台</a>查看迁移说明。</p>
                    @endif
                </div>
            @endif
            @if($desk === 'work' && $work)
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:12px;align-items:center">
                    <strong>{{ $work['title'] }}</strong>
                    <span class="muted">#{{ $work['id'] }}</span>
                    <span>{{ $work['serialize_label'] }}</span>
                    <span class="muted">{{ (int) $work['chapter_count'] }} 话 · 人气 {{ (int) $work['hits'] }}</span>
                    @if(! empty($work['author']))
                        <span class="muted">作者 {{ $work['author'] }}</span>
                    @endif
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas/{{ (int) $work['id'] }}/edit">编辑作品</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=pics&manga_id={{ $work['id'] }}">图片明细</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=comments&manga_id={{ $work['id'] }}">评论</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=favors&manga_id={{ $work['id'] }}">书架</a>
                </div>
            @endif
            @if($filterMangaId > 0 && in_array($desk, ['chapters', 'pics', 'comments', 'favors'], true))
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                    <span>正在看作品</span>
                    <strong>{{ $filterMangaTitle !== '' ? $filterMangaTitle : ('#'.$filterMangaId) }}</strong>
                    <a class="btn btn-sm" href="/admin/video/mangas?desk=work&manga_id={{ $filterMangaId }}">作品工作台</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas">返回作品</a>
                    <a class="btn btn-muted btn-sm" href="/manga/{{ $filterMangaId }}" target="_blank" rel="noopener">前台预览</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk={{ $desk }}">清除作品筛选</a>
                </div>
            @endif
            @if($desk === 'favors' && $filterMemberId > 0)
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                    <span>正在看会员</span>
                    <strong>{{ $filterMemberName !== '' ? $filterMemberName : ('#'.$filterMemberId) }}</strong>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=favors{{ $filterMangaId > 0 ? '&manga_id='.$filterMangaId : '' }}">清除会员筛选</a>
                </div>
            @endif
            @if(in_array($desk, ['works', 'pending'], true))
                <div class="tag-compose" id="manga-work-compose-box">
                    <form class="tag-compose-form" id="manga-work-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="manga-work-quick">新增作品</label>
                        <div class="tag-compose-row">
                            <input id="manga-work-quick" type="text" name="title" value="" placeholder="输入名称" aria-label="新增作品" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">添加</button>
                                <a class="btn btn-muted" href="/admin/video/mangas/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
                            </span>
                        </div>
                        <p class="muted field-hint">回车可连续添加。分类、标签、封面等请用右侧「完整表单」。</p>
                    </form>
                </div>
            @elseif(in_array($desk, ['chapters', 'work'], true))
                <div class="tag-compose" id="manga-chapter-compose-box">
                    <form class="tag-compose-form" id="manga-chapter-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="manga-chapter-quick">新增章节</label>
                        <div class="tag-compose-row">
                            @if($desk === 'chapters' && ! ($desk === 'work' && $work) && $filterMangaId < 1)
                                <select name="manga_id" aria-label="作品" required style="max-width:180px">
                                    <option value="">选择作品</option>
                                    @foreach($works as $w)
                                        <option value="{{ $w['id'] }}">{{ $w['title'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="manga_id" value="{{ $desk === 'work' && $work ? (int) $work['id'] : $filterMangaId }}">
                            @endif
                            <input id="manga-chapter-quick" type="text" name="name" value="" placeholder="输入话名，如 第1话" aria-label="新增章节" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">添加</button>
                                <a class="btn btn-muted" href="/admin/video/manga-chapters/create{{ ($desk === 'work' && $work) ? '?manga_id='.(int) $work['id'] : ($filterMangaId > 0 ? '?manga_id='.$filterMangaId : '') }}">完整表单</a>
                            </span>
                        </div>
                        <p class="muted field-hint">回车可连续添加。贴图、VIP 请用右侧「完整表单」。</p>
                    </form>
                </div>
            @elseif($desk === 'pics')
                <div class="tag-compose">
                    <p class="muted field-hint" style="margin:0">图片字段较多，请用<a href="/admin/video/manga-pics/create{{ $filterMangaId > 0 ? '?manga_id='.$filterMangaId : '' }}">完整表单</a>添加；或在章节完整表单里一次贴多行。</p>
                </div>
            @elseif($desk === 'comments')
                <div class="tag-compose">
                    <p class="muted field-hint" style="margin:0">评论请用<a href="/admin/video/manga-comments/create{{ $filterMangaId > 0 ? '?manga_id='.$filterMangaId : '' }}">完整表单</a>添加或编辑。</p>
                </div>
            @endif
            @if($desk !== 'stats')
        <form class="filter-bar" id="manga-search" onsubmit="return false;">
            <input type="hidden" name="yid" value="{{ $desk === 'pending' ? '1' : ($desk === 'works' ? '0' : '') }}">
            <input type="hidden" name="manga_id" value="{{ ($desk === 'work' || $filterMangaId > 0) ? ($desk === 'work' && $work ? (int) $work['id'] : $filterMangaId) : '' }}">
            @if($desk === 'favors')
                <input type="hidden" name="member_id" value="{{ $filterMemberId > 0 ? $filterMemberId : '' }}">
                <input type="hidden" name="today" value="">
                <input type="hidden" name="missing" value="">
            @endif
            <input type="search" name="q" value="{{ $filterQ }}" placeholder="{{ $desk === 'types' ? '搜分类名' : (in_array($desk, ['chapters', 'work'], true) ? '搜章节' : ($desk === 'pics' ? '搜图片地址' : ($desk === 'comments' ? '搜评论、作品' : ($desk === 'favors' ? '搜会员、作品或 ID' : '搜名称、作者、标签')))) }}" autocomplete="off">
            @if(in_array($desk, ['works', 'pending'], true) && $types !== [])
                <select name="type_id" aria-label="分类">
                    <option value="">全部分类</option>
                    @foreach($types as $type)
                        <option value="{{ $type['id'] }}" @selected($filterTypeId === (int) $type['id'])>{{ $type['label'] ?? $type['name'] }}</option>
                    @endforeach
                </select>
            @endif
            @if(in_array($desk, ['works', 'pending'], true) && $filterTagId > 0)
                <input type="hidden" name="tag_id" value="{{ $filterTagId }}">
            @endif
            @if(in_array($desk, ['works', 'pending'], true) && $filterAuthorId > 0)
                <input type="hidden" name="author_id" value="{{ $filterAuthorId }}">
            @endif
            @if(in_array($desk, ['works', 'pending'], true))
                <select name="serialize" aria-label="连载">
                    <option value="">全部状态</option>
                    <option value="0" @selected($filterSerialize === '0')>连载</option>
                    <option value="1" @selected($filterSerialize === '1')>完结</option>
                </select>
                <select name="recommend" aria-label="推荐">
                    <option value="">全部</option>
                    <option value="1" @selected($filterRecommend === '1')>推荐</option>
                </select>
            @endif
            @if($desk === 'comments')
                <select name="status" aria-label="状态" hidden>
                    <option value="">全部状态</option>
                    <option value="1">显示</option>
                    <option value="0">待审</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="manga-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-reset-btn">重置</button>
        </form>
        @if($desk === 'comments')
            <div class="queue-chips" id="manga-comment-queues">
                <button type="button" class="chip" data-queue="" data-value="">全部@if($cq('all') > 0)<em>{{ $cq('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="0">待审@if($cq('pending') > 0)<em>{{ $cq('pending') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="1">已通过@if($cq('pass') > 0)<em>{{ $cq('pass') }}</em>@endif</button>
            </div>
        @endif
        @if($filterTag && in_array($desk, ['works', 'pending'], true))
            <div class="queue-chips">
                <a class="chip active" href="/admin/video/mangas{{ $desk === 'pending' ? '?desk=pending' : '' }}">标签 {{ $filterTag['name'] }} ×</a>
            </div>
        @endif
        @if($filterAuthor && in_array($desk, ['works', 'pending'], true))
            <div class="queue-chips">
                <a class="chip active" href="/admin/video/mangas{{ $desk === 'pending' ? '?desk=pending' : '' }}">作者 {{ $filterAuthor['name'] }} ×</a>
            </div>
        @endif
        @if($desk === 'favors')
            <div class="queue-chips" id="manga-favor-queues">
                <button type="button" class="chip" data-queue="">全部@if($fq('all') > 0)<em>{{ $fq('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="today" data-value="1">今天@if($fq('today') > 0)<em>{{ $fq('today') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="missing" data-value="1">作品已删@if($fq('missing') > 0)<em>{{ $fq('missing') }}</em>@endif</button>
            </div>
        @endif
        @if(in_array($desk, ['works', 'pending', 'comments', 'chapters', 'pics', 'work', 'types', 'favors'], true))
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
                <button type="button" class="btn btn-danger btn-sm" id="manga-batch-del">{{ $desk === 'favors' ? '取消收藏' : '删除' }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="manga-batch-clear">取消选择</button>
            </div>
        @endif
        <div id="manga-table"></div>
            @endif
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    var filterMangaId = @json($filterMangaId);
    var workPayload = @json($work);
    if (desk === 'stats') return;
    var form = document.getElementById('manga-search');
    var countEl = document.getElementById('manga-count');
    if (desk === 'work' && workPayload && workPayload.id) {
        filterMangaId = Number(workPayload.id) || filterMangaId;
    }
    var modules = {
        works: 'mangas', pending: 'mangas', types: 'manga_types',
        chapters: 'manga_chapters', work: 'manga_chapters',
        pics: 'manga_pics', comments: 'manga_comments', favors: 'manga_favors'
    };
    var module = modules[desk] || 'mangas';
    var fullCreate = {
        works: '/admin/video/mangas/create',
        pending: '/admin/video/mangas/create?desk=pending',
        chapters: '/admin/video/manga-chapters/create' + (filterMangaId ? ('?manga_id=' + filterMangaId) : ''),
        work: '/admin/video/manga-chapters/create' + (filterMangaId ? ('?manga_id=' + filterMangaId) : ''),
        pics: '/admin/video/manga-pics/create' + (filterMangaId ? ('?manga_id=' + filterMangaId) : ''),
        comments: '/admin/video/manga-comments/create' + (filterMangaId ? ('?manga_id=' + filterMangaId) : '')
    };

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
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="manga-empty-reset">清除筛选</button></p></div>';
        }
        var href = fullCreate[desk] || '';
        var label = {works:'新增作品',pending:'新增作品',chapters:'完整表单',work:'完整表单',pics:'完整表单',comments:'完整表单'}[desk] || '完整表单';
        var tip = {works:'还没有漫画作品',pending:'没有待审作品',chapters:'还没有章节',work:'这部还没有章节',pics:'还没有图片',comments:'还没有评论',favors:'还没有书架收藏'}[desk] || '还没有记录';
        if (desk === 'favors') {
            return '<div class="list-empty"><p>' + tip + '</p><p class="muted">会员在漫画详情点「加入书架」后会出现。</p><p><a class="btn btn-muted btn-sm" href="/manga" target="_blank" rel="noopener">打开前台漫画</a></p></div>';
        }
        if (href) {
            return '<div class="list-empty"><p>' + tip + '</p><p class="muted">上方快捷添加，或打开完整表单。</p><p><a class="btn btn-primary btn-sm" href="' + href + '">' + label + '</a></p></div>';
        }
        return '<div class="list-empty"><p>' + tip + '</p></div>';
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
                var badge = parseInt(d.recommend, 10) === 1 ? '<span class="badge">推荐</span> ' : '';
                return badge + '<a class="entry-row-title" href="/admin/video/mangas/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.title || '未填写') + '</a>';
            }},
            {title: '分类', html: function (d) { return U.escape(d.type_name || '未分类'); }},
            {title: '作者', html: function (d) { return U.escape(d.author_label || d.author || ''); }},
            {title: '连载', width: 72, html: function (d) { return U.escape(d.serialize_label || ''); }},
            {title: '状态/待审', width: 110, html: workStatus},
            {title: '章节数', width: 72, html: function (d) { return U.escape(String(d.chapter_count == null ? 0 : d.chapter_count)); }},
            {title: '浏览', width: 72, html: function (d) { return U.escape(String(d.hits == null ? 0 : d.hits)); }},
            {title: '收藏', width: 64, html: function (d) { return U.escape(String(d.favor_count == null ? 0 : d.favor_count)); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id || '');
                return '<a href="/admin/video/mangas?desk=work&manga_id=' + id + '" class="btn-link">管理</a>'
                    + '<a href="/admin/video/mangas/' + id + '/edit" class="btn-link">编辑</a>'
                    + '<a href="' + U.escape(d.front_url || ('/manga/' + id)) + '" class="btn-link" target="_blank" rel="noopener">前台</a>';
            }}
        ];
    } else if (desk === 'comments') {
        cols = [
            {check: true, width: 36},
            {title: '内容', html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/manga-comments/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.content || '未填写') + '</a>';
            }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '昵称', width: 100, html: function (d) { return U.escape(d.author_name || ''); }},
            {title: '状态', width: 90, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '待审');
            }},
            {title: '时间', width: 140, html: function (d) { return U.escape(d.created_label || ''); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || '');
                return '<a href="/admin/video/manga-comments/' + encodeURIComponent(d.id || '') + '/edit" class="btn-link">编辑</a>'
                    + (mid ? '<a href="/manga/' + mid + '" class="btn-link" target="_blank" rel="noopener">前台</a>' : '')
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'favors') {
        cols = [
            {check: true, width: 36},
            {title: '会员', html: function (d) {
                var name = d.member_name || ('#' + (d.member_id || ''));
                var miss = String(d.member_missing) === '1' ? ' <span class="muted">已删</span>' : '';
                return '<a class="entry-row-title" href="/admin/video/mangas?desk=favors&member_id=' + encodeURIComponent(d.member_id || '') + '">' + U.escape(name) + '</a>' + miss;
            }},
            {title: '作品', html: function (d) {
                var title = d.manga_title || ('#' + (d.manga_id || ''));
                var miss = String(d.manga_missing) === '1' ? ' <span class="muted">已删</span>' : '';
                return '<a href="/admin/video/mangas?desk=favors&manga_id=' + encodeURIComponent(d.manga_id || '') + '">' + U.escape(title) + '</a>' + miss;
            }},
            {title: '时间', width: 140, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || '');
                return (mid ? '<a href="/manga/' + mid + '" class="btn-link" target="_blank" rel="noopener">前台</a>' : '')
                    + '<a href="#" class="btn-link js-del">取消</a>';
            }}
        ];
    } else if (desk === 'chapters' || desk === 'work') {
        cols = [
            {check: true, width: 36},
            {title: '章节', html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/manga-chapters/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.name || '未填写') + '</a>';
            }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: 'VIP', width: 64, html: function (d) { return String(d.vip) === '1' ? U.status(true, 'VIP') : U.status(false, '免费'); }},
            {title: '图片数', width: 72, html: function (d) { return U.escape(String(d.pic_count == null ? 0 : d.pic_count)); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || filterMangaId || '');
                var cid = encodeURIComponent(d.id || '');
                return '<a href="/admin/video/manga-chapters/' + cid + '/edit" class="btn-link">编辑</a>'
                    + (mid && cid ? '<a href="/manga/' + mid + '/' + cid + '" class="btn-link" target="_blank" rel="noopener">阅读</a>' : '')
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'pics') {
        cols = [
            {check: true, width: 36},
            {title: '图片', html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/manga-pics/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.url || '未填写') + '</a>';
            }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '章节', html: function (d) { return U.escape(d.chapter_name || ('#' + (d.chapter_id || ''))); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function (d) {
                return '<a href="/admin/video/manga-pics/' + encodeURIComponent(d.id || '') + '/edit" class="btn-link">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#manga-table',
        url: '/admin/video/' + module + '/list',
        where: queryWhere(),
        pager: true,
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list, parsed) {
            var total = parsed && parsed.total != null ? parseInt(parsed.total, 10) : list.length;
            countEl.textContent = total > 0 ? '· ' + total : '';
            var reset = document.getElementById('manga-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            var bar = document.getElementById('manga-batch');
            var count = document.getElementById('manga-batch-count');
            if (!bar) return;
            bar.hidden = !ids.length;
            if (count) count.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function batch(action, value, confirmText) {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请先勾选记录', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/' + module + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    var workCompose = document.getElementById('manga-work-compose');
    if (workCompose) {
        workCompose.addEventListener('submit', function (e) {
            e.preventDefault();
            var title = String((workCompose.title && workCompose.title.value) || '').trim();
            if (!title) { U.toast('请填写名称', 'err'); workCompose.title.focus(); return; }
            U.loading(true);
            U.post('/admin/video/mangas/save', {
                title: title,
                status: 1,
                yid: desk === 'pending' ? 1 : 0,
                serialize: 0,
                recommend: 0,
                type_id: 0,
                tag_ids: [],
                tag_extra: '',
                author_ids: [],
                author_extra: ''
            }).then(function (res) {
                U.loading(false);
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '添加失败', 'err'); return; }
                workCompose.title.value = '';
                workCompose.title.focus();
                table.refresh();
                U.toast('已添加', 'ok');
            }).catch(function () { U.loading(false); U.toast('添加失败', 'err'); });
        });
    }
    var chapterCompose = document.getElementById('manga-chapter-compose');
    if (chapterCompose) {
        chapterCompose.addEventListener('submit', function (e) {
            e.preventDefault();
            var data = U.formData(chapterCompose);
            var mid = parseInt(data.manga_id, 10) || 0;
            var name = String(data.name || '').trim();
            if (!mid) { U.toast('请选择作品', 'err'); return; }
            if (!name) { U.toast('请填写章节名', 'err'); chapterCompose.name.focus(); return; }
            U.loading(true);
            U.post('/admin/video/manga_chapters/save', {manga_id: mid, name: name, sort: 0, vip: 0, pics: ''}).then(function (res) {
                U.loading(false);
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '添加失败', 'err'); return; }
                chapterCompose.name.value = '';
                chapterCompose.name.focus();
                table.refresh();
                U.toast('已添加', 'ok');
            }).catch(function () { U.loading(false); U.toast('添加失败', 'err'); });
        });
    }

    U.on('#manga-search-btn', 'click', runSearch);
    U.on('#manga-reset-btn', 'click', function () {
        setTimeout(function () {
            if (desk === 'favors') {
                var today = form.querySelector('[name=today]');
                var missing = form.querySelector('[name=missing]');
                if (today) today.value = '';
                if (missing) missing.value = '';
                if (favorQueues) {
                    Array.prototype.forEach.call(favorQueues.querySelectorAll('.chip'), function (c, i) {
                        c.classList.toggle('active', i === 0);
                    });
                }
            }
            if (desk === 'comments') {
                var statusSelReset = form.querySelector('[name=status]');
                if (statusSelReset) statusSelReset.value = '';
                if (commentQueues) {
                    Array.prototype.forEach.call(commentQueues.querySelectorAll('.chip'), function (c, i) {
                        c.classList.toggle('active', i === 0);
                    });
                }
            }
            runSearch();
        }, 0);
    });
    U.on('#manga-batch-on', 'click', function () { batch('status', 1); });
    U.on('#manga-batch-off', 'click', function () { batch('status', 0); });
    U.on('#manga-batch-pass', 'click', function () { batch('yid', 0); });
    U.on('#manga-batch-rec', 'click', function () { batch('recommend', 1); });
    U.on('#manga-batch-unrec', 'click', function () { batch('recommend', 0); });
    U.on('#manga-batch-del', 'click', function () {
        batch('delete', '', desk === 'favors' ? '确认取消选中的书架收藏？' : '确认删除选中记录？');
    });
    U.on('#manga-batch-clear', 'click', function () { table.clearSelection(); });
    var favorQueues = document.getElementById('manga-favor-queues');
    if (favorQueues) {
        favorQueues.addEventListener('click', function (e) {
            var btn = e.target.closest('button.chip');
            if (!btn) return;
            var key = btn.getAttribute('data-queue') || '';
            form.querySelector('[name=today]').value = '';
            form.querySelector('[name=missing]').value = '';
            if (key) form.querySelector('[name=' + key + ']').value = btn.getAttribute('data-value') || '1';
            Array.prototype.forEach.call(favorQueues.querySelectorAll('.chip'), function (c) {
                c.classList.toggle('active', c === btn);
            });
            runSearch();
        });
        var first = favorQueues.querySelector('.chip');
        if (first) first.classList.add('active');
    }
    var commentQueues = document.getElementById('manga-comment-queues');
    if (commentQueues) {
        var statusSel = form.querySelector('[name=status]');
        commentQueues.addEventListener('click', function (e) {
            var btn = e.target.closest('button.chip');
            if (!btn || !statusSel) return;
            statusSel.value = btn.getAttribute('data-value') || '';
            Array.prototype.forEach.call(commentQueues.querySelectorAll('.chip'), function (c) {
                c.classList.toggle('active', c === btn);
            });
            runSearch();
        });
        var cFirst = commentQueues.querySelector('.chip');
        if (cFirst) cFirst.classList.add('active');
    }
    U.on('#manga-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (!U.confirm(desk === 'favors' ? '确认取消这条书架？' : '确认删除？')) return;
        U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(desk === 'favors' ? '已取消' : '已删除', 'ok');
        });
    });
})();
</script>
@endpush
