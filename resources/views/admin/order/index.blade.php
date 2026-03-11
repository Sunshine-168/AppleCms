@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="{{ route('admin.order.index') }}">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value="">{{ __('admin.select_order_status') }}</option>
                        <option value="0" @selected(($param['status'] ?? '') === '0')>{{ __('admin.not_paid') }}</option>
                        <option value="1" @selected(($param['status'] ?? '') === '1')>{{ __('admin.paid') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('admin.btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ route('admin.order.del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
            <a data-href="{{ route('admin.order.del', ['ids' => 1, 'all' => 1]) }}" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="50">{{ __('admin.id') }}</th>
                <th width="100">{{ __('admin.admin/order/order_no') }}</th>
                <th width="80">{{ __('admin.admin/order/order_money') }}</th>
                <th width="80">{{ __('admin.admin/order/order_status') }}</th>
                <th width="130">{{ __('admin.admin/order/order_time') }}</th>
                <th width="100">{{ __('admin.admin/order/pay_type') }}</th>
                <th width="130">{{ __('admin.admin/order/pay_time') }}</th>
                <th width="80">{{ __('admin.user') }}</th>
                <th width="50">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $vo.order_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{{ $vo.order_id }}</td>
                <td>{{ $vo.order_code }}</td>
                <td>{{ $vo.order_price }}</td>
                <td>{{ mac_get_order_status_text($vo.order_status) }}</td>
                <td>{{ mac_day($vo.order_time, 'color') }}</td>
                <td>{{ $vo.order_pay_type ?: $vo.order_type }}</td>
                <td>{{ mac_day($vo.order_pay_time, 'color') }}</td>
                <td>{{ $vo.user_id }}、{{ $vo->user->user_name ?? '' }}</td>
                <td>
                    <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.order.del', ['ids' => $vo['order_id']]) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                </td>
            </tr>
            @endforeach
            @if($list->isEmpty())
                <tr><td colspan="10" class="center">{{ __('admin.empty_data') }}</td></tr>
            @endif
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = @json(route('admin.order.index', $param));
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