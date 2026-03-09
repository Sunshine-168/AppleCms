@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box">
        <div class="center mb10">
            <form class="layui-form" method="get" action="{{ route('admin.user.index') }}">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value="">{{ __('select_status') }}</option>
                        <option value="0" @selected(($param['status'] ?? '') === '0')>{{ __('reviewed_not') }}</option>
                        <option value="1" @selected(($param['status'] ?? '') === '1')>{{ __('reviewed') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="group">
                        <option value="">{{ __('select_group') }}</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->group_id }}" @selected((string) ($param['group'] ?? '') === (string) $group->group_id)>{{ $group->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <button class="layui-btn mgl-20 j-search">{{ __('btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ route('admin.user.info') }}" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i>{{ __('add') }}</a>
            <a data-href="{{ route('admin.user.del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('del') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100">{{ __('id') }}</th>
                <th>{{ __('name') }}</th>
                <th width="100">{{ __('group') }}</th>
                <th width="80">{{ __('status') }}</th>
                <th width="80">{{ __('points') }}</th>
                <th width="130">{{ __('last_login_time') }}</th>
                <th width="130">{{ __('last_login_ip') }}</th>
                <th width="80">{{ __('login_num') }}</th>
                <th width="260">{{ __('related_data') }}</th>
                <th width="100">{{ __('opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($list as $user)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $user->user_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td>{{ $user->user_id }}</td>
                    <td>{{ $user->user_name }}</td>
                    <td>{{ $user->group->group_name ?? '' }}</td>
                    <td>
                        @if((int) $user->user_status === 1)
                            <span class="layui-badge layui-bg-green">{{ __('open') }}</span>
                        @else
                            <span class="layui-badge">{{ __('close') }}</span>
                        @endif
                    </td>
                    <td>{{ $user->user_points }}</td>
                    <td>{{ mac_day($user->user_login_time, 'color') }}</td>
                    <td>{{ $user->user_login_ip ? long2ip($user->user_login_ip) : '' }}</td>
                    <td>{{ $user->user_login_num }}</td>
                    <td>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.order.index', ['uid' => $user->user_id]) }}" href="javascript:;" title="{{ __('admin/user/order_record') }}">{{ __('admin/user/order_record') }}</a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.visit.index', ['uid' => $user->user_id]) }}" href="javascript:;" title="{{ __('admin/user/visit_record') }}">{{ __('admin/user/visit_record') }}</a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.plog.index', ['uid' => $user->user_id]) }}" href="javascript:;" title="{{ __('admin/user/point_record') }}">{{ __('admin/user/point_record') }}</a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.cash.index', ['uid' => $user->user_id]) }}" href="javascript:;" title="{{ __('admin/user/withdrawals_record') }}">{{ __('admin/user/withdrawals_record') }}</a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.user.reward', ['uid' => $user->user_id]) }}" href="javascript:;" title="{{ __('admin/user/three_distribution') }}">{{ __('admin/user/three_distribution') }}</a>
                    </td>
                    <td>
                        <a class="layui-badge-rim j-iframe" data-href="{{ route('admin.user.info', ['id' => $user->user_id]) }}" href="javascript:;" title="{{ __('edit') }}">{{ __('edit') }}</a>
                        <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.user.del', ['ids' => $user->user_id]) }}" href="javascript:;" title="{{ __('del') }}">{{ __('del') }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="center">{{ __('empty_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>
        <div id="pages" class="center"></div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = @json(route('admin.user.index', $param));
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage, layer = layui.layer;

        laypage.render({
            elem: 'pages'
            ,count: {{ $total }}
            ,limit: {{ $limit }}
            ,curr: {{ $page }}
            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj, first){
                if(!first){
                    location.href = curUrl.replace('%7Bpage%7D', obj.curr).replace('%7Blimit%7D', obj.limit);
                }
            }
        });
    });
</script>
