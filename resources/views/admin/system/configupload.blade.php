@include('admin.public.head')

<div class="page-container">

    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>

    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.system.configupload') }}">
        @csrf
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this">{{ lang('admin/system/configupload/title') }}</li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">

                    <div class="page-tip-blue">
                        @php
                            $tip = (string) lang('admin/system/configupload/tip');
                            $tip = preg_replace('/<br>\s+/u', '<br>', $tip) ?? $tip;
                            $temp_file = @tempnam(sys_get_temp_dir(), 'Tux');
                            if ($temp_file) {
                                @unlink($temp_file);
                            }
                        @endphp
                        {!! $tip !!}{{ sys_get_temp_dir() }}
                        <div class="mt10">
                            @if($temp_file)
                                <span class="layui-badge layui-bg-green">{{ lang('admin/system/configupload/write_ok') }}</span>
                            @else
                                <span class="layui-badge">{{ lang('admin/system/configupload/write_err') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/img_key') }}：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" name="upload[img_key]" placeholder="" value="{{ data_get($config, 'upload.img_key', '') }}" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ lang('admin/system/configupload/img_key_tip') }}</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/img_api') }}：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" name="upload[img_api]" placeholder="" value="{{ data_get($config, 'upload.img_api', '') }}" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ lang('admin/system/configupload/img_api_tip') }}</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('pic_thumb') }}：</label>
                        <div class="layui-input-inline">
                            <input type="radio" name="upload[thumb]" value="0" title="{{ lang('close') }}" @checked((string) data_get($config, 'upload.thumb', '0') !== '1')>
                            <input type="radio" name="upload[thumb]" value="1" title="{{ lang('open') }}" @checked((string) data_get($config, 'upload.thumb', '0') === '1')>
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ lang('admin/system/configupload/thumb_tip') }}</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/thumb_size') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="upload[thumb_size]" placeholder="" value="{{ data_get($config, 'upload.thumb_size', '') }}" class="layui-input w150">
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ lang('admin/system/configupload/thumb_size_tip') }}</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/thumb_type') }}：</label>
                        <div class="layui-input-inline">
                            <select class="w150" name="upload[thumb_type]">
                                <option value="1" @selected((string) data_get($config, 'upload.thumb_type', '1') === '1')>{{ lang('admin/system/configupload/thumb_type1') }}</option>
                                <option value="2" @selected((string) data_get($config, 'upload.thumb_type', '1') === '2')>{{ lang('admin/system/configupload/thumb_type2') }}</option>
                                <option value="3" @selected((string) data_get($config, 'upload.thumb_type', '1') === '3')>{{ lang('admin/system/configupload/thumb_type3') }}</option>
                                <option value="4" @selected((string) data_get($config, 'upload.thumb_type', '1') === '4')>{{ lang('admin/system/configupload/thumb_type4') }}</option>
                                <option value="5" @selected((string) data_get($config, 'upload.thumb_type', '1') === '5')>{{ lang('admin/system/configupload/thumb_type5') }}</option>
                                <option value="6" @selected((string) data_get($config, 'upload.thumb_type', '1') === '6')>{{ lang('admin/system/configupload/thumb_type6') }}</option>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux"></div>
                    </div>
                <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/watermark') }}：</label>
                        <div class="layui-input-inline">
                            <input type="radio" name="upload[watermark]" value="0" title="{{ lang('close') }}" @checked((string) data_get($config, 'upload.watermark', '0') !== '1')>
                            <input type="radio" name="upload[watermark]" value="1" title="{{ lang('open') }}" @checked((string) data_get($config, 'upload.watermark', '0') === '1')>
                        </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/watermark_location') }}：</label>
                        <div class="layui-input-inline">
                            <select class="w150" name="upload[watermark_location]">

                                <option value="1" @selected((string) data_get($config, 'upload.watermark_location', '1') === '1')>{{ lang('admin/system/configupload/watermark_location1') }}</option>
                                <option value="2" @selected((string) data_get($config, 'upload.watermark_location', '1') === '2')>{{ lang('admin/system/configupload/watermark_location2') }}</option>
                                <option value="3" @selected((string) data_get($config, 'upload.watermark_location', '1') === '3')>{{ lang('admin/system/configupload/watermark_location3') }}</option>
                                <option value="4" @selected((string) data_get($config, 'upload.watermark_location', '1') === '4')>{{ lang('admin/system/configupload/watermark_location4') }}</option>
                                <option value="5" @selected((string) data_get($config, 'upload.watermark_location', '1') === '5')>{{ lang('admin/system/configupload/watermark_location5') }}</option>
                                <option value="6" @selected((string) data_get($config, 'upload.watermark_location', '1') === '6')>{{ lang('admin/system/configupload/watermark_location6') }}</option>
                                <option value="7" @selected((string) data_get($config, 'upload.watermark_location', '1') === '7')>{{ lang('admin/system/configupload/watermark_location7') }}</option>
                                <option value="8" @selected((string) data_get($config, 'upload.watermark_location', '1') === '8')>{{ lang('admin/system/configupload/watermark_location8') }}</option>
                                <option value="9" @selected((string) data_get($config, 'upload.watermark_location', '1') === '9')>{{ lang('admin/system/configupload/watermark_location9') }}</option>


                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/watermark_content') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="upload[watermark_content]" placeholder="" value="{{ data_get($config, 'upload.watermark_content', '') }}" class="layui-input w150"  >
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/watermark_size') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="upload[watermark_size]" placeholder="{{ lang('admin/system/configupload/watermark_size_tip') }}" value="{{ data_get($config, 'upload.watermark_size', '') }}" class="layui-input w150"  >
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/watermark_color') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="upload[watermark_color]" placeholder="{{ lang('admin/system/configupload/watermark_color_tip') }}" value="{{ data_get($config, 'upload.watermark_color', '') }}" class="layui-input w150"  >
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ lang('admin/system/configupload/protocol') }}：</label>
                        <div class="layui-input-inline">
                            <select class="w150" name="upload[protocol]" lay-filter="upload[protocol]">
                                <option value="http" @selected((string) data_get($config, 'upload.protocol', 'http') === 'http')>http</option>
                                <option value="https" @selected((string) data_get($config, 'upload.protocol', 'http') === 'https')>https</option>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ lang('admin/system/configupload/protocol_tip') }}</div>
                    </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ lang('admin/system/configupload/mode') }}：</label>
                    <div class="layui-input-inline">
                        <select class="w150" name="upload[mode]" lay-filter="upload[mode]">
                            <option value="local" @selected((string) data_get($config, 'upload.mode', 'local') === 'local')>{{ lang('admin/system/configupload/mode_local') }}</option>
                            <option value="remote" @selected((string) data_get($config, 'upload.mode', 'local') === 'remote')>{{ lang('admin/system/configupload/mode_remote') }}</option>
                            @foreach(($extends['ext_list'] ?? []) as $key => $vo)
                                <option value="{{ $key }}" @selected((string) data_get($config, 'upload.mode', 'local') === (string) $key)>{{ $vo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ lang('admin/system/configupload/keep_local') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="upload[keep_local]" value="0" title="{{ lang('close') }}" @checked((string) data_get($config, 'upload.keep_local', '0') !== '1')>
                        <input type="radio" name="upload[keep_local]" value="1" title="{{ lang('open') }}" @checked((string) data_get($config, 'upload.keep_local', '0') === '1')>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ lang('admin/system/configupload/keep_local_tip') }}</div>
                </div>

                <div class="layui-form-item upload_mode mode_remote" @if((string) data_get($config, 'upload.mode', '') !== 'remote')style="display:none;" @endif>
                    <label class="layui-form-label">{{ lang('admin/system/configupload/remoteurl') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="upload[remoteurl]" placeholder="{{ lang('admin/system/configupload/remoteurl_tip') }}" value="{{ data_get($config, 'upload.remoteurl', '') }}" class="layui-input w500">
                    </div>
                </div>

                @foreach(($extends['ext_list'] ?? []) as $key => $vo)
                    @includeIf('admin.extend.upload.' . strtolower($key))
                @endforeach

                </div>


                <div class="layui-form-item center">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">{{ lang('btn_save') }}</button>
                        <button class="layui-btn layui-btn-warm" type="reset">{{ lang('btn_reset') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript">
    layui.use(['form','layer'], function(){
        // 操作对象
        var form = layui.form
                , layer = layui.layer;

        form.on('select(upload[mode])', function(data){
            $('.upload_mode').hide();
            $('.mode_'+ data.value).show();
        });


    });


</script>

</body>
</html>
