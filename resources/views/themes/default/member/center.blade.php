@extends('themes.default.layout')
@section('content')
    <div class="member-page">
        <header class="member-hero">
            <div class="member-hero-main">
                <p class="member-eyebrow muted">会员中心</p>
                <h1>{{ $member->name }}</h1>
                <p class="muted">登录后可收藏、签到领积分，兑换也会记在这里。</p>
            </div>
            <div class="member-points">
                <span class="muted">积分</span>
                <strong>{{ (int) $member->points }}</strong>
            </div>
        </header>

        <nav class="member-nav" aria-label="会员功能">
            <a href="{{ url('/member/activity') }}">用户活动</a>
            <a href="{{ url('/member/favorites') }}">我的收藏</a>
            <a href="{{ url('/member/history') }}">观看历史</a>
            <a href="{{ url('/member/inbox') }}">站内信</a>
            @includeIf('mall::member')
            @includeIf('pay::member')
            @includeIf('coupon::member')
            @includeIf('manga::member')
        </nav>

        <div class="member-grid">
            <section class="member-card">
                <div class="sec-head"><h2>卡密充值</h2></div>
                <form class="member-form" method="post" action="{{ url('/member/redeem') }}">
                    @csrf
                    <label class="auth-field">
                        <span>积分卡密</span>
                        <input name="code" placeholder="粘贴卡密" required autocomplete="off">
                    </label>
                    <div class="auth-actions">
                        <button type="submit" class="btn-play btn-sm">兑换</button>
                    </div>
                </form>
            </section>

            <section class="member-card">
                <div class="sec-head"><h2>修改密码</h2></div>
                <form class="member-form" method="post" action="{{ url('/member/password') }}">
                    @csrf
                    <label class="auth-field">
                        <span>原密码</span>
                        <input type="password" name="old_password" required autocomplete="current-password">
                    </label>
                    <label class="auth-field">
                        <span>新密码</span>
                        <input type="password" name="password" required minlength="6" autocomplete="new-password">
                    </label>
                    <div class="auth-actions">
                        <button type="submit" class="btn-play btn-sm">保存密码</button>
                    </div>
                </form>
            </section>

            <section class="member-card">
                <div class="sec-head"><h2>邀请码</h2></div>
                @if(isset($invites) && $invites->isNotEmpty())
                    <ul class="member-invite-list">
                        @foreach($invites as $row)
                            <li>
                                <code>{{ $row->code }}</code>
                                <span class="muted">{{ (int) $row->status === 1 ? '未用' : '已用' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="muted">还没有邀请码，生成后可分享给朋友注册。</p>
                @endif
                <form method="post" action="{{ url('/member/invite/generate') }}">
                    @csrf
                    <button type="submit" class="btn-ghost">生成邀请码</button>
                </form>
            </section>
        </div>

        <form class="member-logout" method="post" action="{{ url('/member/logout') }}">
            @csrf
            <button type="submit" class="btn-ghost">退出登录</button>
        </form>
    </div>
@endsection
