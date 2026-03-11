@include('admin.public.head')
<div class="page-container p10">
    <div class="layui-textarea" style="height:auto; min-height:420px; line-height:22px;">{!! implode('<br>', $logs) !!}</div>
    @if(!empty($nextUrl))
        <div style="margin-top: 12px;">
            <a href="{{ $nextUrl }}" class="layui-btn">下一页继续推送</a>
        </div>
        <script type="text/javascript">
            setTimeout(function () {
                location.href = @json($nextUrl);
            }, 3000);
        </script>
    @endif
</div>
@include('admin.public.foot')
