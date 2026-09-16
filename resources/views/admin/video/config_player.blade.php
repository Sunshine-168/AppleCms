@extends('admin.layouts.inner')
@section('title', '播放器参数')

@section('content')
    <form id="site-form">
        <label>缓冲秒数</label>
        <input type="number" name="play_buffer" value="{{ $site['play_buffer'] ?? 5 }}">
        <label>地址编码</label>
        <select name="play_encrypt">
            <option value="0" @selected(($site['play_encrypt'] ?? '0')==='0')>明文</option>
            <option value="1" @selected(($site['play_encrypt'] ?? '0')==='1')>前端 Base64</option>
        </select>
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">保存</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
