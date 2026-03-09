@include('admin.public.head')
<div class="page-container p10">
    <div class="my-btn-box lh30">
        <div class="layui-btn-group fl">
            <a data-full="1" data-href="{{ route('admin.template.info', ['fpath' => $curpath]) }}" class="layui-btn layui-btn-primary j-iframe">
                <i class="layui-icon">&#xe654;</i>{{ __('admin.add') }}
            </a>
        </div>
    </div>

    <form class="layui-form layui-form-pane" action="">
        <table class="layui-table mt10">
            <thead>
            <tr>
                <th>{{ __('admin.file_name') }}</th>
                <th width="200">{{ __('admin.file_des') }}</th>
                <th width="200">{{ __('admin.file_size') }}</th>
                <th width="200">{{ __('admin.file_time') }}</th>
                <th width="100">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @if($ischild == 1)
                <tr>
                    <td colspan="5">
                        <a href="{{ route('admin.template.index', ['path' => $uppath]) }}">...{{ __('admin.return_parent_dir') }}</a>
                    </td>
                </tr>
            @endif
            @foreach($files as $vo)
                <tr>
                    @if($vo['isfile'] == 1)
                        <td>{{ $vo['name'] }}</td>
                        <td>{{ $vo['note'] }}</td>
                        <td>{{ $vo['size'] }}</td>
                        <td>{{ $vo['time'] ? date('Y-m-d H:i:s', $vo['time']) : '' }}</td>
                        <td>
                            <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.template.info', ['fpath' => $vo['path'], 'fname' => $vo['name']]) }}" href="javascript:;" title="{{ __('admin.edit') }}">{{ __('admin.edit') }}</a>
                            <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.template.del', ['fname' => $vo['fullname']]) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                        </td>
                    @else
                        <td><a href="{{ route('admin.template.index', ['path' => $vo['path']]) }}">{{ $vo['name'] }}</a></td>
                        <td>{{ $vo['note'] }}</td>
                        <td></td>
                        <td>{{ $vo['time'] ? date('Y-m-d H:i:s', $vo['time']) : '' }}</td>
                        <td></td>
                    @endif
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">
                    {{ __('admin.admin/template/current_dir') }}：{{ str_replace('@', '/', $curpath) }}，
                    {{ __('admin.sum') }}<b class="red">{{ $num_path }}</b>{{ __('admin.dir') }}，
                    <b class="red">{{ $num_file }}</b>{{ __('admin.file') }}，
                    {{ __('admin.occupies') }}<b class="red">{{ $sum_size }}</b>{{ __('admin.space') }}
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
@include('admin.public.foot')