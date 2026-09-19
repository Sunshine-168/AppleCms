@php
    $queues = $queues ?? ['all' => 0, 'today' => 0, 'people' => 0, 'bot' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $access_ip = trim((string) ($access_ip ?? ''));
    $show_header_links = (bool) ($show_header_links ?? true);
    $compact = (bool) ($compact ?? false);
@endphp

@if($show_header_links)
    <div class="accesslog-links">
        <a class="btn btn-muted btn-sm" href="/admin/video/botlogs">爬虫日志</a>
        <a class="btn btn-muted btn-sm" href="/admin/stats/logs">访问明细</a>
        <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">蜘蛛统计</a>
        <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">IP 白名单</a>
    </div>
@endif

<form class="filter-bar" id="accesslog-search" onsubmit="return false;">
    <input type="hidden" name="today">
    <input type="hidden" name="visitor">
    <input type="hidden" name="ip" value="{{ $access_ip }}">
    <input type="search" name="q" placeholder="搜 IP、地址或标识" autocomplete="off" aria-label="搜索访问流水">
    <button type="button" class="btn btn-sm" id="accesslog-search-btn">{{ admin_t('ui.search') }}</button>
    <button type="reset" class="btn btn-muted btn-sm" id="accesslog-reset-btn">{{ admin_t('ui.reset') }}</button>
</form>
<div class="queue-chips" id="accesslog-queues">
    <button type="button" class="chip" data-chip="all">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="today">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="people">访客@if($q('people') > 0)<em>{{ $q('people') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="bot">爬虫@if($q('bot') > 0)<em>{{ $q('bot') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="ip" id="accesslog-ip-chip" @if($access_ip === '') hidden @endif>{{ $access_ip }}</button>
</div>
@if($compact)
    <p class="muted recycle-lead">只记前台页面 GET。后台、js/css、插件静态不记。点 IP 只看这个地址。<strong>不能封 IP</strong>。</p>
@else
    <p class="muted recycle-lead">只记前台页面 GET。后台、js/css、插件静态不记。点 IP 只看这个地址。<strong>不能封 IP</strong>，也没有限频。白名单只拦 <code>/admin</code>。标题和来路去「<a href="/admin/stats/logs">访问明细</a>」；程序报错去「<a href="/admin/system/monitor/system-logs">系统日志</a>」；蜘蛛是同一张表，切片在「<a href="/admin/video/botlogs">爬虫日志</a>」。</p>
@endif
<div class="batch-bar" id="accesslog-batch" hidden>
    <strong id="accesslog-batch-count">已选 0 条</strong>
    <button type="button" class="btn btn-danger btn-sm" id="accesslog-batch-del">删除</button>
    <button type="button" class="btn btn-muted btn-sm" id="accesslog-batch-clear">取消选择</button>
</div>
<div id="accesslog-table"></div>
