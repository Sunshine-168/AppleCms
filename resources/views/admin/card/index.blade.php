@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" id="searchForm" action="{{ route('admin.card.index') }}">
                <div class="layui-input-inline w150">
                    <select name="sale_status">
                        <option value="">{{ __('admin.select_sale_status') }}</option>
                        <option value="0" @selected(($param['sale_status'] ?? '') === '0')>{{ __('admin.not_sale') }}</option>
                        <option value="1" @selected(($param['sale_status'] ?? '') === '1')>{{ __('admin.sold') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="use_status">
                        <option value="">{{ __('admin.select_use_status') }}</option>
                        <option value="0" @selected(($param['use_status'] ?? '') === '0')>{{ __('admin.not_used') }}</option>
                        <option value="1" @selected(($param['use_status'] ?? '') === '1')>{{ __('admin.used') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="time">
                        <option value="">{{ __('admin.select_time') }}</option>
                        <option value="1" @selected(($param['time'] ?? '') === '1')>{{ __('admin.the_last_time') }}</option>
                        <option value="0" @selected(($param['time'] ?? '') === '0')>{{ __('admin.that_day') }}</option>
                        <option value="7" @selected(($param['time'] ?? '') === '7')>{{ __('admin.in_a_week') }}</option>
                        <option value="30" @selected(($param['time'] ?? '') === '30')>{{ __('admin.in_a_month') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('admin.btn_search') }}</button>
                <button class="layui-btn mgl-20" type="button" id="btnExport">{{ __('admin.export') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ route('admin.card.info') }}" class="layui-btn layui-btn-primary j-iframe" data-width="600px" data-height="400px"><i class="layui-icon">&#xe654;</i>{{ __('admin.add') }}</a>
            <a data-href="{{ route('admin.card.del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
            <a data-href="{{ route('admin.card.del', ['ids' => 1, 'all' => 1]) }}" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="80">{{ __('admin.id') }}</th>
                <th width="150">{{ __('admin.card_no') }}</th>
                <th width="100">{{ __('admin.pass') }}</th>
                <th width="100">{{ __('admin.money') }}</th>
                <th width="100">{{ __('admin.points') }}</th>
                <th width="100">{{ __('admin.add_time') }}</th>
                <th width="100">{{ __('admin.user') }}</th>
                <th width="150">{{ __('admin.use_time') }}</th>
                <th width="50">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($list as $vo)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $vo.card_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td>{{ $vo.card_id }}</td>
                    <td>{{ $vo.card_no }}</td>
                    <td>{{ $vo.card_pwd }}</td>
                    <td>{{ $vo.card_money }}</td>
                    <td>{{ $vo.card_points }}</td>
                    <td>{{ mac_day($vo.card_add_time, 'color') }}</td>
                    <td>{{ $vo.user_id }}、{{ $vo->user->user_name ?? '' }}</td>
                    <td>{{ $vo.card_use_time ? mac_day($vo.card_use_time, 'color') : '' }}</td>
                    <td>
                        <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.card.del', ['ids' => $vo['card_id']]) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="center">{{ __('admin.empty_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
    <iframe id="if" width="0" height="0"></iframe>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = @json(route('admin.card.index', $param));
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

        $('#btnExport').click(function(){
            var par = $('#searchForm').serialize() + '&export=1';

            $('#if').attr('src', @json(route('admin.card.index')) + '?' + par);
        });
    });
</script>
</body>
</html>