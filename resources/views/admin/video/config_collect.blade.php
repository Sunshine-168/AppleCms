@extends('admin.layouts.inner')
@section('title', admin_t('page.config_collect'))

@section('content')
    <form id="site-form">
        <label>站外入库密钥</label>
        <input type="text" name="inbound_key" value="{{ $site['inbound_key'] ?? '' }}" placeholder="POST /api.php/receive/vod">
        <label>地区词库</label>
        <textarea name="collect_areawords" placeholder="每行 from=to 或 from,to，如 大陆=中国">{{ $site['collect_areawords'] ?? '' }}</textarea>
        <label>语言词库</label>
        <textarea name="collect_langwords" placeholder="每行 from=to 或 from,to">{{ $site['collect_langwords'] ?? '' }}</textarea>
        <label>先入临时表</label>
        <select name="collect_to_temp">
            <option value="0" @selected(($site['collect_to_temp'] ?? '0')==='0')>直接入库</option>
            <option value="1" @selected(($site['collect_to_temp'] ?? '0')==='1')>写入临时表再审核转入</option>
        </select>
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">保存</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
