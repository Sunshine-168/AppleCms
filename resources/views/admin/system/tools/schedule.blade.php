@extends('admin.layouts.inner')
@section('title', admin_t('page.schedule'))

@php
    $board = $board ?? [];
    $ui = $board['ui'] ?? [];
    $cmds = $board['artisan_cmds'] ?? [];
    $cronPresets = $board['cron_presets'] ?? [];
@endphp

@section('plain')
<div class="card card-panel schedule-index" id="schedule-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '' }}</span>
    </div>
    <div class="card-body">
        @include('admin.partials.schedule-kind-tabs', ['tab' => 'other'])
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}推地址去「<a href="/admin/video/push">搜索推送</a>」。改完还不生效去「<a href="/admin/system/tools/cache">缓存</a>」。</p>
        @if(($board['collect_note'] ?? '') !== '')
            <p class="muted">{{ $board['collect_note'] }}</p>
        @endif

        <section class="schedule-install">
            <div class="schedule-install-head">
                <h3>{{ $ui['install'] ?? '' }}</h3>
            </div>
            <p class="muted field-hint">{{ $ui['install_hint'] ?? '' }}</p>
            <div class="schedule-cron-row">
                <code id="schedule-cron-line">{{ $board['cron_line'] ?? '' }}</code>
                <button type="button" class="btn btn-muted btn-sm" id="schedule-copy-cron">{{ $ui['copy'] ?? '' }}</button>
            </div>
            @if(($board['idle_on'] ?? 0) > 0)
                <p class="schedule-idle-hint">{{ $board['idle_on'] }} {{ $ui['idle'] ?? '' }}</p>
            @endif
        </section>

        <section class="schedule-block">
            <h3>{{ $ui['builtin'] ?? '' }}</h3>
            <p class="muted field-hint">{{ $ui['builtin_hint'] ?? '' }}</p>
            <div class="schedule-builtin-grid">
                @foreach(($board['builtins'] ?? []) as $row)
                    <article class="schedule-builtin">
                        <strong>{{ $row['label'] }}</strong>
                        <span>{{ $row['when'] }}</span>
                        <p>{{ $row['hint'] }}</p>
                        @if(!empty($row['url']))
                            <a href="{{ $row['url'] }}">{{ $ui['look'] ?? '' }}</a>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="schedule-block">
            <div class="schedule-block-head">
                <div>
                    <h3>{{ $ui['custom'] ?? '' }}</h3>
                    <p class="muted field-hint">{{ $ui['custom_hint'] ?? '' }}</p>
                </div>
                <button type="button" class="btn btn-muted btn-sm" id="schedule-toggle-form">{{ $ui['add_custom'] ?? '' }}</button>
            </div>
            <div class="schedule-presets">
                @foreach(($board['presets'] ?? []) as $preset)
                    @if(!empty($preset['added']))
                        <span class="schedule-preset is-added">{{ $ui['added_prefix'] ?? '' }}{{ $preset['label'] }}</span>
                    @else
                        <button type="button" class="btn btn-muted btn-sm schedule-preset" data-preset="{{ $preset['command'] }}" data-params="{{ $preset['params'] ?? '' }}">{{ $ui['add_prefix'] ?? '' }}{{ $preset['label'] }}</button>
                    @endif
                @endforeach
            </div>
            @if(empty($board['tasks']))
                <div class="schedule-empty">
                    <p>{{ $ui['empty'] ?? '' }}</p>
                    <p class="muted">{{ $ui['empty_hint'] ?? '' }}</p>
                </div>
            @else
                <div class="schedule-task-list">
                    @foreach($board['tasks'] as $row)
                        <article class="schedule-task{{ empty($row['on']) ? ' is-off' : '' }}" data-id="{{ $row['id'] }}">
                            <div class="schedule-task-main">
                                <strong>{{ $row['name'] }}</strong>
                                <span class="schedule-task-cmd">{{ $row['command_label'] }}</span>
                                <p class="muted">{{ $row['cron_label'] }} / {{ $ui['next'] ?? '' }} {{ $row['next_text'] }}</p>
                                <p class="muted">{{ $row['last_text'] }}</p>
                                @if(!empty($row['last_error']))
                                    <p class="schedule-err">{{ $ui['last_fail'] ?? '' }}: {{ $row['last_error'] }}</p>
                                @endif
                                @if(!empty($row['legacy_run']))
                                    <p class="muted">{{ $ui['no_duration'] ?? '' }}</p>
                                @endif
                                @if(!empty($row['logs']))
                                    <details class="schedule-logs">
                                        <summary>{{ $ui['recent'] ?? '' }}</summary>
                                        @foreach($row['logs'] as $log)
                                            <article class="schedule-log{{ empty($log['ok']) ? ' is-fail' : '' }}">
                                                <strong>{{ $log['status_text'] }}</strong>
                                                <span>{{ $log['duration_text'] }} · {{ $log['time_text'] }}</span>
                                                @if(($log['error'] ?? '') !== '')
                                                    <p class="schedule-err">{{ $log['error'] }}</p>
                                                @elseif(($log['output'] ?? '') !== '')
                                                    <p class="muted">{{ $log['output'] }}</p>
                                                @endif
                                            </article>
                                        @endforeach
                                    </details>
                                @endif
                            </div>
                            <div class="schedule-task-side">
                                <button type="button" class="schedule-switch js-toggle{{ !empty($row['on']) ? ' is-on' : '' }}" title="{{ !empty($row['on']) ? ($ui['stop'] ?? '') : ($ui['start'] ?? '') }}" aria-label="{{ !empty($row['on']) ? ($ui['stop'] ?? '') : ($ui['start'] ?? '') }}" aria-pressed="{{ !empty($row['on']) ? 'true' : 'false' }}"></button>
                                <button type="button" class="btn btn-sm js-run">{{ $ui['run'] ?? '' }}</button>
                                <button type="button" class="btn-link js-edit" data-row="{{ e(json_encode($row, JSON_UNESCAPED_UNICODE)) }}">{{ $ui['edit'] ?? '' }}</button>
                                <button type="button" class="btn-link js-del">{{ $ui['delete'] ?? '' }}</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <form class="schedule-form" id="schedule-form" hidden>
            <h3 id="schedule-form-title">{{ $ui['form_add'] ?? '' }}</h3>
            <input type="hidden" name="id" value="0">
            <label>{{ $ui['name'] ?? '' }}<input type="text" name="name" maxlength="80" placeholder="{{ $ui['name_ph'] ?? '' }}"></label>
            <label>{{ $ui['kind'] ?? '' }}
                <select name="type" id="schedule-type">
                    <option value="artisan">{{ $ui['kind_artisan'] ?? '' }}</option>
                    <option value="http">{{ $ui['kind_http'] ?? '' }}</option>
                    <option value="shell">{{ $ui['kind_shell'] ?? '' }}</option>
                </select>
            </label>
            <div class="js-type-artisan">
                <label>{{ $ui['cmd'] ?? '' }}
                    <select name="artisan_cmd">
                        @foreach($cmds as $cmd => $label)
                            <option value="{{ $cmd }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ $ui['params'] ?? '' }}<input type="text" name="artisan_params" placeholder="{{ $ui['params_ph'] ?? '' }}"></label>
            </div>
            <div class="js-type-http" hidden>
                <label>{{ $ui['url'] ?? '' }}<input type="url" name="http_url" placeholder="https://"></label>
                <p class="muted field-hint">{{ $ui['http_hint'] ?? '' }}</p>
            </div>
            <div class="js-type-shell" hidden>
                <label>{{ $ui['kind_shell'] ?? '' }}<input type="text" name="shell_command"></label>
                <p class="muted field-hint">{{ $ui['shell_hint'] ?? '' }}</p>
            </div>
            <label>{{ $ui['every'] ?? '' }}
                <select name="cron" id="schedule-cron">
                    @foreach($cronPresets as $expr => $label)
                        <option value="{{ $expr }}" @if($expr === '0 4 * * *') selected @endif>{{ $label }}</option>
                    @endforeach
                    <option value="custom">{{ $ui['cron_custom'] ?? '' }}</option>
                </select>
            </label>
            <label id="schedule-cron-custom" hidden>cron<input type="text" name="cron_custom" placeholder="0 4 * * *"></label>
            <label class="inline"><input type="checkbox" name="status" value="1" checked> {{ $ui['keep_on'] ?? '' }}</label>
            <div class="schedule-form-actions">
                <button type="submit" class="btn btn-sm">{{ $ui['save'] ?? '' }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="schedule-form-cancel">{{ $ui['cancel'] ?? '' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var root = document.getElementById('schedule-index');
    if (!root || !U) return;
    var form = document.getElementById('schedule-form');
    var presets = @json($board['presets'] ?? []);
    var ui = @json($ui);
    function say(res) { U.toast((res && res.msg) || '', res && res.code === 0 ? 'ok' : 'err'); }
    function reload() { location.reload(); }
    function showForm() { form.hidden = false; form.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    function setType(t) {
        form.querySelector('[name=type]').value = t;
        form.querySelector('.js-type-artisan').hidden = t !== 'artisan';
        form.querySelector('.js-type-http').hidden = t !== 'http';
        form.querySelector('.js-type-shell').hidden = t !== 'shell';
    }
    function hideForm() {
        form.hidden = true;
        form.reset();
        form.querySelector('[name=id]').value = '0';
        document.getElementById('schedule-form-title').textContent = ui.form_add || '';
        form.querySelector('[name=status]').checked = true;
        document.getElementById('schedule-cron').value = '0 4 * * *';
        document.getElementById('schedule-cron-custom').hidden = true;
        setType('artisan');
    }
    document.getElementById('schedule-copy-cron').addEventListener('click', function () {
        var line = (document.getElementById('schedule-cron-line').textContent || '').trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(line).then(function () { U.toast(ui.copied || '', 'ok'); });
            return;
        }
        U.toast(line, 'ok');
    });
    document.getElementById('schedule-toggle-form').addEventListener('click', function () { showForm(); });
    document.getElementById('schedule-form-cancel').addEventListener('click', hideForm);
    document.getElementById('schedule-type').addEventListener('change', function () { setType(this.value); });
    document.getElementById('schedule-cron').addEventListener('change', function () {
        document.getElementById('schedule-cron-custom').hidden = this.value !== 'custom';
    });
    root.querySelectorAll('[data-preset]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var cmd = btn.getAttribute('data-preset');
            var p = presets.find(function (x) { return x.command === cmd; }) || {};
            U.loading(true);
            U.post('/admin/system/tools/schedule/save', {
                name: p.label || cmd,
                type: p.type || 'artisan',
                command: cmd,
                params: btn.getAttribute('data-params') || p.params || '',
                cron: p.cron || '0 4 * * *',
                status: 1
            }).then(function (res) {
                U.loading(false);
                say(res);
                if (res && res.code === 0) reload();
            }).catch(function () { U.loading(false); U.toast('没连上', 'err'); });
        });
    });
    root.querySelectorAll('.js-run').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.closest('.schedule-task').getAttribute('data-id');
            btn.disabled = true;
            U.loading(true);
            U.post('/admin/system/tools/schedule/run', { id: id }).then(function (res) {
                U.loading(false);
                say(res);
                if (res && res.code === 0) setTimeout(reload, 400);
            }).catch(function () { U.loading(false); U.toast('没连上', 'err'); }).finally(function () { btn.disabled = false; });
        });
    });
    root.querySelectorAll('.js-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = btn.closest('.schedule-task');
            var on = !card.classList.contains('is-off');
            btn.disabled = true;
            U.post('/admin/system/tools/schedule/status', {
                id: card.getAttribute('data-id'),
                status: on ? 0 : 1
            }).then(function (res) {
                say(res);
                if (!res || res.code !== 0) return;
                card.classList.toggle('is-off', on);
                btn.classList.toggle('is-on', !on);
                btn.setAttribute('aria-pressed', on ? 'false' : 'true');
                btn.title = on ? (ui.start || '') : (ui.stop || '');
                btn.setAttribute('aria-label', btn.title);
            }).finally(function () { btn.disabled = false; });
        });
    });
    root.querySelectorAll('.js-del').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!U.confirm(ui.del_confirm || '')) return;
            U.post('/admin/system/tools/schedule/delete', { id: btn.closest('.schedule-task').getAttribute('data-id') }).then(function (res) {
                say(res);
                if (res && res.code === 0) reload();
            });
        });
    });
    root.querySelectorAll('.js-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = {};
            try { row = JSON.parse(btn.getAttribute('data-row') || '{}'); } catch (e) {}
            hideForm();
            form.querySelector('[name=id]').value = row.id || 0;
            form.querySelector('[name=name]').value = row.name || '';
            form.querySelector('[name=status]').checked = !!row.on;
            document.getElementById('schedule-form-title').textContent = ui.edit || '';
            var t = row.type || 'artisan';
            setType(t);
            if (t === 'artisan') {
                var full = (row.command || '') + (row.params_text ? ' ' + row.params_text : '');
                var sel = form.querySelector('[name=artisan_cmd]');
                var picked = '';
                for (var oi = 0; oi < sel.options.length; oi++) {
                    if (sel.options[oi].value === full || sel.options[oi].value === (row.command || '')) {
                        picked = sel.options[oi].value;
                        break;
                    }
                }
                if (picked === full && full.indexOf(' ') > 0) {
                    sel.value = full;
                    form.querySelector('[name=artisan_params]').value = '';
                } else {
                    sel.value = picked || row.command || 'video:baidu-push';
                    form.querySelector('[name=artisan_params]').value = row.params_text || '';
                }
            } else if (t === 'http') {
                form.querySelector('[name=http_url]').value = row.command || '';
            } else {
                form.querySelector('[name=shell_command]').value = row.command || '';
            }
            var cronSel = document.getElementById('schedule-cron');
            var found = false;
            for (var i = 0; i < cronSel.options.length; i++) {
                if (cronSel.options[i].value === row.cron) { cronSel.value = row.cron; found = true; break; }
            }
            if (!found) {
                cronSel.value = 'custom';
                form.querySelector('[name=cron_custom]').value = row.cron || '';
                document.getElementById('schedule-cron-custom').hidden = false;
            } else {
                document.getElementById('schedule-cron-custom').hidden = true;
            }
            showForm();
        });
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var t = form.querySelector('[name=type]').value;
        var cron = document.getElementById('schedule-cron').value;
        if (cron === 'custom') cron = form.querySelector('[name=cron_custom]').value;
        var payload = {
            id: form.querySelector('[name=id]').value,
            name: form.querySelector('[name=name]').value,
            type: t,
            cron: cron,
            status: form.querySelector('[name=status]').checked ? 1 : 0,
            command: '',
            params: ''
        };
        if (t === 'artisan') {
            var raw = form.querySelector('[name=artisan_cmd]').value;
            var extra = form.querySelector('[name=artisan_params]').value;
            if (raw.indexOf('plugin:run ') === 0) {
                payload.command = 'plugin:run';
                payload.params = (raw.slice(11) + ' ' + extra).trim();
            } else {
                payload.command = raw;
                payload.params = extra;
            }
        } else if (t === 'http') {
            payload.command = form.querySelector('[name=http_url]').value;
        } else {
            payload.command = form.querySelector('[name=shell_command]').value;
        }
        U.loading(true);
        U.post('/admin/system/tools/schedule/save', payload).then(function (res) {
            U.loading(false);
            say(res);
            if (res && res.code === 0) reload();
        }).catch(function () { U.loading(false); U.toast('没连上', 'err'); });
    });
})();
</script>
@endpush
