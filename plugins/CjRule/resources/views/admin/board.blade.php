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
    $cjJsLang = [
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'finished' => admin_t('ui.finished'),
        'fetching' => admin_t('ui.fetching'),
        'try_fetch_fail' => admin_t('ui.try_fetch_fail'),
        'try_fetch_fail_hint' => admin_t('ui.try_fetch_fail_hint'),
        'untitled' => admin_t('ui.untitled'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_cj_logs' => admin_t('ui.empty_cj_logs'),
        'empty_cj_logs_hint' => admin_t('ui.empty_cj_logs_hint'),
        'empty_cj' => admin_t('ui.empty_cj'),
        'empty_cj_hint' => admin_t('ui.empty_cj_hint'),
        'new_website_collect' => admin_t('ui.new_website_collect'),
        'collect_source_col' => admin_t('ui.collect_source_col'),
        'result' => admin_t('ui.result'),
        'note_col' => admin_t('ui.note_col'),
        'success' => admin_t('ui.success'),
        'jobs' => admin_t('ui.jobs'),
        'disabled' => admin_t('ui.disabled'),
        'enabled' => admin_t('ui.enabled'),
        'last_fail' => admin_t('ui.last_fail'),
        'every_n_min_max' => admin_t('ui.every_n_min_max'),
        'write_col' => admin_t('ui.write_col'),
        'chip_videos' => admin_t('ui.chip_videos'),
        'no_category' => admin_t('ui.no_category'),
        'last_run' => admin_t('ui.last_run'),
        'never_ran' => admin_t('ui.never_ran'),
        'collect_now' => admin_t('ui.collect_now'),
        'confirm_del_cj' => admin_t('ui.confirm_del_cj'),
        'n_files' => admin_t('ui.n_files'),
        'list_page_url' => admin_t('ui.list_page_url'),
        'cj_url_hint_html' => admin_t('ui.cj_url_hint_html'),
        'rss_url' => admin_t('ui.rss_url'),
        'rss_url_hint' => admin_t('ui.rss_url_hint'),
        'json_url' => admin_t('ui.json_url'),
        'json_url_hint' => admin_t('ui.json_url_hint'),
        'publish_vod' => admin_t('ui.publish_vod'),
        'publish_vod_hint' => admin_t('ui.publish_vod_hint'),
        'cat_hint_vod' => admin_t('ui.cat_hint_vod'),
        'publish_art' => admin_t('ui.publish_art'),
        'publish_art_hint' => admin_t('ui.publish_art_hint'),
        'cat_hint_art' => admin_t('ui.cat_hint_art'),
        'publish_manga' => admin_t('ui.publish_manga'),
        'publish_manga_hint' => admin_t('ui.publish_manga_hint'),
        'cat_hint_manga' => admin_t('ui.cat_hint_manga'),
    ];
@endphp

@section('plain')
<div class="card card-panel cj-board desk-board" id="cj-board">
    <div class="card-header">
        <span>
            @if($desk === 'form')
                {{ $editId > 0 ? admin_t('ui.edit_website_collect') : admin_t('ui.new_website_collect') }}
            @else
                {{ admin_t('ui.website_collect') }} <em id="cj-count"></em>
            @endif
        </span>
        <div>
            @if($desk === 'rules')
                <a class="btn btn-sm" href="/admin/video/cj?desk=form">{{ admin_t('ui.add') }}</a>
            @elseif($desk === 'form')
                <a class="btn btn-muted btn-sm" href="/admin/video/cj">{{ admin_t('ui.back_list') }}</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($desk !== 'form')
            <p class="muted recycle-lead">{{ admin_t('ui.cj_lead') }}<a href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>{{ admin_t('ui.cj_lead_after') }}</p>
            <div class="queue-chips">
                <a class="chip{{ $desk === 'rules' ? ' active' : '' }}" href="/admin/video/cj">{{ admin_t('ui.jobs') }}</a>
                <a class="chip{{ $desk === 'logs' ? ' active' : '' }}" href="/admin/video/cj?desk=logs">{{ admin_t('ui.run_logs') }}</a>
            </div>
        @endif

        @if($desk === 'form')
            <form class="admin-form collector-form" id="cj-form">
                <input type="hidden" name="id" value="{{ $editId > 0 ? $editId : '' }}">
                <input type="hidden" name="desk" value="form">

                <label for="cj-name">{{ admin_t('ui.name') }}</label>
                <input id="cj-name" type="text" name="name" value="{{ $collector['name'] ?? '' }}" placeholder="{{ admin_t('ui.ph_cj_name') }}" required autofocus>
                <p class="muted field-hint">{{ admin_t('ui.name_list_hint') }}</p>

                <label>{{ admin_t('ui.collect_method') }}</label>
                <div class="collector-types" id="cj-types">
                    @foreach([
                        'html' => [admin_t('ui.cj_type_html'), admin_t('ui.cj_type_html_hint')],
                        'rss' => [admin_t('ui.cj_type_rss'), admin_t('ui.cj_type_rss_hint')],
                        'json' => [admin_t('ui.cj_type_json'), admin_t('ui.cj_type_json_hint')],
                    ] as $value => $meta)
                        <label class="collector-type{{ $type === $value ? ' is-on' : '' }}">
                            <input type="radio" name="type" value="{{ $value }}" @checked($type === $value)>
                            <strong>{{ $meta[0] }}</strong>
                            <span>{{ $meta[1] }}</span>
                        </label>
                    @endforeach
                </div>

                <label>{{ admin_t('ui.write_into') }}</label>
                <div class="collector-types" id="cj-into">
                    @foreach([
                        'vod' => [admin_t('ui.chip_videos'), admin_t('ui.cj_into_vod_hint')],
                        'art' => [admin_t('ui.articles'), admin_t('ui.cj_into_art_hint')],
                        'manga' => [admin_t('ui.chip_manga'), $mangaReady ? admin_t('ui.cj_into_manga_hint') : admin_t('ui.manga_plugin_off')],
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
                <p class="muted field-hint">{{ admin_t('ui.cj_into_hint') }}</p>

                <label for="cj-url" id="cj-url-label">{{ admin_t('ui.list_page_url') }}</label>
                <input id="cj-url" type="url" name="source_url" value="{{ $collector['source_url'] ?? '' }}" placeholder="https://" required>
                <p class="muted field-hint" id="cj-url-hint">{{ admin_t('ui.cj_url_hint_html') }}</p>

                <label for="cj-cat" id="cj-cat-label">{{ admin_t('ui.write_category') }}</label>
                <select id="cj-cat" name="type_id">
                    <option value="0">{{ admin_t('ui.no_category') }}</option>
                    @foreach($catTypes as $cat)
                        <option value="{{ $cat['id'] }}" @selected((int) ($collector['type_id'] ?? 0) === (int) $cat['id'])>{{ $cat['name'] }}</option>
                    @endforeach
                </select>
                <p class="muted field-hint" id="cj-cat-hint">{{ admin_t('ui.cj_cat_hint') }}</p>

                <div class="collector-inline">
                    <div>
                        <label for="cj-interval">{{ admin_t('ui.interval_min') }}</label>
                        <input id="cj-interval" type="number" name="interval_minutes" value="{{ (int) ($collector['interval_minutes'] ?? 60) }}" min="1">
                    </div>
                    <div>
                        <label for="cj-limit">{{ admin_t('ui.max_items_each') }}</label>
                        <input id="cj-limit" type="number" name="limit_items" value="{{ (int) ($collector['limit_items'] ?? 10) }}" min="1" max="100">
                    </div>
                </div>

                <label class="inline">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" @checked((int) ($collector['status'] ?? 1) === 1)>
                    {{ admin_t('ui.enable_with_schedule') }}
                </label>
                <label class="inline" id="cj-publish-wrap">
                    <input type="hidden" name="publish_immediately" value="0">
                    <input type="checkbox" name="publish_immediately" value="1" @checked((int) ($collector['publish_immediately'] ?? 0) === 1)>
                    <span id="cj-publish-label">{{ admin_t('ui.publish_vod') }}</span>
                </label>
                <p class="muted field-hint" id="cj-publish-hint">{{ admin_t('ui.publish_vod_hint') }}</p>

                <div id="cj-html-opts" class="collector-box" @if($type !== 'html') hidden @endif>
                    <h3>{{ admin_t('ui.how_html') }}</h3>
                    <p class="muted field-hint">{{ admin_t('ui.html_how_hint') }}</p>
                    <label for="item_selector">{{ admin_t('ui.list_items') }}</label>
                    <input id="item_selector" type="text" name="item_selector" value="{{ $collector['item_selector'] ?? '' }}" placeholder="article.post">
                    <p class="muted field-hint">{{ admin_t('ui.list_items_hint') }}</p>

                    <label for="link_selector">{{ admin_t('ui.title_link') }}</label>
                    <input id="link_selector" type="text" name="link_selector" value="{{ $collector['link_selector'] ?? 'a' }}" placeholder="h2 a">
                    <p class="muted field-hint">{{ admin_t('ui.title_link_hint') }}</p>

                    <label for="title_selector">{{ admin_t('ui.title_optional') }}</label>
                    <input id="title_selector" type="text" name="title_selector" value="{{ $collector['title_selector'] ?? '' }}" placeholder="h2 a">

                    <label for="summary_selector">{{ admin_t('ui.summary_optional') }}</label>
                    <input id="summary_selector" type="text" name="summary_selector" value="{{ $collector['summary_selector'] ?? '' }}" placeholder=".excerpt">

                    <label for="cover_selector">{{ admin_t('ui.cover_optional') }}</label>
                    <input id="cover_selector" type="text" name="cover_selector" value="{{ $collector['cover_selector'] ?? '' }}" placeholder="img">

                    <div class="cj-play-only">
                    <label for="play_selector">{{ admin_t('ui.play_url_optional') }}</label>
                    <input id="play_selector" type="text" name="play_selector" value="{{ $collector['play_selector'] ?? '' }}" placeholder="a.play">
                    <p class="muted field-hint">{{ admin_t('ui.play_url_hint') }}</p>
                    </div>

                    <label for="detail_content_selector">{{ admin_t('ui.detail_summary') }}</label>
                    <input id="detail_content_selector" type="text" name="detail_content_selector" value="{{ $collector['detail_content_selector'] ?? '' }}" placeholder="article.body">
                    <p class="muted field-hint">{{ admin_t('ui.detail_summary_hint') }}</p>

                    <h3>{{ admin_t('ui.paging') }}</h3>
                    <label for="page_count">{{ admin_t('ui.max_pages') }}</label>
                    <input id="page_count" type="number" name="page_count" value="{{ (int) ($collector['page_count'] ?? 1) }}" min="1" max="10">
                    <p class="muted field-hint">{{ admin_t('ui.max_pages_hint') }}</p>

                    <label for="page_url">{{ admin_t('ui.page_url_opt') }}</label>
                    <input id="page_url" type="text" name="page_url" value="{{ $collector['page_url'] ?? '' }}" placeholder="https://example.com/list?page={page}">
                    <p class="muted field-hint">{{ admin_t('ui.page_url_hint') }}</p>

                    <label for="next_selector">{{ admin_t('ui.next_btn') }}</label>
                    <input id="next_selector" type="text" name="next_selector" value="{{ $collector['next_selector'] ?? '' }}" placeholder="a.next">
                    <p class="muted field-hint">{{ admin_t('ui.next_btn_hint') }}</p>
                </div>

                <div id="cj-json-opts" class="collector-box" @if($type !== 'json') hidden @endif>
                    <h3>{{ admin_t('ui.api_fields') }}</h3>
                    <label>{{ admin_t('ui.list_path') }}</label>
                    <input type="text" name="list_path" value="{{ $collector['list_path'] ?? 'items' }}">
                    <p class="muted field-hint">{{ admin_t('ui.list_path_hint') }}</p>
                    <label>{{ admin_t('ui.title_field') }}</label>
                    <input type="text" name="title_key" value="{{ $collector['title_key'] ?? 'title' }}">
                    <label>{{ admin_t('ui.link_field') }}</label>
                    <input type="text" name="link_key" value="{{ $collector['link_key'] ?? 'url' }}">
                    <label>{{ admin_t('ui.summary_field') }}</label>
                    <input type="text" name="summary_key" value="{{ $collector['summary_key'] ?? 'summary' }}">
                    <label>{{ admin_t('ui.content_field') }}</label>
                    <input type="text" name="content_key" value="{{ $collector['content_key'] ?? 'content' }}">
                    <label>{{ admin_t('ui.guid_field') }}</label>
                    <input type="text" name="guid_key" value="{{ $collector['guid_key'] ?? 'id' }}">
                    <label>{{ admin_t('ui.cover_field') }}</label>
                    <input type="text" name="cover_key" value="{{ $collector['cover_key'] ?? 'cover' }}">
                    <div class="cj-play-only">
                    <label>{{ admin_t('ui.play_field') }}</label>
                    <input type="text" name="play_key" value="{{ $collector['play_key'] ?? 'play' }}">
                    <p class="muted field-hint">{{ admin_t('ui.play_field_hint') }}</p>
                    </div>
                </div>

                <div id="cj-preview" class="collector-preview" hidden>
                    <p class="collector-preview-msg muted"></p>
                    <ol class="collector-preview-list"></ol>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit" id="cj-save-btn">{{ $editId > 0 ? admin_t('ui.save') : admin_t('ui.add_action') }}</button>
                    <button class="btn btn-muted" type="button" id="cj-preview-btn">{{ admin_t('ui.try_fetch') }}</button>
                    <a class="btn btn-muted" href="/admin/video/cj">{{ admin_t('ui.cancel') }}</a>
                </div>
            </form>

            @if($recentLogs !== [])
                <h3 style="margin-top:28px">{{ admin_t('ui.recent_runs') }}</h3>
                <table class="data">
                    <thead><tr><th>{{ admin_t('ui.time') }}</th><th>{{ admin_t('ui.result') }}</th><th>{{ admin_t('ui.note_col') }}</th></tr></thead>
                    <tbody>
                    @foreach($recentLogs as $log)
                        <tr>
                            <td>{{ $log['created_at'] ?? '' }}</td>
                            <td>
                                @if(($log['status'] ?? '') === 'ok')
                                    <span class="badge badge-ok">{{ admin_t('ui.success') }}</span>
                                @elseif(($log['status'] ?? '') === 'fail')
                                    <span class="badge badge-warn">{{ admin_t('ui.fail') }}</span>
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
                <input type="search" name="q" placeholder="{{ $desk === 'logs' ? admin_t('ui.ph_search_cj_logs') : admin_t('ui.ph_search_cj') }}" autocomplete="off">
                @if($desk === 'rules')
                    <select name="type" aria-label="{{ admin_t('ui.method_aria') }}">
                        <option value="">{{ admin_t('ui.all_methods') }}</option>
                        <option value="html">{{ admin_t('ui.cj_type_html') }}</option>
                        <option value="rss">{{ admin_t('ui.cj_type_rss') }}</option>
                        <option value="json">{{ admin_t('ui.cj_type_json') }}</option>
                    </select>
                    <select name="status" aria-label="{{ admin_t('ui.status') }}">
                        <option value="">{{ admin_t('ui.all_status') }}</option>
                        <option value="1">{{ admin_t('ui.enabled') }}</option>
                        <option value="0">{{ admin_t('ui.disabled') }}</option>
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="cj-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="cj-reset-btn">{{ admin_t('ui.reset') }}</button>
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
    var desk = @json($desk, JSON_UNESCAPED_UNICODE);
    var L = @json($cjJsLang, JSON_UNESCAPED_UNICODE);
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
            vod: @json($vodTypes, JSON_UNESCAPED_UNICODE),
            art: @json($artTypes, JSON_UNESCAPED_UNICODE),
            manga: @json($mangaTypes, JSON_UNESCAPED_UNICODE)
        };
        var intoHints = {
            vod: [L.publish_vod, L.publish_vod_hint, L.cat_hint_vod],
            art: [L.publish_art, L.publish_art_hint, L.cat_hint_art],
            manga: [L.publish_manga, L.publish_manga_hint, L.cat_hint_manga]
        };
        var selectedInto = function () {
            var on = form && form.querySelector('input[name="into"]:checked');
            return (on && on.value) || 'vod';
        };
        var fillCats = function (into, keepId) {
            if (!catSel) return;
            var rows = catalogs[into] || [];
            var html = '<option value="0">' + U.escape(L.no_category || '') + '</option>';
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
            html: [L.list_page_url, L.cj_url_hint_html],
            rss: [L.rss_url, L.rss_url_hint],
            json: [L.json_url, L.json_url_hint]
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
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                    U.toast(res.msg || L.saved, 'ok');
                    location.href = '/admin/video/cj';
                });
            });
        }
        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                if (!form || !previewBox) return;
                previewBtn.disabled = true;
                previewBox.hidden = false;
                if (previewMsg) previewMsg.textContent = L.fetching;
                if (previewList) previewList.innerHTML = '';
                U.post('/admin/video/cj/try', U.formData(form)).then(function (res) {
                    if (previewMsg) previewMsg.textContent = (res && res.msg) || L.try_fetch_fail;
                    var items = (res && res.data && res.data.items) || [];
                    items.forEach(function (item) {
                        var li = document.createElement('li');
                        var title = document.createElement(item.link ? 'a' : 'span');
                        title.textContent = item.title || L.untitled;
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
                    if (previewMsg) previewMsg.textContent = L.try_fetch_fail_hint;
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
            return '<div class="list-empty"><p>' + U.escape(L.no_match) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="cj-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
        }
        if (desk === 'logs') {
            return '<div class="list-empty"><p>' + U.escape(L.empty_cj_logs) + '</p><p class="muted">' + U.escape(L.empty_cj_logs_hint) + '</p></div>';
        }
        return '<div class="list-empty"><p>' + U.escape(L.empty_cj) + '</p><p class="muted">' + U.escape(L.empty_cj_hint) + '</p><p><a class="btn btn-sm" href="/admin/video/cj?desk=form">' + U.escape(L.new_website_collect) + '</a></p></div>';
    }
    var cols = [];
    if (desk === 'logs') {
        cols = [
            {title: L.collect_source_col, html: function (d) {
                return '<a href="/admin/video/cj?desk=form&id=' + U.escape(String(d.rule_id || '')) + '">' + U.escape(d.rule_name || AdminUi.t('unnamed')) + '</a>';
            }},
            {title: L.result, width: 80, html: function (d) {
                return d.status === 'ok' ? U.status(true, L.success) : U.status(false, d.status_label || L.fail);
            }},
            {title: L.note_col, html: function (d) { return '<span class="muted">' + U.escape(d.message || '') + '</span>'; }},
            {title: AdminUi.t('time'), width: 130, html: function (d) { return U.escape(d.created_at || ''); }}
        ];
    } else {
        cols = [
            {title: L.jobs, html: function (d) {
                var html = '<div><a href="/admin/video/cj?desk=form&id=' + U.escape(String(d.id || '')) + '">' + U.escape(d.name || AdminUi.t('unnamed')) + '</a>';
                html += ' <span class="badge">' + U.escape(d.type_label || '') + '</span>';
                if (String(d.status) !== '1') html += ' <span class="badge badge-off">' + U.escape(L.disabled) + '</span>';
                if (String(d.status) === '1' && d.last_status === 'fail') html += ' <span class="badge badge-warn">' + U.escape(L.last_fail) + '</span>';
                html += '</div>';
                html += '<div class="muted">' + U.escape((L.every_n_min_max || '').replace(':min', String(d.interval_minutes || 60)).replace(':n', String(d.limit_items || 10))) + '</div>';
                return html;
            }},
            {title: L.write_col, width: 160, html: function (d) {
                var into = U.escape(d.into_label || L.chip_videos);
                var cat = U.escape(d.type_name || L.no_category);
                return into + ' · ' + cat;
            }},
            {title: L.last_run, width: 160, html: function (d) {
                var html = U.escape(d.last_run_text || L.never_ran);
                if (d.last_message) html += '<div class="muted">' + U.escape(String(d.last_message).slice(0, 36)) + '</div>';
                return html;
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                var on = String(d.status) === '1';
                return '<a href="#" class="btn-link js-run">' + U.escape(L.collect_now) + '</a>'
                    + '<a href="#" class="btn-link js-toggle">' + U.escape(on ? L.disabled : L.enabled) + '</a>'
                    + '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
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
            if (countEl) countEl.textContent = parsed && parsed.total ? (L.n_files || '').replace(':n', parsed.total) : '';
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
                U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
                if (res && res.code === 0) table.refresh();
            });
        }
        if (a.classList.contains('js-toggle')) {
            e.preventDefault();
            U.post('/admin/video/cj/toggle', {id: row.id}).then(function (res) {
                U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
                if (res && res.code === 0) table.refresh();
            });
        }
        if (a.classList.contains('js-del')) {
            e.preventDefault();
            if (!confirm((L.confirm_del_cj || '').replace(':name', row.name || AdminUi.t('unnamed')))) return;
            U.post('/admin/video/cj/delete', {id: row.id, desk: 'rules'}).then(function (res) {
                U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
                if (res && res.code === 0) table.refresh();
            });
        }
    });
})();
</script>
@endpush
