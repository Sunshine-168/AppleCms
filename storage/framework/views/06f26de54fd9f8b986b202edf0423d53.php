<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box" >

        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(url('data')); ?>">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value=""><?php echo e(lang('select_status')); ?></option>
                        <option value="0" <?php if(condition="$param['status'] == '0'"): ?>selected <?php endif; ?>><?php echo e(lang('reviewed_not')); ?></option>
                        <option value="1" <?php if(condition="$param['status'] == '1'"): ?>selected <?php endif; ?>><?php echo e(lang('reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(lang('wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']|mac_filter_xss); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(lang('btn_search')); ?></button>
            </form>
        </div>
        <div class="layui-btn-group">
            <a data-full="1" data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=topic&col=topic_level&tpl=select_level&url=topic/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('level')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=topic&col=topic_status&tpl=select_status&url=topic/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status')); ?></a>
            <a class="layui-btn layui-btn-primary j-iframe" data-href="<?php echo e(url('images/opt?tab=topic')); ?>" href="javascript:;" title="<?php echo e(lang('pic_sync')); ?>"><i class="layui-icon">&#xe620;</i><?php echo e(lang('pic_sync')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm" >
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100"><?php echo e(lang('id')); ?></th>
                <th ><?php echo e(lang('name')); ?></th>
                <th width="30"><?php echo e(lang('hits')); ?></th>
                <th width="30"><?php echo e(lang('score')); ?></th>
                <th width="30"><?php echo e(lang('level')); ?></th>
                <th width="30"><?php echo e(lang('browse')); ?></th>
                <th width="150"><?php echo e(lang('update_time')); ?></th>
                <th width="100"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.topic_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.topic_id); ?></td>
                <td><a target="_blank" class="layui-badge-rim " href="<?php echo e(mac_url_topic_detail($vo)); ?>"><?php echo e($vo.topic_name|htmlspecialchars); ?></a> <?php if($vo.topic_status eq 0): ?> <span class="layui-badge"><?php echo e(lang('reviewed_not')); ?></span><?php endif; ?> </td>
                <td><?php echo e($vo.topic_hits); ?></td>
                <td><?php echo e($vo.topic_score); ?></td>
                <td><a data-href="<?php echo e(url('index/select')); ?>?tab=topic&col=topic_level&tpl=select_level&url=topic/field&ids=<?php echo e($vo.topic_id); ?>" data-width="270" data-height="100" class=" j-select"><span class="layui-badge layui-bg-orange"><?php echo e($vo.topic_level); ?></span></a></td>
                <td><?php if($vo.ismake eq 1): ?><a target="_blank" class="layui-badge layui-bg-green " href="<?php echo e(mac_url_topic_detail($vo)); ?>">Y</a>{else/}<a class="layui-badge" href="<?php echo e(url('make/make?ac=topic_info')); ?>?topic=<?php echo e($vo.topic_id); ?>&ref=1">N</a><?php endif; ?></td>
                <td><?php echo e($vo.topic_time|mac_day='color'); ?></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info?id='.$vo['topic_id'])); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['topic_id'])); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
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
    var curUrl="<?php echo e(url('topic/data',$param)); ?>";
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


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\topic\index.blade.php ENDPATH**/ ?>