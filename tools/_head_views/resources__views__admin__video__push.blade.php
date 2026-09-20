fatal: path 'resources\views\admin\video\push.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.push'))

@php
    $engines = $engines ?? [];
    $videoCount = (int) ($video_count ?? 0);
    $recentCount = (int) ($recent_count ?? 0);
    $siteUrl = (string) ($site_url ?? '');
    $host = (string) ($host ?? '');
    $local = (bool) ($local ?? false);
    $samples = $samples ?? [];
    $sitemapUrl = (string) ($sitemap_url ?? url('/sitemap.xml'));
    $sitemapIncUrl = (string) ($sitemap_inc_url ?? url('/sitemap.xml?inc=1'));
    $robotsUrl = (string) ($robots_url ?? url('/robots.txt'));
    $rssUrl = (string) ($rss_url ?? url('/rss.xml'));
    $readyN = 0;
    $tokenMap = ['baidu' => '', 'shenma' => '', 'bing' => ''];
    foreach ($engines as $row) {
        if (! empty($row['ready'])) {
            $readyN++;
        }
        if (! empty($row['id'])) {
            $tokenMap[$row['id']] = (string) ($row['token'] ?? '');
        }
    }
@endphp

@section('plain')
<div class="card card-panel push-index">
    <div class="card-header">
        <span>搜索推送</span>
        <div>
            <a class="btn btn-muted btn-sm" href="{{ $sitemapUrl }}" target="_blank" rel="noopener">sitemap</a>
            <a class="btn btn-muted btn-sm" href="{{ $robotsUrl }}" target="_blank" rel="noopener">robots</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">把<strong>已发布影片</strong>的地址交给百度 / 神马 / 必应。sitemap 给蜘蛛自己抓，不等于推送成功。没有站长账号就留空。</p>

        <div class="ai-stock">
            @if($readyN > 0)
                <span class="badge badge-ok">{{ $readyN }} 家已填 Token</span>
            @else
                <span class="badge">还没填 Token</span>
            @endif
            <span class="muted">已发布 {{ $videoCount }} 部 · 近 48 小时改过 {{ $recentCount }} 部</span>
        </div>

        @if($local)
            <p class="push-local">当前站点地址是 <code>{{ $siteUrl !== '' ? $siteUrl : '未设置' }}</code>。搜索引擎访问不到本机，推上去也收录不了。先把 <code>APP_URL</code> 改成公网域名，并跟站长平台里绑定的域名一致。</p>
        @endif

        <form class="settings-page push-form" id="site-form">
            <h3>给蜘蛛的地址</h3>
            <p class="muted field-hint">提交给站长平台「站点地图」，或让 <code>robots.txt</code> 指向它。增量只含最近 48 小时改过的片子。</p>
            <div class="rewrite-examples">
                <div class="rewrite-ex">
                    <span>全站</span>
                    <code>{{ $sitemapUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $sitemapUrl }}">复制</button>
                </div>
                <div class="rewrite-ex">
                    <span>增量</span>
                    <code>{{ $sitemapIncUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $sitemapIncUrl }}">复制</button>
                </div>
                <div class="rewrite-ex">
                    <span>robots</span>
                    <code>{{ $robotsUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $robotsUrl }}">复制</button>
                </div>
                <div class="rewrite-ex">
                    <span>RSS</span>
                    <code>{{ $rssUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $rssUrl }}">复制</button>
                </div>
            </div>

            <h3>Token</h3>
            <p class="muted field-hint">在对应站长后台申请。空着保存表示这家不推。Token 必须和上面的域名是同一套。</p>
            @foreach($engines as $row)
                <label for="{{ $row['token_key'] }}">{{ $row['label'] }}</label>
                <input id="{{ $row['token_key'] }}" type="text" name="{{ $row['token_key'] }}" value="{{ $row['token'] }}" autocomplete="off" spellcheck="false" placeholder="{{ $row['hint'] }}">
            @endforeach
            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">保存 Token</button>
            </div>
        </form>

        <h3>主动推一批</h3>
        <p class="muted field-hint">按最新发布的往外推。一次最多 100 条。失败时会把接口原话报出来。</p>
        <div class="push-limit-row">
            <label for="push-limit">每次条数</label>
            <select id="push-limit">
                <option value="20">20</option>
                <option value="50" selected>50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="push-engines">
            @foreach($engines as $row)
                <div class="push-engine">
                    <div class="push-engine-head">
                        <strong>{{ $row['label'] }}</strong>
                        @if($row['ready'])
                            <span class="badge badge-ok">已填</span>
                        @else
                            <span class="badge">未填</span>
                        @endif
                    </div>
                    <p class="muted">{{ $row['hint'] }}</p>
                    <button type="button" class="btn{{ $row['ready'] ? '' : ' btn-muted' }}" data-engine="{{ $row['id'] }}" @disabled(! $row['ready'] || $videoCount < 1)>推最近一批</button>
                </div>
            @endforeach
        </div>
        <p class="muted" id="push-flash" hidden></p>

        @if($samples !== [])
            <h3>这次会推哪些</h3>
            <p class="muted field-hint">预览最新 5 条，实际条数看上面的选择。</p>
            <ul class="push-samples">
                @foreach($samples as $u)
                    <li><code>{{ $u }}</code></li>
                @endforeach
            </ul>
        @else
            <p class="muted field-hint">片库还没有已发布的影片，推不出去。</p>
        @endif

        <div class="interface-howto">
            <p>定时可跑：</p>
            <ul>
                <li><code>php artisan video:baidu-push --limit=50</code></li>
            </ul>
            <p class="muted">没有接通 Google Indexing。神马 / 必应也没有单独的命令，用这页的按钮。</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var original = @json($tokenMap);
    function copyText(text) {
        text = String(text || '');
        if (!text) { U && U.toast('没有可复制的内容', 'err'); return; }
        var done = function () { U && U.toast('已复制', 'ok'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () { fallback(text, done); });
            return;
        }
        fallback(text, done);
    }
    function fallback(text, done) {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) { U && U.toast('复制失败', 'err'); }
        ta.remove();
    }
    document.querySelectorAll('.js-copy').forEach(function (btn) {
        btn.addEventListener('click', function () { copyText(btn.getAttribute('data-copy') || ''); });
    });
    function val(id) {
        var el = document.getElementById(id);
        return el ? String(el.value || '').trim() : '';
    }
    var save = document.getElementById('site-save');
    var form = document.getElementById('site-form');
    function payload() {
        return {
            baidu_push_token: val('baidu_push_token'),
            shenma_push_token: val('shenma_push_token'),
            bing_push_token: val('bing_push_token')
        };
    }
    function doSave() {
        var data = payload();
        if (data.baidu_push_token === original.baidu && data.shenma_push_token === original.shenma && data.bing_push_token === original.bing) {
            U && U.toast('没有改动，不用保存', 'ok');
            return;
        }
        U.post('/admin/video/settings', data).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) {
                original.baidu = data.baidu_push_token;
                original.shenma = data.shenma_push_token;
                original.bing = data.bing_push_token;
                location.reload();
            }
        });
    }
    if (save) save.addEventListener('click', doSave);
    if (form) form.addEventListener('submit', function (e) { e.preventDefault(); doSave(); });

    var flash = document.getElementById('push-flash');
    function showFlash(text, ok) {
        if (!flash) return;
        flash.hidden = false;
        flash.textContent = text;
        flash.classList.toggle('is-ok', !!ok);
        flash.classList.toggle('is-err', !ok);
    }
    document.querySelectorAll('[data-engine]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var engine = btn.getAttribute('data-engine') || 'baidu';
            var limit = parseInt((document.getElementById('push-limit') || {}).value || '50', 10) || 50;
            var key = engine === 'shenma' ? 'shenma' : (engine === 'bing' ? 'bing' : 'baidu');
            var saved = original[key] || '';
            var typed = val(engine === 'shenma' ? 'shenma_push_token' : (engine === 'bing' ? 'bing_push_token' : 'baidu_push_token'));
            if (!saved) { U && U.toast('先填 Token 并保存', 'err'); return; }
            if (typed !== saved) { U && U.toast('Token 改动还没保存，推的是网上正在用的', 'ok'); }
            btn.disabled = true;
            U.loading(true);
            U.post('/admin/video/push/run', { engine: engine, limit: limit }).then(function (res) {
                U.loading(false);
                btn.disabled = false;
                var ok = res && res.code === 0;
                U.toast((res && res.msg) || '完成', ok ? 'ok' : 'err');
                showFlash((res && res.msg) || '', ok);
            }).catch(function () {
                U.loading(false);
                btn.disabled = false;
                U.toast('推送失败', 'err');
            });
        });
    });
})();
</script>
@endpush