@include('admin.public.head')

<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.urlsend.index') }}">
        @csrf
        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                @foreach($extends['ext_list'] as $key => $label)
                    <li data-key="{{ $key }}" lay-id="urlsend_{{ $key }}" class="{{ $loop->first ? 'layui-this' : '' }}">{{ $label }}{{ __('admin.config') }}</li>
                @endforeach
            </ul>
            <div class="layui-tab-content">
                {!! $extends['ext_html'] !!}
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>

    <form class="layui-form layui-form-pane" method="get" action="{{ route('admin.urlsend.push') }}" target="_blank" id="form_post">
        <blockquote class="layui-elem-quote">
            {{ __('admin/urlsend/tip2') }}{{ $siteUrl }}<br>
        </blockquote>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin/urlsend/send_genre') }}：</label>
            <div class="layui-input-inline">
                <select class="w150" id="ac" name="ac">
                    @foreach($extends['ext_list'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin/urlsend/send_range') }}：</label>
            <div class="layui-input-inline w300">
                <input type="radio" name="range" value="0" title="{{ __('admin/urlsend/add_update') }}" checked>
                <input type="radio" name="range" value="1" title="{{ __('admin/urlsend/add') }}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin/urlsend/page_send_num') }}：</label>
            <div class="layui-input-inline w200">
                <input type="text" name="limit" id="limit" value="50" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin/urlsend/start_page') }}：</label>
            <div class="layui-input-inline w200">
                <input type="text" name="page" id="page" value="1" class="layui-input">
            </div>
        </div>

        <hr class="layui-bg-gray">
        <input type="button" value="{{ __('that_day') }}{{ __('vod') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=1');">
        <input type="button" value="{{ __('all') }}{{ __('vod') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=1');">

        <input type="button" value="{{ __('that_day') }}{{ __('art') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=2');">
        <input type="button" value="{{ __('all') }}{{ __('art') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=2');">
        <input type="button" value="{{ __('that_day') }}{{ __('topic') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=3');">
        <input type="button" value="{{ __('all') }}{{ __('topic') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=3');">
        <hr class="layui-bg-gray">
        <input type="button" value="{{ __('that_day') }}{{ __('actor') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=8');">
        <input type="button" value="{{ __('all') }}{{ __('actor') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=8');">
        <input type="button" value="{{ __('that_day') }}{{ __('role') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=9');">
        <input type="button" value="{{ __('all') }}{{ __('role') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=9');">
        <hr class="layui-bg-gray">
        <input type="button" value="{{ __('that_day') }}{{ __('website') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=11');">
        <input type="button" value="{{ __('all') }}{{ __('website') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=11');">
        <input type="button" value="{{ __('that_day') }}{{ __('manga') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=12');">
        <input type="button" value="{{ __('all') }}{{ __('manga') }}" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=12');">

        @if(!empty($urlsendBreakBaiduPush))
            <hr class="layui-bg-gray">
            <a href="{{ $urlsendBreakBaiduPush }}" target="_blank" class="layui-btn layui-btn-danger">【{{ __('admin/urlsend/in_break_point_exec') }} - Baidu】</a>
        @endif
        @if(!empty($urlsendBreakBaidufastPush))
            <a href="{{ $urlsendBreakBaidufastPush }}" target="_blank" class="layui-btn layui-btn-danger">【{{ __('admin/urlsend/in_break_point_exec') }} - Baidufast】</a>
        @endif
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript" src="{{ asset('static/js/jquery.cookie.js') }}"></script>
<script type="text/javascript">
    layui.use(['element'], function () {
        var element = layui.element;
        element.on('tab(tb1)', function () {
            $.cookie('urlsend_tab', this.getAttribute('lay-id'));
        });
        if ($.cookie('urlsend_tab') != null) {
            element.tabChange('tb1', $.cookie('urlsend_tab'));
        }
    });

    function postPush(extraQuery) {
        var limit = $('#limit').val();
        var page = $('#page').val();
        var ac = $('#ac').val();
        var range = $('input[name="range"]:checked').val();
        var action = "{{ route('admin.urlsend.push') }}" + '?' + 'ac=' + encodeURIComponent(ac) + '&limit=' + encodeURIComponent(limit) + '&page=' + encodeURIComponent(page) + '&range=' + encodeURIComponent(range) + '&' + extraQuery;
        $('#form_post').attr('action', action).submit();
    }
</script>
