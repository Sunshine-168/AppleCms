@extends('themes.default.layout')

@section('content')
    <h1>申请友链</h1>
    <p class="muted">提交后进入待审。强化模式下，有足够来路才会显示；普通模式要等管理员通过。不会假装已经成功。</p>
    <form method="post" action="{{ url('/links/apply') }}">
        @csrf
        <p>
            <label>名称<br>
            <input type="text" name="name" required value="{{ old('name') }}" maxlength="120"></label>
        </p>
        <p>
            <label>网址<br>
            <input type="url" name="url" required value="{{ old('url') }}" placeholder="https://"></label>
        </p>
        <p>
            <label>分类<br>
            <select name="cate_id">
                <option value="0">未分类</option>
                @foreach($cates as $cate)
                    <option value="{{ $cate->id }}" @selected((string) old('cate_id') === (string) $cate->id)>{{ $cate->name }}</option>
                @endforeach
            </select></label>
        </p>
        <p>
            <label>类型<br>
            <select name="type">
                <option value="text" @selected(old('type', 'text') === 'text')>文字</option>
                <option value="image" @selected(old('type') === 'image')>图片</option>
            </select></label>
        </p>
        <p>
            <label>Logo<br>
            <input type="text" name="logo" value="{{ old('logo') }}" placeholder="图片地址，可空"></label>
        </p>
        <p>
            <label>邮箱<br>
            <input type="email" name="email" value="{{ old('email') }}"></label>
        </p>
        <p>
            <label>验证码<br>
            <img src="{{ url('/links/captcha') }}" alt="captcha" width="160" height="48">
            <input type="text" name="captcha" required inputmode="numeric" autocomplete="off"></label>
        </p>
        <p><button type="submit">提交</button></p>
    </form>
@endsection
