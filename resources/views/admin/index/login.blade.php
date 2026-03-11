<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ __('admin.admin/index/login/title') }}</title>
    <link rel="stylesheet" href="{{ asset('static/layui/css/layui.css') }}?v=1024">
    <link rel="stylesheet" href="{{ asset('static/css/admin_style.css') }}?v=1024">
    <style type="text/css">
        body {
            color:#999;
            @if($background ?? '')
            background:url('{{ $background }}');
            background-size:cover;
            @endif
        }
    </style>
</head>
<body class="login-body body">
<div class="login-head">
    <h1><a href="//www.maccms.la/">{{ __('admin.admin/index/login/tip_welcome') }}</a></h1>
</div>
<div class="login-box">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.login') }}">
        @csrf
        <div class="layui-form-item">
            <h3>{{ __('admin.admin/index/login/tip_sys') }}</h3>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.account') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="admin_name" class="layui-input" lay-verify="admin_name" placeholder="" autocomplete="on" maxlength="20" value="{{ old('admin_name') }}"/>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.pass') }}：</label>
            <div class="layui-input-block">
                <input type="password" name="admin_pwd" class="layui-input" lay-verify="admin_pwd" placeholder="" maxlength="20"/>
            </div>
        </div>
        @if(config('maccms.app.admin_login_verify', '0') != '0')
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.verify') }}：</label>
            <div class="layui-input-block">
                <input type="number" name="verify" class="layui-input" lay-verify="verify" placeholder="" maxlength="4"  max="9999"/><img id="verify_img" src="{{ url('/verify') }}" onclick="this.src = this.src+'?'">
            </div>
        </div>
        @endif
        @if($errors->any())
        <div class="layui-form-item">
            <div class="layui-input-block">
                <div class="layui-form-mid layui-word-aux" style="color:red;">{{ $errors->first() }}</div>
            </div>
        </div>
        @endif
        <button type="button" class="layui-btn btn-submit" lay-submit="" lay-filter="sub">{{ __('admin.admin/index/login/btn_submit') }}</button>
    </form>
    <div class="copyright">
        {{ __('admin.maccms_copyright') }}
    </div>

    <fieldset class="layui-elem-field">
        <legend>{{ __('admin.admin/index/login/tip_declare') }}</legend>
        <div class="layui-field-box">
            {{ __('admin.admin/index/login/tip_declare_txt') }}
        </div>
    </fieldset>
</div>

<script type="text/javascript" src="{{ asset('static/layui/layui.js') }}"></script>
<script type="text/javascript" src="{{ asset('static/js/admin_common.js') }}"></script>
<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;

        form.verify({
            admin_name: function (value) {
                if (value == "") {
                    return "{{ __('admin.admin/index/login/verify_no') }}";
                }
            },
            admin_pwd: function (value) {
                if (value == "") {
                    return "{{ __('admin.admin/index/login/verify_pass') }}";
                }
            },
            verify: function (value) {
                if (value == "") {
                    return "{{ __('admin.admin/index/login/verify_verify') }}";
                }
            }
        });

        form.on('submit(sub)', function (data) {
            layer.msg("{{ __('admin.wait_submit') }}",{time:500000});
            $.post("{{ route('admin.login') }}",data.field,function(r){
                if(r.code==1){
                    location.href="{{ route('admin.index') }}";
                }
                else{
                    layer.msg(r.msg,{time:1800});
                    $('#verify_img').click();
                }
            });
            return false;
        });

        $("input[name='admin_name']").bind('keypress',function(event){
            if(event.keyCode == "13") {
                if($("input[name='admin_name']").val()!=''){
                    $("input[name='admin_pwd']").focus();
                }
            }
        });

        $("input[name='admin_pwd']").bind('keypress',function(event){
            if(event.keyCode == "13") {
                if($("input[name='admin_pwd']").val()!=''){
                    @if(config('maccms.app.admin_login_verify', '0') != '0')
                    $("input[name='verify']").focus();
                    @else
                    $('.btn-submit').click();
                    @endif
                }
            }
        });

        @if(config('maccms.app.admin_login_verify', '0') != '0')
        $("input[name='verify']").bind('keypress',function(event){
            if(event.keyCode == "13") {
                if($("input[name='verify']").val()!=''){
                    $('.btn-submit').click();
                }
            }
        });
        @endif
    });
</script>
</body>
</html>
