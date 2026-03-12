<?php echo $__env->make('install.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="install-box">
    <div class="protocol-box">
        <div class="title"><?php echo e(__('install.user_agreement_title')); ?></div>
        <div class="protocol">
            <p>
                <?php echo e(__('install.user_agreement')); ?>

            </p>
        </div>
    </div>
    <form class="layui-form layui-form-pane" action="" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('install.lang')); ?></label>
            <div class="layui-input-inline w200 ">
                <select class="" name="lang" lay-filter="lang" style="z-index:99999;">
                    <option value=""><?php echo e(__('install.select_lang')); ?></option>
                    <?php $__currentLoopData = $langs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php if($lang == $key): ?>selected <?php endif; ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('install.lang_tip')); ?></div>
        </div>
    </form>
    <div class="step-btns">
        <a href="<?php echo e(route('install.step2')); ?>" class="layui-btn layui-btn-big layui-btn-normal"><?php echo e(__('install.user_agreement_agree')); ?></a>
    </div>
</div>
<?php echo $__env->make('install.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var test=0;
    layui.define(['element', 'form'], function(exports) {
        var $ = layui.jquery, layer = layui.layer, form = layui.form;
        form.on('select(lang)',function(data){
            if(data.value !='') {
                location.href = "<?php echo e(route('install.index')); ?>?lang=" + (data.value);
            }
        });
    });
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\install\index.blade.php ENDPATH**/ ?>