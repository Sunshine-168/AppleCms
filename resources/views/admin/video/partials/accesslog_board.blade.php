@php
    $queues = $queues ?? ['all' => 0, 'today' => 0, 'people' => 0, 'bot' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $access_ip = trim((string) ($access_ip ?? ''));
    $show_header_links = (bool) ($show_header_links ?? true);
    $compact = (bool) ($compact ?? false);
@endphp

@if($show_header_links)
    <div class="accesslog-links">
        <a class="btn btn-muted btn-sm" href="/admin/video/botlogs">{{ admin_t('ui.botlogs') }}</a>
        <a class="btn btn-muted btn-sm" href="/admin/stats/logs">{{ admin_t('ui.visit_detail') }}</a>
        <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">{{ admin_t('ui.spider_stats') }}</a>
        <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">{{ admin_t('nav.config_ip') }}</a>
    </div>
@endif

<form class="filter-bar" id="accesslog-search" onsubmit="return false;">
    <input type="hidden" name="today">
    <input type="hidden" name="visitor">
    <input type="hidden" name="ip" value="{{ $access_ip }}">
    <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_access') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_access') }}">
    <button type="button" class="btn btn-sm" id="accesslog-search-btn">{{ admin_t('ui.search') }}</button>
    <button type="reset" class="btn btn-muted btn-sm" id="accesslog-reset-btn">{{ admin_t('ui.reset') }}</button>
</form>
<div class="queue-chips" id="accesslog-queues">
    <button type="button" class="chip" data-chip="all">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="today">{{ admin_t('ui.today_chip') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="people">{{ admin_t('ui.visitor') }}@if($q('people') > 0)<em>{{ $q('people') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="bot">{{ admin_t('ui.spider') }}@if($q('bot') > 0)<em>{{ $q('bot') }}</em>@endif</button>
    <button type="button" class="chip" data-chip="ip" id="accesslog-ip-chip" @if($access_ip === '') hidden @endif>{{ $access_ip }}</button>
</div>
@if($compact)
    <p class="muted recycle-lead">{{ admin_t('ui.accesslog_lead_before') }}<strong>{{ admin_t('ui.botlogs_lead_strong') }}</strong>{{ admin_t('ui.accesslog_lead_end') }}</p>
@else
    <p class="muted recycle-lead">{{ admin_t('ui.accesslog_lead_before') }}<strong>{{ admin_t('ui.botlogs_lead_strong') }}</strong>{{ admin_t('ui.accesslog_lead_mid') }}<code>/admin</code>{{ admin_t('ui.accesslog_lead_after_admin') }}<a href="/admin/stats/logs">{{ admin_t('ui.visit_detail') }}</a>{{ admin_t('ui.accesslog_lead_after_detail') }}<a href="/admin/system/monitor/system-logs">{{ admin_t('page.system_logs') }}</a>{{ admin_t('ui.accesslog_lead_after_sys') }}<a href="/admin/video/botlogs">{{ admin_t('ui.botlogs') }}</a>{{ admin_t('ui.accesslog_lead_tail') }}</p>
@endif
<div class="batch-bar" id="accesslog-batch" hidden>
    <strong id="accesslog-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
    <button type="button" class="btn btn-danger btn-sm" id="accesslog-batch-del">{{ admin_t('ui.delete') }}</button>
    <button type="button" class="btn btn-muted btn-sm" id="accesslog-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
</div>
<div id="accesslog-table"></div>
