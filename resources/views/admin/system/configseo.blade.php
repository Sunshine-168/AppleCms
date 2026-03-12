@include('admin.public.head')

<div class="page-container">
        <form class="layui-form layui-form-pane" action="{{ route('admin.system.configseo') }}" method="post">
            @csrf
            <div class="layui-tab" lay-filter="tb1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="configseo_1">{{ __('admin/system/configseo/vod_index') }}SEO</li>
                    <li lay-id="configseo_2">{{ __('admin/system/configseo/art_index') }}SEO</li>
                    <li lay-id="configseo_3">{{ __('admin/system/configseo/actor_index') }}SEO</li>
                    <li lay-id="configseo_4">{{ __('admin/system/configseo/role_index') }}SEO</li>
                    <li lay-id="configseo_5">{{ __('admin/system/configseo/plot_index') }}SEO</li>
                    <li lay-id="configseo_6">{{ __('admin/system/configseo/website_index') }}SEO</li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <div class="page-tip-blue">
                            <strong>{{ __('admin/system/configseo/tip_des') }}：</strong><br>
                            {{ __('admin/system/configseo/vod_index') }} vod/index
                        </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin/system/configseo/tit') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[name]" placeholder="{{ __('admin/system/configseo/tit') }}title" value="{{ $config['vod']['name'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin/system/configseo/key') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[key]" placeholder="{{ __('admin/system/configseo/key') }}keywords" value="{{ $config['vod']['key'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin/system/configseo/des') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[des]" placeholder="{{ __('admin/system/configseo/des') }}description" value="{{ $config['vod']['des'] ?? '' }}" class="layui-input">
                    </div>
                </div>

            </div>
                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong>{{ __('admin/system/configseo/tip_des') }}：</strong><br>
                            {{ __('admin/system/configseo/art_index') }} art/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/tit') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[name]" placeholder="{{ __('admin/system/configseo/tit') }}title" value="{{ $config['art']['name'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/key') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[key]" placeholder="{{ __('admin/system/configseo/key') }}keywords" value="{{ $config['art']['key'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/des') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[des]" placeholder="{{ __('admin/system/configseo/des') }}description" value="{{ $config['art']['des'] ?? '' }}" class="layui-input">
                            </div>
                        </div>

                    </div>


                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong>{{ __('admin/system/configseo/tip_des') }}：</strong><br>
                            {{ __('admin/system/configseo/actor_index') }} actor/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/tit') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[name]" placeholder="{{ __('admin/system/configseo/tit') }}title" value="{{ $config['actor']['name'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/key') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[key]" placeholder="{{ __('admin/system/configseo/key') }}keywords" value="{{ $config['actor']['key'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/des') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[des]" placeholder="{{ __('admin/system/configseo/des') }}description" value="{{ $config['actor']['des'] ?? '' }}" class="layui-input">
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong>{{ __('admin/system/configseo/tip_des') }}：</strong><br>
                            {{ __('admin/system/configseo/role_index') }} role/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/tit') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[name]" placeholder="{{ __('admin/system/configseo/tit') }}title" value="{{ $config['role']['name'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/key') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[key]" placeholder="{{ __('admin/system/configseo/key') }}keywords" value="{{ $config['role']['key'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/des') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[des]" placeholder="{{ __('admin/system/configseo/des') }}description" value="{{ $config['role']['des'] ?? '' }}" class="layui-input">
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong>{{ __('admin/system/configseo/tip_des') }}：</strong><br>
                            {{ __('admin/system/configseo/plot_index') }} plot/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/tit') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="plot[name]" placeholder="{{ __('admin/system/configseo/tit') }}title" value="{{ $config['plot']['name'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/key') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="plot[key]" placeholder="{{ __('admin/system/configseo/key') }}keywords" value="{{ $config['plot']['key'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/des') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="plot[des]" placeholder="{{ __('admin/system/configseo/des') }}description" value="{{ $config['plot']['des'] ?? '' }}" class="layui-input">
                            </div>
                        </div>

                    </div>


                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong>{{ __('admin/system/configseo/tip_des') }}：</strong><br>
                            {{ __('admin/system/configseo/website_index') }} website/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/tit') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[name]" placeholder="{{ __('admin/system/configseo/tit') }}title" value="{{ $config['website']['name'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/key') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[key]" placeholder="{{ __('admin/system/configseo/key') }}keywords" value="{{ $config['website']['key'] ?? '' }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin/system/configseo/des') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[des]" placeholder="{{ __('admin/system/configseo/des') }}description" value="{{ $config['website']['des'] ?? '' }}" class="layui-input">
                            </div>
                        </div>

                    </div>

                </div>
        </div>
            <div class="layui-form-item center">
                <div class="layui-input-block">
                    <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                    <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
                </div>
            </div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript" src="{{ asset('static/js/jquery.cookie.js') }}"></script>
<script type="text/javascript">
    layui.use(['element', 'form', 'layer'], function() {
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer;


        element.on('tab(tb1)', function(){
            $.cookie('configseo_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configseo_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configseo_tab'));
        }

    });
</script>
