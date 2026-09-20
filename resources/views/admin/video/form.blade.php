@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_video') : admin_t('ui.add_video'))

@php
    $video = is_array($video ?? null) ? $video : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $collects = is_array($collects ?? null) ? $collects : [];
    $areas = is_array($areas ?? null) ? $areas : [];
    $langs = is_array($langs ?? null) ? $langs : [];
    $years = is_array($years ?? null) ? $years : [];
    $publishAt = $publishAt ?? '';
    $title = (string) ($video['title'] ?? '');
    $cover = trim((string) ($video['cover'] ?? ''));
    $banner = trim((string) ($video['banner'] ?? ''));
    $typeId = (string) ($video['type_id'] ?? '');
    $collectId = (string) ($video['collect_source_id'] ?? '');
    $status = (string) ($video['status'] ?? ($isEdit ? '1' : '2'));
    $videoJsLang = [
        'uploaded' => admin_t('ui.uploaded'),
        'upload_fail' => admin_t('ui.upload_fail'),
        'please_fill_title' => admin_t('ui.please_fill_title'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
    ];
@endphp

@section('plain')
<div class="card card-panel video-form-page">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('ui.edit_video') : admin_t('ui.add_video') }}@if($isEdit) <em>{{ $title }}</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.back_list') }}</a>
            @if($isEdit)
                <a class="btn btn-muted btn-sm" href="/admin/video/sources?video_id={{ (int) ($video['id'] ?? 0) }}">{{ admin_t('ui.play_lines') }}</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                {{ admin_t('ui.video_form_lead_edit') }}
            @else
                {{ admin_t('ui.video_form_lead_new') }}
            @endif
        </p>

        <form class="admin-form video-form" id="video-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($video['id'] ?? 0) : '' }}">

            <h3>{{ admin_t('ui.this_title') }}</h3>
            <label for="video-title">{{ admin_t('ui.title_label') }}</label>
            <input id="video-title" type="text" name="title" value="{{ $title }}" placeholder="{{ admin_t('ui.ph_front_name') }}" required>
            <label for="video-subtitle">{{ admin_t('ui.subtitle') }}</label>
            <input id="video-subtitle" type="text" name="subtitle" value="{{ $video['subtitle'] ?? '' }}" placeholder="{{ admin_t('ui.ph_subtitle') }}">
            <label for="video-type">{{ admin_t('ui.types') }}</label>
            <select id="video-type" name="type_id">
                <option value="">{{ admin_t('ui.pick_later') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}" @selected($typeId === (string) $type['id'])>{{ $type['name'] }}</option>
                @endforeach
            </select>
            @if($types === [])
                <p class="muted field-hint">{{ admin_t('ui.empty_types_hint') }} <a href="/admin/video/types">{{ admin_t('ui.go_create_type') }}</a></p>
            @else
                <p class="muted field-hint">{{ admin_t('ui.types_hint') }} <a href="/admin/video/types">{{ admin_t('ui.types') }}</a></p>
            @endif
            <label for="video-remarks">{{ admin_t('ui.remarks') }}</label>
            <input id="video-remarks" type="text" name="remarks" value="{{ $video['remarks'] ?? '' }}" placeholder="{{ admin_t('ui.ph_remarks') }}">
            <p class="muted field-hint">{{ admin_t('ui.remarks_hint') }}</p>

            <h3>{{ admin_t('ui.cover') }}</h3>
            <label for="video-cover">{{ admin_t('ui.poster') }}</label>
            <div class="settings-file-preview video-cover-preview-box">
                <div class="settings-file-thumb video-cover-thumb{{ $cover === '' ? ' is-empty' : '' }}" id="cover-thumb">
                    <img id="cover-img" src="{{ $cover }}" alt="" @if($cover === '') hidden @endif>
                    <span class="settings-file-empty muted" id="cover-empty" @if($cover !== '') hidden @endif>{{ admin_t('ui.no_poster') }}</span>
                </div>
                <div class="field-inline">
                    <input id="video-cover" type="text" name="cover" value="{{ $cover }}" placeholder="{{ admin_t('ui.ph_image_or_upload') }}">
                    <button type="button" class="btn btn-muted" id="cover-upload">{{ admin_t('ui.upload') }}</button>
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.poster_hint') }}</p>

            <label for="video-banner">{{ admin_t('ui.banner') }}</label>
            <div class="settings-file-preview">
                <div class="settings-file-thumb video-banner-thumb{{ $banner === '' ? ' is-empty' : '' }}" id="banner-thumb">
                    <img id="banner-img" src="{{ $banner }}" alt="" @if($banner === '') hidden @endif>
                    <span class="settings-file-empty muted" id="banner-empty" @if($banner !== '') hidden @endif>{{ admin_t('ui.optional') }}</span>
                </div>
                <div class="field-inline">
                    <input id="video-banner" type="text" name="banner" value="{{ $banner }}" placeholder="{{ admin_t('ui.ph_banner') }}">
                    <button type="button" class="btn btn-muted" id="banner-upload">{{ admin_t('ui.upload') }}</button>
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.banner_hint') }}</p>

            <h3>{{ admin_t('ui.meta_block') }}</h3>
            <div class="settings-two">
                <div>
                    <label for="video-year">{{ admin_t('ui.year') }}</label>
                    <input id="video-year" type="text" name="year" value="{{ $video['year'] ?? '' }}" list="video-year-list" placeholder="2024">
                    @if($years !== [])
                        <datalist id="video-year-list">
                            @foreach($years as $year)
                                <option value="{{ $year }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>
                <div>
                    <label for="video-area">{{ admin_t('ui.area') }}</label>
                    <input id="video-area" type="text" name="area" value="{{ $video['area'] ?? '' }}" list="video-area-list" placeholder="{{ admin_t('ui.ph_area') }}">
                    @if($areas !== [])
                        <datalist id="video-area-list">
                            @foreach($areas as $area)
                                <option value="{{ $area }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>
            </div>
            <div class="settings-two">
                <div>
                    <label for="video-lang">{{ admin_t('ui.lang_label') }}</label>
                    <input id="video-lang" type="text" name="lang" value="{{ $video['lang'] ?? '' }}" list="video-lang-list" placeholder="{{ admin_t('ui.ph_lang') }}">
                    @if($langs !== [])
                        <datalist id="video-lang-list">
                            @foreach($langs as $lang)
                                <option value="{{ $lang }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>
                <div>
                    <label for="video-weekday">{{ admin_t('ui.weekday') }}</label>
                    <input id="video-weekday" type="text" name="weekday" value="{{ $video['weekday'] ?? '' }}" placeholder="{{ admin_t('ui.ph_weekday_csv') }}">
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.meta_filter_hint') }} <a href="/admin/video/settings?tab=more">{{ admin_t('nav.settings') }}</a></p>
            <label for="video-class">{{ admin_t('ui.class_words') }}</label>
            <input id="video-class" type="text" name="class" value="{{ $video['class'] ?? '' }}" placeholder="{{ admin_t('ui.ph_class_words') }}">
            <p class="muted field-hint">{{ admin_t('ui.class_words_hint') }} <a href="/admin/video/classes">{{ admin_t('page.classes') }}</a></p>
            <label for="video-director">{{ admin_t('ui.director') }}</label>
            <input id="video-director" type="text" name="director" value="{{ $video['director'] ?? '' }}">
            <label for="video-actors">{{ admin_t('ui.starring') }}</label>
            <input id="video-actors" type="text" name="actors_text" value="{{ $video['actors_text'] ?? '' }}" placeholder="{{ admin_t('ui.ph_actors') }}">
            <p class="muted field-hint">{{ admin_t('ui.actors_save_hint') }}</p>
            <label for="video-tags">{{ admin_t('ui.tags') }}</label>
            <input id="video-tags" type="text" name="tags_text" value="{{ $video['tags_text'] ?? '' }}" placeholder="{{ admin_t('ui.ph_tags') }}">
            <p class="muted field-hint">{{ admin_t('ui.tags_save_hint') }}</p>

            <h3>{{ admin_t('ui.synopsis') }}</h3>
            <label for="video-desc">{{ admin_t('ui.plot_label') }}</label>
            <textarea id="video-desc" name="description" rows="8" placeholder="{{ admin_t('ui.ph_plot') }}">{{ $video['description'] ?? '' }}</textarea>
            @includeIf('ai_content::form_button')

            @if($isEdit)
                <h3>{{ admin_t('nav.roles') }}</h3>
                @php $roleRows = is_array($roles ?? null) ? $roles : []; @endphp
                @if($roleRows === [])
                    <p class="muted field-hint">{{ admin_t('ui.no_roles_hint') }} <a href="/admin/video/roles?video_id={{ (int) ($video['id'] ?? 0) }}">{{ admin_t('nav.roles') }}</a></p>
                @else
                    <ul class="muted">
                        @foreach($roleRows as $role)
                            <li>{{ $role['name'] ?? '' }} @if((int)($role['status'] ?? 1) !== 1){{ admin_t('ui.stopped_paren') }}@endif</li>
                        @endforeach
                    </ul>
                    <p class="muted field-hint"><a href="/admin/video/roles?video_id={{ (int) ($video['id'] ?? 0) }}">{{ admin_t('nav.roles') }}</a> {{ admin_t('ui.roles_edit_hint') }}</p>
                @endif

                <h3>{{ admin_t('ui.plots') }}</h3>
                @php $plotRows = is_array($plots ?? null) ? $plots : []; @endphp
                @if($plotRows === [])
                    <p class="muted field-hint">{{ admin_t('ui.no_plots_hint') }} <a href="/admin/video/plots?video_id={{ (int) ($video['id'] ?? 0) }}">{{ admin_t('ui.plots') }}</a></p>
                @else
                    <ul class="muted">
                        @foreach($plotRows as $plot)
                            <li>{{ admin_t('ui.episode_n', ['n' => (int) ($plot['episode_num'] ?? 0)]) }}{{ trim((string) ($plot['title'] ?? '')) !== '' ? ' · '.$plot['title'] : '' }}</li>
                        @endforeach
                    </ul>
                    <p class="muted field-hint"><a href="/admin/video/plots?video_id={{ (int) ($video['id'] ?? 0) }}">{{ admin_t('ui.plots') }}</a> {{ admin_t('ui.plots_edit_hint') }}</p>
                @endif
            @endif

            <h3>{{ admin_t('ui.listing') }}</h3>
            <label for="video-status">{{ admin_t('ui.status') }}</label>
            <select id="video-status" name="status">
                <option value="2" @selected($status === '2')>{{ admin_t('ui.draft_hidden') }}</option>
                <option value="1" @selected($status === '1')>{{ admin_t('ui.on') }}</option>
                <option value="0" @selected($status === '0')>{{ admin_t('ui.off') }}</option>
                <option value="4" @selected($status === '4')>{{ admin_t('ui.schedule_publish') }}</option>
                <option value="3" @selected($status === '3')>{{ admin_t('ui.rejected') }}</option>
            </select>
            <div class="video-publish-row" id="video-publish-row" @if($status !== '4') hidden @endif>
                <label for="video-publish-at">{{ admin_t('ui.publish_at') }}</label>
                <input id="video-publish-at" type="datetime-local" name="publish_at" value="{{ $publishAt }}">
                <p class="muted field-hint">{{ admin_t('ui.publish_at_hint') }}</p>
            </div>
            <div class="settings-two">
                <div>
                    <label for="video-points">{{ admin_t('ui.vod_points') }}</label>
                    <input id="video-points" type="number" name="points" min="0" value="{{ $video['points'] ?? 0 }}">
                    <p class="muted field-hint">{{ admin_t('ui.vod_points_hint') }}</p>
                </div>
                <div>
                    <label for="video-score">{{ admin_t('ui.score') }}</label>
                    <input id="video-score" type="number" name="score" min="0" max="10" step="0.1" value="{{ $video['score'] ?? 0 }}">
                </div>
            </div>
            <label for="video-sort">{{ admin_t('ui.sort') }}</label>
            <input id="video-sort" type="number" name="sort" value="{{ $video['sort'] ?? 0 }}">
            <p class="muted field-hint">{{ admin_t('ui.sort_desc_hint') }}</p>
            <input type="hidden" name="is_recommend" value="0">
            <input type="hidden" name="is_hot" value="0">
            <input type="hidden" name="lock" value="0">
            <div class="video-form-checks">
                <label class="inline">
                    <input type="checkbox" name="is_recommend" value="1" @checked((string) ($video['is_recommend'] ?? '0') === '1')>
                    {{ admin_t('ui.rec_home') }}
                </label>
                <label class="inline">
                    <input type="checkbox" name="is_hot" value="1" @checked((string) ($video['is_hot'] ?? '0') === '1')>
                    {{ admin_t('ui.mark_hot') }}
                </label>
                <label class="inline">
                    <input type="checkbox" name="lock" value="1" @checked((string) ($video['lock'] ?? '0') === '1')>
                    {{ admin_t('ui.lock_collect') }}
                </label>
            </div>

            <details class="settings-details" @if(trim((string) ($video['collect_id'] ?? '')) !== '' || $collectId !== '') open @endif>
                <summary>{{ admin_t('ui.collect_match') }}</summary>
                <p class="muted field-hint">{{ admin_t('ui.collect_match_hint') }}</p>
                <label for="video-collect-source">{{ admin_t('ui.collect_source') }}</label>
                <select id="video-collect-source" name="collect_source_id">
                    <option value="">{{ admin_t('ui.none_opt') }}</option>
                    @foreach($collects as $src)
                        <option value="{{ $src['id'] }}" @selected($collectId === (string) $src['id'])>
                            {{ $src['name'] }}@if((string) ($src['status'] ?? '1') === '0'){{ admin_t('ui.stopped_paren') }}@endif
                        </option>
                    @endforeach
                </select>
                <label for="video-collect-id">{{ admin_t('ui.collect_id') }}</label>
                <input id="video-collect-id" type="text" name="collect_id" value="{{ $video['collect_id'] ?? '' }}">
            </details>

            <div class="form-actions">
                <button type="submit" class="btn" id="video-save">{{ admin_t('ui.save') }}</button>
                <button type="button" class="btn btn-muted" id="video-save-play">{{ admin_t('ui.save_and_add_play') }}</button>
                <a class="btn btn-muted" href="/admin/video">{{ admin_t('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($videoJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('video-form');
    var statusEl = document.getElementById('video-status');
    var pubRow = document.getElementById('video-publish-row');
    var isEdit = !!String(form.id.value || '').trim();

    function showStatus() {
        var timed = statusEl && statusEl.value === '4';
        if (pubRow) pubRow.hidden = !timed;
    }
    if (statusEl) statusEl.addEventListener('change', showStatus);
    showStatus();

    function bindImage(inputId, btnId, imgId, emptyId, thumbId) {
        var input = document.getElementById(inputId);
        var btn = document.getElementById(btnId);
        var img = document.getElementById(imgId);
        var empty = document.getElementById(emptyId);
        var thumb = document.getElementById(thumbId);
        function sync(url) {
            url = String(url || '').trim();
            if (!img || !empty || !thumb) return;
            if (!url) {
                img.removeAttribute('src');
                img.hidden = true;
                empty.hidden = false;
                thumb.classList.add('is-empty');
                return;
            }
            img.hidden = false;
            empty.hidden = true;
            thumb.classList.remove('is-empty');
            if (img.getAttribute('src') !== url) img.src = url;
        }
        if (input) input.addEventListener('input', function () { sync(input.value); });
        if (btn) btn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        input.value = res.data.url;
                        sync(res.data.url);
                        U.toast(L.uploaded || '', 'ok');
                    } else U.toast((res && res.msg) || L.upload_fail || '', 'err');
                });
            });
        });
    }
    bindImage('video-cover', 'cover-upload', 'cover-img', 'cover-empty', 'cover-thumb');
    bindImage('video-banner', 'banner-upload', 'banner-img', 'banner-empty', 'banner-thumb');

    function save(goPlay) {
        var data = U.formData(form);
        if (!String(data.title || '').trim()) {
            U.toast(L.please_fill_title || '', 'err');
            document.getElementById('video-title').focus();
            return;
        }
        U.loading(true);
        U.post('/admin/video/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail || '', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(L.saved || '', 'ok');
            if (goPlay && id) {
                location.href = '/admin/video/sources?video_id=' + encodeURIComponent(id);
                return;
            }
            if (!isEdit && id) {
                location.href = '/admin/video/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail || '', 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save(false);
    });
    document.getElementById('video-save-play').addEventListener('click', function () { save(true); });
})();
</script>
@endpush
