@php
    $qq = false;
    $wx = false;
    try {
        $svc = app(\Plugins\Connect\Services\ConnectService::class);
        $qq = $svc->qqReady();
        $wx = $svc->wechatReady();
    } catch (\Throwable) {
    }
@endphp
@if($qq || $wx)
    <p>
        @if($qq)<a href="{{ url('/connect/qq') }}">QQ 登录</a>@endif
        @if($qq && $wx) · @endif
        @if($wx)<a href="{{ url('/connect/wechat') }}">微信登录</a>@endif
    </p>
@endif
