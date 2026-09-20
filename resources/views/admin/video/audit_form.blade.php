@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_rule') : admin_t('ui.new_rule'))

@php
    $rule = is_array($rule ?? null) ? $rule : [];
    $isEdit = (bool) ($isEdit ?? false);
    $scopes = $scopes ?? ['title' => admin_t('ui.title_label'), 'content' => admin_t('ui.intro'), 'actor' => admin_t('ui.actors')];
    $actions = $actions ?? ['skip' => admin_t('ui.audit_skip_full'), 'review' => admin_t('ui.audit_review_full'), 'replace' => admin_t('ui.audit_replace_full')];
    $name = (string) ($rule['name'] ?? '');
    $scope = (string) ($rule['scope'] ?? 'title');
    $action = (string) ($rule['action'] ?? 'skip');
    $words = (string) ($rule['words'] ?? '');
    $isRegex = (int) ($rule['is_regex'] ?? 0) === 1;
    $status = (string) ($rule['status'] ?? '1');
    $sort = (int) ($rule['sort'] ?? 0);
    $title = $isEdit ? admin_t('ui.edit_rule') : admin_t('ui.new_rule');
    $auditFormJsLang = [
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'save_fail' => admin_t('ui.save_fail'),
        'please_fill_keywords' => admin_t('ui.please_fill_keywords'),
        'please_fill_sample' => admin_t('ui.please_fill_sample'),
        'no_try_result' => admin_t('ui.no_try_result'),
        'hint_skip' => admin_t('ui.audit_hint_skip'),
        'hint_review' => admin_t('ui.audit_hint_review'),
        'hint_replace' => admin_t('ui.audit_hint_replace'),
    ];
@endphp

@section('plain')
<div class="card card-panel audit-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/audits">{{ admin_t('ui.back_audits') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.audit_form_lead') }}</p>

        <form class="admin-form audit-form" id="audit-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($rule['id'] ?? 0) : '' }}">

            <h3>{{ admin_t('ui.block_what') }}</h3>
            <label for="audit-name">{{ admin_t('ui.name') }}</label>
            <input id="audit-name" type="text" name="name" value="{{ $name }}" placeholder="{{ admin_t('ui.ph_audit_name') }}" maxlength="80">
            <label for="audit-scope">{{ admin_t('ui.look_where') }}</label>
            <select id="audit-scope" name="scope">
                @foreach($scopes as $val => $lab)
                    <option value="{{ $val }}" @selected($scope === $val)>{{ $lab }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.audit_scope_hint') }}</p>
            <label for="audit-words">{{ admin_t('ui.keywords') }}</label>
            <textarea id="audit-words" name="words" placeholder="{{ admin_t('ui.ph_audit_words') }}">{{ $words }}</textarea>
            <p class="muted field-hint">{{ admin_t('ui.audit_words_hint') }}</p>
            <input type="hidden" name="is_regex" value="0">
            <label class="inline">
                <input type="checkbox" name="is_regex" value="1" @checked($isRegex)>
                {{ admin_t('ui.match_regex') }}
            </label>

            <h3>{{ admin_t('ui.after_hit') }}</h3>
            <label for="audit-action">{{ admin_t('ui.action_label') }}</label>
            <select id="audit-action" name="action">
                @foreach($actions as $val => $lab)
                    <option value="{{ $val }}" @selected($action === $val)>{{ $lab }}</option>
                @endforeach
            </select>
            <p class="muted field-hint" id="audit-action-hint"></p>

            <h3>{{ admin_t('ui.try_a_line') }}</h3>
            <label for="audit-sample">{{ admin_t('ui.sample') }}</label>
            <input id="audit-sample" type="text" name="sample" placeholder="{{ admin_t('ui.ph_audit_sample') }}" autocomplete="off">
            <p class="muted field-hint">{{ admin_t('ui.audit_sample_hint') }}</p>
            <button type="button" class="btn btn-muted btn-sm" id="audit-try-btn">{{ admin_t('ui.try_it') }}</button>
            <p class="muted" id="audit-try-msg" hidden></p>

            <h3>{{ admin_t('ui.status') }}</h3>
            <label for="audit-sort">{{ admin_t('ui.sort') }}</label>
            <input id="audit-sort" type="number" name="sort" value="{{ $sort }}" min="0">
            <p class="muted field-hint">{{ admin_t('ui.sort_first_hint') }}</p>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                {{ admin_t('ui.enable_on_collect') }}
            </label>
            <p class="muted field-hint">{{ admin_t('ui.audit_keep_off_hint') }}</p>

            <div class="form-actions">
                <button type="submit" class="btn" id="audit-save">{{ admin_t('ui.save') }}</button>
                <a class="btn btn-muted" href="/admin/video/audits">{{ admin_t('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($auditFormJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('audit-form');
    var action = document.getElementById('audit-action');
    var hint = document.getElementById('audit-action-hint');
    var tryBtn = document.getElementById('audit-try-btn');
    var tryMsg = document.getElementById('audit-try-msg');
    var hints = {
        skip: L.hint_skip,
        review: L.hint_review,
        replace: L.hint_replace
    };
    function syncHint() {
        hint.textContent = hints[action.value] || '';
    }
    action.addEventListener('change', syncHint);
    syncHint();

    tryBtn.addEventListener('click', function () {
        var data = U.formData(form);
        if (!String(data.words || '').trim()) {
            U.toast(L.please_fill_keywords, 'err');
            document.getElementById('audit-words').focus();
            return;
        }
        if (!String(data.sample || '').trim()) {
            U.toast(L.please_fill_sample, 'err');
            document.getElementById('audit-sample').focus();
            return;
        }
        U.post('/admin/video/audits/try', data).then(function (res) {
            tryMsg.hidden = false;
            tryMsg.textContent = (res && res.msg) || L.no_try_result;
            U.toast((res && res.msg) || L.no_try_result, res && res.code === 0 ? 'ok' : 'err');
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.words || '').trim()) {
            U.toast(L.please_fill_keywords, 'err');
            document.getElementById('audit-words').focus();
            return;
        }
        if (!String(form.id.value || '').trim()) delete data.id;
        delete data.sample;
        U.loading(true);
        U.post('/admin/video/audits/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            U.toast(L.saved, 'ok');
            location.href = '/admin/video/audits';
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    });
})();
</script>
@endpush
