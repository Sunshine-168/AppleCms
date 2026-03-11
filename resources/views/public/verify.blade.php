<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title>{{ __('verify') }}</title>
    <link rel="stylesheet" href="{{ asset('static/css/home.css') }}">
    <style>
        body{background:#F9FAFD;color:#818181;}
        input{
            border:1px solid #ccc;
            padding:7px 0;
            border-radius:3px;
            padding-left:5px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            -webkit-transition:border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
            -o-transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;
            transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
        .mac_verify_img{
            padding:7px 0;
            border-radius:3px;
            padding-left:5px;
            cursor:pointer;
        }
    </style>
    <script src="{{ asset('static/js/jquery.js') }}"></script>
    <script>
        var maccms = {
            path: @json($rootPath),
            mid: '',
            aid: '',
            url: @json(url('/')),
            wapurl: '',
            mob_status: ''
        };
    </script>
    <script src="{{ asset('static/js/home.js') }}"></script>
    <script>
        $(function () {
            $('.mac_verify').focus();
            $("input[name='verify']").on('keypress', function (event) {
                if (event.keyCode === 13 && $("input[name='verify']").val() !== '') {
                    $('.verify_submit').click();
                }
            });
            $('.verify_submit').on('click', function () {
                var v = $('input[name="verify"]').val();
                $.ajax({
                    url: @json(url('/index.php/ajax/verify_check')),
                    type: 'post',
                    dataType: 'json',
                    data: {
                        type: @json($type),
                        verify: v,
                        id: @json($id)
                    },
                    success: function (r) {
                        if (parseInt(r.code, 10) === 1) {
                            location.reload();
                        } else {
                            alert(r.msg || @json(__('verify_err')));
                            MAC.Verify.Refresh();
                        }
                    },
                    error: function () {
                        alert('请求失败，请重试');
                        MAC.Verify.Refresh();
                    }
                });
            });
        });
    </script>
</head>
<body>
<div class="mac_msg_jump">
    <div class="msg_jump_tit">{{ __('verify') }}...</div>
    <div class="title">请输入验证码：</div>
    <div class="text">
        <input type="text" name="verify" class="mac_verify">
    </div>
    <div class="jump">
        <input type="button" class="verify_submit submit_btn" value="提交验证">
    </div>
</div>
</body>
</html>
