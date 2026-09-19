@extends('themes.default.layout')
@section('content')
@php
    $type = (string) ($type ?? 'goods');
    $typeLabel = \Plugins\Mall\Services\MallService::typeLabel($type);
    $vipDays = (int) ($vipDays ?? 0);
    $autoCredit = (bool) ($autoCredit ?? false);
    $canBuy = (int) $goods->stock > 0;
    $cover = trim((string) ($goods->cover ?? ''));
@endphp
    @vodBreadcrumb(['last' => $goods->name])

    <div class="mall-detail">
        <div class="mall-detail-cover{{ $cover === '' ? ' is-empty' : '' }}">
            @if($cover !== '')
                <img src="{{ $cover }}" alt="{{ $goods->name }}">
            @else
                暂无封面
            @endif
        </div>
        <div>
            <h1>{{ $goods->name }}</h1>
            <p>
                <span class="mall-tag">{{ $typeLabel }}</span>
                <strong style="color:var(--accent)">{{ (int) $goods->points }}</strong> 积分
                · 库存 {{ (int) $goods->stock }}
                @if((int) ($goods->sales ?? 0) > 0)
                    · 已兑 {{ (int) $goods->sales }}
                @endif
            </p>
            @if($type === 'vip')
                <p class="muted">兑换后立即开通会员组，时长：{{ \Plugins\Mall\Services\MallService::vipDaysLabel($vipDays) }}。同组可叠加。</p>
            @elseif($type === 'card')
                <p class="muted">{{ $autoCredit ? '兑换后积分立即到账。' : '兑换后获得卡密，可在会员中心兑换，或转赠他人。' }}
                    @if(($pool ?? 0) > 0) 卡池剩余 {{ $pool }}。@endif
                </p>
            @else
                <p class="muted">兑换后由管理员发货。请填写联系方式。</p>
            @endif
            @auth('member')
                <p class="muted">当前积分：{{ (int) ($points ?? 0) }}
                    @if((int) ($points ?? 0) < (int) $goods->points)
                        · <span class="mall-warn">不足</span>
                    @endif
                    · <a href="{{ url('/mall/orders') }}">我的兑换</a>
                </p>
            @endauth
            @if($goods->hint)<div class="desc detail-desc">{{ $goods->hint }}</div>@endif

            @auth('member')
                @if($canBuy)
                    <form method="post" action="{{ url('/mall/'.$goods->id.'/buy') }}" class="mall-buy" id="mall-buy-form">
                        @csrf
                        @if($type === 'goods')
                            <label class="auth-field">
                                <span>联系方式</span>
                                <input type="text" name="contact" value="{{ old('contact') }}" required maxlength="80" placeholder="手机 / QQ / 微信">
                            </label>
                            <label class="auth-field">
                                <span>收货备注 <em class="muted">可选</em></span>
                                <input type="text" name="address" value="{{ old('address') }}" maxlength="250" placeholder="地址或取件说明">
                            </label>
                        @endif
                        <button type="submit" class="btn-play" id="mall-buy-btn">确认兑换（{{ (int) $goods->points }} 积分）</button>
                    </form>
                    <p class="muted" id="mall-buy-msg" hidden></p>
                @else
                    <p class="muted">已兑完</p>
                @endif
            @else
                <p><a class="btn-play" href="{{ url('/member/login') }}">登录后兑换</a></p>
            @endauth
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('mall-buy-form');
    if (!form) return;
    var btn = document.getElementById('mall-buy-btn');
    var msg = document.getElementById('mall-buy-msg');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!window.confirm('确认用积分兑换？')) return;
        btn.disabled = true;
        var fd = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (res) {
            var ok = res && Number(res.code) === 0;
            if (msg) {
                msg.hidden = false;
                msg.textContent = (res && res.msg) || (ok ? '兑换成功' : '兑换失败');
                msg.className = ok ? 'flash is-ok' : 'flash is-err';
            }
            if (ok) {
                var code = res.data && res.data.delivery && res.data.delivery.code;
                if (code) {
                    msg.textContent += ' · 卡密 ' + code;
                }
                setTimeout(function () {
                    location.href = (res.data && res.data.orders_url) || '/mall/orders';
                }, 900);
            } else {
                btn.disabled = false;
            }
        }).catch(function () {
            btn.disabled = false;
            if (msg) { msg.hidden = false; msg.className = 'flash is-err'; msg.textContent = '网络错误'; }
        });
    });
})();
</script>
@endpush
