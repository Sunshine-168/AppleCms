<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <fieldset class="layui-elem-field">
        <legend><?php echo e(__('admin.base_info')); ?></legend>
        <div class="layui-field-box">
            <?php echo e($url_list); ?> <br><?php echo e(__('admin.sum')); ?>：<?php echo e($total); ?> <?php echo e(__('admin.data')); ?>，<?php echo e(__('admin.duplicate_data')); ?>：<?php echo e($re); ?><?php echo e(__('admin.data')); ?>，<?php echo e(__('admin.distinct_into')); ?><?php echo e($total-$re); ?><?php echo e(__('admin.data')); ?>。
        </div>
    </fieldset>

    <table class="layui-table" lay-size="sm">
    <thead>
      <tr>
        <th width="50"><?php echo e(__('admin.serial_num')); ?></th>
		<th><?php echo e(__('admin.link')); ?></th>
        <th><?php echo e(__('admin.name')); ?></th>
      </tr> 
    </thead>
    <tbody>
        <?php $__currentLoopData = $url; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
              <td><?php echo e($index + 1); ?></td>
              <td><?php echo e($v['url'] ?? ''); ?></td>
              <td><?php echo e($v['title'] ?? ''); ?></td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<script>
var total_page = <?php echo e($total_page); ?>;
var page = <?php echo e($param['page']); ?>;
var id = <?php echo e($param['id']); ?>;
if (total_page > page) {
    var url = "<?php echo e(route('admin.cj.col_url', ['id' => $param['id']])); ?>";
	page += 1;
    //location.href= url + '?page='+page+'&id='+id;
} else {
	//alert('采集完成');
}
</script>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\col_url.blade.php ENDPATH**/ ?>