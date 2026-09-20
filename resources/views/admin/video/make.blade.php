@extends('admin.layouts.inner')
@section('title', admin_t('page.make'))

@php
    $desk = in_array((string) ($desk ?? 'opt'), ['opt', 'index', 'map', 'cache'], true)
        ? (string) $desk
        : 'opt';
    $enabled = (bool) ($enabled ?? false);
    $ttl = (int) ($ttl ?? 3600);
    $ttlOptions = $ttlOptions ?? \App\Services\Video\HtmlCacheService::ttlOptions();
    $lastBust = $lastBust ?? null;
    $lastBustLabel = $lastBustLabel ?? admin_t('ui.never_refreshed');
    $diskEnabled = (bool) ($diskEnabled ?? false);
    $diskCount = (int) ($diskCount ?? 0);
    $diskJob = $diskJob ?? ['status' => 'idle', 'percent' => 0, 'message' => '', 'kind' => 'build'];
    $vodTypes = is_array($vodTypes ?? null) ? $vodTypes : [];
    $artTypes = is_array($artTypes ?? null) ? $artTypes : [];
    $topics = is_array($topics ?? null) ? $topics : [];
    $actors = is_array($actors ?? null) ? $actors : [];
    $roles = is_array($roles ?? null) ? $roles : [];
    $hasArts = (bool) ($hasArts ?? false);
    $detailCap = (int) ($detailCap ?? 2000);
    $makeJsLang = [
        'cleared_n' => admin_t('ui.cleared_n'),
        'wrote_n' => admin_t('ui.wrote_n'),
        'disk_files_n' => admin_t('ui.disk_files_n'),
        'disk_files_none' => admin_t('ui.disk_files_none'),
        'job_stopped' => admin_t('ui.job_stopped'),
        'enable_disk_first' => admin_t('ui.enable_disk_first'),
        'listing_pages' => admin_t('ui.listing_pages'),
        'start_fail' => admin_t('ui.start_fail'),
        'please_select' => admin_t('ui.please_select'),
        'confirm_del_static' => admin_t('ui.confirm_del_static'),
        'listing_files' => admin_t('ui.listing_files'),
        'start_del_fail' => admin_t('ui.start_del_fail'),
        'make_no_result' => admin_t('ui.make_no_result'),
    ];
@endphp

