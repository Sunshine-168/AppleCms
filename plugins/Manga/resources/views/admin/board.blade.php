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
        <span>{{ $desk === 'stats' ? admin_t('manga.title_stats') : ($desk === 'work' ? admin_t('manga.title_workbench') : ($desk === 'chapters' ? admin_t('ui.chapters') : ($desk === 'pics' ? admin_t('ui.pics') : ($desk === 'comments' ? admin_t('ui.comments') : ($desk === 'favors' ? admin_t('ui.bookshelf') : admin_t('manga.title')))))) }} <em id="manga-count"></em></span>
        <div>
            @if(in_array($desk, ['works', 'pending'], true))
                <span class="btn-split" role="group" aria-label="{{ admin_t('manga.add_work_aria') }}">
                    <button type="button" class="btn btn-sm" data-focus="#manga-work-quick">{{ admin_t('ui.add_work') }}</button>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
                </span>
            @elseif(in_array($desk, ['chapters', 'work'], true))
                <span class="btn-split" role="group" aria-label="{{ admin_t('novel.add_chapter_aria') }}">
                    <button type="button" class="btn btn-sm" data-focus="#manga-chapter-quick">{{ admin_t('ui.add_chapter') }}</button>
                    <a class="btn btn-muted btn-sm" href="/admin/video/manga-chapters/create{{ ($desk === 'work' && $work) ? '?manga_id='.(int) $work['id'] : ($filterMangaId > 0 ? '?manga_id='.$filterMangaId : '') }}">{{ admin_t('ui.full_form') }}</a>
                </span>
            @elseif($desk === 'pics')
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-pics/create{{ $filterMangaId > 0 ? '?manga_id='.$filterMangaId : '' }}">{{ admin_t('ui.full_form') }}</a>
            @elseif($desk === 'comments')
                <a class="btn btn-muted btn-sm" href="/admin/video/manga-comments/create{{ $filterMangaId > 0 ? '?manga_id='.$filterMangaId : '' }}">{{ admin_t('ui.full_form') }}</a>
            @elseif($desk === 'favors')
                <a class="btn btn-muted btn-sm" href="/manga/shelf" target="_blank" rel="noopener">{{ admin_t('ui.view_front') }}</a>
            @endif
            @if($desk === 'work' && $work)
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas/{{ (int) $work['id'] }}/edit">{{ admin_t('ui.edit') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas">{{ admin_t('ui.works') }}</a>
                <a class="btn btn-muted btn-sm" href="{{ $work['front_url'] }}" target="_blank" rel="noopener">{{ admin_t('ui.front') }}</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($desk !== 'stats')
        <p class="muted recycle-lead">
            @if($desk === 'work')
                {{ admin_t('manga.lead_work') }}
            @elseif(in_array($desk, ['chapters', 'pics', 'comments'], true))
                {{ admin_t('manga.lead_list') }}
            @elseif($desk === 'favors')
                {{ admin_t('manga.lead_favors') }}
            @elseif(in_array($desk, ['works', 'pending'], true))
                {{ admin_t('manga.lead') }}
            @else
                {{ admin_t('manga.lead_short') }}
            @endif
        </p>
        @endif
        @if(in_array($desk, ['works', 'pending'], true))
            <p class="muted field-hint">
                <a href="/admin/video/mangas?desk=chapters">{{ admin_t('nav.manga_chapters') }}</a>
                ·
                <a href="/admin/video/mangas?desk=pics">{{ admin_t('nav.manga_pics') }}</a>
            </p>
        @endif

        @if($desk === 'stats')
            <p class="muted recycle-lead manga-stats-lead">{{ admin_t('manga.lead_stats') }}</p>
            <div class="stat-grid dash manga-stats-grid">
                <div class="stat-card">
                    <em>{{ admin_t('ui.today_reads') }}</em>
                    <strong>{{ (int) ($stats['today_reads'] ?? 0) }}</strong>
                    <span class="muted">{{ admin_t('ui.member_reads') }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.week_reads') }}</em>
                    <strong>{{ (int) ($stats['week_reads'] ?? 0) }}</strong>
                    <span class="muted">{{ admin_t('ui.last_30d_n', ['n' => (int) ($stats['month_reads'] ?? 0)]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.week_chapters') }}</em>
                    <strong>{{ (int) ($stats['week_chapters'] ?? 0) }}</strong>
                    <span class="muted">{{ admin_t('ui.last_30d_n', ['n' => (int) ($stats['month_chapters'] ?? 0)]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.works_on') }}</em>
                    <strong>{{ (int) ($worksStat['show'] ?? 0) }}</strong>
                    <span class="muted">{{ admin_t('ui.n_total_pending_off', ['all' => (int) ($worksStat['all'] ?? 0), 'pending' => (int) ($worksStat['pending'] ?? 0), 'off' => (int) ($worksStat['off'] ?? 0)]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.bookshelf') }}</em>
                    <strong>{{ (int) ($stats['favor_total'] ?? 0) }}</strong>
                    <span class="muted"><a href="/admin/video/mangas?desk=favors">{{ admin_t('ui.open_bookshelf') }}</a></span>
                </div>
            </div>
            <div class="flink-stats-split manga-stats-split">
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">{{ admin_t('ui.hits_top') }}</h3>
                    @if($topHits === [])
                        <p class="muted">{{ admin_t('ui.empty_works_dot') }}</p>
                        <p><a class="btn btn-muted btn-sm" href="/admin/video/mangas">{{ admin_t('ui.go_works') }}</a></p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>{{ admin_t('ui.works') }}</th><th>{{ admin_t('ui.hits') }}</th></tr></thead>
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
                    <h3 style="font-size:15px;margin:0 0 10px">{{ admin_t('ui.favor_top') }}</h3>
                    @if($topFavors === [])
                        <p class="muted">{{ admin_t('ui.empty_favors_hint') }}</p>
                        <p><a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=favors">{{ admin_t('ui.open_bookshelf') }}</a></p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>{{ admin_t('ui.works') }}</th><th>{{ admin_t('ui.favors') }}</th></tr></thead>
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
            <h3 style="font-size:15px;margin:20px 0 10px">{{ admin_t('ui.last_14d_trend') }}</h3>
            @if($daily === [])
                <p class="muted">{{ admin_t('ui.none') }}</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ admin_t('ui.date') }}</th><th>{{ admin_t('ui.reads') }}</th><th>{{ admin_t('ui.new_chapters') }}</th></tr></thead>
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
                        <p class="muted" style="margin:0">{!! str_replace(':link', '<a href="/admin/video/manga-tags">'.e(admin_t('ui.open_tags_desk')).'</a>', e(admin_t('ui.tags_table_missing'))) !!}</p>
                    @endif
                    @if(! $authorsReady)
                        <p class="muted" style="margin:{{ $tagsReady ? '0' : '6px 0 0' }}">{!! str_replace(':link', '<a href="/admin/video/manga-authors">'.e(admin_t('ui.open_authors_desk')).'</a>', e(admin_t('ui.authors_table_missing'))) !!}</p>
                    @endif
                </div>
            @endif
            @if($desk === 'work' && $work)
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:12px;align-items:center">
                    <strong>{{ $work['title'] }}</strong>
                    <span class="muted">#{{ $work['id'] }}</span>
                    <span>{{ $work['serialize_label'] }}</span>
                    <span class="muted">{{ admin_t('ui.chapters_n_hits', ['n' => (int) $work['chapter_count'], 'hits' => (int) $work['hits']]) }}</span>
                    @if(! empty($work['author']))
                        <span class="muted">{{ admin_t('ui.authors') }} {{ $work['author'] }}</span>
                    @endif
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas/{{ (int) $work['id'] }}/edit">{{ admin_t('ui.edit_work') }}</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=pics&manga_id={{ $work['id'] }}">{{ admin_t('ui.manage_pics') }}</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=comments&manga_id={{ $work['id'] }}">{{ admin_t('ui.comments') }}</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=favors&manga_id={{ $work['id'] }}">{{ admin_t('ui.bookshelf') }}</a>
                </div>
            @endif
            @if($filterMangaId > 0 && in_array($desk, ['chapters', 'pics', 'comments', 'favors'], true))
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                    <span>{{ admin_t('ui.viewing_work') }}</span>
                    <strong>{{ $filterMangaTitle !== '' ? $filterMangaTitle : ('#'.$filterMangaId) }}</strong>
                    <a class="btn btn-sm" href="/admin/video/mangas?desk=work&manga_id={{ $filterMangaId }}">{{ admin_t('ui.work_desk') }}</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas">{{ admin_t('ui.back_works') }}</a>
                    <a class="btn btn-muted btn-sm" href="/manga/{{ $filterMangaId }}" target="_blank" rel="noopener">{{ admin_t('ui.view_front') }}</a>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk={{ $desk }}">{{ admin_t('ui.clear_work_filter') }}</a>
                </div>
            @endif
            @if($desk === 'favors' && $filterMemberId > 0)
                <div class="flash is-ok" style="margin:10px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                    <span>{{ admin_t('ui.viewing_member') }}</span>
                    <strong>{{ $filterMemberName !== '' ? $filterMemberName : ('#'.$filterMemberId) }}</strong>
                    <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=favors{{ $filterMangaId > 0 ? '&manga_id='.$filterMangaId : '' }}">{{ admin_t('ui.clear_member_filter') }}</a>
                </div>
            @endif
            @if(in_array($desk, ['works', 'pending'], true))
                <div class="tag-compose" id="manga-work-compose-box">
                    <form class="tag-compose-form" id="manga-work-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="manga-work-quick">{{ admin_t('ui.add_work') }}</label>
                        <div class="tag-compose-row">
                            <input id="manga-work-quick" type="text" name="title" value="" placeholder="{{ admin_t('manga.ph_work') }}" aria-label="{{ admin_t('ui.add_work') }}">
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
                                <a class="btn btn-muted" href="/admin/video/mangas/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ admin_t('manga.hint_work') }}</p>
                    </form>
                </div>
            @elseif(in_array($desk, ['chapters', 'work'], true))
                <div class="tag-compose" id="manga-chapter-compose-box">
                    <form class="tag-compose-form" id="manga-chapter-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="manga-chapter-quick">{{ admin_t('ui.add_chapter') }}</label>
                        <div class="tag-compose-row">
                            @if($desk === 'chapters' && ! ($desk === 'work' && $work) && $filterMangaId < 1)
                                <select name="manga_id" aria-label="{{ admin_t('ui.works') }}" required style="max-width:180px">
                                    <option value="">{{ admin_t('manga.select_work') }}</option>
                                    @foreach($works as $w)
                                        <option value="{{ $w['id'] }}">{{ $w['title'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="manga_id" value="{{ $desk === 'work' && $work ? (int) $work['id'] : $filterMangaId }}">
                            @endif
                            <input id="manga-chapter-quick" type="text" name="name" value="" placeholder="{{ admin_t('manga.ph_chapter') }}" aria-label="{{ admin_t('ui.add_chapter') }}">
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
                                <a class="btn btn-muted" href="/admin/video/manga-chapters/create{{ ($desk === 'work' && $work) ? '?manga_id='.(int) $work['id'] : ($filterMangaId > 0 ? '?manga_id='.$filterMangaId : '') }}">{{ admin_t('ui.full_form') }}</a>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ admin_t('manga.hint_chapter') }}</p>
                    </form>
                </div>
            @elseif($desk === 'pics')
                <div class="tag-compose">
                    <p class="muted field-hint" style="margin:0">{!! str_replace(':link', '<a href="/admin/video/manga-pics/create'.($filterMangaId > 0 ? '?manga_id='.$filterMangaId : '').'">'.e(admin_t('ui.full_form')).'</a>', e(admin_t('manga.pics_form_hint'))) !!}</p>
                </div>
            @elseif($desk === 'comments')
                <div class="tag-compose">
                    <p class="muted field-hint" style="margin:0">{!! str_replace(':link', '<a href="/admin/video/manga-comments/create'.($filterMangaId > 0 ? '?manga_id='.$filterMangaId : '').'">'.e(admin_t('ui.full_form')).'</a>', e(admin_t('manga.comments_form_hint'))) !!}</p>
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
            <input type="search" name="q" value="{{ $filterQ }}" placeholder="{{ $desk === 'types' ? admin_t('ui.ph_search_noun', ['name' => admin_t('ui.types')]) : (in_array($desk, ['chapters', 'work'], true) ? admin_t('ui.ph_search_chapter') : ($desk === 'pics' ? admin_t('ui.ph_search_pic_url') : ($desk === 'comments' ? admin_t('ui.ph_search_comment_work') : ($desk === 'favors' ? admin_t('ui.ph_search_member_work') : admin_t('ui.ph_search_name_author_tag'))))) }}" autocomplete="off">
            @if(in_array($desk, ['works', 'pending'], true) && $types !== [])
                <select name="type_id" aria-label="{{ admin_t('ui.types') }}">
                    <option value="">{{ admin_t('ui.all_categories') }}</option>
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
                <select name="serialize" aria-label="{{ admin_t('ui.serialize') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="0" @selected($filterSerialize === '0')>{{ admin_t('ui.serialize_ongoing') }}</option>
                    <option value="1" @selected($filterSerialize === '1')>{{ admin_t('ui.serialize_done') }}</option>
                </select>
                <select name="recommend" aria-label="{{ admin_t('ui.recommend') }}">
                    <option value="">{{ admin_t('ui.all') }}</option>
                    <option value="1" @selected($filterRecommend === '1')>{{ admin_t('ui.recommend') }}</option>
                </select>
            @endif
            @if($desk === 'comments')
                <select name="status" aria-label="{{ admin_t('ui.status') }}" hidden>
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.show') }}</option>
                    <option value="0">{{ admin_t('ui.pending') }}</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="manga-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        @if($desk === 'comments')
            <div class="queue-chips" id="manga-comment-queues">
                <button type="button" class="chip" data-queue="" data-value="">{{ admin_t('ui.all') }}@if($cq('all') > 0)<em>{{ $cq('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.pending') }}@if($cq('pending') > 0)<em>{{ $cq('pending') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.approved') }}@if($cq('pass') > 0)<em>{{ $cq('pass') }}</em>@endif</button>
            </div>
        @endif
        @if($filterTag && in_array($desk, ['works', 'pending'], true))
            <div class="queue-chips">
                <a class="chip active" href="/admin/video/mangas{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.tag_chip', ['name' => $filterTag['name']]) }}</a>
            </div>
        @endif
        @if($filterAuthor && in_array($desk, ['works', 'pending'], true))
            <div class="queue-chips">
                <a class="chip active" href="/admin/video/mangas{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.author_chip', ['name' => $filterAuthor['name']]) }}</a>
            </div>
        @endif
        @if($desk === 'favors')
            <div class="queue-chips" id="manga-favor-queues">
                <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($fq('all') > 0)<em>{{ $fq('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.chip_today') }}@if($fq('today') > 0)<em>{{ $fq('today') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="missing" data-value="1">{{ admin_t('ui.work_gone') }}@if($fq('missing') > 0)<em>{{ $fq('missing') }}</em>@endif</button>
            </div>
        @endif
        @if(in_array($desk, ['works', 'pending', 'comments', 'chapters', 'pics', 'work', 'types', 'favors'], true))
            <div class="batch-bar" id="manga-batch" hidden>
                <strong id="manga-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
                @if(in_array($desk, ['works', 'pending'], true))
                    <button type="button" class="btn btn-sm" id="manga-batch-on">{{ admin_t('ui.on') }}</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">{{ admin_t('ui.off') }}</button>
                    <button type="button" class="btn btn-sm" id="manga-batch-pass">{{ admin_t('ui.approve') }}</button>
                    <button type="button" class="btn btn-sm" id="manga-batch-rec">{{ admin_t('ui.recommend') }}</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-unrec">{{ admin_t('ui.unset_recommend') }}</button>
                @endif
                @if($desk === 'comments')
                    <button type="button" class="btn btn-sm" id="manga-batch-on">{{ admin_t('ui.approve') }}</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">{{ admin_t('ui.hide') }}</button>
                @endif
                @if($desk === 'types')
                    <button type="button" class="btn btn-sm" id="manga-batch-on">{{ admin_t('ui.enabled') }}</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">{{ admin_t('ui.disabled') }}</button>
                    <select id="manga-batch-parent" class="batch-select"><option value="">{{ admin_t('ui.move_parent') }}</option></select>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-move">{{ admin_t('ui.move') }}</button>
                @endif
                <button type="button" class="btn btn-danger btn-sm" id="manga-batch-del">{{ $desk === 'favors' ? admin_t('manga.unfavor') : admin_t('ui.delete') }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="manga-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
            </div>
        @endif
        <div id="manga-table"></div>
            @endif
        @endif
    </div>
</div>

@endsection

@php
    $mangaJsLang = [
        'add_work' => admin_t('ui.add_work'),
        'full_form' => admin_t('ui.full_form'),
        'name' => admin_t('ui.name'),
        'types' => admin_t('ui.types'),
        'authors' => admin_t('ui.authors'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'front' => admin_t('ui.front'),
        'works' => admin_t('ui.works'),
        'chapters' => admin_t('ui.chapters'),
        'pics' => admin_t('ui.pics'),
        'favors' => admin_t('ui.favors'),
        'status' => admin_t('ui.status'),
        'sort' => admin_t('ui.sort'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'pending' => admin_t('ui.pending'),
        'visible' => admin_t('ui.visible'),
        'member' => admin_t('ui.member'),
        'content' => admin_t('ui.content'),
        'nickname' => admin_t('ui.nickname'),
        'time' => admin_t('ui.time'),
        'free' => admin_t('ui.free'),
        'recommend' => admin_t('ui.recommend'),
        'uncategorized' => admin_t('ui.uncategorized'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'please_select' => admin_t('ui.please_select'),
        'added' => admin_t('ui.added'),
        'deleted' => admin_t('ui.deleted'),
        'fail' => admin_t('ui.fail'),
        'select_work' => admin_t('manga.select_work'),
        'empty_works' => admin_t('manga.empty_works'),
        'empty_pending' => admin_t('manga.empty_pending'),
        'empty_chapters' => admin_t('manga.empty_chapters'),
        'empty_work_chapters' => admin_t('manga.empty_work_chapters'),
        'empty_pics' => admin_t('manga.empty_pics'),
        'empty_comments' => admin_t('manga.empty_comments'),
        'empty_favors' => admin_t('manga.empty_favors'),
        'empty_none' => admin_t('manga.empty_none'),
        'empty_favors_hint' => admin_t('manga.empty_favors_hint'),
        'empty_compose_hint' => admin_t('manga.empty_compose_hint'),
        'open_front' => admin_t('manga.open_front'),
        'col_serialize' => admin_t('manga.col_serialize'),
        'col_status_audit' => admin_t('manga.col_status_audit'),
        'col_chapters' => admin_t('manga.col_chapters'),
        'col_views' => admin_t('manga.col_views'),
        'col_pics' => admin_t('manga.col_pics'),
        'manage' => admin_t('manga.manage'),
        'read' => admin_t('manga.read'),
        'audited' => admin_t('manga.audited'),
        'blank' => admin_t('manga.blank'),
        'removed' => admin_t('manga.removed'),
        'need_title' => admin_t('manga.need_title'),
        'need_chapter' => admin_t('manga.need_chapter'),
        'add_fail' => admin_t('manga.add_fail'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'canceled' => admin_t('manga.canceled'),
        'cancel_favor' => admin_t('gallery.cancel_favor'),
        'selected_rows' => admin_t('manga.selected_rows', ['n' => '__N__']),
        'confirm_batch_del' => admin_t('manga.confirm_batch_del'),
        'confirm_batch_unfavor' => admin_t('manga.confirm_batch_unfavor'),
        'confirm_del' => admin_t('manga.confirm_del'),
        'confirm_unfavor' => admin_t('manga.confirm_unfavor'),
    ];
@endphp
@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($mangaJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk, JSON_UNESCAPED_UNICODE);
    var filterMangaId = @json($filterMangaId, JSON_UNESCAPED_UNICODE);
    var workPayload = @json($work, JSON_UNESCAPED_UNICODE);
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
            return '<div class="list-empty"><p>' + L.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="manga-empty-reset">' + L.clear_filter + '</button></p></div>';
        }
        var href = fullCreate[desk] || '';
        var label = {works: L.add_work, pending: L.add_work, chapters: L.full_form, work: L.full_form, pics: L.full_form, comments: L.full_form}[desk] || L.full_form;
        var tip = {works: L.empty_works, pending: L.empty_pending, chapters: L.empty_chapters, work: L.empty_work_chapters, pics: L.empty_pics, comments: L.empty_comments, favors: L.empty_favors}[desk] || L.empty_none;
        if (desk === 'favors') {
            return '<div class="list-empty"><p>' + tip + '</p><p class="muted">' + L.empty_favors_hint + '</p><p><a class="btn btn-muted btn-sm" href="/manga" target="_blank" rel="noopener">' + L.open_front + '</a></p></div>';
        }
        if (href) {
            return '<div class="list-empty"><p>' + tip + '</p><p class="muted">' + L.empty_compose_hint + '</p><p><a class="btn btn-primary btn-sm" href="' + href + '">' + label + '</a></p></div>';
        }
        return '<div class="list-empty"><p>' + tip + '</p></div>';
    }
    function workStatus(d) {
        var st = String(d.status) === '1' ? L.on : L.off;
        var yid = d.yid_label || (String(d.yid) === '1' ? L.pending : L.audited);
        return U.escape(st + ' / ' + yid);
    }

    var cols = [];
    if (desk === 'works' || desk === 'pending') {
        cols = [
            {check: true, width: 36},
            {title: '{{ admin_t('ui.name') }}', html: function (d) {
                var badge = parseInt(d.recommend, 10) === 1 ? '<span class="badge">' + L.recommend + '</span> ' : '';
                return badge + '<a class="entry-row-title" href="/admin/video/mangas/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.title || L.blank) + '</a>';
            }},
            {title: L.types, html: function (d) { return U.escape(d.type_name || L.uncategorized); }},
            {title: L.authors, html: function (d) { return U.escape(d.author_label || d.author || ''); }},
            {title: L.col_serialize, width: 72, html: function (d) { return U.escape(d.serialize_label || ''); }},
            {title: L.col_status_audit, width: 110, html: workStatus},
            {title: L.col_chapters, width: 72, html: function (d) { return U.escape(String(d.chapter_count == null ? 0 : d.chapter_count)); }},
            {title: L.col_views, width: 72, html: function (d) { return U.escape(String(d.hits == null ? 0 : d.hits)); }},
            {title: L.favors, width: 64, html: function (d) { return U.escape(String(d.favor_count == null ? 0 : d.favor_count)); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id || '');
                return '<a href="/admin/video/mangas?desk=work&manga_id=' + id + '" class="btn-link">' + L.manage + '</a>'
                    + '<a href="/admin/video/mangas/' + id + '/edit" class="btn-link">' + L.edit + '</a>'
                    + '<a href="' + U.escape(d.front_url || ('/manga/' + id)) + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a>';
            }}
        ];
    } else if (desk === 'comments') {
        cols = [
            {check: true, width: 36},
            {title: L.content, html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/manga-comments/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.content || L.blank) + '</a>';
            }},
            {title: L.works, html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: L.nickname, width: 100, html: function (d) { return U.escape(d.author_name || ''); }},
            {title: L.status, width: 90, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.visible) : U.status(false, L.pending);
            }},
            {title: L.time, width: 140, html: function (d) { return U.escape(d.created_label || ''); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || '');
                return '<a href="/admin/video/manga-comments/' + encodeURIComponent(d.id || '') + '/edit" class="btn-link">' + L.edit + '</a>'
                    + (mid ? '<a href="/manga/' + mid + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a>' : '')
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ];
    } else if (desk === 'favors') {
        cols = [
            {check: true, width: 36},
            {title: L.member, html: function (d) {
                var name = d.member_name || ('#' + (d.member_id || ''));
                var miss = String(d.member_missing) === '1' ? ' <span class="muted">' + L.removed + '</span>' : '';
                return '<a class="entry-row-title" href="/admin/video/mangas?desk=favors&member_id=' + encodeURIComponent(d.member_id || '') + '">' + U.escape(name) + '</a>' + miss;
            }},
            {title: L.works, html: function (d) {
                var title = d.manga_title || ('#' + (d.manga_id || ''));
                var miss = String(d.manga_missing) === '1' ? ' <span class="muted">' + L.removed + '</span>' : '';
                return '<a href="/admin/video/mangas?desk=favors&manga_id=' + encodeURIComponent(d.manga_id || '') + '">' + U.escape(title) + '</a>' + miss;
            }},
            {title: L.time, width: 140, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || '');
                return (mid ? '<a href="/manga/' + mid + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a>' : '')
                    + '<a href="#" class="btn-link js-del">' + L.cancel_favor + '</a>';
            }}
        ];
    } else if (desk === 'chapters' || desk === 'work') {
        cols = [
            {check: true, width: 36},
            {title: L.chapters, html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/manga-chapters/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.name || L.blank) + '</a>';
            }},
            {title: L.works, html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: 'VIP', width: 64, html: function (d) { return String(d.vip) === '1' ? U.status(true, 'VIP') : U.status(false, L.free); }},
            {title: L.col_pics, width: 72, html: function (d) { return U.escape(String(d.pic_count == null ? 0 : d.pic_count)); }},
            {title: L.sort, width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var mid = encodeURIComponent(d.manga_id || filterMangaId || '');
                var cid = encodeURIComponent(d.id || '');
                return '<a href="/admin/video/manga-chapters/' + cid + '/edit" class="btn-link">' + L.edit + '</a>'
                    + (mid && cid ? '<a href="/manga/' + mid + '/' + cid + '" class="btn-link" target="_blank" rel="noopener">' + L.read + '</a>' : '')
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ];
    } else if (desk === 'pics') {
        cols = [
            {check: true, width: 36},
            {title: L.pics, html: function (d) {
                return '<a class="entry-row-title" href="/admin/video/manga-pics/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(d.url || L.blank) + '</a>';
            }},
            {title: L.works, html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: L.chapters, html: function (d) { return U.escape(d.chapter_name || ('#' + (d.chapter_id || ''))); }},
            {title: L.sort, width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="/admin/video/manga-pics/' + encodeURIComponent(d.id || '') + '/edit" class="btn-link">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
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
            if (count) count.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function batch(action, value, confirmText) {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(L.please_select, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/' + module + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    var workCompose = document.getElementById('manga-work-compose');
    if (workCompose) {
        workCompose.addEventListener('submit', function (e) {
            e.preventDefault();
            var title = String((workCompose.title && workCompose.title.value) || '').trim();
            if (!title) { U.toast(L.need_title, 'err'); workCompose.title.focus(); return; }
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
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.add_fail, 'err'); return; }
                workCompose.title.value = '';
                workCompose.title.focus();
                table.refresh();
                U.toast(L.added, 'ok');
            }).catch(function () { U.loading(false); U.toast(L.add_fail, 'err'); });
        });
    }
    var chapterCompose = document.getElementById('manga-chapter-compose');
    if (chapterCompose) {
        chapterCompose.addEventListener('submit', function (e) {
            e.preventDefault();
            var data = U.formData(chapterCompose);
            var mid = parseInt(data.manga_id, 10) || 0;
            var name = String(data.name || '').trim();
            if (!mid) { U.toast(L.select_work, 'err'); return; }
            if (!name) { U.toast(L.need_chapter, 'err'); chapterCompose.name.focus(); return; }
            U.loading(true);
            U.post('/admin/video/manga_chapters/save', {manga_id: mid, name: name, sort: 0, vip: 0, pics: ''}).then(function (res) {
                U.loading(false);
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.add_fail, 'err'); return; }
                chapterCompose.name.value = '';
                chapterCompose.name.focus();
                table.refresh();
                U.toast(L.added, 'ok');
            }).catch(function () { U.loading(false); U.toast(L.add_fail, 'err'); });
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
        batch('delete', '', desk === 'favors' ? L.confirm_batch_unfavor : L.confirm_batch_del);
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
        if (!U.confirm(desk === 'favors' ? L.confirm_unfavor : L.confirm_del)) return;
        U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(desk === 'favors' ? L.canceled : L.deleted, 'ok');
        });
    });
})();
</script>
@endpush
