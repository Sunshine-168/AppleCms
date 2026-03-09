@include('admin.public.head')
<div class="page-container p10">

    <form class="layui-form " method="post" action="{{ route('admin.images.sync') }}">
        @csrf
        <input type="hidden" name="tab" value="{{ $tab }}">
        <fieldset class="layui-elem-field">
            <legend>{{ __('admin.admin/images/sync_range') }}</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                        <div class="layui-input-inline" style="width: 110px;">
                            <input type="radio" checked value="1" name="range" title="{{ __('admin.all') }}">
                        </div>
                        <div class="layui-input-inline" style="width: 110px;">
                            <input type="radio" value="2" name="range" title="{{ __('admin.admin/images/date') }}">
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <input type="text" name="date" required  placeholder="" autocomplete="off" class="layui-input" value="{{ date('Y-m-d') }}">
                        </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend>{{ __('admin.admin/images/sync_option') }}</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio"  value="0" name="opt" title="{{ __('admin.all') }}">
                    </div>
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" value="1" name="opt" title="{{ __('admin.not_pic_sync_err') }}">
                    </div>
                    <div class="layui-input-inline" style="width: 120px;">
                        <input type="radio" checked value="2" name="opt" title="{{ __('admin.not_pic_sync_today_err') }}">
                    </div>
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" value="3" name="opt" title="{{ __('admin.pic_err') }}">
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend>{{ __('admin.admin/images/opt/tip1') }}</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" checked value="1" name="col" title="{{ __('admin.admin/images/opt/pic') }}">
                    </div>
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" value="2" name="col" title="{{ __('admin.admin/images/pic_content') }}">
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend>{{ __('admin.admin/images/opt/tip2') }}</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="text" name="limit" required placeholder="" autocomplete="off" class="layui-input" value="10">
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="layui-form-item">
            <button type="submit" class="layui-btn btn_submit">{{ __('admin.start_exec') }}</button>
        </div>

    </form>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    layui.use(['element', 'layer'], function() {

    });

    $('.btn_submit').click(function(){
        $('form').submit();
    })

</script>