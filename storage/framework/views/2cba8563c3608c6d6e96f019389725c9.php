<?php echo $__env->make('install.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style type="text/css">
    .layui-table td,
    .layui-table th {
        text-align: left;
    }

    .layui-table tbody tr.no {
        background-color: #f00;
        color: #fff;
    }

    .install-box {
        width: 912px;
        padding-top: 15px;
    }

    .title-run {
        height: 28px;
        font-family: PingFangSC, PingFang SC;
        font-weight: 500;
        font-size: 20px;
        margin: 10px 0;
        color: #1F2937;
        line-height: 28px;
        text-align: center;
        font-style: normal;
    }

    .step-btns .last {
        width: 300px;
        height: 40px;
        background: rgba(64, 204, 146, 0.2);
        border-radius: 6px;
        color: rgba(64, 204, 146, 1);
    }

    .test {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .test .layui-btn {
        width: 300px;
        height: 40px;
        background: #40CC92;
        border-radius: 6px;
        line-height: 40px;
    }

    .test-text {
        font-family: PingFang-SC, PingFang-SC;
        font-weight: 500;
        font-size: 14px;
        color: rgba(75, 85, 99, 0.5);
        line-height: 24px;
        text-align: center;
        font-style: normal;
    }

    .layui-form-item .layui-input-inline.w200 {
        width: 480px !important;
        text-align: left;
    }

    .layui-form-mid {
        color: rgba(75, 85, 99, 0.5) !important;
    }
</style>
<div class="install-box">
    <div class="title-run">
        <?php echo e(__('install.database_config')); ?>

    </div>
    <form class="layui-form layui-form-pane" action="<?php echo e(route('install.step4')); ?>" method="post">
        <?php echo csrf_field(); ?>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.server_address')); ?></label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="hostname" lay-verify="title" value="127.0.0.1">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.server_address_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.database_port')); ?></label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="hostport" lay-verify="title" value="3306">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.database_port_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.database_name')); ?></label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="database" lay-verify="title">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.database_name_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.database_username')); ?></label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="username" lay-verify="title">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.database_username_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.database_pass')); ?></label>
            <div class="layui-input-inline w200">
                <input type="password" class="layui-input" name="password" lay-verify="title">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.database_pass_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.database_pre')); ?></label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="prefix" lay-verify="title" value="mac_">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.database_pre_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.overwrite_database')); ?></label>
            <div class="layui-input-inline w200">
                <input type="radio" name="cover" value="1" title="<?php echo e(__('install.overwrite')); ?>">
                <input type="radio" name="cover" value="0" title="<?php echo e(__('install.not_overwrite')); ?>" checked>
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.overwrite_tip')); ?></div>
        </div>
        <div class="test">
            <button type="submit" class="layui-btn" lay-submit=""
                lay-filter="formTest"><?php echo e(__('install.test_connect')); ?></button>
            <div class="test-text"><?php echo e(__('install.test_connect_tip')); ?></div>
        </div>
    </form>
    <form class="layui-form layui-form-pane" action="<?php echo e(route('install.step5')); ?>" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="install_dir" value="<?php echo e($install_dir); ?>">
        <fieldset class="layui-elem-field layui-field-title"></fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.admin_name')); ?></label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="account" lay-verify="title">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.admin_name_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.admin_pass')); ?></label>
            <div class="layui-input-inline w200">
                <input type="password" class="layui-input" name="password" lay-verify="title">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.admin_pass_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.init_data')); ?></label>
            <div class="layui-input-inline w200">
                <input type="radio" name="initdata" value="1" title="<?php echo e(__('install.create')); ?>" checked="">
                <input type="radio" name="initdata" value="0" title="<?php echo e(__('install.not_create')); ?>">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.create_tip')); ?></div>
        </div>
        <div class="step-btns">
            <a href="<?php echo e(route('install.step2')); ?>" class="layui-btn last fl"><?php echo e(__('install.back_step')); ?></a>
            <button type="submit" class="layui-btn layui-btn-big layui-btn-normal fr" lay-submit=""
                lay-filter="formSubmit"><?php echo e(__('install.exec')); ?></button>
        </div>
    </form>
</div>
<span style="display: none">
    <iframe src="//www.maccms.la/tongji.html?v10-php" MARGINWIDTH="0" MARGINHEIGHT="0" HSPACE="0" VSPACE="0"
        FRAMEBORDER="0" SCROLLING="no" width="0" height="0"></iframe>
</span>
<?php echo $__env->make('install.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var test = 0;
    layui.define(['element', 'form'], function (exports) {
        var $ = layui.jquery, layer = layui.layer, form = layui.form;
        form.on('submit(formTest)', function (data) {
            var _form = '';
            if ($(this).attr('data-form')) {
                _form = $($(this).attr('data-form'));
            } else {
                _form = $(this).parents('form');
            }

            layer.msg("<?php echo e(__('install.wait_submit')); ?>", { time: 500000 });
            $.ajax({
                type: "POST",
                url: _form.attr('action'),
                data: _form.serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.code == 1) {
                        test = 1;
                    }
                    layer.msg(res.msg);
                }
            });
            return false;
        });
        form.on('submit(formSubmit)', function (data) {
            if (test == 0) {
                layer.msg("<?php echo e(__('install.submit_tip')); ?>");
                return false;
            }
        });
    });
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\install\step3.blade.php ENDPATH**/ ?>