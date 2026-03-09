@include('admin.public.head')
<div class="page-container p10">
    <div class="update">
        <h1 class="layui-font-20">{{ $title }}</h1>
        <textarea rows="25" class="layui-textarea" readonly>@foreach($logs as $line){{ $line }}
@endforeach</textarea>
        @if($nextUrl)
            <div style="margin-top: 12px;">
                <a href="{{ $nextUrl }}" class="layui-btn">{{ $nextText ?? '下一步' }}</a>
            </div>
        @endif
    </div>
</div>
@include('admin.public.foot')
