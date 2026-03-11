@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="layui-btn-group">
            <a data-href="{{ url('info') }}" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i>{{ __('admin.add') }}</a>
            <a data-href="{{ url('del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100">{{ __('admin.id') }}</th>
                <th >{{ __('admin.name') }}</th>
                <th width="100">{{ __('admin.status') }}</th>
                <th width="100">{{ __('admin.admin/group/pack_day') }}</th>
                <th width="100">{{ __('admin.admin/group/pack_week') }}</th>
                <th width="100">{{ __('admin.admin/group/pack_month') }}</th>
                <th width="100">{{ __('admin.admin/group/pack_year') }}</th>
                <th width="100">{{ __('admin.opt') }}</th>
            </tr>
            </thead>

            @foreach($list as $vo)
            <tr>
                <td>
                    @if(condition="$vo['group_id'] > 2")
                    <input type="checkbox" name="ids[]" value="{{ $vo.group_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary">
                    @endif
                </td>
                <td>{{ $vo.group_id }}</td>
                <td>{{ $vo.group_name|htmlspecialchars }}</td>
                <td>
                    @if(condition="$vo['group_id'] > 2")
                    <input type="checkbox" name="status" @if(condition="$vo['group_status'] == 1")checked@endif value="{{ $vo['group_status'] }}" lay-skin="switch" lay-filter="switchStatus" lay-text="{{ __('admin.open') }}|{{ __('admin.close') }}" data-href="{{ url('field?col=group_status&ids='.$vo['group_id']) }}">
                    @endif
                </td>
                <td>{{ $vo.group_points_day }}</td>
                <td>{{ $vo.group_points_week }}</td>
                <td>{{ $vo.group_points_month }}</td>
                <td>{{ $vo.group_points_year }}</td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="{{ url('info?id='.$vo['group_id']) }}" href="javascript:;" title="{{ __('admin.edit') }}">{{ __('admin.edit') }}</a>
                    @if(condition="$vo['group_id'] > 2")
                    <a class="layui-badge-rim j-tr-del" data-href="{{ url('del?ids='.$vo['group_id']) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>

    </form>

    <blockquote class="layui-elem-quote layui-quote-nm">
        {{ __('admin.admin/group/help_tip') }}
    </blockquote>
</div>

@include('../../../application/admin/view/public/foot')

<script type="text/javascript">

    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;


    });
</script>
</body>
</html>