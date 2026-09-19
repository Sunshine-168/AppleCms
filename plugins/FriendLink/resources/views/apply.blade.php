@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '申请友链'])

    <div class="flink-page">
        <header class="flink-hero">
            <h1>申请友链</h1>
            <p class="muted">提交后进入待审。强化模式需有足够来路才会显示；普通模式等管理员通过。不会假装已成功。</p>
        </header>

        <div class="flink-card">
            <form class="flink-form" method="post" action="{{ url('/links/apply') }}">
                @csrf
                <div class="flink-grid">
                    <label class="flink-field">
                        <span>站点名称</span>
                        <input type="text" name="name" required value="{{ old('name') }}" maxlength="120" placeholder="显示在页脚的名称">
                    </label>
                    <label class="flink-field">
                        <span>网址</span>
                        <input type="url" name="url" required value="{{ old('url') }}" placeholder="https://">
                    </label>
                    <label class="flink-field">
                        <span>分类</span>
                        <select name="cate_id">
                            <option value="0">未分类</option>
                            @foreach($cates as $cate)
                                <option value="{{ $cate->id }}" @selected((string) old('cate_id') === (string) $cate->id)>{{ $cate->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flink-field">
                        <span>邮箱 <em class="muted">可选</em></span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="方便站长联系">
                    </label>
                </div>

                <div class="flink-captcha">
                    <span class="flink-captcha-label">验证码</span>
                    <div class="flink-captcha-row">
                        <button type="button" class="flink-captcha-img" id="flink-captcha-refresh" title="点击刷新">
                            <img src="{{ url('/links/captcha') }}" alt="验证码" width="160" height="48" id="flink-captcha-pic">
                        </button>
                        <input type="text" name="captcha" required inputmode="numeric" autocomplete="off" placeholder="计算结果" aria-label="验证码">
                    </div>
                </div>

                <div class="flink-actions">
                    <button type="submit" class="btn-play">提交申请</button>
                    <a class="btn-ghost" href="{{ url('/') }}">回首页</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('flink-captcha-refresh');
    var img = document.getElementById('flink-captcha-pic');
    if (!btn || !img) return;
    function refresh() {
        img.src = @json(url('/links/captcha')) + '?t=' + Date.now();
    }
    btn.addEventListener('click', refresh);
    img.addEventListener('click', function (e) {
        e.preventDefault();
        refresh();
    });
})();
</script>
@endpush
