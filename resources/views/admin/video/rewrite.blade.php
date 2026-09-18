@extends('admin.layouts.inner')
@section('title', admin_t('page.rewrite'))

@php
    $modeLabel = (string) ($mode_label ?? '本站路由');
    $modeSample = (string) ($mode_sample ?? '/vod/123');
    $mac = (bool) ($mac ?? false);
    $suffix = (string) ($suffix ?? '.html');
    $routeGroups = $route_groups ?? [];
@endphp

@section('plain')
<div class="card card-panel rewrite-index">
    <div class="card-header">
        <span>伪静态</span>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">本机 <code>php artisan serve</code> 只适合试用，不用配下面规则。正式站点要把网站根指到项目的 <code>public</code>，不要指到项目根。影片链接怎么写在「<a href="/admin/video/settings?tab=more">站点设置 → 更多</a>」：本站路由一般是 <code>/vod/123</code>，苹果风格才是 <code>/index.php/vod/detail/id/123.html</code>。改过之后，外面收藏的旧地址可能打不开。</p>

        <div class="rewrite-now">
            <div class="rewrite-now-head">
                <h3>当前写法</h3>
                <span class="badge badge-ok">{{ $modeLabel }}</span>
            </div>
            <p class="muted">详情页现在是 <code>{{ $modeSample }}</code>。@if($mac) 后缀 <code>{{ $suffix }}</code> 会加在苹果链接后面。@else 本站路由一般不带 <code>.html</code>。@endif</p>
        </div>

        <h3>本系统怎么走</h3>
        <p class="muted">地址写在 Laravel 路由里，不是苹果那种伪静态规则文件。没有真实文件的请求交给 <code>public/index.php</code> 即可。两种链接写法用同一段 Nginx / Apache，不必按每种页面再写 location。</p>

        <div class="help-faq">
            <details>
                <summary>上线打开是目录或 404</summary>
                <p>网站根必须指到项目的 <code>public</code>，不要指到项目根。本页下面的 Nginx / Apache 就是干这个的。</p>
            </details>
        </div>

        <h3>前台地址</h3>
        <p class="muted field-hint">数字 1 只是举例。下面是当前写法实际会生成的地址。</p>
        @if($mac)
            <p class="muted field-hint">最近更新、演员库、专题列表、播放器内嵌仍走本站路径，没有苹果别名。</p>
        @endif
        <div class="rewrite-route-groups">
            @foreach($routeGroups as $group)
                <div class="rewrite-route-group">
                    <h4>{{ $group['title'] }}</h4>
                    <div class="rewrite-examples">
                        @foreach($group['rows'] as $row)
                            <div class="rewrite-ex{{ !empty($row['note']) ? ' has-note' : '' }}">
                                <span>{{ $row['label'] }}</span>
                                <code>{{ $row['path'] }}</code>
                                @if(!empty($row['note']))
                                    <span class="rewrite-ex-note muted">{{ $row['note'] }}</span>
                                @endif
                                <button type="button" class="btn-link js-copy" data-copy="{{ $row['path'] }}">复制</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rewrite-snippet">
            <div class="rewrite-snippet-head">
                <h3>Nginx</h3>
                <button type="button" class="btn btn-muted btn-sm js-copy" data-copy-el="#rewrite-nginx">复制</button>
            </div>
            <p class="muted field-hint">网站根必须是 <code>public</code>。PHP 那一行按机器改。苹果链接也进 <code>index.php</code>，不必再单独写一段 location。</p>
            <pre class="code-block" id="rewrite-nginx">{{ $nginx ?? '' }}</pre>
        </div>

        <div class="rewrite-snippet">
            <div class="rewrite-snippet-head">
                <h3>Apache</h3>
                <button type="button" class="btn btn-muted btn-sm js-copy" data-copy-el="#rewrite-apache">复制</button>
            </div>
            <p class="muted field-hint">仓库 <code>public/.htaccess</code> 已有一份。虚拟主机要允许 <code>AllowOverride All</code>。</p>
            <pre class="code-block" id="rewrite-apache">{{ $apache ?? '' }}</pre>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function copyText(text) {
        text = String(text || '');
        if (!text) { U.toast('没有可复制的内容', 'err'); return; }
        var ok = function () { U.toast('已复制', 'ok'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(ok).catch(function () { fallback(text); });
            return;
        }
        fallback(text);
        function fallback(value) {
            var ta = document.createElement('textarea');
            ta.value = value;
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); ok(); }
            catch (e) { U.toast('复制失败，请手动选', 'err'); }
            document.body.removeChild(ta);
        }
    }
    document.querySelectorAll('.js-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sel = btn.getAttribute('data-copy-el');
            if (sel) {
                var el = document.querySelector(sel);
                copyText(el ? el.textContent : '');
                return;
            }
            copyText(btn.getAttribute('data-copy') || '');
        });
    });
})();
</script>
@endpush
