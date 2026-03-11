@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="{{ route('admin.cash.index') }}">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value="">{{ __('admin.select_status') }}</option>
                        <option value="0" @selected(($param['status'] ?? '') === '0')>{{ __('admin.reviewed_not') }}</option>
                        <option value="1" @selected(($param['status'] ?? '') === '1')>{{ __('admin.reviewed') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('admin.btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ route('admin.cash.del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
            <a data-href="{{ route('admin.cash.del', ['ids' => 1, 'all' => 1]) }}" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>
            <a data-href="{{ route('admin.cash.audit') }}" class="layui-btn layui-btn-primary j-page-btns confirm" confirm="{{ __('admin.audit_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.audit') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="50">{{ __('admin.id') }}</th>
                <th width="50">{{ __('admin.user') }}</th>
                <th width="50">{{ __('admin.status') }}</th>
                <th width="50">{{ __('admin.points') }}</th>
                <th width="50">{{ __('admin.money') }}</th>
                <th width="50">{{ __('admin.bank') }}</th>
                <th width="50">{{ __('admin.account') }}</th>
                <th width="50">{{ __('admin.name') }}</th>
                <th width="100">{{ __('admin.remarks') }}</th>
                <th width="100">{{ __('admin.time') }}</th>
                <th width="100">{{ __('admin.audit_time') }}</th>
                <th width="50">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($list as $vo)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $vo.cash_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td>{{ $vo.cash_id }}</td>
                    <td>[{{ $vo.user_id }}]{{ $vo->user->user_name ?? '' }}</td>
                    <td>@if((int) $vo.cash_status === 1)<span class="layui-badge layui-bg-green">{{ __('admin.reviewed') }}</span>@else<span class="layui-badge">{{ __('admin.reviewed_not') }}</span>@endif</td>
                    <td>{{ $vo.cash_points }}</td>
                    <td>{{ $vo.cash_money }}</td>
                    <td>{{ $vo.cash_bank_name }}</td>
                    <td>{{ $vo.cash_bank_no }}</td>
                    <td>{{ $vo.cash_payee_name }}</td>
                    <td>{{ $vo.cash_remarks }}</td>
                    <td>{{ mac_day($vo.cash_time, 'color') }}</td>
                    <td>{{ mac_day($vo.cash_time_audit, 'color') }}</td>
                    <td>
                        <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.cash.del', ['ids' => $vo['cash_id']]) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                        @if((int) $vo.cash_status !== 1)
                            <a class="layui-badge-rim j-ajax" confirm="{{ __('admin.audit_confirm') }}" data-href="{{ route('admin.cash.audit', ['ids' => $vo['cash_id']]) }}" href="javascript:;" title="{{ __('admin.audit') }}">{{ __('admin.audit') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="center">{{ __('admin.empty_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = @json(route('admin.cash.index', $param));
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