@extends('admin.layouts.inner')
@section('title', admin_t('page.make'))

@php
    $desk = in_array((string) ($desk ?? 'opt'), ['opt', 'index', 'map', 'cache'], true)
        ? (string) $desk
        : 'opt';
    $enabled = (bool) ($enabled ?? false);
    $ttl = (int) ($ttl ?? 3600);
    $ttlOptions = $ttlOptions ?? \App\Services\Video\HtmlCacheService::ttlOptions();
    $lastBust = $lastBust ?? null;
    $lastBustLabel = $lastBustLabel ?? '还没有刷新过';
    $diskEnabled = (bool) ($diskEnabled ?? false);
    $diskCount = (int) ($diskCount ?? 0);
    $diskJob = $diskJob ?? ['status' => 'idle', 'percent' => 0, 'message' => '', 'kind' => 'build'];
    $vodTypes = is_array($vodTypes ?? null) ? $vodTypes : [];
    $artTypes = is_array($artTypes ?? null) ? $artTypes : [];
    $topics = is_array($topics ?? null) ? $topics : [];
    $actors = is_array($actors ?? null) ? $actors : [];
    $roles = is_array($roles ?? null) ? $roles : [];
    $hasArts = (bool) ($hasArts ?? false);
    $detailCap = (int) ($detailCap ?? 2000);
@endphp

