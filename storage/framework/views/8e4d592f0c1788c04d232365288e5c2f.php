<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">


  <form class="layui-form layui-form-pane" method="post" action="<?php echo e(url('comment/blacklist')); ?>">
    <div class="layui-form-item">
      <label class="layui-form-label"><?php echo e(__('admin.blacklist_keywords')); ?></label>
      <div class="layui-input-block">
        <textarea style="height: 500px" name="keywords" placeholder="<?php echo e(__('admin.index/blacklist_placeholder')); ?>" class="layui-textarea" ><?php echo e($black_keyword_list); ?></textarea>
      </div>
    </div>
    <div class="layui-form-item center">
      <div class="layui-input-block">
        <button type="submit" class="layui-btn" lay-submit><?php echo e(__('admin.save')); ?></button>
        <button type="reset" class="layui-btn layui-btn-primary"><?php echo e(__('admin.btn_reset')); ?></button>
      </div>
    </div>
  </form>
</div>


<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\comment\blacklist.blade.php ENDPATH**/ ?>