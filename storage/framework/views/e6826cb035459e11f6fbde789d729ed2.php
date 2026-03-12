<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="mb10">
            <div class="layui-input-inline w150 m5"><a href="javascript:;" data-id="" class="select_type red"><?php echo e(__('admin.admin/collect/view_all_resource')); ?></a></div>
            <?php $__currentLoopData = $type; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="layui-input-inline w150 m5">
                <a href="javascript:;" data-id="<?php echo e($vo.type_id); ?>" class="select_type"><?php echo e($vo.type_name|htmlspecialchars); ?></a>
                <a id="<?php echo e($param['cjflag']); ?>_<?php echo e($vo.type_id); ?>" data-href="<?php echo e(url('index/select')); ?>?tab=art&col=<?php echo e($param['cjflag']); ?>_<?php echo e($vo.type_id); ?>&ids=1&tpl=select_type&refresh=no&url=collect/bind" data-width="270" data-height="100" class="j-select" >
                    <?php if($vo.isbind == 1): ?>
                    <span class="red">[<?php echo e($vo.local_type_name); ?>]</span>
                    {else}
                    [<?php echo e(__('admin.bind')); ?>]
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>

        <div class="center mb10">
            <form class="layui-form " method="">
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" id="wd" name="wd" value="<?php echo e($param['wd']); ?>">
                </div>
                <button type="button" class="layui-btn mgl-20 j-btn" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>

    </div>


    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th ><?php echo e(__('admin.name')); ?></th>
                <th width="60"><?php echo e(__('admin.type')); ?></th>
                <th width="60"><?php echo e(__('admin.from')); ?></th>
                <th width="140"><?php echo e(__('admin.time')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.art_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.art_name|htmlspecialchars); ?></td>
                <td><?php echo e($vo.type_name|htmlspecialchars); ?></td>
                <td><?php echo e($vo.art_from|htmlspecialchars); ?></td>
                <td><?php echo e($vo.art_time|mac_day='color'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="layui-btn-group">
            {php}
                $p1 = $param;
                unset($p1['ac']);
                $p1_str = http_build_query($p1);
            {/php}
            <a data-href="<?php echo e(url('api')); ?>?<?php echo e($p1_str); ?>&ac=cjsel" data-ajax="no" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.admin/collect/cj_select')); ?></a>
            <a data-href="<?php echo e(url('api')); ?>?<?php echo e($p1_str); ?>&h=24&ac=cjday" data-checkbox="no" data-ajax="no" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.admin/collect/cj_today')); ?></a>
            <a data-href="<?php echo e(url('api')); ?>?<?php echo e($p1_str); ?>&ac=cjall" data-checkbox="no" data-ajax="no" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.admin/collect/cj_all')); ?></a>
        </div>

        <div id="pages" class="center"></div>
    </form>

</div>


<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    var curUrl="<?php echo e(url('api')); ?>?<?php echo e($param_str); ?>";
    layui.use(['laypage', 'layer','form'], function() {
        var laypage = layui.laypage
                , layer = layui.layer,
                form = layui.form;

        laypage.render({
            elem: 'pages'
            ,count: <?php echo e($total); ?>

            ,limit: <?php echo e($limit); ?>

            ,curr: <?php echo e($page); ?>

            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj,first){
                if(!first){
                    location.href = curUrl.replace('%7Bpage%7D',obj.curr).replace('%7Blimit%7D',obj.limit);
                }
            }
        });

        $('#wd').on('keydown', function (event) {
            if (event.keyCode == 13) {
                $('.j-btn').click();
                return false;
            }
        });

        $('.j-btn').click(function(){
           var wd = $('input[name="wd"]').val();
            var url = changeParam(curUrl,'wd',wd);
            location.href = url.replace('%7Bpage%7D',1).replace('%7Blimit%7D','');
        });

        $('.select_type').click(function(){
            var t = $(this).attr('data-id');
            var url = changeParam(curUrl,'t',t);
            location.href = url.replace('%7Bpage%7D',1).replace('%7Blimit%7D','');
        });

    });
    function onSubmitResult(res)
    {
        if(res.data.st==1){
            $('#'+res.data.id).html('<span class="red">['+ res.data.local_type_name +']</span>');
        }
        else{
            $('#'+res.data.id).html("[<?php echo e(__('admin.bind')); ?>]");
        }
    }
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\collect\art.blade.php ENDPATH**/ ?>