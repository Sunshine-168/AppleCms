@php
    $isEdit = (bool) ($isEdit ?? false);
    $kind = ($kind ?? 'tag') === 'author' ? 'author' : 'tag';
    $entity = is_array($entity ?? null) ? $entity : [];
    $name = (string) ($entity['name'] ?? '');
    $slug = (string) ($entity['slug'] ?? '');
    $sort = (int) ($entity['sort'] ?? 0);
    $status = (string) ($entity['status'] ?? '1');
    $frontUrl = trim((string) ($entity['url'] ?? ''));
    $count = (int) ($count ?? 0);
    $formId = (string) $formId;
    $listUrl = (string) $listUrl;
    $saveUrl = (string) $saveUrl;
    $countUrl = (string) ($countUrl ?? '');
    $frontPrefix = (string) $frontPrefix;
    $example = (string) ($example ?? 'name');
    $namePh = (string) $namePh;
    $offHint = (string) $offHint;
    $usedTail = (string) $usedTail;
    $needName = (string) $needName;
    $nameId = $kind.'-name';
    $slugId = $kind.'-slug';
    $hintId = $kind.'-slug-hint';
    $titleText = $isEdit
        ? admin_t($kind === 'author' ? 'ui.edit_author' : 'ui.edit_tag')
        : admin_t($kind === 'author' ? 'ui.new_author' : 'ui.new_tag');
    $backText = admin_t($kind === 'author' ? 'ui.back_authors' : 'ui.back_tags');
    $addBtn = admin_t($kind === 'author' ? 'ui.add_author' : 'ui.add_tag');
    $jsLang = [
        'front_url' => admin_t('ui.front_url_is', ['path' => '__PATH__']),
        'slug_auto' => admin_t('ui.slug_auto_example', ['example' => $frontPrefix.$example]),
        'need_name' => $needName,
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'added' => admin_t('ui.added'),
        'prefix' => $frontPrefix,
        'list' => $listUrl,
        'save' => $saveUrl,
    ];
@endphp
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $titleText }}</span>
        <a class="btn btn-muted btn-sm" href="{{ $listUrl }}">{{ $backText }}</a>
    </div>
    <div class="card-body">
        @if($isEdit && $count > 0 && $countUrl !== '')
            <p class="muted recycle-lead">{{ admin_t('ui.used_by') }} <a href="{{ $countUrl }}">{{ admin_t('ui.works_n', ['n' => $count]) }}</a> — {{ $usedTail }}</p>
        @endif
        <form class="admin-form tag-form" id="{{ $formId }}">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($entity['id'] ?? 0) : '' }}">
            <label for="{{ $nameId }}">{{ admin_t('ui.name') }}</label>
            <input id="{{ $nameId }}" type="text" name="name" value="{{ $name }}" placeholder="{{ $namePh }}" required autofocus>
            <p class="muted field-hint" id="{{ $hintId }}">{{ admin_t('ui.slug_auto_example', ['example' => $frontPrefix.'…']) }}</p>
            <details class="entry-seo" @if($isEdit) open @endif>
                <summary>{{ admin_t('ui.url_section') }}</summary>
                <label for="{{ $slugId }}">{{ admin_t('ui.url_slug') }}</label>
                <input id="{{ $slugId }}" type="text" name="slug" value="{{ $slug }}" placeholder="{{ admin_t('ui.ph_slug_from_name') }}">
                <p class="muted field-hint">{{ admin_t('ui.slug_no_redirect') }}</p>
                <label for="{{ $kind }}-sort">{{ admin_t('ui.sort') }}</label>
                <input id="{{ $kind }}-sort" type="number" name="sort" min="0" value="{{ $sort }}">
                <label for="{{ $kind }}-status">{{ admin_t('ui.status') }}</label>
                <select id="{{ $kind }}-status" name="status">
                    <option value="1" @selected($status === '1')>{{ admin_t('ui.enabled') }}</option>
                    <option value="0" @selected($status === '0')>{{ admin_t('ui.disabled') }}</option>
                </select>
                <p class="muted field-hint">{{ $offHint }}</p>
            </details>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? admin_t('ui.save') : $addBtn }}</button>
                <a class="btn btn-muted" href="{{ $listUrl }}">{{ admin_t('ui.cancel') }}</a>
                @if($isEdit && $frontUrl !== '')
                    <a class="btn btn-muted" href="{{ $frontUrl }}" target="_blank" rel="noopener">{{ admin_t('ui.front') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById(@json($formId));
    var title = document.getElementById(@json($nameId));
    var slug = document.getElementById(@json($slugId));
    var hint = document.getElementById(@json($hintId));
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    function preview() {
        var custom = (slug && slug.value || '').trim();
        var raw = custom || (title.value || '').trim().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]+/g, '');
        hint.textContent = raw
            ? String(L.front_url || '').replace('__PATH__', L.prefix + raw)
            : (L.slug_auto || '');
    }
    title.addEventListener('input', preview);
    if (slug) slug.addEventListener('input', preview);
    preview();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast(L.need_name, 'err');
            title.focus();
            return;
        }
        U.loading(true);
        U.post(L.save, data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.added, 'ok');
            if (!isEdit && id) location.href = L.list + '/' + encodeURIComponent(id) + '/edit';
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    });
})();
</script>
@endpush
