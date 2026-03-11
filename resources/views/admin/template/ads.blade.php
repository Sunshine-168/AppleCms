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
                <th width="150">{{ __('admin.file_des') }}</th>
                <th width="150">{{ __('admin.file_size') }}</th>
                <th width="150">{{ __('admin.file_time') }}</th>
                <th width="260">{{ __('admin.admin/template/call_code') }}</th>
                <th width="130">{{ __('admin.opt') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($files as $index => $vo)
                <tr>
                    <td>{{ $vo['name'] }}</td>
                    <td>{{ $vo['note'] }}</td>
                    <td>{{ $vo['size'] }}</td>
                    <td>{{ $vo['time'] ? date('Y-m-d H:i:s', $vo['time']) : '' }}</td>
                    <td>
                        <input id="txt{{ $index }}" type="text" class="layui-input" value='<script src="{{ $pathAds }}/{{ $vo['name'] }}"></script>' readonly>
                    </td>
                    <td>
                        <a class="layui-badge-rim j-clipboard" data-clipboard-target="#txt{{ $index }}" href="javascript:;" title="{{ __('admin.copy') }}">{{ __('admin.copy') }}</a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="{{ route('admin.template.info', ['fpath' => $vo['path'], 'fname' => $vo['name']]) }}" href="javascript:;" title="{{ __('admin.edit') }}">{{ __('admin.edit') }}</a>
                        <a class="layui-badge-rim j-tr-del" data-href="{{ route('admin.template.del', ['fname' => $vo['fullname']]) }}" href="javascript:;" title="{{ __('admin.del') }}">{{ __('admin.del') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="6">
                    {{ __('admin.admin/template/current_dir') }}：{{ str_replace('@', '/', $curpath) }}，
                    {{ __('admin.sum') }}<b class="red">{{ $num_file }}</b>{{ __('admin.file') }}，
                    {{ __('admin.occupies') }}<b class="red">{{ $sum_size }}</b>{{ __('admin.space') }}
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
@include('admin.public.foot')
<script type="text/javascript" src="{{ asset('static/js/jquery.clipboard.js') }}"></script>
<script type="text/javascript">
    var clipboard = new ClipboardJS('.j-clipboard');
    clipboard.on('success', function () {
        layer.msg('copy ok');
    });
</script>