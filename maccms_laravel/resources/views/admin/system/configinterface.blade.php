@include('admin.public.head')
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.system.configinterface') }}">
        @csrf
        <blockquote class="layui-elem-quote layui-quote-nm">
            {{ __('admin.admin/system/configinterface/tip') }}
        </blockquote>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configinterface/status') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="interface[status]" value="0" title="{{ __('admin.close') }}" @checked((string) data_get($config, 'interface.status', '0') !== '1')>
                <input type="radio" name="interface[status]" value="1" title="{{ __('admin.open') }}" @checked((string) data_get($config, 'interface.status', '0') === '1')>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configinterface/pass') }}：</label>
            <div class="layui-input-inline w400">
                <input type="text" name="interface[pass]" value="{{ data_get($config, 'interface.pass', '') }}" class="layui-input">
            </div>
            <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configinterface/pass_tip') }}</div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configinterface/vod_type') }}：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[vodtype]" class="layui-textarea" rows="20">{{ mac_replace_text((string) data_get($config, 'interface.vodtype', '')) }}</textarea>
            </div>
            <label class="layui-form-label">{{ __('admin.admin/system/configinterface/art_type') }}：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[arttype]" class="layui-textarea" rows="20">{{ mac_replace_text((string) data_get($config, 'interface.arttype', '')) }}</textarea>
            </div>
            <label class="layui-form-label">{{ __('admin.admin/system/configinterface/actor_type') }}：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[actortype]" class="layui-textarea" rows="20">{{ mac_replace_text((string) data_get($config, 'interface.actortype', '')) }}</textarea>
            </div>
            <label class="layui-form-label">{{ __('admin.admin/system/configinterface/website_type') }}：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[websitetype]" class="layui-textarea" rows="20">{{ mac_replace_text((string) data_get($config, 'interface.websitetype', '')) }}</textarea>
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