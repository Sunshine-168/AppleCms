@extends('admin.layouts.inner')
@section('title', '开放API')

@section('content')
    <form id="site-form">
        <label>资源接口密钥</label>
        <input type="text" name="provide_key" value="{{ $site['provide_key'] ?? '' }}" placeholder="非空时 /api.php/provide/vod 需带 key">
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">保存</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
