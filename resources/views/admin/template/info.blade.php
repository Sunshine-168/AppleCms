@include('admin.public.head')
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="">
        @csrf
        <blockquote class="layui-elem-quote layui-quote-nm">
            提示信息：<br>
            为了安全考量避免通过模板写入后门文件，文件内出现以下任意字符串时禁止在线保存修改，如需修改请使用其他方式。<br>
            {{ $filter }}
        </blockquote>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.path') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $fpath }}" id="fpath" name="fpath" readonly>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.file_name') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $fname }}" placeholder="{{ __('admin.admin/template/name_tip') }}" id="fname" name="fname" {{ $fname !== '' ? 'readonly' : '' }}>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.content') }}：</label>
            <div class="layui-input-block">
                <textarea name="fcontent" class="layui-textarea" style="height:550px;">{{ $fcontent }}</textarea>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>
@include('admin.public.foot')