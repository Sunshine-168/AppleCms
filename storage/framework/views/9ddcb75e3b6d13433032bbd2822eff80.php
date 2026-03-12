<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">
        <form class="layui-form layui-form-pane" action="<?php echo e(route('admin.system.configseo')); ?>" method="post">
            <?php echo csrf_field(); ?>
            <div class="layui-tab" lay-filter="tb1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="configseo_1"><?php echo e(__('admin/system/configseo/vod_index')); ?>SEO</li>
                    <li lay-id="configseo_2"><?php echo e(__('admin/system/configseo/art_index')); ?>SEO</li>
                    <li lay-id="configseo_3"><?php echo e(__('admin/system/configseo/actor_index')); ?>SEO</li>
                    <li lay-id="configseo_4"><?php echo e(__('admin/system/configseo/role_index')); ?>SEO</li>
                    <li lay-id="configseo_5"><?php echo e(__('admin/system/configseo/plot_index')); ?>SEO</li>
                    <li lay-id="configseo_6"><?php echo e(__('admin/system/configseo/website_index')); ?>SEO</li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <div class="page-tip-blue">
                            <strong><?php echo e(__('admin/system/configseo/tip_des')); ?>：</strong><br>
                            <?php echo e(__('admin/system/configseo/vod_index')); ?> vod/index
                        </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin/system/configseo/tit')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[name]" placeholder="<?php echo e(__('admin/system/configseo/tit')); ?>title" value="<?php echo e($config['vod']['name'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin/system/configseo/key')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[key]" placeholder="<?php echo e(__('admin/system/configseo/key')); ?>keywords" value="<?php echo e($config['vod']['key'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin/system/configseo/des')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[des]" placeholder="<?php echo e(__('admin/system/configseo/des')); ?>description" value="<?php echo e($config['vod']['des'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>

            </div>
                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong><?php echo e(__('admin/system/configseo/tip_des')); ?>：</strong><br>
                            <?php echo e(__('admin/system/configseo/art_index')); ?> art/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/tit')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[name]" placeholder="<?php echo e(__('admin/system/configseo/tit')); ?>title" value="<?php echo e($config['art']['name'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/key')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[key]" placeholder="<?php echo e(__('admin/system/configseo/key')); ?>keywords" value="<?php echo e($config['art']['key'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/des')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[des]" placeholder="<?php echo e(__('admin/system/configseo/des')); ?>description" value="<?php echo e($config['art']['des'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>

                    </div>


                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong><?php echo e(__('admin/system/configseo/tip_des')); ?>：</strong><br>
                            <?php echo e(__('admin/system/configseo/actor_index')); ?> actor/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/tit')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[name]" placeholder="<?php echo e(__('admin/system/configseo/tit')); ?>title" value="<?php echo e($config['actor']['name'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/key')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[key]" placeholder="<?php echo e(__('admin/system/configseo/key')); ?>keywords" value="<?php echo e($config['actor']['key'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/des')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[des]" placeholder="<?php echo e(__('admin/system/configseo/des')); ?>description" value="<?php echo e($config['actor']['des'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong><?php echo e(__('admin/system/configseo/tip_des')); ?>：</strong><br>
                            <?php echo e(__('admin/system/configseo/role_index')); ?> role/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/tit')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[name]" placeholder="<?php echo e(__('admin/system/configseo/tit')); ?>title" value="<?php echo e($config['role']['name'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/key')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[key]" placeholder="<?php echo e(__('admin/system/configseo/key')); ?>keywords" value="<?php echo e($config['role']['key'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/des')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[des]" placeholder="<?php echo e(__('admin/system/configseo/des')); ?>description" value="<?php echo e($config['role']['des'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong><?php echo e(__('admin/system/configseo/tip_des')); ?>：</strong><br>
                            <?php echo e(__('admin/system/configseo/plot_index')); ?> plot/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/tit')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="plot[name]" placeholder="<?php echo e(__('admin/system/configseo/tit')); ?>title" value="<?php echo e($config['plot']['name'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/key')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="plot[key]" placeholder="<?php echo e(__('admin/system/configseo/key')); ?>keywords" value="<?php echo e($config['plot']['key'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/des')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="plot[des]" placeholder="<?php echo e(__('admin/system/configseo/des')); ?>description" value="<?php echo e($config['plot']['des'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>

                    </div>


                    <div class="layui-tab-item">
                        <div class="page-tip-blue">
                            <strong><?php echo e(__('admin/system/configseo/tip_des')); ?>：</strong><br>
                            <?php echo e(__('admin/system/configseo/website_index')); ?> website/index
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/tit')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[name]" placeholder="<?php echo e(__('admin/system/configseo/tit')); ?>title" value="<?php echo e($config['website']['name'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/key')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[key]" placeholder="<?php echo e(__('admin/system/configseo/key')); ?>keywords" value="<?php echo e($config['website']['key'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin/system/configseo/des')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[des]" placeholder="<?php echo e(__('admin/system/configseo/des')); ?>description" value="<?php echo e($config['website']['des'] ?? ''); ?>" class="layui-input">
                            </div>
                        </div>

                    </div>

                </div>
        </div>
            <div class="layui-form-item center">
                <div class="layui-input-block">
                    <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                    <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
                </div>
            </div>
    </form>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="<?php echo e(asset('static/js/jquery.cookie.js')); ?>"></script>
<script type="text/javascript">
    layui.use(['element', 'form', 'layer'], function() {
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer;


        element.on('tab(tb1)', function(){
            $.cookie('configseo_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configseo_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configseo_tab'));
        }

    });
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\system\configseo.blade.php ENDPATH**/ ?>