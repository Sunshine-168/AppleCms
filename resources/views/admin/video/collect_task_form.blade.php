@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑定时采集' : '新增定时采集')

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
    $title = $isEdit ? '编辑定时采集' : '新增定时采集';
@endphp

@section('plain')
<div class="card card-panel collect-task-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/collect_tasks">返回定时采集</a>
    </div>
    <div class="card-body">
        @include('admin.partials.schedule-kind-tabs', ['tab' => 'collect'])
        <p class="muted recycle-lead">选一个采集源，设好多久采一次。到期后会按「当天 / 近 7 天 / 全库」去拉接口，结果记在采集日志里。备份、推送、插件任务（数据统计这类）去「备份 / 推送 / 插件」。</p>

        @if($sources === [])
            <p class="hint">还没有采集源。<a href="/admin/video/collects">先去加一个</a>，再回来设定时。</p>
        @endif

        <form class="collect-task-form" id="ctask-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($task['id'] ?? 0) : '' }}">

            <h3>采哪个站</h3>
            <label for="ctask-source">采集源</label>
            <select id="ctask-source" name="collect_source_id" required>
                <option value="">请选择采集源</option>
                @foreach($sources as $source)
                    <option value="{{ (int) $source['id'] }}" @selected($sourceId === (int) $source['id'])>
                        {{ $source['name'] }}@if((int) ($source['status'] ?? 1) !== 1)（已停用）@endif
                    </option>
                @endforeach
                @if($sourceMissing)
                    <option value="{{ $sourceId }}" selected>采集源 #{{ $sourceId }}（已不存在）</option>
                @endif
            </select>
            <p class="muted field-hint">没绑定分类的源，到期跑也会跳过那些分类。</p>
            <label for="ctask-name">名称</label>
            <input id="ctask-name" type="text" name="name" value="{{ $name }}" placeholder="可空，默认用采集源名" maxlength="80">

            <h3>多久采一次</h3>
            <label for="ctask-cron-pick">周期</label>
            <select id="ctask-cron-pick">
                @foreach($cronPresets as $expr => $label)
                    <option value="{{ $expr }}" @selected($cronIsPreset && $cron === $expr)>{{ $label }}</option>
                @endforeach
                <option value="custom" @selected(! $cronIsPreset)>自定义</option>
            </select>
            <input id="ctask-cron" type="text" name="cron_expression" value="{{ $cron }}" maxlength="40" @if($cronIsPreset) hidden @endif autocomplete="off">
            <p class="muted field-hint" id="ctask-cron-hint">标准 5 段 Cron，例如 <code>0 */2 * * *</code> 表示每 2 小时。</p>

            <h3>每次采多少</h3>
            <label for="ctask-hours-pick">范围</label>
            <select id="ctask-hours-pick">
                @foreach($hourPresets as $val => $label)
                    <option value="{{ $val }}" @selected($hoursIsPreset && $hours === (int) $val)>{{ $label }}</option>
                @endforeach
                <option value="custom" @selected(! $hoursIsPreset)>自定义小时</option>
            </select>
            <input id="ctask-hours" type="number" name="hours" value="{{ $hours }}" min="0" max="8760" @if($hoursIsPreset) hidden @endif>
            <p class="muted field-hint">当天一般够用。全库会按页拉完，比较慢。</p>
            <label for="ctask-pages">页数</label>
            <input id="ctask-pages" type="number" name="pages" value="{{ $pages }}" min="1" max="50">
            <p class="muted field-hint">一次拉几页。当天更新 1～3 页通常就够。</p>

            <h3>状态</h3>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                启用，到期会跑
            </label>
            <p class="muted field-hint">关掉后还留着，只是调度不会碰它。列表里仍可点「立刻采」。</p>

            <div class="form-actions">
                <button type="submit" class="btn" id="ctask-save">保存</button>
                <a class="btn btn-muted" href="/admin/video/collect_tasks">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('ctask-form');
    var source = document.getElementById('ctask-source');
    var name = document.getElementById('ctask-name');
    var cronPick = document.getElementById('ctask-cron-pick');
    var cron = document.getElementById('ctask-cron');
    var hoursPick = document.getElementById('ctask-hours-pick');
    var hours = document.getElementById('ctask-hours');
    var named = name.value.trim() !== '';

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
        name.placeholder = opt.text.replace(/（已停用）$/, '') + ' 定时';
    });
    syncCron();
    syncHours();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        syncCron();
        syncHours();
        var data = U.formData(form);
        if (!String(data.collect_source_id || '').trim()) {
            U.toast('请选择采集源', 'err');
            source.focus();
            return;
        }
        if (!String(data.cron_expression || '').trim()) {
            U.toast('请填写周期', 'err');
            cron.focus();
            return;
        }
        if (!String(form.id.value || '').trim()) delete data.id;
        U.loading(true);
        U.post('/admin/video/collect_tasks/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            U.toast('已保存', 'ok');
            location.href = '/admin/video/collect_tasks';
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
