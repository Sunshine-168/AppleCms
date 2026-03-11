@include('admin.public.head')
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.system.configconnect') }}">
        @csrf
        <blockquote class="layui-elem-quote layui-quote-nm">
            {{ __('admin.admin/system/configconnect/tip') }}
        </blockquote>

        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
            <legend>{{ __('admin.admin/system/configconnect/qq') }} <a target="_blank" href="http://connect.qq.com/?maccms" class="layui-btn layui-btn-primary">{{ __('admin.admin/system/configconnect/go_reg') }}</a></legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.status') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="connect[qq][status]" value="0" title="{{ __('admin.close') }}" @checked((string) data_get($config, 'connect.qq.status', '0') !== '1')>
                <input type="radio" name="connect[qq][status]" value="1" title="{{ __('admin.open') }}" @checked((string) data_get($config, 'connect.qq.status', '0') === '1')>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_KEY：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[qq][key]" value="{{ data_get($config, 'connect.qq.key', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_SECRET：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[qq][secret]" value="{{ data_get($config, 'connect.qq.secret', '') }}" class="layui-input">
            </div>
        </div>

        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
            <legend>{{ __('admin.admin/system/configconnect/wx') }} <a target="_blank" href="https://open.weixin.qq.com/?maccms" class="layui-btn layui-btn-primary">{{ __('admin.admin/system/configconnect/go_reg') }}</a></legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.status') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="connect[weixin][status]" value="0" title="{{ __('admin.close') }}" @checked((string) data_get($config, 'connect.weixin.status', '0') !== '1')>
                <input type="radio" name="connect[weixin][status]" value="1" title="{{ __('admin.open') }}" @checked((string) data_get($config, 'connect.weixin.status', '0') === '1')>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_KEY：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[weixin][key]" value="{{ data_get($config, 'connect.weixin.key', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_SECRET：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[weixin][secret]" value="{{ data_get($config, 'connect.weixin.secret', '') }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>
@include('admin.public.foot')