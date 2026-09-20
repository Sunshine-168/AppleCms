@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_union') : admin_t('ui.add_union'))

@php
    $union = is_array($union ?? null) ? $union : [];
    $isEdit = (bool) ($isEdit ?? false);
    $name = (string) ($union['name'] ?? '');
    $apiUrl = (string) ($union['api_url'] ?? '');
    $note = (string) ($union['note'] ?? '');
    $status = (string) ($union['status'] ?? '1');
    $adopted = (int) ($union['adopted'] ?? 0) === 1;
    $title = $isEdit ? admin_t('ui.edit_union') : admin_t('ui.add_union');
    $unionFormJsLang = [
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_fill_api' => admin_t('ui.please_fill_api'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'saved_adopt_fail' => admin_t('ui.saved_adopt_fail'),
        'adopt_ok' => admin_t('ui.adopt_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel union-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/unions">{{ admin_t('ui.back_unions') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($adopted)
                {{ admin_t('ui.union_lead_adopted') }}
            @else
                {{ admin_t('ui.union_lead_new') }}
            @endif
        </p>

        <form class="admin-form tag-form union-form" id="union-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($union['id'] ?? 0) : '' }}">

            <h3>{{ admin_t('ui.union_section_site') }}</h3>
            <div class="form-field">
                <label for="union-name">{{ admin_t('ui.label_name') }}</label>
                <input id="union-name" class="entry-title" type="text" name="name" value="{{ $name }}" placeholder="{{ admin_t('ui.ph_union_name') }}" required>
            </div>
            <div class="form-field">
                <label for="union-url">{{ admin_t('ui.label_api_url') }}</label>
                <input id="union-url" type="text" name="api_url" value="{{ $apiUrl }}" placeholder="https://xxx/api.php/provide/vod/" required>
                <p class="muted field-hint">{{ admin_t('ui.hint_apple_api') }}</p>
            </div>
            <div class="form-field">
                <label for="union-note">{{ admin_t('ui.label_note') }}</label>
                <input id="union-note" type="text" name="note" value="{{ $note }}" placeholder="{{ admin_t('ui.ph_union_note') }}">
            </div>

            <h3>{{ admin_t('ui.union_section_display') }}</h3>
            <div class="form-field">
                <label for="union-sort">{{ admin_t('ui.sort') }}</label>
                <input id="union-sort" type="number" name="sort" value="{{ $union['sort'] ?? 0 }}">
                <p class="muted field-hint">{{ admin_t('ui.hint_sort_desc') }}</p>
            </div>
            <div class="form-field">
                <input type="hidden" name="status" value="0">
                <label class="inline">
                    <input type="checkbox" name="status" value="1" @checked($status === '1')>
                    {{ admin_t('ui.show_in_list') }}
                </label>
                <p class="muted field-hint">{{ admin_t('ui.hint_hide_bookmark') }}</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn" id="union-save">{{ admin_t('ui.save') }}</button>
                @if(! $adopted)
                    <button type="button" class="btn btn-muted" id="union-save-adopt">{{ admin_t('ui.save_and_adopt') }}</button>
                @endif
                <a class="btn btn-muted" href="/admin/video/unions">{{ admin_t('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($unionFormJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('union-form');
    var isEdit = !!String(form.id.value || '').trim();
    var adoptBtn = document.getElementById('union-save-adopt');

    function save(goAdopt) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast(L.please_fill_name, 'err');
            document.getElementById('union-name').focus();
            return;
        }
        if (!String(data.api_url || '').trim()) {
            U.toast(L.please_fill_api, 'err');
            document.getElementById('union-url').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/unions/save', data).then(function (res) {
            if (!res || res.code !== 0) {
                U.loading(false);
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            if (goAdopt && id) {
                return U.post('/admin/video/unions/adopt', {id: id}).then(function (adoptRes) {
                    U.loading(false);
                    if (!adoptRes || adoptRes.code !== 0) {
                        U.toast((adoptRes && adoptRes.msg) || L.saved_adopt_fail, 'err');
                        location.href = '/admin/video/unions';
                        return;
                    }
                    U.toast((adoptRes && adoptRes.msg) || L.adopt_ok, 'ok');
                    location.href = '/admin/video/collects';
                });
            }
            U.loading(false);
            U.toast(L.saved, 'ok');
            location.href = '/admin/video/unions';
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save(false);
    });
    if (adoptBtn) adoptBtn.addEventListener('click', function () { save(true); });
})();
</script>
@endpush
