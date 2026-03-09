@include('admin.public.head')

<div class="page-container">
    <form class="layui-form layui-form-pane" method="get" action="">
        <input name="ck" value="1" type="hidden">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this">{{ __('admin.admin/safety/data_inspect') }}</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">

                    <div class="layui-input-block" >
                        <blockquote class="layui-elem-quote layui-quote-nm">
                            {{ __('admin.admin/safety/data_inspect_tip') }}
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" >{{ __('admin.admin/safety/exec') }}</button>
            </div>
        </div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript">
    $(function(){
        $('.layui-btn').click(function(){
            layer.msg("{{ __('admin.wait_submit') }}");
        });
    });
</script>
