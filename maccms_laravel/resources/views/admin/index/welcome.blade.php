@include('../../../application/admin/view/public/head')
<script>
    var MAC_VERSION='{{ $version.code }}',PHP_VERSION='{php}echo PHP_VERSION{/php}',THINK_VERSION='{php}echo THINK_VERSION{/php}';MAC_LANG='{{ $mac_lang }}';
</script>
<div class="page-container">
    @php$pass="<strong class='green'>√</strong>";$error="<strong class='red'>×</strong>";@endphp

    <blockquote class="layui-elem-quote layui-quote-nm mt10">
        <p class="f-20 text-success">{{ __('admin.admin/index/welcome/tip_warn') }}</p>
        <p>{{ __('admin.admin/index/welcome/filed_login_num') }}：{{ $admin.admin_login_num }}  {{ __('admin.admin/index/welcome/filed_last_login_ip') }}：{{ $admin.admin_last_login_ip|long2ip }}  {{ __('admin.admin/index/welcome/filed_last_login_time') }}：{{ $admin.admin_last_login_time|mac_day }}</p>
    </blockquote>

    <table class="layui-table" >
        <tbody>
        <tr>
            <td width="110">{{ __('admin.admin/index/welcome/filed_os') }}</td>
            <td>@phpecho PHP_OS @endphp (@phpecho $_SERVER['SERVER_SOFTWARE'] @endphp)</td>
        </tr>
        <tr>
            <td>{{ __('admin.admin/index/welcome/filed_host') }}</td>
            <td>@phpecho $_SERVER['HTTP_HOST'] @endphp</td>
        </tr>
        <tr>
            <td>{{ __('admin.admin/index/welcome/filed_max_upload') }}</td>
            <td>@phpecho get_cfg_var("file_uploads") ? get_cfg_var("upload_max_filesize") : $error;@endphp</td>
        </tr>
        <tr>
            <td>{{ __('admin.admin/index/welcome/filed_date') }}</td>
            <td>@phpecho date('Y-m-d'); @endphp</td>
        </tr>
         <tr>
            <td>{{ __('admin.admin/index/welcome/filed_php_ver') }}</td>
            <td>@phpecho PHP_VERSION @endphp</td>
        </tr>
        <tr>
            <td>{{ __('admin.admin/index/welcome/filed_thinkphp_ver') }}</td>
            <td>@phpecho THINK_VERSION; @endphp</td>
        </tr>
        <tr>
            <td>{{ __('admin.admin/index/welcome/filed_ver') }}</td>
            <td><span class="layui-badge">{{ $version.code }}</span></td>
        </tr>
        </tbody>
    </table>
    @if($update_sql)
    <table class="tbinfo pleft layui-table" ><thead><th colspan="2">{{ __('admin.admin/index/welcome/tip_update_db') }}</th></thead><tr><td colspan="2"><font class="tif s20">{{ __('admin.admin/index/welcome/tip_update_db_txt') }}</font><a class="j-iframe" title="{{ __('admin.admin/index/welcome/tip_update_go') }}" data-href="{{ url('update/step2') }}"><font class="tit s20">{{ __('admin.admin/index/welcome/tip_update_go') }}</font></a> </td></tr></table>
    @endif
</div>
@include('../../../application/admin/view/public/foot')
</body>
</html>