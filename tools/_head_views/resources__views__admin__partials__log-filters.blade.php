fatal: path 'resources\views\admin\partials\log-filters.blade.php' exists on disk, but not in 'HEAD'
@php
    $kind = (string) ($kind ?? 'login');
    $prefix = $kind === 'error' ? 'system' : $kind;
    $placeholders = [
        'login' => admin_t('ui.ph_search_login_log'),
        'operate' => admin_t('ui.ph_search_operate_log'),
        'error' => admin_t('ui.ph_search_error_log'),
    ];
    $placeholder = $placeholders[$kind] ?? admin_t('ui.search');
    $ipName = $kind === 'error' ? 'ip' : 'login_ip';
    $ipChipId = $prefix.'-log-ip-chip';
@endphp
<form class="filter-bar log-find" id="{{ $prefix }}-log-search">
    <input type="hidden" name="{{ $ipName }}">
    <input type="search" name="q" placeholder="{{ $placeholder }}" autocomplete="off" aria-label="{{ admin_t('ui.search') }}">
    <select id="{{ $prefix }}-log-when" aria-label="{{ admin_t('ui.time') }}">
        <option value="">{{ admin_t('ui.all_time') }}</option>
        <option value="today">{{ admin_t('ui.today_chip') }}</option>
        <option value="yesterday">{{ admin_t('ui.yesterday') }}</option>
        <option value="week">{{ admin_t('ui.last_7d') }}</option>
        <option value="month">{{ admin_t('ui.last_30d') }}</option>
        <option value="custom">{{ admin_t('ui.custom_dates') }}</option>
    </select>
    <div class="field log-dates" id="{{ $prefix }}-log-dates" hidden>
        <label for="{{ $prefix }}-log-from">{{ admin_t('ui.date_from') }}</label>
        <input id="{{ $prefix }}-log-from" type="date" name="start_time" aria-label="{{ admin_t('ui.date_from') }}">
        <label for="{{ $prefix }}-log-to">{{ admin_t('ui.date_to') }}</label>
        <input id="{{ $prefix }}-log-to" type="date" name="end_time" aria-label="{{ admin_t('ui.date_to') }}">
    </div>
    @if($kind === 'error')
        <select name="level" aria-label="{{ admin_t('ui.all_levels') }}">
            <option value="">{{ admin_t('ui.all_levels') }}</option>
            <option value="error">{{ admin_t('ui.errors_only') }}</option>
        </select>
        <select name="area" aria-label="{{ admin_t('ui.all_positions') }}">
            <option value="">{{ admin_t('ui.all_positions') }}</option>
            <option value="admin">{{ admin_t('ui.admin_side') }}</option>
            <option value="front">{{ admin_t('ui.front') }}</option>
        </select>
    @else
        <label class="log-mine"><input type="checkbox" name="mine" value="1"> {{ admin_t('ui.mine_only') }}</label>
    @endif
    <button type="submit" class="btn btn-sm" id="{{ $prefix }}-log-search-btn">{{ admin_t('ui.search') }}</button>
    <button type="button" class="btn btn-muted btn-sm" id="{{ $prefix }}-log-reset-btn">{{ admin_t('ui.reset') }}</button>
    <button type="button" class="chip log-ip-chip" id="{{ $ipChipId }}" hidden></button>
</form>