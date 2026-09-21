@php
    $modeLabel = (string) ($mode_label ?? admin_t('ui.rewrite_local_mode'));
    $modeSample = (string) ($mode_sample ?? '/vod/123');
    $mac = (bool) ($mac ?? false);
    $suffix = (string) ($suffix ?? '.html');
    $routeGroups = $route_groups ?? [];
    $jsLang = [
        'nothing_to_copy' => admin_t('ui.nothing_to_copy'),
        'copied' => admin_t('ui.copied'),
        'copy_fail_pick' => admin_t('ui.copy_fail_pick'),
    ];
@endphp
<div class="rewrite-index">
    <p class="muted">{!! admin_t('ui.rewrite_lead_before') !!}「<a href="/admin/video/settings?tab=more">{{ admin_t('ui.settings_to_more') }}</a>」{!! admin_t('ui.rewrite_lead_after') !!}</p>

    <div class="rewrite-now">
        <div class="rewrite-now-head">
            <h3>{{ admin_t('ui.rewrite_now') }}</h3>
            <span class="badge badge-ok">{{ $modeLabel }}</span>
        </div>
        <p class="muted">{{ admin_t('ui.rewrite_detail_now') }} <code>{{ $modeSample }}</code>。@if($mac) {{ admin_t('ui.rewrite_suffix') }} <code>{{ $suffix }}</code> {{ admin_t('ui.rewrite_suffix_mac') }}@else {!! admin_t('ui.rewrite_no_html') !!}@endif</p>
    </div>

    <h3>{{ admin_t('ui.rewrite_how') }}</h3>
    <p class="muted">{!! admin_t('ui.rewrite_how_body') !!}</p>

    <div class="help-faq">
        <details>
            <summary>{{ admin_t('ui.rewrite_faq_title') }}</summary>
            <p>{!! admin_t('ui.rewrite_faq_body') !!}</p>
        </details>
    </div>

    <h3>{{ admin_t('ui.front_urls') }}</h3>
    <p class="muted field-hint">{{ admin_t('ui.front_urls_hint') }}</p>
    @if($mac)
        <p class="muted field-hint">{{ admin_t('ui.rewrite_no_mac_alias') }}</p>
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
                            <button type="button" class="btn-link js-copy" data-copy="{{ $row['path'] }}">{{ admin_t('ui.copy') }}</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="rewrite-snippet">
        <div class="rewrite-snippet-head">
            <h3>Nginx</h3>
            <button type="button" class="btn btn-muted btn-sm js-copy" data-copy-el="#rewrite-nginx">{{ admin_t('ui.copy') }}</button>
        </div>
        <p class="muted field-hint">{!! admin_t('ui.rewrite_nginx_hint') !!}</p>
        <pre class="code-block" id="rewrite-nginx">{{ $nginx ?? '' }}</pre>
    </div>

    <div class="rewrite-snippet">
        <div class="rewrite-snippet-head">
            <h3>Apache</h3>
            <button type="button" class="btn btn-muted btn-sm js-copy" data-copy-el="#rewrite-apache">{{ admin_t('ui.copy') }}</button>
        </div>
        <p class="muted field-hint">{!! admin_t('ui.rewrite_apache_hint') !!}</p>
        <pre class="code-block" id="rewrite-apache">{{ $apache ?? '' }}</pre>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    function toast(msg, kind) {
        if (U && U.toast) { U.toast(msg, kind); return; }
        console.log(msg);
    }
    function copyText(text) {
        text = String(text || '');
        if (!text) { toast(L.nothing_to_copy, 'err'); return; }
        var ok = function () { toast(L.copied, 'ok'); };
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
            catch (e) { toast(L.copy_fail_pick, 'err'); }
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
