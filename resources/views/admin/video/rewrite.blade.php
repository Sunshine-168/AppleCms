@extends('admin.layouts.inner')
@section('title', admin_t('page.rewrite'))

@php
    $modeLabel = (string) ($mode_label ?? '本站路由');
    $modeSample = (string) ($mode_sample ?? '/vod/123');
    $mac = (bool) ($mac ?? false);
    $suffix = (string) ($suffix ?? '.html');
    $examples = $examples ?? [];
@endphp

@section('plain')
<div class="card card-panel rewrite-index">
    <div class="card-header">
        <span>伪静态</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/settings?tab=more">站点设置</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">这页是给服务器抄的规则，不会改库。链接怎么写在「<a href="/admin/video/settings?tab=more">站点设置 → 更多</a>」。本机 <code>php artisan serve</code> 不用配。</p>

        <div class="rewrite-now">
            <div class="rewrite-now-head">
                <h3>当前写法</h3>
                <span class="badge badge-ok">{{ $modeLabel }}</span>
            </div>
            <p class="muted">详情页现在是 <code>{{ $modeSample }}</code>。@if($mac) 后缀 <code>{{ $suffix }}</code> 会加在苹果链接后面。@else 本站路由一般不带 <code>.html</code>。@endif 改过之后，外面收藏的旧地址可能打不开。</p>
        </div>

        <h3>前台长什么样</h3>
        <p class="muted field-hint">数字 1 只是举例。网站根目录要指到项目的 <code>public</code>。</p>
        <div class="rewrite-examples">
            @foreach($examples as $row)
                <div class="rewrite-ex">
                    <span>{{ $row['label'] }}</span>
                    <code>{{ $row['path'] }}</code>
                    <button type="button" class="btn-link js-copy" data-copy="{{ $row['path'] }}">复制</button>
                </div>
            @endforeach
        </div>

        <div class="rewrite-snippet">
            <div class="rewrite-snippet-head">
                <h3>Nginx</h3>
                <button type="button" class="btn btn-muted btn-sm js-copy" data-copy-el="#rewrite-nginx">复制</button>
            </div>
            <p class="muted field-hint">PHP 那一行按机器改。苹果链接也是进 <code>index.php</code>，不必再单独写一段 location。</p>
            <pre class="code-block" id="rewrite-nginx">{{ $nginx ?? '' }}</pre>
        </div>

        <div class="rewrite-snippet">
            <div class="rewrite-snippet-head">
                <h3>Apache</h3>
                <button type="button" class="btn btn-muted btn-sm js-copy" data-copy-el="#rewrite-apache">复制</button>
            </div>
            <p class="muted field-hint">仓库里 <code>public/.htaccess</code> 已经有一份。虚拟主机要允许 <code>AllowOverride All</code>。</p>
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
