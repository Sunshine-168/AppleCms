<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo e(__('admin.admin/index/login/title')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('static/layui/css/layui.css')); ?>?v=1024">
    <link rel="stylesheet" href="<?php echo e(asset('static/css/admin_style.css')); ?>?v=1024">
    <style type="text/css">
        body {
            color:#999;
            <?php if($background ?? ''): ?>
            background:url('<?php echo e($background); ?>');
            background-size:cover;
            <?php endif; ?>
        }
    </style>
</head>
<body class="login-body body">
<div class="login-head">
    <h1><a href="//www.maccms.la/"><?php echo e(__('admin.admin/index/login/tip_welcome')); ?></a></h1>
</div>
<div class="login-box">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.login')); ?>">
        <?php echo csrf_field(); ?>
        <div class="layui-form-item">
            <h3><?php echo e(__('admin.admin/index/login/tip_sys')); ?></h3>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.account')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" name="admin_name" class="layui-input" lay-verify="admin_name" placeholder="" autocomplete="on" maxlength="20" value="<?php echo e(old('admin_name')); ?>"/>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.pass')); ?>：</label>
            <div class="layui-input-block">
                <input type="password" name="admin_pwd" class="layui-input" lay-verify="admin_pwd" placeholder="" maxlength="20"/>
            </div>
        </div>
        <?php if(config('maccms.app.admin_login_verify', '0') != '0'): ?>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.verify')); ?>：</label>
            <div class="layui-input-block">
                <input type="number" name="verify" class="layui-input" lay-verify="verify" placeholder="" maxlength="4"  max="9999"/><img id="verify_img" src="<?php echo e(url('/verify')); ?>" onclick="this.src = this.src+'?'">
            </div>
        </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <div class="layui-form-mid layui-word-aux" style="color:red;"><?php echo e($errors->first()); ?></div>
            </div>
        </div>
        <?php endif; ?>
        <button type="button" class="layui-btn btn-submit" lay-submit="" lay-filter="sub"><?php echo e(__('admin.admin/index/login/btn_submit')); ?></button>
    </form>
    <div class="copyright">
        <?php echo e(__('admin.maccms_copyright')); ?>

    </div>

    <fieldset class="layui-elem-field">
        <legend><?php echo e(__('admin.admin/index/login/tip_declare')); ?></legend>
        <div class="layui-field-box">
            <?php echo e(__('admin.admin/index/login/tip_declare_txt')); ?>

        </div>
    </fieldset>
</div>

<script type="text/javascript" src="<?php echo e(asset('static/layui/layui.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('static/js/admin_common.js')); ?>"></script>
<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;

        form.verify({
            admin_name: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.admin/index/login/verify_no')); ?>";
                }
            },
            admin_pwd: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.admin/index/login/verify_pass')); ?>";
                }
            },
            verify: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.admin/index/login/verify_verify')); ?>";
                }
            }
        });

        form.on('submit(sub)', function (data) {
            layer.msg("<?php echo e(__('admin.wait_submit')); ?>",{time:500000});
            $.post("<?php echo e(route('admin.login')); ?>",data.field,function(r){
                if(r.code==1){
                    location.href="<?php echo e(route('admin.index')); ?>";
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
                    <?php if(config('maccms.app.admin_login_verify', '0') != '0'): ?>
                    $("input[name='verify']").focus();
                    <?php else: ?>
                    $('.btn-submit').click();
                    <?php endif; ?>
                }
            }
        });

        <?php if(config('maccms.app.admin_login_verify', '0') != '0'): ?>
        $("input[name='verify']").bind('keypress',function(event){
            if(event.keyCode == "13") {
                if($("input[name='verify']").val()!=''){
                    $('.btn-submit').click();
                }
            }
        });
        <?php endif; ?>
    });
</script>
</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\index\login.blade.php ENDPATH**/ ?>