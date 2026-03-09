@include('../../../application/admin/view/public/head')

<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">

        <div class="layui-form-item">
            <label class="layui-form-label">url：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $info.url }}" lay-verify="url" placeholder="" id="url" name="url">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">title：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ $info.title }}" lay-verify="title" placeholder="" id="title" name="title">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">other：</label>
            <div class="layui-input-block">
                @foreach($$info.data as $vo)
                    {{ $key }}<input type="text" class="layui-input" value="{{ $vo }}"><br>
                @endforeach
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">

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
            link_name: function (value) {
                if (value == "") {
                    return "{{ __('admin.name_empty') }}";
                }
            },
            link_url: function (value) {
                if (value == "") {
                    return "{{ __('admin.link_empty') }}";
                }
            }
        });


    });
</script>

</body>
</html>