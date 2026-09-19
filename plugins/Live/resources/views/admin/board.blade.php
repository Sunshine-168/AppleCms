@extends('admin.layouts.inner')
@section('title', admin_t('nav.live'))

@php
    $desk = in_array(request('desk', 'channels'), ['channels', 'pending', 'categories', 'stats'], true)
        ? request('desk', 'channels')
        : 'channels';
    $categories = $categories ?? collect();
    $stats = is_array($stats ?? null) ? $stats : [];
    $chStat = is_array($stats['channels'] ?? null) ? $stats['channels'] : ['all' => 0, 'on' => 0, 'pending' => 0];
    $cateId = (int) request('cate_id', 0);
    $hasRecommend = (bool) ($hasRecommend ?? \Illuminate\Support\Facades\Schema::hasColumn('plugin_live_channels', 'recommend'));
    $headTitle = match ($desk) {
        'categories' => admin_t('live.title_categories'),
        'pending' => admin_t('live.title_pending'),
        'stats' => admin_t('live.title_stats'),
        default => admin_t('live.title'),
    };
@endphp

@section('plain')
<div class="card card-panel desk-board" id="live-board">
    <div class="card-header">
        <span>{{ $headTitle }} <em id="live-count"></em></span>
        <div>
            @if(in_array($desk, ['channels', 'pending'], true))
                <span class="btn-split" role="group" aria-label="{{ admin_t('live.aria_add_channel') }}">
                    <button type="button" class="btn btn-sm" id="live-add-btn">{{ admin_t('ui.add') }}</button>
                    <a class="btn btn-muted btn-sm" href="/admin/video/live-channels/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
                </span>
            @elseif($desk === 'categories')
                <span class="btn-split" role="group" aria-label="{{ admin_t('live.aria_add_category') }}">
                    <button type="button" class="btn btn-sm" id="live-cate-add-btn">{{ admin_t('ui.add') }}</button>
                    <a class="btn btn-muted btn-sm" href="/admin/video/live-categories/create">{{ admin_t('ui.full_form') }}</a>
                </span>
            @endif
            <a class="btn btn-muted btn-sm" href="/live" target="_blank" rel="noopener">{{ admin_t('ui.view_front') }}</a>
        </div>
    </div>
    <div class="card-body">
        <div class="queue-chips">
            <a class="chip{{ $desk === 'channels' ? ' active' : '' }}" href="/admin/video/lives">{{ admin_t('nav.live_channels') }}</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="?desk=pending">{{ admin_t('nav.live_pending') }}</a>
            <a class="chip{{ $desk === 'categories' ? ' active' : '' }}" href="?desk=categories">{{ admin_t('nav.live_categories') }}</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="?desk=stats">{{ admin_t('nav.live_stats') }}</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">{{ admin_t('live.lead_stats') }}</p>
            <div class="stat-grid dash">
                <div class="stat-card"><span>{{ admin_t('live.stat_channels') }}</span><strong>{{ (int) ($chStat['all'] ?? 0) }}</strong><span class="muted">{{ admin_t('live.stat_on_pending', ['on' => (int) ($chStat['on'] ?? 0), 'pending' => (int) ($chStat['pending'] ?? 0)]) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('live.stat_categories') }}</span><strong>{{ (int) ($stats['cate_total'] ?? 0) }}</strong><span class="muted">{{ admin_t('live.stat_hit_total', ['n' => (int) ($stats['hit_total'] ?? 0)]) }}</span></div>
            </div>
            <div class="flink-stats-split" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
                <div>
                    <h3>{{ admin_t('live.by_cate') }}</h3>
                    <ul class="plain-list">
                        @forelse(($stats['by_cate'] ?? []) as $row)
                            <li><a href="/admin/video/lives?cate_id={{ (int) $row['id'] }}">{{ $row['name'] }}</a> <em>{{ (int) $row['count'] }}</em></li>
                        @empty
                            <li class="muted">{{ admin_t('ui.none') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <h3>{{ admin_t('live.top_hits') }}</h3>
                    <ul class="plain-list">
                        @forelse(($stats['top_hits'] ?? []) as $row)
                            <li><a href="/admin/video/live-channels/{{ (int) $row['id'] }}/edit">{{ $row['title'] }}</a> <em>{{ (int) $row['hits'] }}</em></li>
                        @empty
                            <li class="muted">{{ admin_t('ui.none') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @else
            @if(in_array($desk, ['channels', 'pending'], true))
                <p class="muted recycle-lead">{{ admin_t('live.lead') }}</p>
                <div class="tag-compose" id="live-channel-compose-box">
                    <form class="tag-compose-form" id="live-channel-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="live-channel-quick">{{ admin_t('live.quick_channel') }}</label>
                        <div class="tag-compose-row">
                            <input id="live-channel-quick" type="text" name="title" value="" placeholder="{{ admin_t('live.ph_channel') }}" aria-label="{{ admin_t('live.add_channel') }}" autofocus>
                            @if($categories->isNotEmpty())
                                <select name="cate_id" aria-label="{{ admin_t('nav.live_categories') }}" style="max-width:160px">
                                    <option value="0">{{ admin_t('ui.uncategorized') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected($cateId === (int) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit" id="live-channel-add">{{ admin_t('ui.add') }}</button>
                                <button class="btn btn-muted" type="button" id="live-channel-compose-more">{{ admin_t('ui.fill_more') }}</button>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ $desk === 'pending' ? admin_t('live.hint_channel_pending') : admin_t('live.hint_channel_on') }}</p>
                    </form>
                </div>
            @elseif($desk === 'categories')
                <p class="muted recycle-lead">{{ admin_t('live.lead_categories') }}</p>
                <div class="tag-compose" id="live-cate-compose-box">
                    <form class="tag-compose-form" id="live-cate-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="live-cate-quick">{{ admin_t('live.quick_category') }}</label>
                        <div class="tag-compose-row">
                            <input id="live-cate-quick" type="text" name="name" value="" placeholder="{{ admin_t('live.ph_category') }}" aria-label="{{ admin_t('live.add_category') }}" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit" id="live-cate-add">{{ admin_t('ui.add') }}</button>
                                <button class="btn btn-muted" type="button" id="live-cate-compose-more">{{ admin_t('ui.fill_more') }}</button>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ admin_t('live.hint_category') }}</p>
                    </form>
                </div>
            @endif

            <form class="filter-bar" id="live-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="{{ $desk }}">
                <input type="search" name="q" placeholder="{{ $desk === 'categories' ? admin_t('live.ph_search_category') : admin_t('live.ph_search_channel') }}" autocomplete="off" aria-label="{{ admin_t('ui.search') }}">
                @if(in_array($desk, ['channels', 'pending'], true) && $categories->isNotEmpty())
                    <select name="cate_id" aria-label="{{ admin_t('nav.live_categories') }}">
                        <option value="">{{ admin_t('ui.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected($cateId === (int) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="live-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="live-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>

            <div class="batch-bar" id="live-batch" hidden>
                <strong id="live-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
                @if($desk !== 'categories')
                    <button type="button" class="btn btn-sm batch" data-action="status" data-value="1">{{ admin_t('ui.on') }}</button>
                    <button type="button" class="btn btn-muted btn-sm batch" data-action="status" data-value="0">{{ admin_t('ui.off') }}</button>
                @endif
                <button type="button" class="btn btn-danger btn-sm batch" data-action="delete">{{ admin_t('ui.delete') }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="live-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
            </div>
            <div id="live-table"></div>
        @endif
    </div>
</div>

<template id="channel-form">
    <form class="admin-form tag-form live-channel-dialog">
        <input type="hidden" name="desk" value="channels">
        <input type="hidden" name="play_from" value="hls">
        <input type="hidden" name="hits" value="0">

        <h3>{{ admin_t('ui.basic') }}</h3>
        <div class="form-field">
            <label for="live-dlg-title">{{ admin_t('live.channel_name') }}</label>
            <input id="live-dlg-title" class="entry-title" name="title" required placeholder="{{ admin_t('live.ph_channel_title') }}" autofocus>
        </div>
        <div class="form-field">
            <label for="live-dlg-sub">{{ admin_t('ui.subtitle') }}</label>
            <input id="live-dlg-sub" name="sub" placeholder="{{ admin_t('live.ph_subtitle') }}">
        </div>
        <div class="form-field">
            <label for="live-dlg-cate">{{ admin_t('ui.types') }}</label>
            <select id="live-dlg-cate" name="cate_id">
                <option value="0">{{ admin_t('ui.uncategorized') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">{!! str_replace(':link', '<a href="/admin/video/live-categories/create" target="_blank" rel="noopener">'.e(admin_t('live.new_category')).'</a>', e(admin_t('live.new_category_hint'))) !!}</p>
        </div>

        <div class="form-field">
            <label for="live-dlg-cover">{{ admin_t('ui.cover') }}</label>
            <div class="field-inline">
                <input id="live-dlg-cover" type="text" name="cover" placeholder="{{ admin_t('live.ph_cover') }}">
                <button type="button" class="btn btn-sm live-cover-upload">{{ admin_t('ui.upload') }}</button>
            </div>
            <img class="img-preview live-cover-preview" alt="" hidden>
        </div>

        <h3>{{ admin_t('ui.playback') }}</h3>
        <div class="form-field">
            <label for="live-dlg-urls">{{ admin_t('live.play_urls') }}</label>
            <textarea id="live-dlg-urls" name="urls" rows="5" placeholder="{{ admin_t('live.ph_urls_example') }}"></textarea>
            <p class="muted field-hint">{{ admin_t('live.play_urls_hint') }}</p>
        </div>

        <h3>{{ admin_t('ui.display') }}</h3>
        @if($hasRecommend)
            <div class="form-field">
                <label for="live-dlg-rec">{{ admin_t('ui.recommend_level') }}</label>
                <input id="live-dlg-rec" type="number" name="recommend" min="0" max="9" value="0">
                <p class="muted field-hint">{{ admin_t('live.recommend_hint') }}</p>
            </div>
        @endif
        <div class="live-dialog-grid">
            <div class="form-field">
                <label for="live-dlg-sort">{{ admin_t('ui.sort') }}</label>
                <input id="live-dlg-sort" type="number" name="sort" value="0">
            </div>
            <div class="form-field">
                <label for="live-dlg-status">{{ admin_t('ui.status') }}</label>
                <select id="live-dlg-status" name="status">
                    <option value="1">{{ admin_t('ui.on') }}</option>
                    <option value="0">{{ admin_t('ui.pending') }} / {{ admin_t('ui.off') }}</option>
                </select>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('live.sort_hint') }}</p>

        <div class="form-field">
            <label for="live-dlg-remarks">{{ admin_t('ui.remarks') }}</label>
            <input id="live-dlg-remarks" name="remarks" placeholder="{{ admin_t('live.ph_remarks') }}">
        </div>
        <div class="form-field">
            <label for="live-dlg-content">{{ admin_t('ui.intro') }}</label>
            <textarea id="live-dlg-content" name="content" rows="3" placeholder="{{ admin_t('ui.optional') }}"></textarea>
        </div>
    </form>
</template>

<template id="category-form">
    <form class="admin-form tag-form live-category-dialog">
        <input type="hidden" name="desk" value="categories">
        <div class="form-field">
            <label for="live-cate-dlg-name">{{ admin_t('live.category_name') }}</label>
            <input id="live-cate-dlg-name" class="entry-title" name="name" required placeholder="{{ admin_t('live.ph_category_name') }}" autofocus>
            <p class="muted field-hint">{{ admin_t('live.category_hint') }}</p>
        </div>
        <div class="form-field">
            <label for="live-cate-dlg-slug">{{ admin_t('ui.slug') }}</label>
            <input id="live-cate-dlg-slug" name="slug" placeholder="{{ admin_t('live.ph_slug') }}">
        </div>
        <div class="form-field">
            <label for="live-cate-dlg-pic">{{ admin_t('ui.image') }}</label>
            <div class="field-inline">
                <input id="live-cate-dlg-pic" type="text" name="pic" placeholder="{{ admin_t('live.ph_cover') }}">
                <button type="button" class="btn btn-sm live-cate-pic-upload">{{ admin_t('ui.upload') }}</button>
            </div>
            <img class="img-preview live-cate-pic-preview" alt="" hidden>
        </div>
        <div class="live-dialog-grid">
            <div class="form-field">
                <label for="live-cate-dlg-sort">{{ admin_t('ui.sort') }}</label>
                <input id="live-cate-dlg-sort" type="number" name="sort" value="0">
            </div>
            <div class="form-field">
                <label for="live-cate-dlg-status">{{ admin_t('ui.status') }}</label>
                <select id="live-cate-dlg-status" name="status">
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.disabled') }}</option>
                </select>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('live.category_disabled_hint') }}</p>
    </form>
</template>
@endsection

@php
    $liveJsLang = [
        'add' => admin_t('ui.add'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'quick' => admin_t('ui.quick'),
        'front' => admin_t('ui.front'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'added' => admin_t('ui.added'),
        'done' => admin_t('ui.done'),
        'actions' => admin_t('ui.actions'),
        'status' => admin_t('ui.status'),
        'sort' => admin_t('ui.sort'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'pending' => admin_t('ui.pending'),
        'uncategorized' => admin_t('ui.uncategorized'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'clear_filter' => admin_t('ui.clear_filter'),
        'no_match' => admin_t('ui.no_match'),
        'please_select' => admin_t('ui.please_select'),
        'col_channel' => admin_t('live.col_channel'),
        'col_category' => admin_t('live.col_category'),
        'col_recommend' => admin_t('live.col_recommend'),
        'col_hits' => admin_t('live.col_hits'),
        'add_channel' => admin_t('live.add_channel'),
        'add_category' => admin_t('live.add_category'),
        'edit_channel' => admin_t('live.edit_channel'),
        'edit_category' => admin_t('live.edit_category'),
        'empty_channel' => admin_t('live.empty_channel'),
        'empty_channel_hint' => admin_t('live.empty_channel_hint'),
        'empty_pending' => admin_t('live.empty_pending'),
        'empty_pending_hint' => admin_t('live.empty_pending_hint'),
        'empty_category' => admin_t('live.empty_category'),
        'empty_category_hint' => admin_t('live.empty_category_hint'),
        'need_title' => admin_t('live.need_title'),
        'need_name' => admin_t('live.need_name'),
        'confirm_del' => admin_t('live.confirm_del', ['name' => '__NAME__']),
        'confirm_batch_channels' => admin_t('live.confirm_batch_channels'),
        'confirm_batch_categories' => admin_t('live.confirm_batch_categories'),
        'channels_n' => admin_t('live.channels_n', ['n' => '__N__']),
        'rec_n' => admin_t('live.rec_n', ['n' => '__N__']),
        'nav_channels' => admin_t('nav.live_channels'),
    ];
@endphp

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    var url = '/admin/video/lives';
    if (desk === 'stats') return;

    var I = @json($liveJsLang);
    function fillTpl(tpl, map) {
        var out = String(tpl || '');
        Object.keys(map || {}).forEach(function (k) {
            out = out.split(k).join(String(map[k]));
        });
        return out;
    }

    var form = document.getElementById('live-search');
    var batchBar = document.getElementById('live-batch');
    var batchCount = document.getElementById('live-batch-count');
    var countEl = document.getElementById('live-count');
    var prefillCate = @json($cateId > 0 ? $cateId : 0);
    var pendingDefault = desk === 'pending';
    var hasRecommend = @json($hasRecommend);

    function cleanWhere(data) {
        var out = { desk: desk, limit: 20 };
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        out.desk = desk;
        return out;
    }
    function queryWhere() {
        return cleanWhere(form ? U.formData(form) : { desk: desk });
    }

    var cols;
    if (desk === 'categories') {
        cols = [
            { check: true, width: 36 },
            { title: I.col_category, html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/live-categories/' + d.id + '/edit">' + U.escape(d.name || '') + '</a>'
                    + '<div class="entry-row-meta">#' + U.escape(d.id) + (d.slug ? ' · ' + U.escape(d.slug) : '') + ' · ' + U.escape(fillTpl(I.channels_n, {__N__: String(d.channel_count || 0)})) + '</div>';
            }},
            { key: 'sort', title: I.sort, width: 64 },
            { title: I.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, I.enabled) : U.status(false, I.disabled);
            }},
            { title: I.actions, cls: 'actions', html: function (d) {
                return '<a class="btn-link" href="/admin/video/lives?cate_id=' + d.id + '">' + U.escape(I.nav_channels) + '</a> <a href="#" class="btn-link js-quick">' + U.escape(I.quick) + '</a> <a class="btn-link" href="/admin/video/live-categories/' + d.id + '/edit">' + U.escape(I.edit) + '</a> <a href="#" class="btn-link js-del">' + U.escape(I.delete) + '</a>';
            }}
        ];
    } else {
        cols = [
            { check: true, width: 36 },
            { title: I.col_channel, html: function (d) {
                var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + U.escape(I.pending) + '</span>';
                var rec = parseInt(d.recommend, 10) || 0;
                return '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/live-channels/' + d.id + '/edit">' + U.escape(d.title || '') + '</a> ' + badge + '</div>'
                    + '<div class="entry-row-meta">' + U.escape(d.sub || d.cate_name || '') + (rec > 0 ? ' · ' + U.escape(fillTpl(I.rec_n, {__N__: String(rec)})) : '') + '</div>';
            }},
            { title: I.col_category, width: 110, html: function (d) { return U.escape(d.cate_name || I.uncategorized); }},
            { key: 'recommend', title: I.col_recommend, width: 64 },
            { key: 'hits', title: I.col_hits, width: 72 },
            { key: 'sort', title: I.sort, width: 64 },
            { title: I.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, I.on) : U.status(false, I.pending);
            }},
            { title: I.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (d.front_url) html += '<a href="' + U.escape(d.front_url) + '" target="_blank" rel="noopener" class="btn-link">' + U.escape(I.front) + '</a> ';
                html += '<a href="#" class="btn-link js-quick">' + U.escape(I.quick) + '</a> ';
                html += '<a class="btn-link" href="/admin/video/live-channels/' + d.id + '/edit">' + U.escape(I.edit) + '</a> ';
                html += '<a href="#" class="btn-link js-del">' + U.escape(I.delete) + '</a>';
                return html;
            }}
        ];
    }

    var table = U.table({
        el: '#live-table',
        countEl: countEl,
        url: url + '/list',
        where: queryWhere(),
        emptyHtml: function (_p, where) {
            var filtered = Object.keys(where || {}).some(function (k) {
                return k !== 'limit' && k !== 'desk' && where[k] !== '' && where[k] != null;
            });
            if (filtered) {
                return '<div class="list-empty"><p>' + U.escape(I.no_match) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="live-empty-reset">' + U.escape(I.clear_filter) + '</button></p></div>';
            }
            if (desk === 'categories') {
                return '<div class="list-empty"><p>' + U.escape(I.empty_category) + '</p><p class="muted">' + U.escape(I.empty_category_hint) + '</p><p><button type="button" class="btn btn-primary btn-sm" id="live-empty-add">' + U.escape(I.add_category) + '</button></p></div>';
            }
            if (desk === 'pending') {
                return '<div class="list-empty"><p>' + U.escape(I.empty_pending) + '</p><p class="muted">' + U.escape(I.empty_pending_hint) + '</p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(I.empty_channel) + '</p><p class="muted">' + U.escape(I.empty_channel_hint) + '</p><p><button type="button" class="btn btn-primary btn-sm" id="live-empty-add">' + U.escape(I.add_channel) + '</button></p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('live-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                if (form) form.reset();
                if (prefillCate && form && form.cate_id) form.cate_id.value = String(prefillCate);
                table.reload(queryWhere());
            });
            var emptyAdd = document.getElementById('live-empty-add');
            if (emptyAdd) emptyAdd.addEventListener('click', function () {
                if (desk === 'categories') openCategory({});
                else openChannel({});
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = fillTpl(I.selected_n, {__N__: String(ids.length)});
        },
        cols: cols
    });

    function openChannel(row) {
        row = row || {};
        U.dialog({
            title: row.id ? I.edit_channel : I.add_channel,
            wide: true,
            content: document.getElementById('channel-form').innerHTML,
            onOpen: function (body) {
                var f = body.querySelector('form');
                var fill = {
                    title: row.title || '',
                    sub: row.sub || '',
                    cate_id: row.cate_id != null ? row.cate_id : (prefillCate || 0),
                    cover: row.cover || '',
                    urls: row.urls || '',
                    remarks: row.remarks || '',
                    content: row.content || '',
                    sort: row.sort != null ? row.sort : 0,
                    status: row.status != null ? String(row.status) : (pendingDefault && !row.id ? '0' : '1'),
                    play_from: 'hls',
                    hits: row.hits != null ? row.hits : 0
                };
                if (hasRecommend) fill.recommend = row.recommend != null ? row.recommend : 0;
                U.fillForm(f, fill);
                U.bindImageField(f, {
                    input: 'input[name=cover]',
                    btn: '.live-cover-upload',
                    preview: '.live-cover-preview'
                });
            },
            onSave: function (body) {
                var d = U.formData(body.querySelector('form'));
                if (!String(d.title || '').trim()) { U.toast(I.need_title, 'err'); return false; }
                if (row.id) d.id = row.id;
                d.desk = 'channels';
                d.play_from = 'hls';
                return U.post(url + '/save', d).then(function (r) {
                    if (!r || r.code !== 0) { U.toast((r && r.msg) || I.fail, 'err'); return false; }
                    U.toast(row.id ? I.saved : I.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function openCategory(row) {
        row = row || {};
        U.dialog({
            title: row.id ? I.edit_category : I.add_category,
            wide: true,
            content: document.getElementById('category-form').innerHTML,
            onOpen: function (body) {
                var f = body.querySelector('form');
                U.fillForm(f, {
                    name: row.name || '',
                    slug: row.slug || '',
                    pic: row.pic || '',
                    sort: row.sort != null ? row.sort : 0,
                    status: row.status != null ? String(row.status) : '1'
                });
                U.bindImageField(f, {
                    input: 'input[name=pic]',
                    btn: '.live-cate-pic-upload',
                    preview: '.live-cate-pic-preview'
                });
            },
            onSave: function (body) {
                var d = U.formData(body.querySelector('form'));
                if (!String(d.name || '').trim()) { U.toast(I.need_name, 'err'); return false; }
                if (row.id) d.id = row.id;
                d.desk = 'categories';
                return U.post(url + '/save', d).then(function (r) {
                    if (!r || r.code !== 0) { U.toast((r && r.msg) || I.fail, 'err'); return false; }
                    U.toast(row.id ? I.saved : I.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function quickAddChannel(e) {
        e.preventDefault();
        var f = document.getElementById('live-channel-compose');
        var d = U.formData(f);
        if (!String(d.title || '').trim()) { U.toast(I.need_title, 'err'); return; }
        d.desk = 'channels';
        d.status = pendingDefault ? 0 : 1;
        d.play_from = 'hls';
        d.urls = d.urls || '';
        d.sort = 0;
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || I.fail, 'err'); return; }
            f.title.value = '';
            f.title.focus();
            U.toast(I.added, 'ok');
            table.refresh();
        });
    }
    function quickAddCate(e) {
        e.preventDefault();
        var f = document.getElementById('live-cate-compose');
        var d = U.formData(f);
        if (!String(d.name || '').trim()) { U.toast(I.need_name, 'err'); return; }
        d.desk = 'categories';
        d.status = 1;
        d.sort = 0;
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || I.fail, 'err'); return; }
            f.name.value = '';
            f.name.focus();
            U.toast(I.added, 'ok');
            table.refresh();
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(I.please_select, 'err'); return; }
        if (action === 'delete' && !U.confirm(desk === 'categories' ? I.confirm_batch_categories : I.confirm_batch_channels)) return;
        U.post(url + '/batch', {
            ids: ids.join(','),
            desk: desk === 'pending' ? 'channels' : desk,
            action: action,
            value: value || ''
        }).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || I.fail, 'err'); return; }
            table.refresh();
            U.toast((r && r.msg) || I.done, 'ok');
        });
    }

    U.on('#live-channel-compose', 'submit', quickAddChannel);
    U.on('#live-cate-compose', 'submit', quickAddCate);
    U.on('#live-add-btn', 'click', function () { openChannel({}); });
    U.on('#live-cate-add-btn', 'click', function () { openCategory({}); });
    U.on('#live-channel-compose-more', 'click', function () {
        var f = document.getElementById('live-channel-compose');
        var title = f && f.title ? String(f.title.value || '').trim() : '';
        openChannel(title ? { title: title, cate_id: f.cate_id ? f.cate_id.value : 0 } : {});
    });
    U.on('#live-cate-compose-more', 'click', function () {
        var f = document.getElementById('live-cate-compose');
        var name = f && f.name ? String(f.name.value || '').trim() : '';
        openCategory(name ? { name: name } : {});
    });
    U.on('#live-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#live-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillCate && form && form.cate_id) form.cate_id.value = String(prefillCate);
            table.reload(queryWhere());
        }, 0);
    });
    U.on('#live-batch-clear', 'click', function () { table.clearSelection(); });
    document.querySelectorAll('.batch').forEach(function (btn) {
        btn.addEventListener('click', function () { batch(btn.dataset.action, btn.dataset.value); });
    });
    U.on('#live-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (a.classList.contains('js-quick')) {
            e.preventDefault();
            if (desk === 'categories') openCategory(row);
            else openChannel(row);
            return;
        }
        if (!a.classList.contains('js-del')) return;
        e.preventDefault();
        if (!U.confirm(fillTpl(I.confirm_del, {__NAME__: row.title || row.name || ''}))) return;
        U.post(url + '/delete', { id: row.id, desk: desk === 'pending' ? 'channels' : desk }).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || I.fail, 'err'); return; }
            table.refresh();
            U.toast(I.deleted, 'ok');
        });
    });
})();
</script>
@endpush
