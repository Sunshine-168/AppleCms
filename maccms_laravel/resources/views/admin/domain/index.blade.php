@include('../../../application/admin/view/public/head')
<div class="page-container p10">
    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>
    
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="vod_id" value="{{ $info.vod_id }}">

        <div class="layui-tab">
            <ul class="layui-tab-title ">
                <li class="layui-this">{{ __('admin.admin/domain/title') }}</a></li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">

                    <blockquote class="layui-elem-quote layui-quote-nm">
                        {{ __('admin.admin/domain/help_tip') }}
                        <a class="layui-btn layui-btn-primary" href="{{ url('export') }}" >{{ __('admin.export') }}</a>
                        <a class="layui-btn layui-btn-primary layui-upload" data-href="{{ url('import') }}" >{{ __('admin.import') }}</a>
                    </blockquote>

                    <script>
                        var arr_len = {{ $domain_list|count }};
                    </script>
                    {php}
                    $n=0;
                    {/php}

                    <div id="domain_list" class="contents">
                        @foreach($$domain_list as $vo)
                        {php}
                        $n++;
                        {/php}
                        <div class="layui-form-item tr" data-i="{{ $key }}">
                        <label class="layui-form-label">{{ __('admin.website') }}{{ $n }}：</label>
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_url][]" class="layui-input" placeholder="{{ __('admin.domain') }}" value="{{ $vo.site_url }}"></div>&nbsp;
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_name][]" class="layui-input" placeholder="{{ __('admin.site_name') }}" value="{{ $vo.site_name }}"></div>&nbsp;
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_keywords][]" class="layui-input" placeholder="{{ __('admin.keywords') }}" value="{{ $vo.site_keywords }}"></div>&nbsp;
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_description][]" class="layui-input" placeholder="{{ __('admin.description') }}" value="{{ $vo.site_description }}"></div>&nbsp;
                            <div class="layui-input-inline w150"><select name="domain[template_dir][]"><option value="no">{{ __('admin.select_template') }}.</option>@foreach($templates as $vo2)<option value="{{ $vo2 }}" @if($vo2 == $vo.template_dir)selected@endif>{{ $vo2 }}</option>@endforeach</select></div>
                            <div class="layui-input-inline w150"><input type="text" name="domain[html_dir][]" class="layui-input" placeholder="{{ __('admin.tpl_dir') }}" value="{{ $vo.html_dir }}"></div>
                            <div class="layui-input-inline w150"><input type="text" name="domain[ads_dir][]" class="layui-input" placeholder="{{ __('admin.ads_dir') }}" value="{{ $vo.ads_dir }}"></div>
                            <div> <a class="layui-badge-rim j-tr-del" data-href="{{ url('del?ids='.$vo['site_url']) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a></div>
                        </div>
                        @endforeach
                    </div>
                    <div class="layui-form-item">
                        <label class=""><button class="layui-btn radius j-player-add" type="button">{{ __('admin.add_group') }}</button></label>
                        <div class="layui-input-block">

                        </div>
                    </div>


        </div>

            </div>
        </div>

                <div class="layui-form-item center">
                    <div class="layui-input-block">

                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="">{{ __('admin.btn_save') }}</button>
                        <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
                    </div>
                </div>
    </form>

</div>
@include('../../../application/admin/view/public/foot')

<script type="text/javascript">
    var template_select='@foreach($templates as $vo)<option value="{{ $vo }}">{{ $vo }}</option>@endforeach';

    layui.use(['form','layer','upload'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
            , upload = layui.upload;


        upload.render({
            elem: '.layui-upload'
            ,url: "{{ url('domain/import') }}"
            ,method: 'post'
            ,exts:'txt'
            ,before: function(input) {
                layer.msg("{{ __('admin.upload_ing') }}", {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                    return false;
                }
                location.reload();
            }
        });

        $('.j-player-add').on('click',function(){
            arr_len++;
            var tpl='<div class="layui-form-item" ><label class="layui-form-label">{{ __('admin.website') }}：'+arr_len+'</label><div class="layui-input-inline w150"><input type="text" name="domain[site_url][]" class="layui-input" placeholder="{{ __('admin.domain') }}" ></div>&nbsp;<div class="layui-input-inline w150"><input type="text" name="domain[site_name][]" class="layui-input" placeholder="{{ __('admin.site_name') }}"></div>&nbsp;<div class="layui-input-inline w150"><input type="text" name="domain[site_keywords][]" class="layui-input" placeholder="{{ __('admin.keywords') }}" ></div>&nbsp;<div class="layui-input-inline w150"><input type="text" name="domain[site_description][]" class="layui-input" placeholder="{{ __('admin.description') }}" ></div>&nbsp;<div class="layui-input-inline w150"><select name="domain[template_dir][]"><option value="no">{{ __('admin.select_template') }}.</option>'+template_select+'</select></div><div class="layui-input-inline w150"><input type="text" name="domain[html_dir][]" class="layui-input" placeholder="{{ __('admin.tpl_dir') }}" ></div><div class="layui-input-inline w150"><input type="text" name="domain[ads_dir][]" class="layui-input" placeholder="{{ __('admin.ads_dir') }}" ></div><div><a href="javascript:void(0)" class="j-editor-remove">{{ __('admin.del') }}</a>&nbsp;</div></div>';
            $("#domain_list").append(tpl);

            form.render('select');
        });

        if(arr_len==0) {
            $('.j-player-add').click();
        }
    });
    
</script>

</body>
</html>