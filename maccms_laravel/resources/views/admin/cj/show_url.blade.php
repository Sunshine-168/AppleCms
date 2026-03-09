@include('admin.public.head')
<div class="page-container p10">
    <fieldset class="layui-elem-field">
        <legend>{{ __('admin.base_info') }}</legend>
        <div class="layui-field-box">
            @forelse($urls as $vo)
            <p>{{ $vo }}</p>
            <hr>
            @empty
            <p>未生成可预览的网址。</p>
            @endforelse
        </div>
    </fieldset>
</div>
@include('admin.public.foot')

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;



    });

</script>
