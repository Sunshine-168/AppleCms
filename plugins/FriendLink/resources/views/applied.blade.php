@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '申请已提交'])

    <div class="flink-page">
        <div class="flink-card flink-result">
            <div class="flink-result-icon" aria-hidden="true">✓</div>
            <h1>申请已提交</h1>
            <p class="flink-result-msg">{{ $msg }}</p>

            @if(trim((string) ($token ?? '')) !== '')
                <div class="flink-token">
                    <span class="flink-token-label">修改令牌（只显示一次，请自行保存）</span>
                    <code id="flink-token-text">{{ $token }}</code>
                    <button type="button" class="btn-ghost" id="flink-token-copy">复制令牌</button>
                </div>
                <p class="muted flink-result-hint">有令牌且后台允许自助修改时，可打开 <a href="{{ url('/links/edit/'.$token) }}">修改页</a>。</p>
            @endif

            <div class="flink-actions">
                <a class="btn-play" href="{{ url('/') }}">回首页</a>
                <a class="btn-ghost" href="{{ url('/links/apply') }}">再申请一条</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('flink-token-copy');
    var text = document.getElementById('flink-token-text');
    if (!btn || !text) return;
    btn.addEventListener('click', function () {
        var v = text.textContent || '';
        var done = function () {
            if (window.vodToast) window.vodToast('已复制令牌', 'ok');
            else alert('已复制');
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(v).then(done).catch(function () {
                window.prompt('复制令牌', v);
                done();
            });
        } else {
            window.prompt('复制令牌', v);
            done();
        }
    });
})();
</script>
@endpush
