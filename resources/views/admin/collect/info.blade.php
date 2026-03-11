@include('../../../application/admin/view/public/head')
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input id="collect_id" name="collect_id" type="hidden" value="{{ $info.collect_id }}">
        <input type="hidden" name="__token__" value="{{ $Request.token }}" />
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/collect/name') }}：</label>
            <div class="layui-input-block  ">
                <input type="text" class="layui-input" value="{{ $info.collect_name }}" placeholder="" id="collect_name" name="collect_name">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/collect/api_url') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $info.collect_url }}" placeholder="" id="collect_url" name="collect_url">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/collect/attach_param') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $info.collect_param }}" placeholder="" id="collect_param" name="collect_param">
            </div>
            <div class="layui-form-mid layui-word-aux" style="margin-left:110px; ">{{ __('admin.admin/collect/attach_param_tip') }}</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/collect/api_type') }}：</label>
            <div class="layui-input-block">
                <input name="collect_type" type="radio" value="1" title="xml" @if(condition="$info['collect_type'] == 1")checked @endif>
                <input name="collect_type" type="radio" value="2" title="json" @if(condition="$info['collect_type'] != 1")checked @endif>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/collect/data_type') }}：</label>
            <div class="layui-input-block">
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="1" title="{{ __('admin.vod') }}" @if(condition="$info['collect_mid'] == 1")checked @endif>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="2" title="{{ __('admin.art') }}" @if(condition="$info['collect_mid'] == 2")checked @endif>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="8" title="{{ __('admin.actor') }}" @if(condition="$info['collect_mid'] == 8")checked @endif>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="9" title="{{ __('admin.role') }}" @if(condition="$info['collect_mid'] == 9")checked @endif>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="11" title="{{ __('admin.website') }}" @if(condition="$info['collect_mid'] == 11")checked @endif>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/collect/data_opt') }}：</label>
            <div class="layui-input-block">
                <input name="collect_opt" type="radio" value="0" title="{{ __('admin.admin/collect/add_update') }}" @if(condition="$info['collect_opt'] == 0")checked @endif>
                <input name="collect_opt" type="radio" value="1" title="{{ __('admin.admin/collect/add') }}" @if(condition="$info['collect_opt'] == 1")checked @endif>
                <input name="collect_opt" type="radio" value="2" title="{{ __('admin.admin/collect/update') }}" @if(condition="$info['collect_opt'] == 2")checked @endif>
            </div>
            <div class="layui-form-mid layui-word-aux" style="">{{ __('admin.admin/collect/data_opt_tip') }}</div>
        </div>

        <div class="layui-form-item row_filer" @if(condition="$info['collect_mid'] != '1'") style="display:none;" @endif>
            <label class="layui-form-label">{{ __('admin.admin/collect/url_filter') }}：</label>
            <div class="layui-input-block">
                <input name="collect_filter" type="radio" value="0" title="{{ __('admin.admin/collect/no_filter') }}" @if(condition="$info['collect_filter'] == 0")checked @endif>
                <input name="collect_filter" type="radio" value="1" title="{{ __('admin.admin/collect/add_update') }}" @if(condition="$info['collect_filter'] == 1")checked @endif>
                <input name="collect_filter" type="radio" value="2" title="{{ __('admin.admin/collect/add') }}" @if(condition="$info['collect_filter'] == 2")checked @endif>
                <input name="collect_filter" type="radio" value="3" title="{{ __('admin.admin/collect/update') }}" @if(condition="$info['collect_filter'] == 3")checked @endif>
            </div>
        </div>
        <div class="layui-form-item row_filer" @if(condition="$info['collect_mid'] != '1'") style="display:none;" @endif>
            <label class="layui-form-label">{{ __('admin.admin/collect/filter_code') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $info.collect_filter_from }}" placeholder="{{ __('admin.admin/collect/filter_code_tip') }}" id="collect_filter_from" name="collect_filter_from">
            </div>
        </div>
        <div class="layui-form-item row_filer" @if(condition="$info['collect_mid'] != '1'") style="display:none;" @endif>
            <label class="layui-form-label">{{ __('admin.admin/collect/filter_year') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $info.collect_filter_year }}" placeholder="{{ __('admin.admin/collect/filter_year_tip') }}" id="collect_filter_year" name="collect_filter_year">
            </div>
        </div>
        <div class="layui-form-item row_filer">
            <label class="layui-form-label">{{ __('admin.pic_sync') }}：</label>
            <div class="layui-input-block">
                <input name="collect_sync_pic_opt" type="radio" value="0" title="{{ __('admin.follow_global') }}" @if(condition="$info['collect_sync_pic_opt'] == 0")checked @endif>
                <input name="collect_sync_pic_opt" type="radio" value="1" title="{{ __('admin.open') }}" @if(condition="$info['collect_sync_pic_opt'] == 1")checked @endif>
                <input name="collect_sync_pic_opt" type="radio" value="2" title="{{ __('admin.close') }}" @if(condition="$info['collect_sync_pic_opt'] == 2")checked @endif>
            </div>
        </div>

        <br>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button class="layui-btn layui-btn-normal" type="button" id="btnTest" >{{ __('admin.test') }}</button>
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>

</div>
@include('../../../application/admin/view/public/foot')

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;

        // 验证
        form.verify({
            collect_name: function (value) {
                if (value == "") {
                    return "{{ __('admin.name_empty') }}";
                }
            },
            collect_url: function (value) {
                if (value == "") {
                    return "{{ __('admin.url_empty') }}";
                }
            }
        });


        $('#btnTest').click(function() {
            var that = $(this);
            var data = 'cjurl='+ $('#collect_url').val() + '&cjflag='+ '&ac=list';

            $.post("{{ url('test') }}",data,function(r){
                if(r.code==1){
                    layer.msg( "{{ __('admin.admin/collect/test_ok') }}" + '：'+ r.msg ,{time:1800});
                    if(r.msg=='json'){
                        $("input[name='collect_type'][value=2]").attr("checked",true);
                    }
                    else{
                        $("input[name='collect_type'][value=1]").attr("checked",true);
                    }
                    form.render('radio');
                }
                else{
                    layer.msg(r.msg,{time:1800});
                }
            });

        });

        form.on('radio(collect_mid)',function(data){
            $('.row_filer').hide();
            if(data.value=='1'){
                $('.row_filer').show();
            }
        });

    });




</script>

</body>
</html>