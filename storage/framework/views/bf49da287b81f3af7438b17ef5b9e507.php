<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title><?php echo e(__('verify')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('static/css/home.css')); ?>">
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
    <script src="<?php echo e(asset('static/js/jquery.js')); ?>"></script>
    <script>
        var maccms = {
            path: <?php echo json_encode($rootPath, 15, 512) ?>,
            mid: '',
            aid: '',
            url: <?php echo json_encode(url('/'), 15, 512) ?>,
            wapurl: '',
            mob_status: ''
        };
    </script>
    <script src="<?php echo e(asset('static/js/home.js')); ?>"></script>
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
                    url: <?php echo json_encode(url('/index.php/ajax/verify_check'), 15, 512) ?>,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        type: <?php echo json_encode($type, 15, 512) ?>,
                        verify: v,
                        id: <?php echo json_encode($id, 15, 512) ?>
                    },
                    success: function (r) {
                        if (parseInt(r.code, 10) === 1) {
                            location.reload();
                        } else {
                            alert(r.msg || <?php echo json_encode(__('verify_err'), 15, 512) ?>);
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
    <div class="msg_jump_tit"><?php echo e(__('verify')); ?>...</div>
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
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\public\verify.blade.php ENDPATH**/ ?>