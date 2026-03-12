<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe" data-width="800px" data-height="610px"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(url('clearbind')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm" data-checkbox="false" data-ajax="yes"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.admin/collect/clear_bind')); ?></a>

                <?php if(condition="$collect_break_vod != ''"): ?>
                <a href="<?php echo e(url('load')); ?>?flag=vod" class="layui-btn layui-btn-danger ">【进入视频断点采集】</a>
                <?php endif; ?>
                <?php if(condition="$collect_break_art != ''"): ?>
                <a href="<?php echo e(url('load')); ?>?flag=art" class="layui-btn layui-btn-danger ">【进入文章断点采集】</a>
                <?php endif; ?>
                <?php if(condition="$collect_break_actor != ''"): ?>
                <a href="<?php echo e(url('load')); ?>?flag=actor" class="layui-btn layui-btn-danger ">【进入明星断点采集】</a>
                <?php endif; ?>
                <?php if(condition="$collect_break_role != ''"): ?>
                <a href="<?php echo e(url('load')); ?>?flag=role" class="layui-btn layui-btn-danger ">【进入角色断点采集】</a>
                <?php endif; ?>
                <?php if(condition="$collect_break_website != ''"): ?>
                <a href="<?php echo e(url('load')); ?>?flag=website" class="layui-btn layui-btn-danger ">【进入网址断点采集】</a>
                <?php endif; ?>

        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100"><?php echo e(__('admin.id')); ?></th>
                <th width="100">接口类型</th>
                <th width="100">资源类型</th>
                <th>资源站</th>
                <th width="200">采集选项</th>
                <th width="100"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.collect_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.collect_id); ?></td>
                <td><?php if(condition="$vo['collect_type'] == 1"): ?>xml@elsejson@endif </td>
                <td><?php echo e($vo.collect_mid|mac_get_mid_text); ?></td>
                <td><a class="layui-badge-rim" href="<?php echo e(url('api')); ?>?<?php echo e(http_build_query(['ac'=>'list','cjflag'=>md5($vo.collect_url),'cjurl'=>$vo.collect_url,'h'=>'','t'=>'','ids'=>'','wd'=>'','type'=>$vo.collect_type,'mid'=>$vo.collect_mid,'opt'=>$vo.collect_opt,'sync_pic_opt'=>$vo.collect_sync_pic_opt,'filter'=>$vo.collect_filter,'filter_from'=>$vo.collect_filter_from,'filter_year'=>$vo.collect_filter_year,'param'=>base64_encode($vo.collect_param)])); ?>" title="进入资源库">【<?php echo e($vo.collect_name); ?>】<?php echo e($vo.collect_url); ?></a></td>
                <td>
                    <a class="layui-badge-rim" href="<?php echo e(url('api')); ?>?<?php echo e(http_build_query(['ac'=>'cj','cjflag'=>md5($vo.collect_url),'cjurl'=>$vo.collect_url,'h'=>'24','t'=>'','ids'=>'','wd'=>'','type'=>$vo.collect_type,'mid'=>$vo.collect_mid,'opt'=>$vo.collect_opt,'sync_pic_opt'=>$vo.collect_sync_pic_opt,'filter'=>$vo.collect_filter,'filter_from'=>$vo.collect_filter_from,'filter_year'=>$vo.collect_filter_year,'param'=>base64_encode($vo.collect_param)])); ?>" title="采集当天">采集当天</a>
                    <a class="layui-badge-rim" href="<?php echo e(url('api')); ?>?<?php echo e(http_build_query(['ac'=>'cj','cjflag'=>md5($vo.collect_url),'cjurl'=>$vo.collect_url,'h'=>'168','t'=>'','ids'=>'','wd'=>'','type'=>$vo.collect_type,'mid'=>$vo.collect_mid,'opt'=>$vo.collect_opt,'sync_pic_opt'=>$vo.collect_sync_pic_opt,'filter'=>$vo.collect_filter,'filter_from'=>$vo.collect_filter_from,'filter_year'=>$vo.collect_filter_year,'param'=>base64_encode($vo.collect_param)])); ?>" title="采集本周">采集本周</a>
                    <a class="layui-badge-rim" href="<?php echo e(url('api')); ?>?<?php echo e(http_build_query(['ac'=>'cj','cjflag'=>md5($vo.collect_url),'cjurl'=>$vo.collect_url,'h'=>'','t'=>'','ids'=>'','wd'=>'','type'=>$vo.collect_type,'mid'=>$vo.collect_mid,'opt'=>$vo.collect_opt,'sync_pic_opt'=>$vo.collect_sync_pic_opt,'filter'=>$vo.collect_filter,'filter_from'=>$vo.collect_filter_from,'filter_year'=>$vo.collect_filter_year,'param'=>base64_encode($vo.collect_param)])); ?>" title="采集所有">采集所有</a>
                </td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info?id='.$vo['collect_id'])); ?>" data-width="800px" data-height="610px" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['collect_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div id="pages" class="center"></div>
    </form>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\collect\index.blade.php ENDPATH**/ ?>