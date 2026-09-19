@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '修改友链'])

    <div class="flink-page">
        <header class="flink-hero">
            <h1>修改友链</h1>
            <p class="muted">可改名称、网址、分类和邮箱。点击次数不能改。</p>
        </header>

        <div class="flink-card">
            <form class="flink-form" method="post" action="{{ url('/links/edit/'.$token) }}">
                @csrf
                <div class="flink-grid">
                    <label class="flink-field">
                        <span>站点名称</span>
                        <input type="text" name="name" required value="{{ old('name', $link->name) }}" maxlength="120">
                    </label>
                    <label class="flink-field">
                        <span>网址</span>
                        <input type="url" name="url" required value="{{ old('url', $link->url) }}" placeholder="https://">
                    </label>
                    <label class="flink-field">
                        <span>分类</span>
                        <select name="cate_id">
                            <option value="0">未分类</option>
                            @foreach($cates as $cate)
                                <option value="{{ $cate->id }}" @selected((string) old('cate_id', $link->cate_id) === (string) $cate->id)>{{ $cate->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flink-field">
                        <span>邮箱 <em class="muted">可选</em></span>
                        <input type="email" name="email" value="{{ old('email', $link->email) }}">
                    </label>
                </div>

                <div class="flink-actions">
                    <button type="submit" class="btn-play">保存修改</button>
                    <a class="btn-ghost" href="{{ url('/') }}">回首页</a>
                </div>
            </form>
        </div>
    </div>
@endsection
