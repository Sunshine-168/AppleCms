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
    $jsLang = [
        'nothing_to_copy' => admin_t('ui.nothing_to_copy'),
        'copied' => admin_t('ui.copied'),
        'copy_failed' => admin_t('ui.copy_failed'),
        'no_change_save' => admin_t('ui.no_change_save'),
        'finished' => admin_t('ui.finished'),
        'need_token_save' => admin_t('ui.need_token_save'),
        'token_unsaved' => admin_t('ui.token_unsaved'),
        'push_fail' => admin_t('ui.push_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel push-index">
    <div class="card-header">
        <span>{{ admin_t('page.push') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="{{ $sitemapUrl }}" target="_blank" rel="noopener">sitemap</a>
            <a class="btn btn-muted btn-sm" href="{{ $robotsUrl }}" target="_blank" rel="noopener">robots</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{!! admin_t('ui.push_lead') !!}</p>

        <div class="ai-stock">
            @if($readyN > 0)
                <span class="badge badge-ok">{{ admin_t('ui.token_ready_n', ['n' => $readyN]) }}</span>
            @else
                <span class="badge">{{ admin_t('ui.token_none') }}</span>
            @endif
            <span class="muted">{{ admin_t('ui.push_published_n', ['videos' => $videoCount, 'recent' => $recentCount]) }}</span>
        </div>

        @if($local)
            <p class="push-local">{!! admin_t('ui.push_local_before') !!}<code>{{ $siteUrl !== '' ? $siteUrl : admin_t('ui.not_set') }}</code>。{!! admin_t('ui.push_local_after') !!}</p>
        @endif

        <form class="settings-page push-form" id="site-form">
            <h3>{{ admin_t('ui.spider_urls') }}</h3>
            <p class="muted field-hint">{!! admin_t('ui.spider_urls_hint') !!}</p>
            <div class="rewrite-examples">
                <div class="rewrite-ex">
                    <span>{{ admin_t('ui.sitemap_all') }}</span>
                    <code>{{ $sitemapUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $sitemapUrl }}">{{ admin_t('ui.copy') }}</button>
                </div>
                <div class="rewrite-ex">
                    <span>{{ admin_t('ui.sitemap_inc') }}</span>
                    <code>{{ $sitemapIncUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $sitemapIncUrl }}">{{ admin_t('ui.copy') }}</button>
                </div>
                <div class="rewrite-ex">
                    <span>robots</span>
                    <code>{{ $robotsUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $robotsUrl }}">{{ admin_t('ui.copy') }}</button>
                </div>
                <div class="rewrite-ex">
                    <span>RSS</span>
                    <code>{{ $rssUrl }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $rssUrl }}">{{ admin_t('ui.copy') }}</button>
                </div>
            </div>

            <h3>Token</h3>
            <p class="muted field-hint">{{ admin_t('ui.token_hint') }}</p>
            @foreach($engines as $row)
                <label for="{{ $row['token_key'] }}">{{ $row['label'] }}</label>
                <input id="{{ $row['token_key'] }}" type="text" name="{{ $row['token_key'] }}" value="{{ $row['token'] }}" autocomplete="off" spellcheck="false" placeholder="{{ $row['hint'] }}">
            @endforeach
            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save_token') }}</button>
            </div>
        </form>

        <h3>{{ admin_t('ui.push_batch') }}</h3>
        <p class="muted field-hint">{{ admin_t('ui.push_batch_hint') }}</p>
        <div class="push-limit-row">
            <label for="push-limit">{{ admin_t('ui.push_limit') }}</label>
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
                            <span class="badge badge-ok">{{ admin_t('ui.token_filled') }}</span>
                        @else
                            <span class="badge">{{ admin_t('ui.token_empty') }}</span>
                        @endif
                    </div>
                    <p class="muted">{{ $row['hint'] }}</p>
                    <button type="button" class="btn{{ $row['ready'] ? '' : ' btn-muted' }}" data-engine="{{ $row['id'] }}" @disabled(! $row['ready'] || $videoCount < 1)>{{ admin_t('ui.push_recent') }}</button>
                </div>
            @endforeach
        </div>
        <p class="muted" id="push-flash" hidden></p>

        @if($samples !== [])
            <h3>{{ admin_t('ui.push_samples') }}</h3>
            <p class="muted field-hint">{{ admin_t('ui.push_samples_hint') }}</p>
            <ul class="push-samples">
                @foreach($samples as $u)
                    <li><code>{{ $u }}</code></li>
                @endforeach
            </ul>
        @else
            <p class="muted field-hint">{{ admin_t('ui.push_no_videos') }}</p>
        @endif

        <div class="interface-howto">
            <p>{{ admin_t('ui.push_cron') }}</p>
            <ul>
                <li><code>php artisan video:baidu-push --limit=50</code></li>
            </ul>
            <p class="muted">{{ admin_t('ui.push_no_google') }}</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var original = @json($tokenMap, JSON_UNESCAPED_UNICODE);
    function copyText(text) {
        text = String(text || '');
        if (!text) { U && U.toast(L.nothing_to_copy, 'err'); return; }
        var done = function () { U && U.toast(L.copied, 'ok'); };
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
        try { document.execCommand('copy'); done(); } catch (e) { U && U.toast(L.copy_failed, 'err'); }
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
            U && U.toast(L.no_change_save, 'ok');
            return;
        }
        U.post('/admin/video/settings', data).then(function (res) {
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
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
            if (!saved) { U && U.toast(L.need_token_save, 'err'); return; }
            if (typed !== saved) { U && U.toast(L.token_unsaved, 'ok'); }
            btn.disabled = true;
            U.loading(true);
            U.post('/admin/video/push/run', { engine: engine, limit: limit }).then(function (res) {
                U.loading(false);
                btn.disabled = false;
                var ok = res && res.code === 0;
                U.toast((res && res.msg) || L.finished, ok ? 'ok' : 'err');
                showFlash((res && res.msg) || '', ok);
            }).catch(function () {
                U.loading(false);
                btn.disabled = false;
                U.toast(L.push_fail, 'err');
            });
        });
    });
})();
</script>
@endpush
