<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">
        <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.system.configapi')); ?>">
            <?php echo csrf_field(); ?>
            <div class="layui-tab" lay-filter="tb1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="configapi_1"><?php echo e(__('admin.admin/system/configapi/vod')); ?></li>
                    <li lay-id="configapi_2"><?php echo e(__('admin.admin/system/configapi/art')); ?></li>
                    <li lay-id="configapi_3"><?php echo e(__('admin.admin/system/configapi/actor')); ?></li>
                    <li lay-id="configapi_4"><?php echo e(__('admin.admin/system/configapi/role')); ?></li>
                    <li lay-id="configapi_5"><?php echo e(__('admin.admin/system/configapi/website')); ?></li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">

                        <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
                            <?php echo __('admin.admin/system/configapi/vod_tip'); ?>

                        </blockquote>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/status')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="vod[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'vod.status', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="vod[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'vod.status', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/charge')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="vod[charge]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'vod.charge', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="vod[charge]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'vod.charge', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/detail_inc_hits')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="radio" name="vod[detail_inc_hits]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'vod.detail_inc_hits', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="vod[detail_inc_hits]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'vod.detail_inc_hits', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configapi/detail_inc_hits_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin.admin/system/configapi/pagesize')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[pagesize]" placeholder="<?php echo e(__('admin.admin/system/configapi/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'vod.pagesize', '')); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin.admin/system/configapi/imgurl')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[imgurl]" placeholder="<?php echo e(__('admin.admin/system/configapi/imgurl_tip')); ?>" value="<?php echo e(data_get($config, 'vod.imgurl', '')); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin.admin/system/configapi/typefilter')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[typefilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/typefilter_tip')); ?>" value="<?php echo e(data_get($config, 'vod.typefilter', '')); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin.admin/system/configapi/datafilter')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="text" name="vod[datafilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/datafilter_tip')); ?>" value="<?php echo e(data_get($config, 'vod.datafilter', '')); ?>" class="layui-input">
                    </div>
                </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/cachetime')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="vod[cachetime]" placeholder="<?php echo e(__('admin.admin/system/configapi/cachetime_tip')); ?>" value="<?php echo e(data_get($config, 'vod.cachetime', '')); ?>" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/from')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="vod[from]" placeholder="<?php echo e(__('admin.admin/system/configapi/from_tip')); ?>" value="<?php echo e(data_get($config, 'vod.from', '')); ?>" class="layui-input">
                            </div>
                        </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/auth')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="vod[auth]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'vod.auth', ''))); ?></textarea>
                    </div>
                </div>

            </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
                            <?php echo __('admin.admin/system/configapi/art_tip'); ?>

                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/status')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="art[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'art.status', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="art[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'art.status', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/charge')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="art[charge]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'art.charge', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="art[charge]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'art.charge', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/pagesize')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[pagesize]" placeholder="<?php echo e(__('admin.admin/system/configapi/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'art.pagesize', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/imgurl')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[imgurl]" placeholder="<?php echo e(__('admin.admin/system/configapi/imgurl_tip')); ?>" value="<?php echo e(data_get($config, 'art.imgurl', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/typefilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[typefilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/typefilter_tip')); ?>" value="<?php echo e(data_get($config, 'art.typefilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/datafilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[datafilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/datafilter_tip_art')); ?>" value="<?php echo e(data_get($config, 'art.datafilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/cachetime')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="art[cachetime]" placeholder="<?php echo e(__('admin.admin/system/configapi/cachetime_tip')); ?>" value="<?php echo e(data_get($config, 'art.cachetime', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/auth')); ?>：</label>
                            <div class="layui-input-block">
                                <textarea name="art[auth]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'art.auth', ''))); ?></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
                            <?php echo __('admin.admin/system/configapi/actor_tip'); ?>

                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/status')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="actor[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'actor.status', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="actor[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'actor.status', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/charge')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="actor[charge]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'actor.charge', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="actor[charge]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'actor.charge', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/pagesize')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[pagesize]" placeholder="<?php echo e(__('admin.admin/system/configapi/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'actor.pagesize', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/imgurl')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[imgurl]" placeholder="<?php echo e(__('admin.admin/system/configapi/imgurl_tip')); ?>" value="<?php echo e(data_get($config, 'actor.imgurl', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/typefilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[typefilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/typefilter_tip')); ?>" value="<?php echo e(data_get($config, 'actor.typefilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/datafilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[datafilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/datafilter_tip_actor')); ?>" value="<?php echo e(data_get($config, 'actor.datafilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/cachetime')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="actor[cachetime]" placeholder="<?php echo e(__('admin.admin/system/configapi/cachetime_tip')); ?>" value="<?php echo e(data_get($config, 'actor.cachetime', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/auth')); ?>：</label>
                            <div class="layui-input-block">
                                <textarea name="actor[auth]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'actor.auth', ''))); ?></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
                            <?php echo __('admin.admin/system/configapi/role_tip'); ?>

                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/status')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="role[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'role.status', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="role[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'role.status', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/charge')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="role[charge]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'role.charge', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="role[charge]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'role.charge', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/pagesize')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[pagesize]" placeholder="<?php echo e(__('admin.admin/system/configapi/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'role.pagesize', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/imgurl')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[imgurl]" placeholder="<?php echo e(__('admin.admin/system/configapi/imgurl_tip')); ?>" value="<?php echo e(data_get($config, 'role.imgurl', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/typefilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[typefilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/typefilter_tip')); ?>" value="<?php echo e(data_get($config, 'role.typefilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/datafilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[datafilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/datafilter_tip_role')); ?>" value="<?php echo e(data_get($config, 'role.datafilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/cachetime')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="role[cachetime]" placeholder="<?php echo e(__('admin.admin/system/configapi/cachetime_tip')); ?>" value="<?php echo e(data_get($config, 'role.cachetime', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/auth')); ?>：</label>
                            <div class="layui-input-block">
                                <textarea name="role[auth]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'role.auth', ''))); ?></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
                            <?php echo __('admin.admin/system/configapi/website_tip'); ?>

                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/status')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="website[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'website.status', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="website[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'website.status', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/charge')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="website[charge]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'website.charge', '0') !== '1'): echo 'checked'; endif; ?>>
                                <input type="radio" name="website[charge]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'website.charge', '0') === '1'): echo 'checked'; endif; ?>>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/pagesize')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[pagesize]" placeholder="<?php echo e(__('admin.admin/system/configapi/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'website.pagesize', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/imgurl')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[imgurl]" placeholder="<?php echo e(__('admin.admin/system/configapi/imgurl_tip')); ?>" value="<?php echo e(data_get($config, 'website.imgurl', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/typefilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[typefilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/typefilter_tip')); ?>" value="<?php echo e(data_get($config, 'website.typefilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/datafilter')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[datafilter]" placeholder="<?php echo e(__('admin.admin/system/configapi/datafilter_tip_website')); ?>" value="<?php echo e(data_get($config, 'website.datafilter', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <?php echo e(__('admin.admin/system/configapi/cachetime')); ?>：</label>
                            <div class="layui-input-block">
                                <input type="text" name="website[cachetime]" placeholder="<?php echo e(__('admin.admin/system/configapi/cachetime_tip')); ?>" value="<?php echo e(data_get($config, 'website.cachetime', '')); ?>" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configapi/auth')); ?>：</label>
                            <div class="layui-input-block">
                                <textarea name="website[auth]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'website.auth', ''))); ?></textarea>
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
<script type="text/javascript" src="<?php echo e(asset('static')); ?>/js/jquery.cookie.js"></script>
<script type="text/javascript">
    layui.use(['element', 'form', 'layer'], function() {
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer;


        element.on('tab(tb1)', function(){
            $.cookie('configapi_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configapi_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configapi_tab'));
        }

    });
</script>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views/admin/system/configapi.blade.php ENDPATH**/ ?>