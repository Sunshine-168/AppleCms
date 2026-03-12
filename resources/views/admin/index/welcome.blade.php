@include('admin.public.head')
<div class="page-container" style="padding:16px;">
    <blockquote class="layui-elem-quote layui-quote-nm mt10">
        <p class="f-20 text-success">{{ __('admin/index/welcome/tip_warn') }}</p>
    </blockquote>
    <table class="layui-table">
        <tbody>
        <tr>
            <td width="160">{{ __('admin/index/welcome/filed_os') }}</td>
            <td>{{ PHP_OS }} ({{ $_SERVER['SERVER_SOFTWARE'] ?? '' }})</td>
        </tr>
        <tr>
            <td>{{ __('admin/index/welcome/filed_host') }}</td>
            <td>{{ request()->getHost() }}</td>
        </tr>
        <tr>
            <td>{{ __('admin/index/welcome/filed_max_upload') }}</td>
            <td>{{ ini_get('file_uploads') ? ini_get('upload_max_filesize') : '×' }}</td>
        </tr>
        <tr>
            <td>{{ __('admin/index/welcome/filed_date') }}</td>
            <td>{{ date('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>{{ __('admin/index/welcome/filed_php_ver') }}</td>
            <td>{{ PHP_VERSION }}</td>
        </tr>
        <tr>
            <td>Laravel</td>
            <td>{{ app()->version() }}</td>
        </tr>
        </tbody>
    </table>
</div>
@include('admin.public.foot')
