<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(url('data')); ?>">
                <div class="layui-input-inline w100">
                    <select name="status">
                        <option value=""><?php echo e(__('admin.select_status')); ?></option>
                        <option value="0" <?php if(condition="$param['status'] == '0'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed_not')); ?></option>
                        <option value="1" <?php if(condition="$param['status'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="mid">
                        <option value=""><?php echo e(__('admin.select_model')); ?></option>
                        <option value="1" <?php if(condition="$param['mid'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.vod')); ?></option>
                        <option value="2" <?php if(condition="$param['mid'] == '2'"): ?>selected <?php endif; ?>><?php echo e(__('admin.art')); ?></option>
                        <option value="3" <?php if(condition="$param['mid'] == '3'"): ?>selected <?php endif; ?>><?php echo e(__('admin.topic')); ?></option>
                        <option value="8" <?php if(condition="$param['mid'] == '8'"): ?>selected <?php endif; ?>><?php echo e(__('admin.actor')); ?></option>
                        <option value="9" <?php if(condition="$param['mid'] == '9'"): ?>selected <?php endif; ?>><?php echo e(__('admin.role')); ?></option>
                        <option value="11" <?php if(condition="$param['mid'] == '11'"): ?>selected <?php endif; ?>><?php echo e(__('admin.website')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="report">
                        <option value=""><?php echo e(__('admin.select_report')); ?></option>
                        <option value="1" <?php if(condition="$param['report'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.report_not')); ?></option>
                        <option value="2" <?php if(condition="$param['report'] == '2'"): ?>selected <?php endif; ?>><?php echo e(__('admin.report_yes')); ?></option>
                    </select>
                </div>

                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=comment&col=comment_status&tpl=select_status&url=comment/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(__('admin.status')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>?all=1" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('admin.clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.clear')); ?></a>

            <a  data-href="<?php echo e(url('comment/blacklist')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe63c;</i><?php echo e(__('admin.blacklist_keywords')); ?></a>
            <a  data-href="<?php echo e(url('comment/blacklist_ip')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe63c;</i><?php echo e(__('admin.blacklist_ip')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm" >
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="60"><?php echo e(__('admin.id')); ?></th>
                <th width="60"><?php echo e(__('admin.model')); ?></th>
                <th width="60"><?php echo e(__('admin.status')); ?></th>
                <th ><?php echo e(__('admin.content')); ?></th>
                <th width="100"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.comment_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.comment_id); ?></td>
                <td><?php echo e($vo.comment_mid|mac_get_mid_text); ?></td>
                <td><?php if($vo.comment_status == 0): ?><span class="layui-badge"><?php echo e(__('admin.reviewed_not')); ?></span>{else}<span class="layui-badge layui-bg-green"><?php echo e(__('admin.reviewed')); ?></span><?php endif; ?></td>
                <td>
                    <div class="c-999 f-12">
                        <u style="cursor:pointer" class="text-primary"><?php echo e($vo.comment_name|htmlspecialchars); ?>：</u>
                        <time>【<?php echo e($vo.comment_time|mac_day='color'); ?>】</time>
                        <span class="ml-20">ip：【<?php echo e($vo.comment_ip|long2ip); ?>】</span>
                        <span class="ml-20"><?php echo e(__('admin.up')); ?>：【<?php echo e($vo.comment_up); ?>】</span>
                        <span class="ml-20"><?php echo e(__('admin.hate')); ?>：【<?php echo e($vo.comment_down); ?>】</span>
                        <span class="ml-20"><?php echo e(__('admin.report')); ?>：【<?php echo e($vo.comment_report); ?>】</span>
                        <span class="ml-20"><?php echo e(__('admin.link')); ?>：
                            <?php if(!is_array($vo.data)): ?>
                            【<?php echo e(__('admin.del_data')); ?>】
                            {elseif condition="$vo.comment_mid == 1"}
                            【<a target="_blank" href="<?php echo e($vo.data|mac_url_vod_detail); ?>"><?php echo e($vo.data.vod_name); ?></a>】</span>
                            {elseif condition="$vo.comment_mid == 2"}
                            【<a target="_blank" href="<?php echo e($vo.data|mac_url_art_detail); ?>"><?php echo e($vo.data.art_name); ?></a>】</span>
                            {elseif condition="$vo.comment_mid == 3"}
                            【<a target="_blank" href="<?php echo e($vo.data|mac_url_topic_detail); ?>"><?php echo e($vo.data.topic_name); ?></a>】</span>
                            {elseif condition="$vo.comment_mid == 8"}
                            【<a target="_blank" href="<?php echo e($vo.data|mac_url_actor_detail); ?>"><?php echo e($vo.data.actor_name); ?></a>】</span>
                            {elseif condition="$vo.comment_mid == 9"}
                            【<a target="_blank" href="<?php echo e($vo.data|mac_url_role_detail); ?>"><?php echo e($vo.data.role_name); ?></a>】</span>
                            <?php endif; ?>
                    </div>
                    <div class="f-12 c-999">
                        <?php echo e(__('admin.comment')); ?>：<?php echo e($vo.comment_content|htmlspecialchars); ?>

                    </div>
                </td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info?id='.$vo['comment_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['comment_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
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
    var curUrl="<?php echo e(url('comment/data',$param)); ?>";
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\comment\index.blade.php ENDPATH**/ ?>