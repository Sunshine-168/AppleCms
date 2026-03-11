@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form " method="post" action="{{ url('data') }}">
                <div class="layui-input-inline w100">
                    <select name="status">
                        <option value="">{{ __('admin.select_status') }}</option>
                        <option value="0" @if(condition="$param['status'] == '0'")selected @endif>{{ __('admin.reviewed_not') }}</option>
                        <option value="1" @if(condition="$param['status'] == '1'")selected @endif>{{ __('admin.reviewed') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="type">
                        <option value="">{{ __('admin.select_reply_status') }}</option>
                        <option value="1" @if(condition="$param['reply'] == '1'")selected @endif>{{ __('admin.reply_not') }}</option>
                        <option value="2" @if(condition="$param['reply'] == '2'")selected @endif>{{ __('admin.reply_yes') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="type">
                        <option value="">{{ __('admin.select_genre') }}</option>
                        <option value="1" @if(condition="$param['type'] == '1'")selected @endif>{{ __('admin.gbook') }}</option>
                        <option value="2" @if(condition="$param['type'] == '2'")selected @endif>{{ __('admin.report') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('admin.btn_search') }}</button>
            </form>
        </div>
        <div class="layui-btn-group">
            <a data-href="{{ url('del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
            <a data-href="{{ url('index/select') }}?tab=gbook&col=gbook_status&tpl=select_status&url=gbook/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ __('admin.status') }}</a>
            <a data-href="{{ url('del') }}?all=1" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>
        </div>
    </div>


        <form class="layui-form" method="post" id="pageListForm" >
            <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="60">{{ __('admin.id') }}</th>
                <th width="60">{{ __('admin.status') }}</th>
                <th width="60">{{ __('admin.genre') }}</th>
                <th >{{ __('admin.gbook') }}</th>
                <th >{{ __('admin.report') }}</th>
                <th width="100">{{ __('admin.opt') }}</th>
            </tr>
            </thead>

            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $vo.gbook_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{{ $vo.gbook_id }}</td>
                <td>@if($vo.gbook_status == 0)<span class="layui-badge">{{ __('admin.reviewed_not') }}</span>{else}<span class="layui-badge layui-bg-green">{{ __('admin.reviewed') }}</span>@endif</td>
                <td>@if($vo.gbook_rid == 0){{ __('admin.gbook') }}@else{{ __('admin.report') }}@endif</td>
                <td>
                    <div class="c-999 f-12">
                        <u style="cursor:pointer" class="text-primary">{{ $vo.gbook_name|htmlspecialchars }}：</u>
                        <time>【{{ $vo.gbook_time|mac_day='color' }}】</time>
                        <span class="ml-20">ip：【{{ $vo.gbook_ip|long2ip }}】</span>
                    </div>
                    <div class="f-12 c-999">
                        <span class="ml-20">{{ __('admin.status') }}：</span>
                        {{ __('admin.gbook') }}：{{ $vo.gbook_content|htmlspecialchars }}
                    </div>
                </td>
                <td>
                    <div class="c-999 f-12">
                        {{ __('admin.reply_time') }}：{{ $vo.gbook_reply_time|mac_day='color' }}
                    </div>
                    <div class="f-12 c-999">
                        {{ __('admin.reply') }}：{{ $vo.gbook_reply|htmlspecialchars }}
                    </div>
                    <div> </div>
                </td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="{{ url('info?id='.$vo['gbook_id']) }}" href="javascript:;" title="{{ __('admin.reply') }}">{{ __('admin.reply') }}</a>
                    <a class="layui-badge-rim j-tr-del" data-href="{{ url('del?ids='.$vo['gbook_id']) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <div id="pages" class="center"></div>

    </form>
</div>

@include('../../../application/admin/view/public/foot')

<script type="text/javascript">
    var curUrl="{{ url('gbook/data',$param) }}";
    layui.use(['laypage', 'layer','form'], function() {
        var laypage = layui.laypage
                , layer = layui.layer,
                form = layui.form;

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