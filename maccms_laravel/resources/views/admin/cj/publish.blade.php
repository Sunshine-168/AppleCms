@include('admin.public.head')
<div class="page-container">
    <form class="layui-form layui-form-pane" action="">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="{{ empty($param['status']) ? 'layui-this' : '' }}"><a href="{{ route('admin.cj.publish', ['id' => $param['id']]) }}">{{ __('admin.all') }}</a></li>
                <li class="{{ ($param['status'] ?? '') === '1' ? 'layui-this' : '' }}"><a href="{{ route('admin.cj.publish', ['id' => $param['id']]) }}?status=1">{{ __('admin.admin/cj/collected_not') }}</a></li>
                <li class="{{ ($param['status'] ?? '') === '2' ? 'layui-this' : '' }}"><a href="{{ route('admin.cj.publish', ['id' => $param['id']]) }}?status=2">{{ __('admin.admin/cj/collected') }}</a></li>
                <li class="{{ ($param['status'] ?? '') === '3' ? 'layui-this' : '' }}"><a href="{{ route('admin.cj.publish', ['id' => $param['id']]) }}?status=3">{{ __('admin.admin/cj/published') }}</a></li>
            </ul>

            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-btn-group">
                        <select id="collect-opt" class="layui-input" style="display:inline-block;width:120px;height:38px;">
                            <option value="0" {{ (string)($param['opt'] ?? '0') === '0' ? 'selected' : '' }}>新增+更新</option>
                            <option value="1" {{ (string)($param['opt'] ?? '') === '1' ? 'selected' : '' }}>仅新增</option>
                            <option value="2" {{ (string)($param['opt'] ?? '') === '2' ? 'selected' : '' }}>仅更新</option>
                        </select>
                        <select id="collect-filter" class="layui-input" style="display:inline-block;width:140px;height:38px;">
                            <option value="0" {{ (string)($param['filter'] ?? '0') === '0' ? 'selected' : '' }}>不过滤资源组</option>
                            <option value="1" {{ (string)($param['filter'] ?? '') === '1' ? 'selected' : '' }}>新增更新都过滤</option>
                            <option value="2" {{ (string)($param['filter'] ?? '') === '2' ? 'selected' : '' }}>仅新增过滤</option>
                            <option value="3" {{ (string)($param['filter'] ?? '') === '3' ? 'selected' : '' }}>仅更新过滤</option>
                        </select>
                        <input id="collect-filter-from" class="layui-input" style="display:inline-block;width:180px;height:38px;" value="{{ $param['filter_from'] ?? '' }}" placeholder="资源组,逗号分隔">
                        <a data-href="{{ route('admin.cj.content_del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ __('admin.del') }}</a>
                        <a data-href="{{ route('admin.cj.content_del') }}?ids=1&all=1" class="layui-btn layui-btn-primary j-ajax" confirm="{{ __('admin.clear_confirm') }}"><i class="layui-icon">&#xe640;</i>{{ __('admin.clear') }}</a>
                        <a data-base-href="{{ route('admin.cj.content_into', ['id' => $param['id']]) }}" data-ajax="no" class="layui-btn layui-btn-primary j-page-btns confirm j-import-btn"><i class="layui-icon">&#xe654;</i>{{ __('admin.import') }}</a>
                        <a data-base-href="{{ route('admin.cj.content_into', ['id' => $param['id']]) }}?all=1" data-ajax="no" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm j-import-btn"><i class="layui-icon">&#xe654;</i>{{ __('admin.import_all') }}</a>
                    </div>
                    <table class="layui-table" lay-size="sm">
                        <thead>
                        <tr>
                            <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                            <th width="50">{{ __('admin.id') }}</th>
                            <th width="50">{{ __('admin.status') }}</th>
                            <th width="250">{{ __('admin.name') }}</th>
                            <th >{{ __('admin.url') }}</th>
                            <th width="40">{{ __('admin.opt') }}</th>
                        </tr>
                        </thead>
                        @foreach($list as $vo)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $vo->id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                            <td>{{ $vo->id }}</td>
                            <td>
                                @if($vo->status == 1)
                                    {{ __('admin.admin/cj/collected_not') }}
                                @elseif($vo->status == 2)
                                    {{ __('admin.admin/cj/collected') }}
                                @else
                                    {{ __('admin.admin/cj/published') }}
                                @endif
                            </td>
                            <td>{{ $vo->title }}</td>
                            <td>{{ $vo->url }}</td>
                            <td>
                                <a class="layui-badge-rim j-iframe" data-href="{{ route('admin.cj.show', ['id' => $vo->id]) }}" href="javascript:;" title="{{ __('admin.view') }}">{{ __('admin.view') }}</a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div id="pages" class="center"></div>

                </div>

            </div>
        </div>

    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript">
    var curUrl = "{{ route('admin.cj.publish', ['id' => $param['id']]) }}";
    var curStatus = "{{ $param['status'] ?? '' }}";
    var curOpt = "{{ $param['opt'] ?? '0' }}";
    var curFilter = "{{ $param['filter'] ?? '0' }}";
    var curFilterFrom = "{{ $param['filter_from'] ?? '' }}";
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
                    var query = '?page=' + obj.curr + '&limit=' + obj.limit;
                    if (curStatus !== '') {
                        query += '&status=' + encodeURIComponent(curStatus);
                    }
                    if (curOpt !== '' && curOpt !== '0') {
                        query += '&opt=' + encodeURIComponent(curOpt);
                    }
                    if (curFilter !== '' && curFilter !== '0') {
                        query += '&filter=' + encodeURIComponent(curFilter);
                    }
                    if (curFilterFrom !== '') {
                        query += '&filter_from=' + encodeURIComponent(curFilterFrom);
                    }
                    location.href = curUrl + query;
                }
            }
        });
    });

    document.querySelectorAll('.j-import-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var baseHref = button.getAttribute('data-base-href') || '';
            var separator = baseHref.indexOf('?') === -1 ? '?' : '&';
            var query = 'opt=' + encodeURIComponent(document.getElementById('collect-opt').value)
                + '&filter=' + encodeURIComponent(document.getElementById('collect-filter').value)
                + '&filter_from=' + encodeURIComponent(document.getElementById('collect-filter-from').value);
            button.setAttribute('data-href', baseHref + separator + query);
        });
    });
</script>