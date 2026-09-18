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
    @endphp
    <h1>用户活动</h1>
    <p class="muted">积分：{{ (int) ($state['points'] ?? $member->points) }}
        · 连续 {{ $days }} 天
        · <a href="{{ url('/member') }}">会员中心</a>
    </p>
    <p>达到连续天数当场入账，不用再点领取。</p>
    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif
    @if(session('error'))
        <p>{{ session('error') }}</p>
    @endif
    <form method="post" action="{{ url('/member/sign') }}" id="sign-form">
        @csrf
        <button type="submit" id="sign-btn" @if($signed) disabled @endif>{{ $signed ? '今天已签到' : '签到' }}</button>
    </form>

    <h2>每日任务</h2>
    @forelse($daily as $task)
        <p>
            {{ $task['name'] }}
            · {{ (int) $task['progress'] }}/{{ (int) $task['target'] }}
            · {{ (int) $task['points'] }} 积分
            @if((int) $task['status'] >= 2)（已入账）@elseif((int) $task['progress'] >= (int) $task['target'])（已完成）@endif
            @if($task['hint']) <span class="muted">{{ $task['hint'] }}</span> @endif
        </p>
    @empty
        <p class="muted">今天没有启用的每日任务</p>
    @endforelse

    <h2>新手任务</h2>
    @forelse($newbie as $task)
        <p>
            {{ $task['name'] }}
            · {{ (int) $task['progress'] }}/{{ (int) $task['target'] }}
            · {{ (int) $task['points'] }} 积分
            @if((int) $task['status'] >= 2)（已入账）@endif
            @if($task['hint']) <span class="muted">{{ $task['hint'] }}</span> @endif
        </p>
    @empty
        <p class="muted">没有启用的新手任务</p>
    @endforelse

    <h2>签到里程碑</h2>
    @forelse($milestones as $row)
        <p>
            {{ $row['name'] ?: ('连续'.$row['days'].'天') }}
            · {{ (int) $row['points'] }} 积分
            @if(! empty($row['granted']))（已入账）@elseif(! empty($row['reached']))（已达标）@endif
        </p>
    @empty
        <p class="muted">还没有启用里程碑</p>
    @endforelse
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
