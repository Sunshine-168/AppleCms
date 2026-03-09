@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <ul class="layui-tab-title mb10">
            <li class="layui-this"><a href="{{ url('index') }}">{{ __('admin.admin/database/backup_db') }}</a></li>
            <li><a href="{{ url('index') }}?group=import">{{ __('admin.admin/database/import_db') }}</a></li>
        </ul>

        <div class="layui-btn-group">
            <a data-href="{{ url('export') }}" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe62d;</i>{{ __('admin.admin/database/backup_db') }}</a>
            <a data-href="{{ url('optimize') }}" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe631;</i>{{ __('admin.admin/database/optimize_db') }}</a>
            <a data-href="{{ url('repair') }}" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe60c;</i>{{ __('admin.admin/database/repair_db') }}</a>
        </div>
    </div>

    <form id="pageListForm" class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th>{{ __('admin.admin/database/table') }}</th>
                <th>{{ __('admin.admin/database/count') }}</th>
                <th>{{ __('admin.admin/database/size') }}</th>
                <th>{{ __('admin.admin/database/redundancy') }}</th>
                <th>{{ __('admin.remarks') }}</th>
                <th width="90">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{{ $vo['Name'] }}" lay-skin="primary"></td>
                <td>{{ $vo['Name'] }}</td>
                <td>{{ $vo['Rows'] }}</td>
                <td>{{ $vo['Data_length']/1024|round=###,2 }} kb</td>
                <td>{{ $vo['Data_free']/1024|round=###,2 }} kb</td>
                <td>{{ $vo['Comment'] }}</td>
                <td>
                        <a data-href="{{ url('optimize?ids='.$vo['Name']) }}" class="layui-badge-rim j-ajax">{{ __('admin.admin/database/optimize') }}</a>
                        <a data-href="{{ url('repair?ids='.$vo['Name']) }}" class="layui-badge-rim  j-ajax">{{ __('admin.admin/database/repair') }}</a>
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