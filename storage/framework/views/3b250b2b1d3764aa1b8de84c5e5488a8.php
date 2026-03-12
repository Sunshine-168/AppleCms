<?php echo $__env->make('install.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style type="text/css">
    .layui-table {
        border: 1px solid #E6E7EB;
    }

    .layui-table td,
    .layui-table th {
        text-align: center;
        font-size: 14px;
        color: #4B5563;
    }

    .layui-table th {
        background: #E6E7EB;
        color: #1F2937;
    }

    .layui-table tbody tr.yes td:last-child::before,
    .layui-table tbody tr.ok td:last-child::before {
        content: '';
        display: inline-block;
        width: 16px;
        vertical-align: middle;
        height: 16px;
        margin-right: 4px;
        background: url(<?php echo e(asset('static/images/install/monitor_ic_check.png')); ?>) 100% 100%;
    }

    .layui-table tbody tr.no td:last-child::before {
        content: '';
        display: inline-block;
        vertical-align: middle;
        width: 16px;
        height: 16px;
        margin-right: 4px;
        background: url(<?php echo e(asset('static/images/install/monitor_ic_wrong.png')); ?>) 100% 100%;
    }

    .install-box {
        width: 1400px;
    }

    .title-run {
        height: 28px;
        font-family: PingFangSC, PingFang SC;
        font-weight: 500;
        font-size: 20px;
        color: #1F2937;
        line-height: 28px;
        text-align: center;
        font-style: normal;
    }

    .word-box {
        display: flex;
        gap: 20px;
    }

    .step-btns .last {
        width: 300px;
        height: 40px;
        background: rgba(64, 204, 146, 0.2);
        border-radius: 6px;
        color: rgba(64, 204, 146, 1);
    }

    .step-btns .common {
        width: 300px;
        height: 40px;
        background: #FFFFFF;
        box-shadow: 0px 1px 3px 0px #EBEDF0;
        border-radius: 6px;
        border: 1px solid #E6E7EB;
        color: rgba(31, 41, 55, 1);
    }
</style>
<div class="install-box">
    <div class="title-run">
        <?php echo e(__('install.environment_title')); ?>

    </div>
    <table class="layui-table" lay-skin="line">
        <thead>
            <tr>
                <th><?php echo e(__('install.environment_name')); ?></th>
                <th><?php echo e(__('install.required_config')); ?></th>
                <th><?php echo e(__('install.current_config')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data['env']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="<?php echo e($vo[4]); ?>">
                <td><?php echo e($vo[0]); ?></td>
                <td><?php echo e($vo[2]); ?></td>
                <td><?php echo e($vo[3]); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <div class="word-box">
        <table class="layui-table" lay-skin="line">
            <thead>
                <tr>
                    <th><?php echo e(__('install.func_ext')); ?></th>
                    <th><?php echo e(__('install.type')); ?></th>
                    <th><?php echo e(__('install.result')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $data['func']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($vo[2]); ?>">
                    <td><?php echo e($vo[0]); ?></td>
                    <td><?php echo e($vo[3]); ?></td>
                    <td><?php echo e($vo[1]); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <table class="layui-table" lay-skin="line">
            <thead>
                <tr>
                    <th><?php echo e(__('install.dir_file')); ?></th>
                    <th><?php echo e(__('install.required_popedom')); ?></th>
                    <th><?php echo e(__('install.current_popedom')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $data['dir']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($vo[4]); ?>">
                    <td><?php echo e($vo[1]); ?></td>
                    <td><?php echo e($vo[2]); ?></td>
                    <td><?php echo e($vo[3]); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="step-btns">
        <a href="<?php echo e(route('install.index')); ?>" class="last layui-btn layui-btn-primary layui-btn-big fl"><?php echo e(__('install.back_step')); ?></a>

        <a href="<?php echo e(route('install.step3')); ?>" class="layui-btn layui-btn-big layui-btn-normal fl"><?php echo e(__('install.next_step')); ?></a><div style="padding: 9px 9px !important;" class="layui-form-mid layui-word-aux"><?php echo e(__('install.next_step_tips')); ?></div>

        <a target="_blank" href="http://www.maccms.la/doc/v10/faq.html"
            class="layui-btn common fr"><?php echo e(__('install.question')); ?></a>
    </div>
</div>
<?php echo $__env->make('install.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\install\step2.blade.php ENDPATH**/ ?>