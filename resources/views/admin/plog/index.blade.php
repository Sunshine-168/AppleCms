@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="{{ route('admin.plog.index') }}">
                <div class="layui-input-inline w150">
                    <select name="type">
                        <option value="">{{ __('admin.select_genre') }}</option>
                        <option value="1" @selected(($param['type'] ?? '') === '1')>{{ __('admin.admin/plog/points_recharge') }}</option>
                        <option value="2" @selected(($param['type'] ?? '') === '2')>{{ __('admin.admin/plog/reg_promote') }}</option>
                        <option value="3" @selected(($param['type'] ?? '') === '3')>{{ __('admin.admin/plog/visit_promote') }}</option>
                        <option value="4" @selected(($param['type'] ?? '') === '4')>{{ __('admin.one_level_distribution') }}</option>
                        <option value="5" @selected(($param['type'] ?? '') === '5')>{{ __('admin.two_level_distribution') }}</option>
                        <option value="6" @selected(($param['type'] ?? '') === '6')>{{ __('admin.three_level_distribution') }}</option>
                        <option value="7" @selected(($param['type'] ?? '') === '7')>{{ __('admin.admin/plog/points_upgrade') }}</option>
                        <option value="8" @selected(($param['type'] ?? '') === '8')>{{ __('admin.admin/plog/points_buy') }}</option>
                        <option value="9" @selected(($param['type'] ?? '') === '9')>{{ __('admin.admin/plog/points_withdrawal') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('admin.btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ route('admin.plog.del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
            <a data-href="{{ route('admin.plog.del', ['ids' => 1, 'all' => 1]) }}" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="80">{{ __('admin.id') }}</th>
                <th width="100">{{ __('admin.user') }}</th>
                <th width="50">{{ __('admin.genre') }}</th>
                <th width="50">{{ __('admin.points') }}</th>
                <th width="200">{{ __('admin.remarks') }}</th>
                <th width="140">{{ __('admin.admin/plog/log_time') }}</th>
                <th width="50">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $vo.plog_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{{ $vo.plog_id }}</td>
                <td>[{{ $vo.user_id }}]{{ $vo->user->user_name ?? '' }}</td>
                <td>{{ mac_get_plog_type_text($vo.plog_type) }}</td>
                <td>@if(in_array((int) $vo.plog_type, [1, 2, 3, 4, 5, 6], true))+@else-@endif{{ $vo.plog_points }}</td>
                <td>{{ $vo.plog_remarks }}</td>
                <td>{{ mac_day($vo.plog_time, 'color') }}</td>
                <td>
                    <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.plog.del', ['ids' => $vo['plog_id']]) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                </td>
            </tr>
            @endforeach
            @if($list->isEmpty())
                <tr><td colspan="8" class="center">{{ __('admin.empty_data') }}</td></tr>
            @endif
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = @json(route('admin.plog.index', $param));
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage, layer = layui.layer;

        laypage.render({
            elem: 'pages'
            ,count: {{ $total }}
            ,limit: {{ $limit }}
            ,curr: {{ $page }}
            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj,first){
                if(!first){
                    location.href = curUrl.replace('%7Bpage%7D',obj.curr).replace('%7Blimit%7D',obj.limit);
                }
            }
        });
    });
</script>
</body>
</html>