@section('plain')
<div class="card card-panel make-index" id="make-index" data-off="{{ $diskEnabled ? '0' : '1' }}">
    <div class="card-header"><span>{{ admin_t('page.make') }}</span></div>
    <div class="card-body">
        <div class="html-cache-page">
            <p class="muted recycle-lead">写出 <code>public/html</code>。播放页、搜索、会员中心不写。没有独立 WAP。地图是 sitemap.xml。详情每次最多 {{ $detailCap }} 条。</p>

            <div class="queue-chips" id="make-desks">
                <a class="chip{{ $desk === 'opt' ? ' active' : '' }}" href="/admin/video/make">生成选项</a>
                <a class="chip{{ $desk === 'index' ? ' active' : '' }}" href="/admin/video/make?desk=index">首页</a>
                <a class="chip{{ $desk === 'map' ? ' active' : '' }}" href="/admin/video/make?desk=map">地图</a>
                <a class="chip{{ $desk === 'cache' ? ' active' : '' }}" href="/admin/video/make?desk=cache">缓存</a>
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
                <p class="muted field-hint">文件写在 <code>public/html/{路径}/index.html</code>。勾上并保存后才能生成。</p>
                <form method="post" action="/admin/video/make/disk" class="disk-html-enable">
                    @csrf
                    <input type="hidden" name="desk" value="{{ $desk }}">
                    <label class="inline">
                        <input type="hidden" name="disk_html_enabled" value="0">
                        <input type="checkbox" name="disk_html_enabled" value="1" @checked($diskEnabled)>
                        启用，允许在后台生成静态文件
                    </label>
                    <button class="btn btn-muted" type="submit">保存这项</button>
                </form>
                <div class="disk-html-meta">
                    <p id="diskHtmlCount" class="muted">
                        @if($diskCount > 0)
                            目前有 {{ $diskCount }} 个文件。
                        @else
                            还没有生成过文件。
                        @endif
                    </p>
                    <div class="html-cache-actions">
                        <button class="btn-quiet" type="button" id="diskCancelBtn" hidden>停止</button>
                        <button class="btn-quiet" type="button" id="diskClearBtn">删掉静态文件</button>
                    </div>
                </div>
            </div>

            <div class="disk-html-progress{{ in_array($diskJob['status'] ?? '', ['done', 'cancelled'], true) ? ' is-done' : '' }}"
                 id="diskHtmlProgress"
                 data-status="{{ $diskJob['status'] ?? 'idle' }}"
                 data-kind="{{ $diskJob['kind'] ?? 'build' }}"
                 data-percent="{{ $diskJob['percent'] ?? 0 }}"
                 @if(($diskJob['status'] ?? 'idle') === 'idle') hidden @endif>
                <div class="disk-html-bar"><i id="diskHtmlBar" style="width: {{ (int) ($diskJob['percent'] ?? 0) }}%"></i></div>
                <p class="muted" id="diskHtmlMsg" style="margin:8px 0 0">{{ $diskJob['message'] ?? '' }}</p>
            </div>

            <div class="make-desk" data-desk-panel="opt" @if($desk !== 'opt') hidden @endif>
                <div class="make-opt-row">
                    <div class="make-opt-label">视频分类</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="vod-type-list">
                            @forelse($vodTypes as $type)
                                <label class="make-opt-d{{ min(3, (int) ($type['depth'] ?? 0)) }}">
                                    <input type="checkbox" name="vod_types" value="{{ (int) $type['id'] }}">
                                    {{ $type['name'] }}
                                </label>
                            @empty
                                <p class="muted">还没有分类</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="type" data-from="vod_types" data-need="ids">选择分类</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="type">全部分类</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="type" data-when="today">当天分类</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail" data-type-from="vod_types" data-need="types">选择内容</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail">全部内容</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail" data-when="today">当天内容</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="detail" data-when="missing" data-type-from="vod_types">未生成的</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="vod_day">一键当天</button>
                        </div>
                    </div>
                </div>

                @if($hasArts)
                <div class="make-opt-row">
                    <div class="make-opt-label">文章分类</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="art-type-list">
                            @forelse($artTypes as $type)
                                <label class="make-opt-d{{ min(3, (int) ($type['depth'] ?? 0)) }}">
                                    <input type="checkbox" name="art_types" value="{{ (int) $type['id'] }}">
                                    {{ $type['name'] }}
                                </label>
                            @empty
                                <p class="muted">还没有文章分类</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_type" data-from="art_types" data-need="ids">选择分类</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_type">全部分类</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_type" data-when="today">当天分类</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art" data-type-from="art_types" data-need="types">选择内容</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art">全部内容</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art" data-when="today">当天内容</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art" data-when="missing" data-type-from="art_types">未生成的</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="art_day">一键当天</button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="make-opt-row">
                    <div class="make-opt-label">专题</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="topic-list">
                            @forelse($topics as $row)
                                <label>
                                    <input type="checkbox" name="topics" value="{{ (int) $row['id'] }}">
                                    {{ $row['name'] }}
                                </label>
                            @empty
                                <p class="muted">还没有专题</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="topic" data-from="topics" data-need="ids">选择专题</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="topic">全部专题</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="topic" data-extra="index">专题首页</button>
                        </div>
                    </div>
                </div>

                <div class="make-opt-row">
                    <div class="make-opt-label">演员</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="actor-list">
                            @forelse($actors as $row)
                                <label>
                                    <input type="checkbox" name="actors" value="{{ (int) $row['id'] }}">
                                    {{ $row['name'] }}
                                </label>
                            @empty
                                <p class="muted">还没有演员</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-from="actors" data-need="ids">选择演员</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor">全部演员</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-when="today">当天演员</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-when="missing">未生成的演员</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="actor" data-extra="index">演员首页</button>
                        </div>
                    </div>
                </div>

                <div class="make-opt-row">
                    <div class="make-opt-label">角色</div>
                    <div class="make-opt-body">
                        <div class="make-opt-list" id="role-list">
                            @forelse($roles as $row)
                                <label>
                                    <input type="checkbox" name="roles" value="{{ (int) $row['id'] }}">
                                    {{ $row['name'] }}
                                </label>
                            @empty
                                <p class="muted">还没有角色</p>
                            @endforelse
                        </div>
                        <div class="make-opt-btns">
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-from="roles" data-need="ids">选择角色</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role">全部角色</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-when="today">当天角色</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-when="missing">未生成的角色</button>
                            <button type="button" class="btn btn-primary" data-make="1" data-scope="role" data-extra="index">角色首页</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="make-desk" data-desk-panel="index" @if($desk !== 'index') hidden @endif>
                <div class="html-cache-card">
                    <h3>首页</h3>
                    <p class="muted field-hint">含列表首页，不是 WAP。</p>
                    <div class="html-cache-actions">
                        <button type="button" class="btn btn-primary" data-make="1" data-scope="index">生成首页</button>
                    </div>
                </div>
            </div>

            <div class="make-desk" data-desk-panel="map" @if($desk !== 'map') hidden @endif>
                <div class="html-cache-card">
                    <h3>地图和 RSS</h3>
                    <p class="muted field-hint">写出 <code>public/sitemap.xml</code> 和 <code>public/rss.xml</code>，给搜索引擎用。不是 map.html。</p>
                    <div class="html-cache-actions">
                        <button type="button" class="btn btn-primary" data-map="sitemap">生成地图</button>
                        <button type="button" class="btn btn-muted" data-map="rss">生成 RSS</button>
                    </div>
                </div>
            </div>

            <div class="make-desk" data-desk-panel="cache" @if($desk !== 'cache') hidden @endif>
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
                        <input type="hidden" name="desk" value="cache">
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var root = document.getElementById('make-index');
    var stopBtn = document.getElementById('diskCancelBtn');
    var clearBtn = document.getElementById('diskClearBtn');
    var box = document.getElementById('diskHtmlProgress');
    var bar = document.getElementById('diskHtmlBar');
    var msg = document.getElementById('diskHtmlMsg');
    var count = document.getElementById('diskHtmlCount');
    if (!root || !box || !bar || !msg) return;
    var looping = false;
    var stepChunk = 6;

    function locked() {
        return root.getAttribute('data-off') === '1';
    }

    function makeButtons() {
        return document.querySelectorAll('[data-make]');
    }

    function idleButtons() {
        makeButtons().forEach(function (el) { el.disabled = locked(); });
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
        makeButtons().forEach(function (el) { el.disabled = busy || locked(); });
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

    function selected(name) {
        return Array.prototype.map.call(document.querySelectorAll('input[name="' + name + '"]:checked'), function (el) {
            return el.value;
        });
    }

    function startMake(payload) {
        if (locked()) {
            U.toast('请先打开磁盘静态页并保存', 'err');
            return;
        }
        makeButtons().forEach(function (el) { el.disabled = true; });
        if (clearBtn) clearBtn.disabled = true;
        box.hidden = false;
        msg.textContent = '正在列出要写的页面…';
        bar.style.width = '0%';
        box.classList.remove('is-done', 'is-stop');
        U.post('/admin/video/make/start', payload).then(function (res) {
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
    }

    document.querySelectorAll('[data-make]').forEach(function (el) {
        el.addEventListener('click', function () {
            var from = el.getAttribute('data-from') || '';
            var typeFrom = el.getAttribute('data-type-from') || '';
            var ids = from ? selected(from) : [];
            var typeIds = typeFrom ? selected(typeFrom) : [];
            var need = el.getAttribute('data-need') || '';
            if (need === 'ids' && !ids.length) {
                U.toast('请先勾选', 'err');
                return;
            }
            if (need === 'types' && !typeIds.length) {
                U.toast('请先勾选', 'err');
                return;
            }
            startMake({
                scope: el.getAttribute('data-scope') || 'all',
                ids: ids.join(','),
                type_ids: typeIds.join(','),
                when: el.getAttribute('data-when') || 'all',
                extra: el.getAttribute('data-extra') || ''
            });
        });
    });

    clearBtn && clearBtn.addEventListener('click', function () {
        if (!confirm('删掉这些文件后，前台改回动态生成。文件多时会分批删。确定？')) return;
        makeButtons().forEach(function (el) { el.disabled = true; });
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
    } else {
        idleButtons();
    }

    document.querySelectorAll('[data-map]').forEach(function (el) {
        el.addEventListener('click', function () {
            U.post('/admin/video/make/map', {scope: el.getAttribute('data-map')}).then(function (res) {
                U.toast((res && res.msg) || '没得到结果', res && res.code === 0 ? 'ok' : 'err');
            });
        });
    });
})();
</script>
@endpush
