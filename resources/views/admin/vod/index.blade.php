@include('admin.public.head')
<style>
    table {
        table-layout: fixed;
    }


    td {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="center mb10">
            <form class="layui-form " method="post" action="{{ route('admin.vod.index') }}">
                <input type="hidden" value="{{ htmlspecialchars($param['select'] ?? '') }}" name="select">
                <input type="hidden" value="{{ htmlspecialchars($param['input'] ?? '') }}" name="input">
                <div class="layui-input-inline w150">
                    <select name="type">
                        <option value="">{{ __('admin.select_type') }}</option>
                        @foreach($type_tree ?? [] as $vo)
                        @if($vo->type_mid == 1)
                        <option value="{{ $vo->type_id }}" @if(($param['type'] ?? '') == $vo->type_id)selected @endif>{{ $vo->type_name }}</option>
                        @foreach($vo->child ?? [] as $ch)
                        <option value="{{ $ch->type_id }}" @if(($param['type'] ?? '') == $ch->type_id)selected @endif>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;{{ $ch->type_name }}</option>
                        @endforeach
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value="">{{ __('admin.select_status') }}</option>
                        <option value="0" @if(($param['status'] ?? '') == '0')selected @endif>{{ __('admin.reviewed_not') }}</option>
                        <option value="1" @if(($param['status'] ?? '') == '1')selected @endif>{{ __('admin.reviewed') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value="">{{ __('admin.select_level') }}</option>
                        <option value="9" @if(($param['level'] ?? '') == '9')selected @endif>{{ __('admin.level') }}9-{{ __('admin.slide') }}</option>
                        <option value="1" @if(($param['level'] ?? '') == '1')selected @endif>{{ __('admin.level') }}1</option>
                        <option value="2" @if(($param['level'] ?? '') == '2')selected @endif>{{ __('admin.level') }}2</option>
                        <option value="3" @if(($param['level'] ?? '') == '3')selected @endif>{{ __('admin.level') }}3</option>
                        <option value="4" @if(($param['level'] ?? '') == '4')selected @endif>{{ __('admin.level') }}4</option>
                        <option value="5" @if(($param['level'] ?? '') == '5')selected @endif>{{ __('admin.level') }}5</option>
                        <option value="6" @if(($param['level'] ?? '') == '6')selected @endif>{{ __('admin.level') }}6</option>
                        <option value="7" @if(($param['level'] ?? '') == '7')selected @endif>{{ __('admin.level') }}7</option>
                        <option value="8" @if(($param['level'] ?? '') == '8')selected @endif>{{ __('admin.level') }}8</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="lock">
                        <option value="">{{ __('admin.select_lock') }}</option>
                        <option value="0" @if(($param['lock'] ?? '') == '0')selected @endif>{{ __('admin.unlock') }}</option>
                        <option value="1" @if(($param['lock'] ?? '') == '1')selected @endif>{{ __('admin.lock') }}</option>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="weekday">
                        <option value="">{{ __('admin.admin/vod/select_weekday') }}</option>
                        @foreach(explode(',', config('maccms.app.vod_extend_weekday', '')) as $vo2)
                        <option value="{{ $vo2 }}" @if(($param['weekday'] ?? '') == $vo2)selected @endif>{{ $vo2 }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="area">
                        <option value="">{{ __('admin.admin/vod/select_area') }}</option>
                        @foreach(explode(',', config('maccms.app.vod_extend_area', '')) as $vo2)
                        <option value="{{ $vo2 }}" @if(($param['area'] ?? '') == $vo2)selected @endif>{{ $vo2 }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="lang">
                        <option value="">{{ __('admin.admin/vod/select_lang') }}</option>
                        @foreach(explode(',', config('maccms.app.vod_extend_lang', '')) as $vo2)
                        <option value="{{ $vo2 }}" @if(($param['lang'] ?? '') == $vo2)selected @endif>{{ $vo2 }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="layui-input-inline w150">
                    <select name="server">
                        <option value="">{{ __('admin.admin/vod/select_server') }}</option>
                        @foreach($server_list ?? [] as $vo)
                        <option value="{{ $vo->from ?? $vo['from'] }}" @if(($param['server'] ?? '') == ($vo->from ?? $vo['from']))selected @endif>{{ $vo->show ?? $vo['show'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="player">
                        <option value="">{{ __('admin.admin/vod/select_player') }}</option>
                        <option value="no" @if(($param['player'] ?? '') == 'no')selected@endif>{{ __('admin.admin/vod/player_empty') }}</option>
                        @foreach($player_list as $vo)
                        <option value="{{ $vo.from }}" @if(condition="$param['player'] eq $vo.from")selected@endif>{{ $vo.show }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="downer">
                        <option value="">{{ __('admin.admin/vod/select_downer') }}</option>
                        <option value="no" @if(($param['downer'] ?? '') == 'no')selected@endif>{{ __('admin.admin/vod/downer_empty') }}</option>
                        @foreach($downer_list as $vo)
                        <option value="{{ $vo.from }}" @if(condition="$param['downer'] eq $vo.from")selected@endif>{{ $vo.show }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="pic">
                        <option value="">{{ lang('select_pic') }}</option>
                        <option value="1" @if(condition="$param['pic'] eq '1'")selected@endif>{{ lang('pic_empty') }}</option>
                        <option value="2" @if(condition="$param['pic'] eq '2'")selected@endif>{{ lang('pic_remote') }}</option>
                        <option value="3" @if(condition="$param['pic'] eq '3'")selected@endif>{{ lang('pic_sync_err') }}</option>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="isend">
                        <option value="">{{ __('admin.admin/vod/select_isend') }}</option>
                        <option value="0" @if(($param['isend'] ?? '') == '0')selected @endif>{{ __('admin.admin/vod/no_end') }}</option>
                        <option value="1" @if(($param['isend'] ?? '') == '1')selected @endif>{{ __('admin.admin/vod/is_end') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="copyright">
                        <option value="">{{ __('admin.admin/vod/select_copyright') }}</option>
                        <option value="0" @if(condition="$param['copyright'] eq '0'")selected @endif>{{ lang('close') }}</option>
                        <option value="1" @if(condition="$param['copyright'] eq '1'")selected @endif>{{ lang('open') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="plot">
                        <option value="">{{ __('admin.admin/vod/select_plot') }}</option>
                        <option value="0" @if(($param['plot'] ?? '') == '0')selected @endif>{{ __('admin.admin/vod/no') }}</option>
                        <option value="1" @if(($param['plot'] ?? '') == '1')selected @endif>{{ __('admin.admin/vod/have') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="role">
                        <option value="">{{ __('admin.admin/vod/select_role') }}</option>
                        <option value="0" @if(($param['role'] ?? '') == '0')selected @endif>{{ __('admin.admin/vod/no') }}</option>
                        <option value="1" @if(($param['role'] ?? '') == '1')selected @endif>{{ __('admin.admin/vod/have') }}</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="order">
                        <option value="">{{ lang('select_sort') }}</option>
                        <option value="vod_time" @if(condition="$param['order'] eq 'vod_time'")selected@endif>{{ lang('update_time') }}</option>
                        <option value="vod_id" @if(condition="$param['order'] eq 'vod_id'")selected@endif>{{ lang('id') }}</option>
                        <option value="vod_hits" @if(condition="$param['order'] eq 'vod_hits'")selected@endif>{{ lang('hits') }}</option>
                        <option value="vod_hits_month" @if(condition="$param['order'] eq 'vod_hits_month'")selected@endif>{{ lang('hits_month') }}</option>
                        <option value="vod_hits_week" @if(condition="$param['order'] eq 'vod_hits_week'")selected@endif>{{ lang('hits_week') }}</option>
                        <option value="vod_hits_day" @if(condition="$param['order'] eq 'vod_hits_day'")selected@endif>{{ lang('hits_day') }}</option>
                    </select>
                </div>


                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="{{ lang('wd') }}" class="layui-input" name="wd" value="{{ $param['wd']|mac_restore_htmlfilter }}">
                </div>
                <input type="hidden" name="repeat" value="{{ $param['repeat']|mac_filter_xss }}" />
                <button class="layui-btn mgl-20 j-search" >{{ lang('btn_search') }}</button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="{{ url('info') }}" data-full="1" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i>{{ lang('add') }}</a>
            <a data-href="{{ url('del') }}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ lang('del') }}</a>
            <a data-href="{{ url('index/select') }}?tab=vod&col=type_id&tpl=select_type&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ lang('type') }}</a>
            <a data-href="{{ url('index/select') }}?tab=vod&col=vod_level&tpl=select_level&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ lang('level') }}</a>
            <a data-href="{{ url('index/select') }}?tab=vod&col=vod_hits&tpl=select_hits&url=vod/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ lang('hits') }}</a>            
            <a data-href="{{ url('index/select') }}?tab=vod&col=vod_status&tpl=select_status&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ lang('status') }}</a>
            <a data-href="{{ url('index/select') }}?tab=vod&col=vod_lock&tpl=select_lock&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ lang('lock') }}</a>
            <a data-href="{{ url('index/select') }}?tab=vod&col=vod_copyright&tpl=select_copyright&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i>{{ lang('admin/vod/copyright') }}</a>
            <a class="layui-btn layui-btn-primary j-iframe" data-href="{{ url('images/opt?tab=vod') }}" href="javascript:;" title="{{ lang('pic_sync') }}"><i class="layui-icon">&#xe620;</i>{{ lang('pic_sync') }}</a>
            <a class="layui-btn layui-btn-primary j-iframe" data-checkbox="true" data-href="{{ url('make/make?ac=info&tab=vod') }}" href="javascript:;" title="{{ lang('make_page') }}"><i class="layui-icon">&#xe620;</i>{{ lang('make_page') }}</a>
            @if($param.select eq 1)
            <a data-href="" onclick="parent.onSelectResult('{{ $param.input|mac_filter_xss }}', $('.checkbox-ids:checked'))" class="layui-btn layui-btn-normal">{{ lang('select_return') }}</a>
            @endif

            @if(condition="$param['repeat'] neq ''")
            <a data-href="{{ url('del') }}?repeat=1&retain=min" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ lang('del_auto_keep_min') }}</a>
            <a data-href="{{ url('del') }}?repeat=1&retain=max" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i>{{ lang('del_auto_keep_max') }}</a>
            <a data-href="{{ url('data') }}?repeat=1&cache=1" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe640;</i>{{ lang('update_repeat_cache') }}</a>
            @endif
        </div>

    </div>


    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="40">{{ lang('id') }}</th>
                <th id="table_th_vod_name">{{ lang('name') }}</th>
                <th width="50">{{ lang('hits') }}</th>
                <th width="50">{{ lang('hits_week') }}</th>
                <th width="40">{{ lang('score') }}</th>
                <th width="30">{{ lang('level') }}</th>
                <th width="30">{{ lang('browse') }}</th>
                <th width="80">{{ lang('player') }}</th>
                <th width="120">{{ lang('update_time') }}</th>
                <th width="190">{{ lang('opt') }}</th>
            </tr>
            </thead>

            @foreach($list as $vo)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $vo.vod_id }}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{{ $vo.vod_id }}</td>
                <td>
                    [{{ $vo.type.type_name }}] <a target="_blank" class="layui-badge-rim" href="{{ mac_url_vod_detail($vo) }}">{{ $vo.vod_name|mac_filter_xss|mac_restore_htmlfilter }}</a>
                    @if($vo.vod_status eq 0) <span class="layui-badge">{{ lang('reviewed_not') }}</span>@endif
                    @if($vo.vod_lock eq 1) <span class="layui-badge">{{ lang('lock') }}</span>@endif
                    @if(condition="$vo.vod_isend eq 0 && $vo.vod_serial neq ''") <span class="layui-badge layui-bg-blue">{{ lang('admin/vod/serialize') }}{{ $vo.vod_serial }}</span>@endif
                    @if(condition="$vo.vod_remarks neq ''") <span class="layui-badge layui-bg-orange">{{ $vo.vod_remarks }}</span>@endif
                    @if($vo.vod_plot eq 1) <span class="layui-badge layui-bg-cyan">{{ lang('plot') }}</span>@endif
                    @if($vo.vod_role eq 1) <span class="layui-badge layui-bg-purple">{{ lang('admin/vod/role') }}</span>@endif
                    @if($vo.vod_copyright eq 1) <span class="layui-badge layui-bg-black">{{ lang('admin/vod/copyright') }}</span>@endif
                </td>
                <td>{{ $vo.vod_hits }}</td>
                <td>{{ $vo.vod_hits_week }}</td>
                <td>{{ $vo.vod_score }}</td>
                <td><a data-href="{{ url('index/select') }}?tab=vod&col=vod_level&tpl=select_level&url=vod/field&ids={{ $vo.vod_id }}" data-width="270" data-height="100" class=" j-select"><span class="layui-badge layui-bg-orange">{{ $vo.vod_level }}</span></a></td>
                <td>@if($vo.ismake eq 1)<a target="_blank" class="layui-badge layui-bg-green " href="{{ mac_url_vod_detail($vo) }}">Y</a>{else/}<a class="layui-badge" href="{{ url('make/make?ac=info&tab=vod') }}?ids={{ $vo.vod_id }}&ref=1">N</a>@endif</td>
                <td><span title="{{ $vo['vod_play_from']|str_replace='$$$',',',### }}-{{ $vo['vod_down_from']|str_replace='$$$',',',### }}">{{ $vo['vod_play_from']|str_replace='$$$',',',### }}-{{ $vo['vod_down_from']|str_replace='$$$',',',### }}</span></td>
                <td>{{ $vo.vod_time|mac_day='color' }}</td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ url('info?id='.$vo['vod_id']) }}" href="javascript:;" title="{{ lang('edit') }}">{{ lang('edit') }}</a>
                    <a class="layui-badge-rim j-tr-del" data-href="{{ url('del?ids='.$vo['vod_id']) }}" href="javascript:;" title="{{ lang('del') }}">{{ lang('del') }}</a>
                    <a class="layui-badge-rim j-iframe" data-full="1"  data-href="{{ url('role/data?select=1&tab=vod&rid='.$vo['vod_id']) }}" href="javascript:;" title="{{ lang('role') }}">{{ lang('role') }}</a>
                    <a class="layui-badge-rim j-iframe" data-full="1"  data-href="{{ url('iplot?id='.$vo['vod_id']) }}" href="javascript:;" title="{{ lang('plot') }}">{{ lang('plot') }}</a>
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
    var curUrl="{{ url('vod/data',$param) }}";
    layui.use(['laypage', 'layer','form'], function() {
        var laypage = layui.laypage
                , layer = layui.layer,
                form = layui.form;
                $ = layui.jquery;

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

        // 小屏幕下名称问题
        if ($('body').width() <= 600) {
            $('#table_th_vod_name').attr('width', '300');
        }

    });
</script>
</body>
</html>