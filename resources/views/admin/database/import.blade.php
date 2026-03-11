@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <ul class="layui-tab-title mb10">
            <li ><a href="{{ url('index') }}">{{ __('admin.admin/database/backup_db') }}</a></li>
            <li class="layui-this"><a href="{{ url('index') }}?group=import">{{ __('admin.admin/database/import_db') }}</a></li>
        </ul>
    </div>

    <form id="pageListForm" class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <thead>
            <tr>
                <th>{{ __('admin.admin/database/backup_name') }}</th>
                <th>{{ __('admin.admin/database/backup_num') }}</th>
                <th>{{ __('admin.admin/database/backup_zip') }}</th>
                <th>{{ __('admin.admin/database/backup_size') }}</th>
                <th>{{ __('admin.admin/database/backup_time') }}</th>
                <th width="80">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list as $vo)
            <tr>
                <td>{{ date('Ymd-His', $vo['time']) }}</td>
                <td>{{ $vo['part'] }}</td>
                <td>{{ $vo['compress'] }}</td>
                <td>{{ round($vo['size']/1024, 2) }} K</td>
                <td>{{ date('Y-m-d H:i:s', $vo['time']) }}</td>
                <td>
                    <div class="layui-btn-group">
                        <a data-href="{{ url('import?id='.strtotime($key)) }}" class="layui-badge-rim layui-btn-small j-ajax" confirm="{{ __('admin.admin/database/import_confirm') }}">{{ __('admin.admin/database/import') }}</a>
                        <a data-href="{{ url('del?id='.strtotime($key)) }}" class="layui-badge-rim layui-btn-small j-tr-del">{{ __('admin.del') }}</a>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </form>

</div>
@include('../../../application/admin/view/public/foot')


<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;



    });
</script>
</body>
</html>