@php
    $kind = (string) ($kind ?? 'login');
    $prefix = $kind === 'error' ? 'system' : $kind;
    $placeholders = [
        'login' => '搜管理员或 IP',
        'operate' => '搜操作人、内容或模块',
        'error' => '搜报错内容、地址或管理员',
    ];
    $placeholder = $placeholders[$kind] ?? '搜索';
    $ipName = $kind === 'error' ? 'ip' : 'login_ip';
    $ipChipId = $prefix.'-log-ip-chip';
@endphp
<form class="filter-bar log-find" id="{{ $prefix }}-log-search">
    <input type="hidden" name="{{ $ipName }}">
    <input type="search" name="q" placeholder="{{ $placeholder }}" autocomplete="off" aria-label="搜索">
    <select id="{{ $prefix }}-log-when" aria-label="时间">
        <option value="">全部时间</option>
        <option value="today">今天</option>
        <option value="yesterday">昨天</option>
        <option value="week">近7天</option>
        <option value="month">近30天</option>
        <option value="custom">自选日期</option>
    </select>
    <div class="field log-dates" id="{{ $prefix }}-log-dates" hidden>
        <label for="{{ $prefix }}-log-from">从</label>
        <input id="{{ $prefix }}-log-from" type="date" name="start_time" aria-label="开始日期">
        <label for="{{ $prefix }}-log-to">到</label>
        <input id="{{ $prefix }}-log-to" type="date" name="end_time" aria-label="结束日期">
    </div>
    @if($kind === 'error')
        <select name="level" aria-label="级别">
            <option value="">全部级别</option>
            <option value="error">仅报错</option>
        </select>
        <select name="area" aria-label="位置">
            <option value="">全部位置</option>
            <option value="admin">后台</option>
            <option value="front">前台</option>
        </select>
    @else
        <label class="log-mine"><input type="checkbox" name="mine" value="1"> 只看我</label>
    @endif
    <button type="submit" class="btn btn-sm" id="{{ $prefix }}-log-search-btn">{{ admin_t('ui.search') }}</button>
    <button type="button" class="btn btn-muted btn-sm" id="{{ $prefix }}-log-reset-btn">{{ admin_t('ui.reset') }}</button>
    <button type="button" class="chip log-ip-chip" id="{{ $ipChipId }}" hidden></button>
</form>
