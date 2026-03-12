<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.urlsend.index')); ?>">
        <?php echo csrf_field(); ?>
        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                <?php $__currentLoopData = $extends['ext_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li data-key="<?php echo e($key); ?>" lay-id="urlsend_<?php echo e($key); ?>" class="<?php echo e($loop->first ? 'layui-this' : ''); ?>"><?php echo e($label); ?><?php echo e(__('admin.config')); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <div class="layui-tab-content">
                <?php echo $extends['ext_html']; ?>

            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>

    <form class="layui-form layui-form-pane" method="get" action="<?php echo e(route('admin.urlsend.push')); ?>" target="_blank" id="form_post">
        <blockquote class="layui-elem-quote">
            <?php echo e(__('admin/urlsend/tip2')); ?><?php echo e($siteUrl); ?><br>
        </blockquote>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin/urlsend/send_genre')); ?>：</label>
            <div class="layui-input-inline">
                <select class="w150" id="ac" name="ac">
                    <?php $__currentLoopData = $extends['ext_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin/urlsend/send_range')); ?>：</label>
            <div class="layui-input-inline w300">
                <input type="radio" name="range" value="0" title="<?php echo e(__('admin/urlsend/add_update')); ?>" checked>
                <input type="radio" name="range" value="1" title="<?php echo e(__('admin/urlsend/add')); ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin/urlsend/page_send_num')); ?>：</label>
            <div class="layui-input-inline w200">
                <input type="text" name="limit" id="limit" value="50" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin/urlsend/start_page')); ?>：</label>
            <div class="layui-input-inline w200">
                <input type="text" name="page" id="page" value="1" class="layui-input">
            </div>
        </div>

        <hr class="layui-bg-gray">
        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('vod')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=1');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('vod')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=1');">

        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('art')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=2');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('art')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=2');">
        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('topic')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=3');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('topic')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=3');">
        <hr class="layui-bg-gray">
        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('actor')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=8');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('actor')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=8');">
        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('role')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=9');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('role')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=9');">
        <hr class="layui-bg-gray">
        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('website')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=11');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('website')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=11');">
        <input type="button" value="<?php echo e(__('that_day')); ?><?php echo e(__('manga')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=today&mid=12');">
        <input type="button" value="<?php echo e(__('all')); ?><?php echo e(__('manga')); ?>" class="layui-btn layui-btn-primary" onclick="postPush('ac2=all&mid=12');">

        <?php if(!empty($urlsendBreakBaiduPush)): ?>
            <hr class="layui-bg-gray">
            <a href="<?php echo e($urlsendBreakBaiduPush); ?>" target="_blank" class="layui-btn layui-btn-danger">【<?php echo e(__('admin/urlsend/in_break_point_exec')); ?> - Baidu】</a>
        <?php endif; ?>
        <?php if(!empty($urlsendBreakBaidufastPush)): ?>
            <a href="<?php echo e($urlsendBreakBaidufastPush); ?>" target="_blank" class="layui-btn layui-btn-danger">【<?php echo e(__('admin/urlsend/in_break_point_exec')); ?> - Baidufast】</a>
        <?php endif; ?>
    </form>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="<?php echo e(asset('static/js/jquery.cookie.js')); ?>"></script>
<script type="text/javascript">
    layui.use(['element'], function () {
        var element = layui.element;
        element.on('tab(tb1)', function () {
            $.cookie('urlsend_tab', this.getAttribute('lay-id'));
        });
        if ($.cookie('urlsend_tab') != null) {
            element.tabChange('tb1', $.cookie('urlsend_tab'));
        }
    });

    function postPush(extraQuery) {
        var limit = $('#limit').val();
        var page = $('#page').val();
        var ac = $('#ac').val();
        var range = $('input[name="range"]:checked').val();
        var action = "<?php echo e(route('admin.urlsend.push')); ?>" + '?' + 'ac=' + encodeURIComponent(ac) + '&limit=' + encodeURIComponent(limit) + '&page=' + encodeURIComponent(page) + '&range=' + encodeURIComponent(range) + '&' + extraQuery;
        $('#form_post').attr('action', action).submit();
    }
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\urlsend\index.blade.php ENDPATH**/ ?>