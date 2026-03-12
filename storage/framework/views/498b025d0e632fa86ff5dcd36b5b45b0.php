<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>
    
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="vod_id" value="<?php echo e($info.vod_id); ?>">

        <div class="layui-tab">
            <ul class="layui-tab-title ">
                <li class="layui-this"><?php echo e(__('admin.admin/domain/title')); ?></a></li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">

                    <blockquote class="layui-elem-quote layui-quote-nm">
                        <?php echo e(__('admin.admin/domain/help_tip')); ?>

                        <a class="layui-btn layui-btn-primary" href="<?php echo e(url('export')); ?>" ><?php echo e(__('admin.export')); ?></a>
                        <a class="layui-btn layui-btn-primary layui-upload" data-href="<?php echo e(url('import')); ?>" ><?php echo e(__('admin.import')); ?></a>
                    </blockquote>

                    <script>
                        var arr_len = <?php echo e($domain_list|count); ?>;
                    </script>
                    {php}
                    $n=0;
                    {/php}

                    <div id="domain_list" class="contents">
                        <?php $__currentLoopData = $$domain_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        {php}
                        $n++;
                        {/php}
                        <div class="layui-form-item tr" data-i="<?php echo e($key); ?>">
                        <label class="layui-form-label"><?php echo e(__('admin.website')); ?><?php echo e($n); ?>：</label>
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_url][]" class="layui-input" placeholder="<?php echo e(__('admin.domain')); ?>" value="<?php echo e($vo.site_url); ?>"></div>&nbsp;
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_name][]" class="layui-input" placeholder="<?php echo e(__('admin.site_name')); ?>" value="<?php echo e($vo.site_name); ?>"></div>&nbsp;
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_keywords][]" class="layui-input" placeholder="<?php echo e(__('admin.keywords')); ?>" value="<?php echo e($vo.site_keywords); ?>"></div>&nbsp;
                            <div class="layui-input-inline w150"><input type="text" name="domain[site_description][]" class="layui-input" placeholder="<?php echo e(__('admin.description')); ?>" value="<?php echo e($vo.site_description); ?>"></div>&nbsp;
                            <div class="layui-input-inline w150"><select name="domain[template_dir][]"><option value="no"><?php echo e(__('admin.select_template')); ?>.</option><?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($vo2); ?>" <?php if($vo2 == $vo.template_dir): ?>selected@endif><?php echo e($vo2); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                            <div class="layui-input-inline w150"><input type="text" name="domain[html_dir][]" class="layui-input" placeholder="<?php echo e(__('admin.tpl_dir')); ?>" value="<?php echo e($vo.html_dir); ?>"></div>
                            <div class="layui-input-inline w150"><input type="text" name="domain[ads_dir][]" class="layui-input" placeholder="<?php echo e(__('admin.ads_dir')); ?>" value="<?php echo e($vo.ads_dir); ?>"></div>
                            <div> <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['site_url'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a></div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="layui-form-item">
                        <label class=""><button class="layui-btn radius j-player-add" type="button"><?php echo e(__('admin.add_group')); ?></button></label>
                        <div class="layui-input-block">

                        </div>
                    </div>


        </div>

            </div>
        </div>

                <div class="layui-form-item center">
                    <div class="layui-input-block">

                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child=""><?php echo e(__('admin.btn_save')); ?></button>
                        <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
                    </div>
                </div>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    var template_select='<?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($vo); ?>"><?php echo e($vo); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';

    layui.use(['form','layer','upload'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
            , upload = layui.upload;


        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(url('domain/import')); ?>"
            ,method: 'post'
            ,exts:'txt'
            ,before: function(input) {
                layer.msg("<?php echo e(__('admin.upload_ing')); ?>", {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                    return false;
                }
                location.reload();
            }
        });

        $('.j-player-add').on('click',function(){
            arr_len++;
            var tpl='<div class="layui-form-item" ><label class="layui-form-label"><?php echo e(__('admin.website')); ?>：'+arr_len+'</label><div class="layui-input-inline w150"><input type="text" name="domain[site_url][]" class="layui-input" placeholder="<?php echo e(__('admin.domain')); ?>" ></div>&nbsp;<div class="layui-input-inline w150"><input type="text" name="domain[site_name][]" class="layui-input" placeholder="<?php echo e(__('admin.site_name')); ?>"></div>&nbsp;<div class="layui-input-inline w150"><input type="text" name="domain[site_keywords][]" class="layui-input" placeholder="<?php echo e(__('admin.keywords')); ?>" ></div>&nbsp;<div class="layui-input-inline w150"><input type="text" name="domain[site_description][]" class="layui-input" placeholder="<?php echo e(__('admin.description')); ?>" ></div>&nbsp;<div class="layui-input-inline w150"><select name="domain[template_dir][]"><option value="no"><?php echo e(__('admin.select_template')); ?>.</option>'+template_select+'</select></div><div class="layui-input-inline w150"><input type="text" name="domain[html_dir][]" class="layui-input" placeholder="<?php echo e(__('admin.tpl_dir')); ?>" ></div><div class="layui-input-inline w150"><input type="text" name="domain[ads_dir][]" class="layui-input" placeholder="<?php echo e(__('admin.ads_dir')); ?>" ></div><div><a href="javascript:void(0)" class="j-editor-remove"><?php echo e(__('admin.del')); ?></a>&nbsp;</div></div>';
            $("#domain_list").append(tpl);

            form.render('select');
        });

        if(arr_len==0) {
            $('.j-player-add').click();
        }
    });
    
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\domain\index.blade.php ENDPATH**/ ?>