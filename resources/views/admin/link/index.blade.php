@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <div class="my-toolbar-box">
        <div class="center mb10">
            <form class="layui-form " method="post" action="{{ url('index') }}">
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] }}">
                </div>
                <button class="layui-btn mgl-20 j-search" >{{ __('admin.btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ url('info') }}" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i>{{ __('admin.add') }}</a>
            <a data-href="{{ url('batch') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe642;</i>{{ __('admin.edit') }}</a>
            <a data-href="{{ url('del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
        <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100">{{ __('admin.id') }}</th>
                <th width="100">{{ __('admin.sort') }}</th>
                <th width="150">{{ __('admin.genre') }}</th>
                <th >{{ __('admin.name') }}</th>
                <th >{{ __('admin.url') }}</th>
                <th >{{ __('admin.logo') }}</th>
                <th width="130">{{ __('admin.opt') }}</th>
            </tr>
            </thead>

            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $vo.link_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{{ $vo.link_id }}</td>
                <td><input type="input" name="link_sort[]" value="{{ $vo.link_sort }}" class="layui-input"></td>
                <td>
                    <select name="link_type[]">
                        <option value="0" @if(condition="$vo['link_type'] == 0")selected @endif>{{ __('admin.admin/link/text_link') }}</option>
                        <option value="1" @if(condition="$vo['link_type'] == 1")selected @endif>{{ __('admin.admin/link/pic_link') }}</option>
                    </select>
                </td>
                <td><input type="input" name="link_name[]" value="{{ $vo.link_name }}" class="layui-input"></td>
                <td><input type="input" name="link_url[]" value="{{ $vo.link_url }}" class="layui-input"></td>
                <td><input type="input" name="link_logo[]" value="{{ $vo.link_logo }}" class="layui-input"></td>
                <td>
                    <a class="layui-badge-rim j-ajax" data-href="{{ url('index/check_back_link') }}?url={{ $vo['link_url'] }}" refresh="no" href="javascript:;" title="{{ __('admin.detect') }}">{{ __('admin.detect') }}</a>
                    <a class="layui-badge-rim j-iframe" data-href="{{ url('info?id='.$vo['link_id']) }}" href="javascript:;" title="{{ __('admin.edit') }}">{{ __('admin.edit') }}</a>
                    <a class="layui-badge-rim j-tr-del" data-href="{{ url('del?ids='.$vo['link_id']) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
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
    var curUrl="{{ url('link/index',$param) }}";
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;

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