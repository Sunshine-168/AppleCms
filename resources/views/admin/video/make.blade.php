@extends('admin.layouts.inner')
@section('title', admin_t('page.make'))

@php
    $enabled = (bool) ($enabled ?? false);
    $ttl = (int) ($ttl ?? 3600);
    $ttlOptions = $ttlOptions ?? \App\Services\Video\HtmlCacheService::ttlOptions();
    $lastBust = $lastBust ?? null;
    $lastBustLabel = $lastBustLabel ?? '还没有刷新过';
    $diskEnabled = (bool) ($diskEnabled ?? false);
    $diskCount = (int) ($diskCount ?? 0);
    $diskJob = $diskJob ?? ['status' => 'idle', 'percent' => 0, 'message' => '', 'kind' => 'build'];
    $scopes = $scopes ?? \App\Services\Video\DiskHtmlService::scopes();
@endphp

@section('plain')
<div class="card card-panel make-index">
    <div class="card-header"><span>{{ admin_t('page.make') }}</span></div>
    <div class="card-body">
        <div class="html-cache-page">
            <p class="muted recycle-lead">全页缓存把首页、分类和影片页先做好存起来，访客打开更快。磁盘静态页写成 <code>public/html</code>，本机也会直接读。后台、会员中心、播放器和搜索不会存。</p>

            <div class="html-cache-card">
                <div class="html-cache-status">
                    <h3>全页缓存</h3>
                    @if($enabled)
                        <span class="badge badge-ok">已开启</span>
                    @else
                        <span class="badge badge-off">未开启</span>
                    @endif
                </div>
                <form method="post" action="/admin/video/make/cache">
                    @csrf
                    <label class="inline">
                        <input type="hidden" name="html_cache_enabled" value="0">
                        <input type="checkbox" name="html_cache_enabled" value="1" @checked($enabled)>
                        启用，访客看到的是刚生成好的页面
                    </label>
                    <p class="muted field-hint">适合访问多、改动不那么频繁的站点。刚发布的改动最多等你选的时长就会出现。</p>

                    <label for="html-cache-ttl">保存多久</label>
                    <select id="html-cache-ttl" class="html-cache-ttl" name="html_cache_ttl">
                        @foreach($ttlOptions as $seconds => $label)
                            <option value="{{ $seconds }}" @selected($ttl === (int) $seconds)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="muted field-hint">选「马上换新」时，改完设置并清空后前台就会用新页面；选一段时间则期间都用存好的。</p>
                    <div class="form-actions">
                        <button class="btn" type="submit">保存设置</button>
                    </div>
                </form>
            </div>

            <div class="html-cache-card">
                <h3>现在</h3>
                <p class="muted" style="margin:0 0 8px">
                    {{ $lastBustLabel }}
                    @if(!empty($lastBust['at']))
                        · {{ \Illuminate\Support\Carbon::parse($lastBust['at'])->format('Y-m-d H:i') }}
                    @endif
                </p>
                <div class="html-cache-actions">
                    <form method="post" action="/admin/video/make/cache-warm">
                        @csrf
                        <input type="hidden" name="entries" value="30">
                        <button class="btn" type="submit" @disabled(! $enabled)>预热常用页</button>
                    </form>
                    <form method="post" action="/admin/video/make/cache-clear" onsubmit="return confirm('清空后访客第一次打开会稍慢，随后又会存起来。确定？')">
                        @csrf
                        <button class="btn btn-muted" type="submit">清空已存页面</button>
                    </form>
                </div>
                <p class="muted field-hint">
                    @if($enabled)
                        预热会现在生成首页、分类和最近 30 部影片，避免刚打开时前台还要现算。
                    @else
                        打开并保存后，才能预热。
                    @endif
                </p>
            </div>

            <div class="html-cache-card" id="diskHtmlCard">
                <div class="html-cache-status">
                    <h3>磁盘静态页</h3>
                    @if($diskEnabled)
                        <span class="badge badge-ok">已打开</span>
                    @else
                        <span class="badge badge-off">未打开</span>
                    @endif
                </div>
                <p class="muted field-hint">把首页、分类和影片写成文件。生成后前台会直接用这些文件。服务器也可把 <code>public/html</code> 当静态根。</p>
                <form method="post" action="/admin/video/make/disk">
                    @csrf
                    <label class="inline">
                        <input type="hidden" name="disk_html_enabled" value="0">
                        <input type="checkbox" name="disk_html_enabled" value="1" @checked($diskEnabled)>
                        启用，允许在后台生成静态文件
                    </label>
                    <div class="form-actions">
                        <button class="btn btn-muted" type="submit">保存这项</button>
                    </div>
                </form>
                <p id="diskHtmlCount" class="muted">
                    @if($diskCount > 0)
                        目前有 {{ $diskCount }} 个文件。
                    @else
                        还没有生成过文件。
                    @endif
                </p>
                <p class="muted field-hint" style="margin-bottom:6px">生成范围</p>
                <div class="queue-chips" id="diskHtmlScopes">
                    @foreach($scopes as $key => $label)
                        <button type="button" class="chip{{ $key === 'all' ? ' active' : '' }}" data-scope="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </div>
                <div class="html-cache-actions">
                    <button class="btn" type="button" id="diskBuildBtn" data-off="{{ $diskEnabled ? '0' : '1' }}" @disabled(! $diskEnabled)>开始生成</button>
                    <button class="btn-quiet" type="button" id="diskCancelBtn" hidden>停止</button>
                    <button class="btn-quiet" type="button" id="diskClearBtn">删掉静态文件</button>
                </div>
                <p class="muted field-hint">
                    @if($diskEnabled)
                        页面多时会分批写，文件多时会分批删。进行中请不要关掉这个页面。
                    @else
                        打开并保存后，才能生成。已有文件仍可分批删除。
                    @endif
                </p>
                <div class="disk-html-progress{{ in_array($diskJob['status'] ?? '', ['done', 'cancelled'], true) ? ' is-done' : '' }}"
                     id="diskHtmlProgress"
                     data-status="{{ $diskJob['status'] ?? 'idle' }}"
                     data-kind="{{ $diskJob['kind'] ?? 'build' }}"
                     data-percent="{{ $diskJob['percent'] ?? 0 }}"
                     @if(($diskJob['status'] ?? 'idle') === 'idle') hidden @endif>
                    <div class="disk-html-bar"><i id="diskHtmlBar" style="width: {{ (int) ($diskJob['percent'] ?? 0) }}%"></i></div>
                    <p class="muted" id="diskHtmlMsg" style="margin:8px 0 0">{{ $diskJob['message'] ?? '' }}</p>
                </div>
            </div>

            <div class="html-cache-card">
                <h3>地图和 RSS</h3>
                <p class="muted field-hint">写出 <code>public/sitemap.xml</code> 和 <code>public/rss.xml</code>，给搜索引擎用。和上面的页面文件不是一回事。</p>
                <div class="html-cache-actions">
                    <button type="button" class="btn btn-muted" data-map="sitemap">生成地图</button>
                    <button type="button" class="btn btn-muted" data-map="rss">生成 RSS</button>
                </div>
            </div>

            <div class="html-cache-card">
                <h3>其它</h3>
                <p class="muted field-hint">日人气会在每天 0 点自动清。这里只在要对账时手动清一次。</p>
                <div class="html-cache-actions">
                    <button type="button" class="btn-quiet" id="hits-reset">重置日人气</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var btn = document.getElementById('diskBuildBtn');
    var stopBtn = document.getElementById('diskCancelBtn');
    var clearBtn = document.getElementById('diskClearBtn');
    var box = document.getElementById('diskHtmlProgress');
    var bar = document.getElementById('diskHtmlBar');
    var msg = document.getElementById('diskHtmlMsg');
    var count = document.getElementById('diskHtmlCount');
    if (!btn || !box || !bar || !msg) return;
    var looping = false;
    var stepChunk = 6;
    var scope = 'all';

    function locked() {
        return btn.getAttribute('data-off') === '1';
    }

    function idleButtons() {
        btn.disabled = locked();
        if (clearBtn) clearBtn.disabled = false;
        if (stopBtn) stopBtn.hidden = true;
    }

    function jobOf(res) {
        var d = (res && res.data) || {};
        if (!d.status) {
            d.status = (res && res.code === 0) ? 'idle' : 'error';
        }
        d.message = d.message || (res && res.msg) || '';
        return d;
    }

    function paint(data) {
        box.hidden = false;
        bar.style.width = (data.percent || 0) + '%';
        var kind = data.kind || 'build';
        var fallback = kind === 'clear'
            ? ('已删 ' + (data.done || 0) + ' / ' + (data.total || 0))
            : ('已写 ' + (data.done || 0) + ' / ' + (data.total || 0));
        msg.textContent = data.message || fallback;
        box.classList.toggle('is-done', data.status === 'done');
        box.classList.toggle('is-stop', data.status === 'cancelled' || data.status === 'error');
        if (count && typeof data.file_count === 'number') {
            count.textContent = data.file_count > 0
                ? ('目前有 ' + data.file_count + ' 个文件。')
                : '还没有生成过文件。';
        }
        var busy = data.status === 'running';
        btn.disabled = busy || locked();
        if (clearBtn) clearBtn.disabled = busy;
        if (stopBtn) stopBtn.hidden = !busy;
        if (kind === 'clear') stepChunk = 40;
        else if (kind === 'build') stepChunk = 6;
    }

    function loop(chunk) {
        if (chunk) stepChunk = chunk;
        looping = true;
        function step() {
            if (!looping) return;
            U.post('/admin/video/make/step', {chunk: stepChunk}).then(function (res) {
                var data = jobOf(res);
                if (res && res.code !== 0 && !data.done) {
                    paint({ status: 'error', percent: 0, message: (res && res.msg) || '处理中断了', done: 0, total: 0 });
                    looping = false;
                    idleButtons();
                    return;
                }
                paint(data);
                if (data.status === 'running') step();
                else {
                    looping = false;
                    idleButtons();
                }
            });
        }
        step();
    }

    document.querySelectorAll('#diskHtmlScopes [data-scope]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            scope = chip.getAttribute('data-scope') || 'all';
            document.querySelectorAll('#diskHtmlScopes [data-scope]').forEach(function (el) {
                el.classList.toggle('active', el === chip);
            });
        });
    });

    btn.addEventListener('click', function () {
        if (locked()) {
            U.toast('请先打开磁盘静态页并保存', 'err');
            return;
        }
        btn.disabled = true;
        if (clearBtn) clearBtn.disabled = true;
        box.hidden = false;
        msg.textContent = '正在列出要写的页面…';
        bar.style.width = '0%';
        box.classList.remove('is-done', 'is-stop');
        U.post('/admin/video/make/start', {scope: scope}).then(function (res) {
            var data = jobOf(res);
            if (res && res.code !== 0 && !data.status) {
                paint({ status: 'error', percent: 0, message: (res && res.msg) || '没能开始', done: 0, total: 0 });
                idleButtons();
                return;
            }
            paint(data);
            if (data.status === 'running') loop(6);
            else idleButtons();
        });
    });

    clearBtn && clearBtn.addEventListener('click', function () {
        if (!confirm('删掉这些文件后，前台改回动态生成。文件多时会分批删。确定？')) return;
        btn.disabled = true;
        clearBtn.disabled = true;
        box.hidden = false;
        msg.textContent = '正在列出要删的文件…';
        bar.style.width = '0%';
        box.classList.remove('is-done', 'is-stop');
        U.post('/admin/video/make/clear', {}).then(function (res) {
            var data = jobOf(res);
            if (res && res.code !== 0 && !data.status) {
                paint({ status: 'error', percent: 0, message: (res && res.msg) || '没能开始删除', done: 0, total: 0 });
                idleButtons();
                return;
            }
            paint(data);
            if (data.status === 'running') loop(40);
            else idleButtons();
        });
    });

    stopBtn && stopBtn.addEventListener('click', function () {
        looping = false;
        U.post('/admin/video/make/cancel', {}).then(function (res) {
            paint(jobOf(res));
            idleButtons();
        });
    });

    if (box.getAttribute('data-status') === 'running') {
        loop((box.getAttribute('data-kind') || 'build') === 'clear' ? 40 : 6);
    } else if ((box.getAttribute('data-status') || 'idle') !== 'idle') {
        stopBtn && (stopBtn.hidden = true);
    }

    U.on('#hits-reset', 'click', function () {
        U.post('/admin/video/hits-reset', {}).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
    document.querySelectorAll('[data-map]').forEach(function (el) {
        el.addEventListener('click', function () {
            U.post('/admin/video/make/map', {scope: el.getAttribute('data-map')}).then(function (res) {
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            });
        });
    });
})();
</script>
@endpush