@section('plain')
<div class="card card-panel make-index" id="make-index" data-off="{{ $diskEnabled ? '0' : '1' }}">
    <div class="card-header"><span>{{ admin_t('page.make') }}</span></div>
    <div class="card-body">
        <div class="html-cache-page">
            <p class="muted recycle-lead">{{ admin_t('ui.make_lead_before') }}<code>public/html</code>{{ admin_t('ui.make_lead_after', ['n' => $detailCap]) }}</p>

            <div class="queue-chips" id="make-desks">
                <a class="chip{{ $desk === 'opt' ? ' active' : '' }}" href="/admin/video/make">{{ admin_t('ui.make_opt') }}</a>
                <a class="chip{{ $desk === 'index' ? ' active' : '' }}" href="/admin/video/make?desk=index">{{ admin_t('ui.make_home') }}</a>
                <a class="chip{{ $desk === 'map' ? ' active' : '' }}" href="/admin/video/make?desk=map">{{ admin_t('ui.make_map') }}</a>
                <a class="chip{{ $desk === 'cache' ? ' active' : '' }}" href="/admin/video/make?desk=cache">{{ admin_t('ui.make_cache') }}</a>
            </div>

            <div class="html-cache-card" id="diskHtmlCard">
                <div class="html-cache-status">
                    <h3>{{ admin_t('ui.disk_static') }}</h3>
                    @if($diskEnabled)
                        <span class="badge badge-ok">{{ admin_t('ui.disk_opened') }}</span>
                    @else
                        <span class="badge badge-off">{{ admin_t('ui.not_opened') }}</span>
                    @endif
                </div>
                <p class="muted field-hint">{{ admin_t('ui.disk_path_hint') }}</p>
                <form method="post" action="/admin/video/make/disk" class="disk-html-enable">
                    @csrf
                    <input type="hidden" name="desk" value="{{ $desk }}">
                    <label class="inline">
                        <input type="hidden" name="disk_html_enabled" value="0">
                        <input type="checkbox" name="disk_html_enabled" value="1" @checked($diskEnabled)>
                        {{ admin_t('ui.enable_disk_html') }}
                    </label>
                    <button class="btn btn-muted" type="submit">{{ admin_t('ui.save_this') }}</button>
                </form>
                <div class="disk-html-meta">
                    <p id="diskHtmlCount" class="muted">
                        @if($diskCount > 0)
                            {{ admin_t('ui.disk_files_n', ['n' => $diskCount]) }}
                        @else
                            {{ admin_t('ui.disk_files_none') }}
                        @endif
                    </p>
                    <div class="html-cache-actions">
                        <button class="btn-quiet" type="button" id="diskCancelBtn" hidden>{{ admin_t('ui.stop') }}</button>
                        <button class="btn-quiet" type="button" id="diskClearBtn">{{ admin_t('ui.del_static') }}</button>
                    </div>
                </div>
            </div>

            <div class="disk-html-progress{{ in_array($diskJob['status'] ?? '', ['done', 'cancelled'], true) ? ' is-done' : '' }}"
                 id="diskHtmlProgress"
                 data-status="{{ $diskJob['status'] ?? 'idle' }}"
                 data-kind="{{ $diskJob['kind'] ?? 'build' }}"
                 data-percent="{{ $diskJob['percent'] ?? 0 }}"
                 @if(($diskJob['status'] ?? 'idle') === 'idle') hidden @endif>
                <div class="disk-html-bar"><i id="diskHtmlBar" style="width: {{ (int) ($diskJob['percent'] ?? 0) }}%"></i></div>
                <p class="muted" id="diskHtmlMsg" style="margin:8px 0 0">{{ $diskJob['message'] ?? '' }}</p>
            </div>

            <div class="make-desk" data-desk-panel="opt" @if($desk !== 'opt') hidden @endif>
                <div class="make-opt-row">
                    <div class="make-opt-label">{{ admin_t('ui.vod_types') }}</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="vod-type-list">
                            @forelse($vodTypes as $type)
                                <label class="make-opt-d{{ min(3, (int) ($type['depth'] ?? 0)) }}">
                                    <input type="checkbox" name="vod_types" value="{{ (int) $type['id'] }}">
                                    {{ $type['name'] }}
                                </label>
                            @empty
                                <p class="muted">{{ admin_t('ui.empty_types') }}</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="type" data-from="vod_types" data-need="ids">{{ admin_t('ui.pick_types') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="type">{{ admin_t('ui.make_all_cats') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="type" data-when="today">{{ admin_t('ui.today_types') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail" data-type-from="vod_types" data-need="types">{{ admin_t('ui.pick_content') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail">{{ admin_t('ui.all_content') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail" data-when="today">{{ admin_t('ui.today_content') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail" data-when="missing" data-type-from="vod_types">{{ admin_t('ui.not_generated') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="vod_day">{{ admin_t('ui.one_click_today') }}</button>
                        </div>
                    </div>
                </div>

                @if($hasArts)
                <div class="make-opt-row">
                    <div class="make-opt-label">{{ admin_t('ui.art_types') }}</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="art-type-list">
                            @forelse($artTypes as $type)
                                <label class="make-opt-d{{ min(3, (int) ($type['depth'] ?? 0)) }}">
                                    <input type="checkbox" name="art_types" value="{{ (int) $type['id'] }}">
                                    {{ $type['name'] }}
                                </label>
                            @empty
                                <p class="muted">{{ admin_t('ui.empty_art_types') }}</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_type" data-from="art_types" data-need="ids">{{ admin_t('ui.pick_types') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_type">{{ admin_t('ui.make_all_cats') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_type" data-when="today">{{ admin_t('ui.today_types') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art" data-type-from="art_types" data-need="types">{{ admin_t('ui.pick_content') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art">{{ admin_t('ui.all_content') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art" data-when="today">{{ admin_t('ui.today_content') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art" data-when="missing" data-type-from="art_types">{{ admin_t('ui.not_generated') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_day">{{ admin_t('ui.one_click_today') }}</button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="make-opt-row">
                    <div class="make-opt-label">{{ admin_t('ui.topics') }}</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="topic-list">
                            @forelse($topics as $row)
                                <label>
                                    <input type="checkbox" name="topics" value="{{ (int) $row['id'] }}">
                                    {{ $row['name'] }}
                                </label>
                            @empty
                                <p class="muted">{{ admin_t('ui.empty_topics') }}</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="topic" data-from="topics" data-need="ids">{{ admin_t('ui.pick_topics') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="topic">{{ admin_t('ui.all_topics') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="topic" data-extra="index">{{ admin_t('ui.topic_home') }}</button>
                        </div>
                    </div>
                </div>

                <div class="make-opt-row">
                    <div class="make-opt-label">{{ admin_t('ui.actors') }}</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="actor-list">
                            @forelse($actors as $row)
                                <label>
                                    <input type="checkbox" name="actors" value="{{ (int) $row['id'] }}">
                                    {{ $row['name'] }}
                                </label>
                            @empty
                                <p class="muted">{{ admin_t('ui.empty_actors') }}</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-from="actors" data-need="ids">{{ admin_t('ui.pick_actors') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor">{{ admin_t('ui.all_actors') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-when="today">{{ admin_t('ui.today_actors') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-when="missing">{{ admin_t('ui.missing_actors') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-extra="index">{{ admin_t('ui.actor_home') }}</button>
                        </div>
                    </div>
                </div>

                <div class="make-opt-row">
                    <div class="make-opt-label">{{ admin_t('ui.cast') }}</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="role-list">
                            @forelse($roles as $row)
                                <label>
                                    <input type="checkbox" name="roles" value="{{ (int) $row['id'] }}">
                                    {{ $row['name'] }}
                                </label>
                            @empty
                                <p class="muted">{{ admin_t('ui.empty_roles_list') }}</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-from="roles" data-need="ids">{{ admin_t('ui.pick_roles') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role">{{ admin_t('ui.all_roles') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-when="today">{{ admin_t('ui.today_roles') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-when="missing">{{ admin_t('ui.missing_roles') }}</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-extra="index">{{ admin_t('ui.role_home') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="make-desk" data-desk-panel="index" @if($desk !== 'index') hidden @endif>
                <div class="html-cache-card">
                    <h3>{{ admin_t('ui.make_home') }}</h3>
                    <p class="muted field-hint">{{ admin_t('ui.home_not_wap') }}</p>
                    <div class="html-cache-actions">
                        <button type="button" class="btn btn-primary" data-make="1" data-scope="index">{{ admin_t('ui.gen_home') }}</button>
                    </div>
                </div>
            </div>

            <div class="make-desk" data-desk-panel="map" @if($desk !== 'map') hidden @endif>
                <div class="html-cache-card">
                    <h3>{{ admin_t('ui.map_and_rss') }}</h3>
                    <p class="muted field-hint">{{ admin_t('ui.map_rss_hint') }}</p>
                    <div class="html-cache-actions">
                        <button type="button" class="btn btn-primary" data-map="sitemap">{{ admin_t('ui.gen_map') }}</button>
                        <button type="button" class="btn btn-muted" data-map="rss">{{ admin_t('ui.gen_rss') }}</button>
                    </div>
                </div>
            </div>

            <div class="make-desk" data-desk-panel="cache" @if($desk !== 'cache') hidden @endif>
                <div class="html-cache-card">
                    <div class="html-cache-status">
                        <h3>{{ admin_t('ui.page_cache') }}</h3>
                        @if($enabled)
                            <span class="badge badge-ok">{{ admin_t('ui.turned_on') }}</span>
                        @else
                            <span class="badge badge-off">{{ admin_t('ui.turned_off') }}</span>
                        @endif
                    </div>
                    <form method="post" action="/admin/video/make/cache">
                        @csrf
                        <input type="hidden" name="desk" value="cache">
                        <label class="inline">
                            <input type="hidden" name="html_cache_enabled" value="0">
                            <input type="checkbox" name="html_cache_enabled" value="1" @checked($enabled)>
                            {{ admin_t('ui.enable_page_cache') }}
                        </label>
                        <p class="muted field-hint">{{ admin_t('ui.page_cache_hint') }}</p>

                        <label for="html-cache-ttl">{{ admin_t('ui.keep_how_long') }}</label>
                        <select id="html-cache-ttl" class="html-cache-ttl" name="html_cache_ttl">
                            @foreach($ttlOptions as $seconds => $label)
                                <option value="{{ $seconds }}" @selected($ttl === (int) $seconds)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="muted field-hint">{{ admin_t('ui.ttl_hint') }}</p>
                        <div class="form-actions">
                            <button class="btn" type="submit">{{ admin_t('page.save') }}</button>
                        </div>
                    </form>
                </div>

                <div class="html-cache-card">
                    <h3>{{ admin_t('ui.now_block') }}</h3>
                    <p class="muted" style="margin:0 0 8px">
                        {{ $lastBustLabel }}
                        @if(!empty($lastBust['at']))
                            · {{ \Illuminate\Support\Carbon::parse($lastBust['at'])->format('Y-m-d H:i') }}
                        @endif
                    </p>
                    <div class="html-cache-actions">
                        <form method="post" action="/admin/video/make/cache-warm">
                            @csrf
                            <input type="hidden" name="entries" value="30">
                            <button class="btn" type="submit" @disabled(! $enabled)>{{ admin_t('ui.warm_common') }}</button>
                        </form>
                        <form method="post" action="/admin/video/make/cache-clear" onsubmit="return confirm(@json(admin_t('ui.confirm_clear_cache'), JSON_UNESCAPED_UNICODE))">
                            @csrf
                            <button class="btn btn-muted" type="submit">{{ admin_t('ui.clear_cached') }}</button>
                        </form>
                    </div>
                    <p class="muted field-hint">
                        @if($enabled)
                            {{ admin_t('ui.warm_hint_on') }}
                        @else
                            {{ admin_t('ui.warm_hint_off') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($makeJsLang, JSON_UNESCAPED_UNICODE);
    var root = document.getElementById('make-index');
    var stopBtn = document.getElementById('diskCancelBtn');
    var clearBtn = document.getElementById('diskClearBtn');
    var box = document.getElementById('diskHtmlProgress');
    var bar = document.getElementById('diskHtmlBar');
    var msg = document.getElementById('diskHtmlMsg');
    var count = document.getElementById('diskHtmlCount');
    if (!root || !box || !bar || !msg) return;
    var looping = false;
    var stepChunk = 6;

    function locked() {
        return root.getAttribute('data-off') === '1';
    }

    function makeButtons() {
        return document.querySelectorAll('[data-make]');
    }

    function idleButtons() {
        makeButtons().forEach(function (el) { el.disabled = locked(); });
        if (clearBtn) clearBtn.disabled = false;
        if (stopBtn) stopBtn.hidden = true;
    }

    function jobOf(res) {
        var d = (res && res.data) || {};
        if (!d.status) {
            d.status = (res && res.code === 0) ? 'idle' : 'error';
        }
        d.message = d.message || (res && res.msg) || '';
        return d;
    }

    function paint(data) {
        box.hidden = false;
        bar.style.width = (data.percent || 0) + '%';
        var kind = data.kind || 'build';
        var fallback = kind === 'clear'
            ? (L.cleared_n || '').replace(':done', data.done || 0).replace(':total', data.total || 0)
            : (L.wrote_n || '').replace(':done', data.done || 0).replace(':total', data.total || 0);
        msg.textContent = data.message || fallback;
        box.classList.toggle('is-done', data.status === 'done');
        box.classList.toggle('is-stop', data.status === 'cancelled' || data.status === 'error');
        if (count && typeof data.file_count === 'number') {
            count.textContent = data.file_count > 0
                ? (L.disk_files_n || '').replace(':n', data.file_count)
                : (L.disk_files_none || '');
        }
        var busy = data.status === 'running';
        makeButtons().forEach(function (el) { el.disabled = busy || locked(); });
        if (clearBtn) clearBtn.disabled = busy;
        if (stopBtn) stopBtn.hidden = !busy;
        if (kind === 'clear') stepChunk = 40;
        else if (kind === 'build') stepChunk = 6;
    }

    function loop(chunk) {
        if (chunk) stepChunk = chunk;
        looping = true;
        function step() {
            if (!looping) return;
            U.post('/admin/video/make/step', {chunk: stepChunk}).then(function (res) {
                var data = jobOf(res);
                if (res && res.code !== 0 && !data.done) {
                    paint({ status: 'error', percent: 0, message: (res && res.msg) || L.job_stopped, done: 0, total: 0 });
                    looping = false;
                    idleButtons();
                    return;
                }
                paint(data);
                if (data.status === 'running') step();
                else {
                    looping = false;
                    idleButtons();
                }
            });
        }
        step();
    }

    function selected(name) {
        return Array.prototype.map.call(document.querySelectorAll('input[name="' + name + '"]:checked'), function (el) {
            return el.value;
        });
    }

    function startMake(payload) {
        if (locked()) {
            U.toast(L.enable_disk_first, 'err');
            return;
        }
        makeButtons().forEach(function (el) { el.disabled = true; });
        if (clearBtn) clearBtn.disabled = true;
        box.hidden = false;
        msg.textContent = L.listing_pages;
        bar.style.width = '0%';
        box.classList.remove('is-done', 'is-stop');
        U.post('/admin/video/make/start', payload).then(function (res) {
            var data = jobOf(res);
            if (res && res.code !== 0 && !data.status) {
                paint({ status: 'error', percent: 0, message: (res && res.msg) || L.start_fail, done: 0, total: 0 });
                idleButtons();
                return;
            }
            paint(data);
            if (data.status === 'running') loop(6);
            else idleButtons();
        });
    }

    document.querySelectorAll('[data-make]').forEach(function (el) {
        el.addEventListener('click', function () {
            var from = el.getAttribute('data-from') || '';
            var typeFrom = el.getAttribute('data-type-from') || '';
            var ids = from ? selected(from) : [];
            var typeIds = typeFrom ? selected(typeFrom) : [];
            var need = el.getAttribute('data-need') || '';
            if (need === 'ids' && !ids.length) {
                U.toast(L.please_select, 'err');
                return;
            }
            if (need === 'types' && !typeIds.length) {
                U.toast(L.please_select, 'err');
                return;
            }
            startMake({
                scope: el.getAttribute('data-scope') || 'all',
                ids: ids.join(','),
                type_ids: typeIds.join(','),
                when: el.getAttribute('data-when') || 'all',
                extra: el.getAttribute('data-extra') || ''
            });
        });
    });

    clearBtn && clearBtn.addEventListener('click', function () {
        if (!confirm(L.confirm_del_static)) return;
        makeButtons().forEach(function (el) { el.disabled = true; });
        clearBtn.disabled = true;
        box.hidden = false;
        msg.textContent = L.listing_files;
        bar.style.width = '0%';
        box.classList.remove('is-done', 'is-stop');
        U.post('/admin/video/make/clear', {}).then(function (res) {
            var data = jobOf(res);
            if (res && res.code !== 0 && !data.status) {
                paint({ status: 'error', percent: 0, message: (res && res.msg) || L.start_del_fail, done: 0, total: 0 });
                idleButtons();
                return;
            }
            paint(data);
            if (data.status === 'running') loop(40);
            else idleButtons();
        });
    });

    stopBtn && stopBtn.addEventListener('click', function () {
        looping = false;
        U.post('/admin/video/make/cancel', {}).then(function (res) {
            paint(jobOf(res));
            idleButtons();
        });
    });

    if (box.getAttribute('data-status') === 'running') {
        loop((box.getAttribute('data-kind') || 'build') === 'clear' ? 40 : 6);
    } else if ((box.getAttribute('data-status') || 'idle') !== 'idle') {
        stopBtn && (stopBtn.hidden = true);
    } else {
        idleButtons();
    }

    document.querySelectorAll('[data-map]').forEach(function (el) {
        el.addEventListener('click', function () {
            U.post('/admin/video/make/map', {scope: el.getAttribute('data-map')}).then(function (res) {
                U.toast((res && res.msg) || L.make_no_result, res && res.code === 0 ? 'ok' : 'err');
            });
        });
    });
})();
</script>
@endpush
