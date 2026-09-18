@extends('themes.default.layout')

@section('content')
    <h1>修改友链</h1>
    <p class="muted">可以改名称、网址、Logo、分类、邮箱和类型。点击次数不能改。</p>
    <form method="post" action="{{ url('/links/edit/'.$token) }}">
        @csrf
        <p>
            <label>名称<br>
            <input type="text" name="name" required value="{{ old('name', $link->name) }}" maxlength="120"></label>
        </p>
        <p>
            <label>网址<br>
            <input type="url" name="url" required value="{{ old('url', $link->url) }}"></label>
        </p>
        <p>
            <label>分类<br>
            <select name="cate_id">
                <option value="0">未分类</option>
                @foreach($cates as $cate)
                    <option value="{{ $cate->id }}" @selected((string) old('cate_id', $link->cate_id) === (string) $cate->id)>{{ $cate->name }}</option>
                @endforeach
            </select></label>
        </p>
        <p>
            <label>类型<br>
            <select name="type">
                <option value="text" @selected(old('type', $link->type) === 'text')>文字</option>
                <option value="image" @selected(old('type', $link->type) === 'image')>图片</option>
            </select></label>
        </p>
        <p>
            <label>Logo<br>
            <input type="text" name="logo" value="{{ old('logo', $link->logo) }}"></label>
        </p>
        <p>
            <label>邮箱<br>
            <input type="email" name="email" value="{{ old('email', $link->email) }}"></label>
        </p>
        <p><button type="submit">保存</button></p>
    </form>
@endsection
