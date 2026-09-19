@extends('admin.layouts.inner')

@php
    $task = is_array($task ?? null) ? $task : [];
    $isEdit = (bool) ($isEdit ?? false);
    $sources = $sources ?? [];
    $cronPresets = $cronPresets ?? [];
    $hourPresets = $hourPresets ?? [];
    $name = (string) ($task['name'] ?? '');
    $sourceId = (int) ($task['collect_source_id'] ?? 0);
    $cron = trim((string) ($task['cron_expression'] ?? '0 * * * *'));
    $hours = (int) ($task['hours'] ?? 24);
    $pages = max(1, (int) ($task['pages'] ?? 1));
    $status = (string) ($task['status'] ?? '1');
    $cronIsPreset = array_key_exists($cron, $cronPresets);
    $hoursIsPreset = array_key_exists($hours, $hourPresets);
    $sourceIds = array_map(fn ($row) => (int) ($row['id'] ?? 0), $sources);
    $sourceMissing = $sourceId > 0 && ! in_array($sourceId, $sourceIds, true);
    $title = $isEdit ? admin_t('ui.edit_collect_task') : admin_t('ui.add_collect_task');
    $ctaskFormJsLang = [
        'please_pick_source' => admin_t('ui.please_pick_source'),
        'please_fill_cron' => admin_t('ui.please_fill_cron'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'source_disabled_suffix' => admin_t('ui.source_disabled_suffix'),
        'task_name_suffix' => admin_t('ui.task_name_suffix'),
    ];
@endphp

@section('title', $title)

@section('plain')
<div class="card card-panel collect-task-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/collect_tasks">{{ admin_t('ui.back_collect_tasks') }}</a>
    </div>
    <div class="card-body">
        @include('admin.partials.schedule-kind-tabs', ['tab' => 'collect'])
        <p class="muted recycle-lead">{{ admin_t('ui.collect_task_form_lead') }}</p>

        @if($sources === [])
            <p class="hint">{{ admin_t('ui.no_sources_yet_before') }}<a href="/admin/video/collects">{{ admin_t('ui.no_sources_yet_link') }}</a>{{ admin_t('ui.no_sources_yet_after') }}</p>
        @endif

        <form class="admin-form collect-task-form" id="ctask-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($task['id'] ?? 0) : '' }}">

            <h3>{{ admin_t('ui.section_which_source') }}</h3>
            <label for="ctask-source">{{ admin_t('ui.label_collect_source') }}</label>
            <select id="ctask-source" name="collect_source_id" required>
                <option value="">{{ admin_t('ui.please_pick_source') }}</option>
                @foreach($sources as $source)
                    <option value="{{ (int) $source['id'] }}" @selected($sourceId === (int) $source['id'])>
                        {{ $source['name'] }}@if((int) ($source['status'] ?? 1) !== 1){{ admin_t('ui.source_disabled_suffix') }}@endif
                    </option>
                @endforeach
                @if($sourceMissing)
                    <option value="{{ $sourceId }}" selected>{{ admin_t('ui.collect_source_n', ['id' => $sourceId]) }}{{ admin_t('ui.source_gone_suffix') }}</option>
                @endif
            </select>
            <p class="muted field-hint">{{ admin_t('ui.hint_unbound_skip') }}</p>
            <label for="ctask-name">{{ admin_t('ui.label_name') }}</label>
            <input id="ctask-name" type="text" name="name" value="{{ $name }}" placeholder="{{ admin_t('ui.ph_task_name') }}" maxlength="80">

            <h3>{{ admin_t('ui.section_how_often') }}</h3>
            <label for="ctask-cron-pick">{{ admin_t('ui.label_period') }}</label>
            <select id="ctask-cron-pick">
                @foreach($cronPresets as $expr => $label)
                    <option value="{{ $expr }}" @selected($cronIsPreset && $cron === $expr)>{{ $label }}</option>
                @endforeach
                <option value="custom" @selected(! $cronIsPreset)>{{ admin_t('ui.custom') }}</option>
            </select>
            <input id="ctask-cron" type="text" name="cron_expression" value="{{ $cron }}" maxlength="40" @if($cronIsPreset) hidden @endif autocomplete="off">
            <p class="muted field-hint" id="ctask-cron-hint">{{ admin_t('ui.hint_cron_before') }}<code>0 */2 * * *</code>{{ admin_t('ui.hint_cron_after') }}</p>

            <h3>{{ admin_t('ui.section_how_much') }}</h3>
            <label for="ctask-hours-pick">{{ admin_t('ui.label_range') }}</label>
            <select id="ctask-hours-pick">
                @foreach($hourPresets as $val => $label)
                    <option value="{{ $val }}" @selected($hoursIsPreset && $hours === (int) $val)>{{ $label }}</option>
                @endforeach
                <option value="custom" @selected(! $hoursIsPreset)>{{ admin_t('ui.custom_hours') }}</option>
            </select>
            <input id="ctask-hours" type="number" name="hours" value="{{ $hours }}" min="0" max="8760" @if($hoursIsPreset) hidden @endif>
            <p class="muted field-hint">{{ admin_t('ui.hint_hours_range') }}</p>
            <label for="ctask-pages">{{ admin_t('ui.label_pages') }}</label>
            <input id="ctask-pages" type="number" name="pages" value="{{ $pages }}" min="1" max="50">
            <p class="muted field-hint">{{ admin_t('ui.hint_pages') }}</p>

            <h3>{{ admin_t('ui.status') }}</h3>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                {{ admin_t('ui.enable_run_due') }}
            </label>
            <p class="muted field-hint">{{ admin_t('ui.hint_task_status') }}</p>

            <div class="form-actions">
                <button type="submit" class="btn" id="ctask-save">{{ admin_t('ui.save') }}</button>
                <a class="btn btn-muted" href="/admin/video/collect_tasks">{{ admin_t('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($ctaskFormJsLang);
    var form = document.getElementById('ctask-form');
    var source = document.getElementById('ctask-source');
    var name = document.getElementById('ctask-name');
    var cronPick = document.getElementById('ctask-cron-pick');
    var cron = document.getElementById('ctask-cron');
    var hoursPick = document.getElementById('ctask-hours-pick');
    var hours = document.getElementById('ctask-hours');
    var named = name.value.trim() !== '';
    var disabledSuffix = L.source_disabled_suffix || '';

    function syncCron() {
        var custom = cronPick.value === 'custom';
        cron.hidden = !custom;
        if (!custom) cron.value = cronPick.value;
    }
    function syncHours() {
        var custom = hoursPick.value === 'custom';
        hours.hidden = !custom;
        if (!custom) hours.value = hoursPick.value;
    }
    cronPick.addEventListener('change', syncCron);
    hoursPick.addEventListener('change', syncHours);
    name.addEventListener('input', function () { named = name.value.trim() !== ''; });
    source.addEventListener('change', function () {
        if (named) return;
        var opt = source.options[source.selectedIndex];
        if (!opt || !opt.value) return;
        var base = opt.text;
        if (disabledSuffix && base.slice(-disabledSuffix.length) === disabledSuffix) {
            base = base.slice(0, -disabledSuffix.length);
        }
        name.placeholder = base + (L.task_name_suffix || '');
    });
    syncCron();
    syncHours();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        syncCron();
        syncHours();
        var data = U.formData(form);
        if (!String(data.collect_source_id || '').trim()) {
            U.toast(L.please_pick_source, 'err');
            source.focus();
            return;
        }
        if (!String(data.cron_expression || '').trim()) {
            U.toast(L.please_fill_cron, 'err');
            cron.focus();
            return;
        }
        if (!String(form.id.value || '').trim()) delete data.id;
        U.loading(true);
        U.post('/admin/video/collect_tasks/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            U.toast(L.saved, 'ok');
            location.href = '/admin/video/collect_tasks';
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    });
})();
</script>
@endpush
