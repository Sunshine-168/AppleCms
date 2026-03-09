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
                    <select name="mid">
                        <option value="">{{ __('admin.select_model') }}</option>
                        <option value="1" @if(condition="$param['mid'] == '1'")selected @endif>{{ __('admin.vod') }}</option>
                        <option value="2" @if(condition="$param['mid'] == '2'")selected @endif>{{ __('admin.art') }}</option>
                        <option value="3" @if(condition="$param['mid'] == '3'")selected @endif>{{ __('admin.topic') }}</option>
                        <option value="8" @if(condition="$param['mid'] == '8'")selected @endif>{{ __('admin.actor') }}</option>
                        <option value="9" @if(condition="$param['mid'] == '9'")selected @endif>{{ __('admin.role') }}</option>
                        <option value="11" @if(condition="$param['mid'] == '11'")selected @endif>{{ __('admin.website') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="report">
                        <option value="">{{ __('admin.select_report') }}</option>
                        <option value="1" @if(condition="$param['report'] == '1'")selected @endif>{{ __('admin.report_not') }}</option>
                        <option value="2" @if(condition="$param['report'] == '2'")selected @endif>{{ __('admin.report_yes') }}</option>
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
            <a data-href="{{ url('index/select') }}?tab=comment&col=comment_status&tpl=select_status&url=comment/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ __('admin.status') }}</a>
            <a data-href="{{ url('del') }}?all=1" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>

            <a  data-href="{{ url('comment/blacklist') }}" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe63c;</i>{{ __('admin.blacklist_keywords') }}</a>
            <a  data-href="{{ url('comment/blacklist_ip') }}" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe63c;</i>{{ __('admin.blacklist_ip') }}</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm" >
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="60">{{ __('admin.id') }}</th>
                <th width="60">{{ __('admin.model') }}</th>
                <th width="60">{{ __('admin.status') }}</th>
                <th >{{ __('admin.content') }}</th>
                <th width="100">{{ __('admin.opt') }}</th>
            </tr>
            </thead>

            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $vo.comment_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{{ $vo.comment_id }}</td>
                <td>{{ $vo.comment_mid|mac_get_mid_text }}</td>
                <td>@if($vo.comment_status == 0)<span class="layui-badge">{{ __('admin.reviewed_not') }}</span>{else}<span class="layui-badge layui-bg-green">{{ __('admin.reviewed') }}</span>@endif</td>
                <td>
                    <div class="c-999 f-12">
                        <u style="cursor:pointer" class="text-primary">{{ $vo.comment_name|htmlspecialchars }}：</u>
                        <time>【{{ $vo.comment_time|mac_day='color' }}】</time>
                        <span class="ml-20">ip：【{{ $vo.comment_ip|long2ip }}】</span>
                        <span class="ml-20">{{ __('admin.up') }}：【{{ $vo.comment_up }}】</span>
                        <span class="ml-20">{{ __('admin.hate') }}：【{{ $vo.comment_down }}】</span>
                        <span class="ml-20">{{ __('admin.report') }}：【{{ $vo.comment_report }}】</span>
                        <span class="ml-20">{{ __('admin.link') }}：
                            @if(!is_array($vo.data))
                            【{{ __('admin.del_data') }}】
                            {elseif condition="$vo.comment_mid == 1"}
                            【<a target="_blank" href="{{ $vo.data|mac_url_vod_detail }}">{{ $vo.data.vod_name }}</a>】</span>
                            {elseif condition="$vo.comment_mid == 2"}
                            【<a target="_blank" href="{{ $vo.data|mac_url_art_detail }}">{{ $vo.data.art_name }}</a>】</span>
                            {elseif condition="$vo.comment_mid == 3"}
                            【<a target="_blank" href="{{ $vo.data|mac_url_topic_detail }}">{{ $vo.data.topic_name }}</a>】</span>
                            {elseif condition="$vo.comment_mid == 8"}
                            【<a target="_blank" href="{{ $vo.data|mac_url_actor_detail }}">{{ $vo.data.actor_name }}</a>】</span>
                            {elseif condition="$vo.comment_mid == 9"}
                            【<a target="_blank" href="{{ $vo.data|mac_url_role_detail }}">{{ $vo.data.role_name }}</a>】</span>
                            @endif
                    </div>
                    <div class="f-12 c-999">
                        {{ __('admin.comment') }}：{{ $vo.comment_content|htmlspecialchars }}
                    </div>
                </td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="{{ url('info?id='.$vo['comment_id']) }}" href="javascript:;" title="{{ __('admin.edit') }}">{{ __('admin.edit') }}</a>
                    <a class="layui-badge-rim j-tr-del" data-href="{{ url('del?ids='.$vo['comment_id']) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
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
    var curUrl="{{ url('comment/data',$param) }}";
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