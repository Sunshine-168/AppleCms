@include('admin.public.head')
<div class="page-container p10">
    <div class="my-toolbar-box">
        <div class="center mb10">
            <form class="layui-form" method="get" action="{{ route('admin.user.reward') }}">
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value="">{{ __('admin/user/reward/select_level') }}</option>
                        <option value="1" @selected(($param['level'] ?? '') === '1')>{{ __('admin/user/reward/one_distribution') }}</option>
                        <option value="2" @selected(($param['level'] ?? '') === '2')>{{ __('admin/user/reward/two_distribution') }}</option>
                        <option value="3" @selected(($param['level'] ?? '') === '3')>{{ __('admin/user/reward/three_distribution') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] ?? '' }}">
                </div>
                <input type="hidden" name="uid" value="{{ $param['uid'] ?? 0 }}">
                <button class="layui-btn mgl-20 j-search" >{{ __('btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a class="layui-btn">{{ __('admin/user/reward/one_people_num') }}【{{ $data['level_cc_1'] }}】{{ __('admin/user/reward/total_commission_points') }}【{{ $data['points_cc_1'] }}】</a>
            <a class="layui-btn layui-btn-normal">{{ __('admin/user/reward/two_people_num') }}【{{ $data['level_cc_2'] }}】{{ __('admin/user/reward/total_commission_points') }}【{{ $data['points_cc_2'] }}】</a>
            <a class="layui-btn layui-btn-warm">{{ __('admin/user/reward/three_people_num') }}【{{ $data['level_cc_3'] }}】{{ __('admin/user/reward/total_commission_points') }}【{{ $data['points_cc_3'] }}】</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100">{{ __('id') }}</th>
                <th>{{ __('name') }}</th>
                <th width="120">{{ __('group') }}</th>
                <th width="120">{{ __('status') }}</th>
                <th width="120">{{ __('admin/user/reward/distribution_level') }}</th>
                <th width="130">{{ __('reg_time') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($list as $vo)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $vo.user_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td>{{ $vo.user_id }}</td>
                    <td>{{ $vo.user_name }}</td>
                    <td>{{ $vo->group->group_name ?? '' }}</td>
                    <td>@if((int) $vo.user_status === 1)<span class="layui-badge layui-bg-green">{{ __('open') }}</span>@else<span class="layui-badge">{{ __('close') }}</span>@endif</td>
                    <td>
                        @if((int) $vo.user_pid === (int) ($param['uid'] ?? 0))
                            {{ __('admin/user/reward/one_distribution') }}
                        @elseif((int) $vo.user_pid_2 === (int) ($param['uid'] ?? 0))
                            {{ __('admin/user/reward/two_distribution') }}
                        @else
                            {{ __('admin/user/reward/three_distribution') }}
                        @endif
                    </td>
                    <td>{{ mac_day($vo.user_reg_time, 'color') }}</td>
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
    var curUrl = @json(route('admin.user.reward', $param));
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