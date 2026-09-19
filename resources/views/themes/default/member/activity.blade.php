@extends('themes.default.layout')
@section('content')
    @php
        $state = is_array($state ?? null) ? $state : [];
        $signed = ! empty($state['signed_today']);
        $days = (int) ($state['days'] ?? 0);
        $tasks = is_array($state['tasks'] ?? null) ? $state['tasks'] : [];
        $milestones = is_array($state['milestones'] ?? null) ? $state['milestones'] : [];
        $daily = array_values(array_filter($tasks, fn ($t) => (int) ($t['type'] ?? 1) === 1));
        $newbie = array_values(array_filter($tasks, fn ($t) => (int) ($t['type'] ?? 1) === 2));
        $points = (int) ($state['points'] ?? $member->points);
    @endphp

    <div class="member-page">
        <header class="member-hero">
            <div class="member-hero-main">
                <p class="member-eyebrow muted">用户活动</p>
                <h1>签到与任务</h1>
                <p class="muted">达到连续天数当场入账，不用再点领取。· <a href="{{ url('/member') }}">会员中心</a></p>
            </div>
            <div class="member-points">
                <span class="muted">积分</span>
                <strong>{{ $points }}</strong>
                <em class="muted">连续 {{ $days }} 天</em>
            </div>
        </header>

        <section class="member-card member-sign">
            <form method="post" action="{{ url('/member/sign') }}" id="sign-form">
                @csrf
                <button type="submit" class="btn-play" id="sign-btn" @if($signed) disabled @endif>
                    {{ $signed ? '今天已签到' : '立即签到' }}
                </button>
                <p class="muted">{{ $signed ? '明天再来，保持连续天数。' : '每天签到可攒积分，连续天数有额外奖励。' }}</p>
            </form>
        </section>

        <div class="member-grid">
            <section class="member-card">
                <div class="sec-head"><h2>每日任务</h2></div>
                @forelse($daily as $task)
                    @php
                        $progress = (int) ($task['progress'] ?? 0);
                        $target = max(1, (int) ($task['target'] ?? 1));
                        $pct = min(100, (int) round($progress / $target * 100));
                        $done = (int) ($task['status'] ?? 0) >= 2 || $progress >= $target;
                    @endphp
                    <article class="member-task{{ $done ? ' is-done' : '' }}">
                        <div class="member-task-top">
                            <strong>{{ $task['name'] }}</strong>
                            <span>{{ (int) $task['points'] }} 积分</span>
                        </div>
                        <div class="member-progress" aria-hidden="true"><i style="width:{{ $pct }}%"></i></div>
                        <p class="muted">
                            {{ $progress }}/{{ $target }}
                            @if((int) $task['status'] >= 2) · 已入账
                            @elseif($progress >= $target) · 已完成
                            @endif
                            @if(!empty($task['hint'])) · {{ $task['hint'] }}@endif
                        </p>
                    </article>
                @empty
                    <p class="muted">今天没有启用的每日任务</p>
                @endforelse
            </section>

            <section class="member-card">
                <div class="sec-head"><h2>新手任务</h2></div>
                @forelse($newbie as $task)
                    @php
                        $progress = (int) ($task['progress'] ?? 0);
                        $target = max(1, (int) ($task['target'] ?? 1));
                        $pct = min(100, (int) round($progress / $target * 100));
                        $done = (int) ($task['status'] ?? 0) >= 2 || $progress >= $target;
                    @endphp
                    <article class="member-task{{ $done ? ' is-done' : '' }}">
                        <div class="member-task-top">
                            <strong>{{ $task['name'] }}</strong>
                            <span>{{ (int) $task['points'] }} 积分</span>
                        </div>
                        <div class="member-progress" aria-hidden="true"><i style="width:{{ $pct }}%"></i></div>
                        <p class="muted">
                            {{ $progress }}/{{ $target }}
                            @if((int) $task['status'] >= 2) · 已入账@endif
                            @if(!empty($task['hint'])) · {{ $task['hint'] }}@endif
                        </p>
                    </article>
                @empty
                    <p class="muted">没有启用的新手任务</p>
                @endforelse
            </section>

            <section class="member-card">
                <div class="sec-head"><h2>签到里程碑</h2></div>
                @forelse($milestones as $row)
                    <article class="member-task{{ !empty($row['granted']) || !empty($row['reached']) ? ' is-done' : '' }}">
                        <div class="member-task-top">
                            <strong>{{ $row['name'] ?: ('连续'.$row['days'].'天') }}</strong>
                            <span>{{ (int) $row['points'] }} 积分</span>
                        </div>
                        <p class="muted">
                            @if(! empty($row['granted']))已入账
                            @elseif(! empty($row['reached']))已达标
                            @else还需连续 {{ (int) $row['days'] }} 天
                            @endif
                        </p>
                    </article>
                @empty
                    <p class="muted">还没有启用里程碑</p>
                @endforelse
            </section>
        </div>
    </div>

    <script>
    (function () {
        var form = document.getElementById('sign-form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('sign-btn');
            if (btn && btn.disabled) return;
            fetch(@json(url('/member/sign')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (r) { return r.json().catch(function () { return null; }); }).then(function (res) {
                if (vodResult(res, '签到失败')) {
                    if (btn) { btn.disabled = true; btn.textContent = '今天已签到'; }
                    setTimeout(function () { location.reload(); }, 600);
                }
            }).catch(function () { vodToast('网络异常，请重试', 'err'); });
        });
    })();
    </script>
@endsection
