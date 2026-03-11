@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="{{ route('admin.visit.index') }}">
                <div class="layui-input-inline w150">
                    <select name="mid">
                        <option value="">{{ __('select_model') }}</option>
                        <option value="6" @selected(($param['mid'] ?? '') === '6')>{{ __('user') }}</option>
                        <option value="11" @selected(($param['mid'] ?? '') === '11')>{{ __('website') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="time">
                        <option value="">{{ __('select_time') }}</option>
                        <option value="0" @selected(($param['time'] ?? '') === '0')>{{ __('that_day') }}</option>
                        <option value="7" @selected(($param['time'] ?? '') === '7')>{{ __('in_a_week') }}</option>
                        <option value="30" @selected(($param['time'] ?? '') === '30')>{{ __('in_a_month') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ route('admin.visit.del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('del') }}</a>
            <a data-href="{{ route('admin.visit.del', ['ids' => 1, 'all' => 1]) }}" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('clear') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="80">{{ __('id') }}</th>
                <th width="100">{{ __('user') }}</th>
                <th width="50">{{ __('model') }}</th>
                <th>{{ __('from') }}</th>
                <th width="130">{{ __('time') }}</th>
                <th width="50">{{ __('opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($list as $vo)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $vo.visit_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td>{{ $vo.visit_id }}</td>
                    <td>[{{ $vo.user_id }}]{{ $vo->user->user_name ?? '' }}</td>
                    <td>{{ mac_get_mid_text($vo.visit_mid) }}</td>
                    <td>{{ $vo.visit_ly }}</td>
                    <td>{{ mac_day($vo.visit_time, 'color') }}</td>
                    <td>
                        <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.visit.del', ['ids' => $vo['visit_id']]) }}" href="javascript:;" title="{{ __('del') }}">{{ __('del') }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="center">{{ __('empty_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = @json(route('admin.visit.index', $param));
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