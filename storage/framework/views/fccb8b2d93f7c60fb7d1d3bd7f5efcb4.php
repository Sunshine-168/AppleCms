<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">

    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>

    <form class="layui-form layui-form-pane" action="<?php echo e(route('admin.system.config')); ?>" method="post">
        <?php echo csrf_field(); ?>
        <?php
            $cfg = $config ?? [];
        ?>

        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                <li class="layui-this" lay-id="config_1"><?php echo e(__('admin/system/config/base')); ?></li>
                <li lay-id="config_2"><?php echo e(__('admin/system/config/performance')); ?></li>
                <li lay-id="config_3"><?php echo e(__('admin/system/config/parameters')); ?></li>
                <li lay-id="config_4"><?php echo e(__('admin/system/config/backstage')); ?></li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_name')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_name]" placeholder="" value="<?php echo e($config['site']['site_name'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_url')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_url]" placeholder="<?php echo e(__('admin/system/config/site_url_tip')); ?>" value="<?php echo e($config['site']['site_url'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/site_wapurl')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="site[site_wapurl]" placeholder="<?php echo e(__('admin/system/config/site_wapurl_tip')); ?>" value="<?php echo e($config['site']['site_wapurl'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_keywords')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_keywords]" placeholder="" value="<?php echo e($config['site']['site_keywords'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_description')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_description]" placeholder="" value="<?php echo e($config['site']['site_description'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_icp')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_icp]" placeholder="" value="<?php echo e($config['site']['site_icp'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_qq')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_qq]" placeholder="" value="<?php echo e($config['site']['site_qq'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_email')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_email]" placeholder="" value="<?php echo e($config['site']['site_email'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/install_dir')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="site[install_dir]" placeholder="<?php echo e(__('admin/system/config/install_dir_tip')); ?>" value="<?php echo e($config['site']['install_dir'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/site_logo')); ?></label>
                        <div class="layui-input-inline w600">
                            <input type="text" name="site[site_logo]" placeholder="<?php echo e(__('admin/system/config/site_logo_tip')); ?>" value="<?php echo e($config['site']['site_logo'] ?? ''); ?>" class="layui-input upload-input">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'site[site_logo]'}}" id="upload1"><?php echo e(__('admin.upload_pic')); ?></button>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/site_waplogo')); ?></label>
                        <div class="layui-input-inline w600">
                            <input type="text" name="site[site_waplogo]" placeholder="<?php echo e(__('admin/system/config/site_logo_tip')); ?>" value="<?php echo e($config['site']['site_waplogo'] ?? ''); ?>" class="layui-input upload-input">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'upload-thumb'}}" id="upload2"><?php echo e(__('admin.upload_pic')); ?></button>
                        </div>
                    </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/template_dir')); ?></label>
                    <div class="layui-input-inline" >
                            <select class="w150" name="site[template_dir]">
                                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vo); ?>" <?php if((($config['site']['template_dir'] ?? '') == $vo)): echo 'selected'; endif; ?>><?php echo e($vo); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                    </div>
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/html_dir')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="site[html_dir]" placeholder="" value="<?php echo e($config['site']['html_dir'] ?? ''); ?>" class="layui-input w150" >
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_polyfill')); ?></label>
                    <div class="layui-input-inline w300">
                        <input type="radio" name="site[site_polyfill]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['site']['site_polyfill'] ?? 1) == 0)): echo 'checked'; endif; ?>>
                        <input type="radio" name="site[site_polyfill]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((!isset($config['site']['site_polyfill']) || ($config['site']['site_polyfill'] ?? 1) == 1)): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/site_polyfill_tip')); ?></div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/mob_status')); ?></label>
                    <div class="layui-input-inline w600">
                        <input type="radio" name="site[mob_status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['site']['mob_status'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="site[mob_status]" value="1" title="<?php echo e(__('admin/system/config/mob_multiple')); ?>" <?php if((($config['site']['mob_status'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="site[mob_status]" value="2" title="<?php echo e(__('admin/system/config/mob_one')); ?>" <?php if((($config['site']['mob_status'] ?? 0) == 2)): echo 'checked'; endif; ?>>
                        <span class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/mob_status_tip')); ?></span>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/mob_template_dir')); ?></label>
                    <div class="layui-input-inline">
                            <select class="w150" name="site[mob_template_dir]">
                                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vo); ?>" <?php if((($config['site']['mob_template_dir'] ?? '') == $vo)): echo 'selected'; endif; ?>><?php echo e($vo); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                    </div>
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/html_dir')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="site[mob_html_dir]" placeholder="" value="<?php echo e($config['site']['mob_html_dir'] ?? ''); ?>" class="layui-input w150" >
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/site_tj')); ?></label>
                    <div class="layui-input-block">
                        <textarea name="site[site_tj]" class="layui-textarea"  placeholder=""><?php echo e($config['site']['site_tj'] ?? ''); ?></textarea>
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/site_status')); ?></label>
                        <div class="layui-input-block">
                            <input type="radio" name="site[site_status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['site']['site_status'] ?? 0) == 0)): echo 'checked'; endif; ?>>
                            <input type="radio" name="site[site_status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['site']['site_status'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/site_close_tip')); ?></label>
                        <div class="layui-input-block">
                            <textarea name="site[site_close_tip]" class="layui-textarea"  placeholder=""><?php echo e($config['site']['site_close_tip'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/mainland_ip_limit')); ?></label>
                        <div class="layui-input-block">
                            <input type="radio" name="site[mainland_ip_limit]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((!isset($config['site']['mainland_ip_limit']) || ($config['site']['mainland_ip_limit'] ?? 0) == 0)): echo 'checked'; endif; ?>>
                            <input type="radio" name="site[mainland_ip_limit]" value="1" title="<?php echo e(__('admin/system/config/mainland_ip_only_allow')); ?>" <?php if((($config['site']['mainland_ip_limit'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                            <input type="radio" name="site[mainland_ip_limit]" value="2" title="<?php echo e(__('admin/system/config/mainland_ip_not_allow')); ?>" <?php if((($config['site']['mainland_ip_limit'] ?? 0) == 2)): echo 'checked'; endif; ?>>
                        </div>
                    </div>
            </div>

                <div class="layui-tab-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/pathinfo_depr')); ?></label>
                        <div class="layui-input-inline w150">
                            <select class="w150" name="app[pathinfo_depr]">
                                <option value="/" <?php if((($config['app']['pathinfo_depr'] ?? '/') == '/')): echo 'selected'; endif; ?>><?php echo e(__('admin/system/config/xg')); ?></option>
                                <option value="-" <?php if((($config['app']['pathinfo_depr'] ?? '/') == '-')): echo 'selected'; endif; ?>><?php echo e(__('admin/system/config/zhx')); ?></option>
                                <option value="_" <?php if((($config['app']['pathinfo_depr'] ?? '/') == '_')): echo 'selected'; endif; ?>><?php echo e(__('admin/system/config/xhx')); ?></option>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/pathinfo_depr_tip')); ?></div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/suffix')); ?></label>
                        <div class="layui-input-inline">
                            <select style="width:150px;" name="app[suffix]">
                                <option value="html" <?php if((($config['app']['suffix'] ?? 'html') == 'html')): echo 'selected'; endif; ?>>html</option>
                                <option value="htm" <?php if((($config['app']['suffix'] ?? 'html') == 'htm')): echo 'selected'; endif; ?>>htm</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/popedom_filter')); ?></label>
                        <div class="layui-input-inline">
                            <input type="radio" name="app[popedom_filter]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['popedom_filter'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                            <input type="radio" name="app[popedom_filter]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['popedom_filter'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/popedom_filter_tip')); ?></div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_type')); ?></label>
                        <div class="layui-input-block">
                            <?php $cacheType = (string)($config['app']['cache_type'] ?? 'file'); ?>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="file" title="file" <?php if(($cacheType === 'file' || $cacheType === '0')): echo 'checked'; endif; ?>>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="memcache" title="memcache" <?php if(($cacheType === 'memcache' || $cacheType === '1')): echo 'checked'; endif; ?>>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="redis" title="redis" <?php if(($cacheType === 'redis' || $cacheType === '2')): echo 'checked'; endif; ?>>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="memcached" title="memcached" <?php if(($cacheType === 'memcached' || $cacheType === '3')): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item row_cache_server " <?php if(($cacheType === '0' || $cacheType === 'file')): ?> style="display:none;" <?php endif; ?>>
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_host')); ?></label>
                        <div class="layui-input-inline w150">
                            <input type="text" name="app[cache_host]" placeholder="<?php echo e(__('admin/system/config/cache_host_tip')); ?>" value="<?php echo e($config['app']['cache_host'] ?? ''); ?>" class="layui-input" >
                        </div>
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_port')); ?></label>
                        <div class="layui-input-inline w150">
                            <input type="text" name="app[cache_port]" placeholder="<?php echo e(__('admin/system/config/cache_port_tip')); ?>" value="<?php echo e($config['app']['cache_port'] ?? ''); ?>" class="layui-input" >
                        </div>
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_username')); ?></label>
                        <div class="layui-input-inline">
                            <input type="text" name="app[cache_username]" placeholder="<?php echo e(__('admin/system/config/cache_username_tip')); ?>" value="<?php echo e($config['app']['cache_username'] ?? ''); ?>" class="layui-input" >
                        </div>
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_password')); ?></label>
                        <div class="layui-input-inline">
                            <input type="text" name="app[cache_password]" placeholder="<?php echo e(__('admin/system/config/cache_password_tip')); ?>" value="<?php echo e($config['app']['cache_password'] ?? ''); ?>" class="layui-input" >
                        </div>
                        <label class="layui-form-label">DB</label>
                        <div class="layui-input-inline w150">
                            <input type="text" name="app[cache_db]" placeholder="Redis数据库索引(默认0)" value="<?php echo e($config['app']['cache_db'] ?? '0'); ?>" class="layui-input" >
                        </div>
                        <button type="button" class="layui-btn layui-btn-normal" onclick="test_cache()"><?php echo e(__('admin/system/config/cache_test')); ?></button>
                    </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_flag')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[cache_flag]" placeholder="<?php echo e(__('admin/system/config/cache_flag_auto')); ?>" value="<?php echo e($config['app']['cache_flag'] ?? ''); ?>" class="layui-input w150" >
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/cache_flag_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_core')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[cache_core]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['cache_core'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[cache_core]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['cache_core'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_time')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[cache_time]" placeholder="" value="<?php echo e($config['app']['cache_time'] ?? ''); ?>" class="layui-input w150" >
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/cache_time_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_page')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[cache_page]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['cache_page'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[cache_page]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['cache_page'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/cache_time_page')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[cache_time_page]" placeholder="" value="<?php echo e($config['app']['cache_time_page'] ?? ''); ?>" class="layui-input w150" >
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/cache_time_tip')); ?></div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/compress')); ?></label>
                        <div class="layui-input-block">
                            <input type="radio" name="app[compress]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['compress'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                            <input type="radio" name="app[compress]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['compress'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                <div class="layui-form-item layui-hide">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/input_type')); ?></label>
                    <div class="layui-input-inline w600">
                        <input type="radio" name="app[input_type]" value="1" title="<?php echo e(__('admin.get+post')); ?>" <?php if((($config['app']['input_type'] ?? 1) != 0)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[input_type]" value="0" title="<?php echo e(__('admin.get')); ?>" <?php if((($config['app']['input_type'] ?? 1) == 0)): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/input_type_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/ajax_page')); ?></label>
                    <div class="layui-input-inline w600">
                        <input type="radio" name="app[ajax_page]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['ajax_page'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[ajax_page]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['ajax_page'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/ajax_page_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/wall_filter')); ?></label>
                    <div class="layui-input-inline w600">
                        <?php $wf = (int)($config['app']['wall_filter'] ?? 0); ?>
                        <input type="radio" name="app[wall_filter]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if(($wf !== 1 && $wf !== 2)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[wall_filter]" value="1" title="<?php echo e(__('admin/system/config/wall_unicode')); ?>" <?php if(($wf === 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[wall_filter]" value="2" title="<?php echo e(__('admin/system/config/wall_blank')); ?>" <?php if(($wf === 2)): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/wall_filter_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/show')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[show]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['show'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[show]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['show'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/show_verify')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[show_verify]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['show_verify'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[show_verify]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['show_verify'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/search')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[search]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['search'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[search]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['search'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/search_verify')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[search_verify]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['search_verify'] ?? 0) != 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[search_verify]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['search_verify'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/search_len')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[search_len]" placeholder="" value="<?php echo e($config['app']['search_len'] ?? ''); ?>" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/search_len_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/search_timespan')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[search_timespan]" placeholder="" value="<?php echo e($config['app']['search_timespan'] ?? ''); ?>" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/search_timespan_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/search_vod_rule')); ?></label>
                    <div class="layui-input-inline w600">
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_name" title="<?php echo e(__('admin.name')); ?>" checked disabled>
                        <?php $svr = (string)($config['app']['search_vod_rule'] ?? ''); ?>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_en" title="<?php echo e(__('admin.en')); ?>" <?php if(strpos($svr,'vod_en') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_sub" title="<?php echo e(__('admin.sub')); ?>" <?php if(strpos($svr,'vod_sub') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_tag" title="<?php echo e(__('admin.tag')); ?>" <?php if(strpos($svr,'vod_tag') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_actor" title="<?php echo e(__('admin.actor')); ?>" <?php if(strpos($svr,'vod_actor') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_director" title="<?php echo e(__('admin.director')); ?>" <?php if(strpos($svr,'vod_director') !== false): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/search_rule_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/search_art_rule')); ?></label>
                    <div class="layui-input-inline w600">
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_name" title="<?php echo e(__('admin.name')); ?>" checked disabled>
                        <?php $sar = (string)($config['app']['search_art_rule'] ?? ''); ?>
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_en" title="<?php echo e(__('admin.en')); ?>" <?php if(strpos($sar,'art_en') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_sub" title="<?php echo e(__('admin.sub')); ?>" <?php if(strpos($sar,'art_sub') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_tag" title="<?php echo e(__('admin.tag')); ?>" <?php if(strpos($sar,'art_tag') !== false): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/search_rule_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_search_optimise')); ?></label>
                    <div class="layui-input-inline">
                        <?php $vso = (string)($config['app']['vod_search_optimise'] ?? ''); ?>
                        <input type="checkbox" lay-skin="primary" name="app[vod_search_optimise][]" value="frontend" title="<?php echo e(__('admin/system/config/vod_search_optimise/frontend')); ?>" <?php if(strpos($vso,'frontend') !== false): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="app[vod_search_optimise][]" value="collect" title="<?php echo e(__('admin/system/config/vod_search_optimise/collect')); ?>" <?php if(strpos($vso,'collect') !== false): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/vod_search_optimise_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_search_optimise_cache_minutes')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[vod_search_optimise_cache_minutes]" placeholder="" value="<?php echo e($config['app']['vod_search_optimise_cache_minutes'] ?? ''); ?>" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/vod_search_optimise_cache_minutes_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/copyright_status')); ?></label>
                    <div class="layui-input-block">
                        <?php $cs = (int)($config['app']['copyright_status'] ?? 0); ?>
                        <input type="radio" name="app[copyright_status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if(($cs === 0)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[copyright_status]" value="1" title="<?php echo e(__('admin/system/config/copyright_msg')); ?>" <?php if(($cs === 1)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[copyright_status]" value="2" title="<?php echo e(__('admin/system/config/copyright_jump_detail')); ?>" <?php if(($cs === 2)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[copyright_status]" value="3" title="<?php echo e(__('admin/system/config/copyright_jump_play')); ?>" <?php if(($cs === 3)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[copyright_status]" value="4" title="<?php echo e(__('admin/system/config/copyright_jump_iframe')); ?>" <?php if(($cs === 4)): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/copyright_notice')); ?></label>
                    <div class="layui-input-inline w500">
                        <input type="text" name="app[copyright_notice]" placeholder="" value="<?php echo e($config['app']['copyright_notice'] ?? ''); ?>" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/browser_junmp')); ?></label>
                    <div class="layui-input-inline">
                        <input type="radio" name="app[browser_junmp]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['browser_junmp'] ?? 0) == 0)): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[browser_junmp]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['browser_junmp'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/browser_junmp_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/404')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[page_404]" placeholder="" value="<?php echo e($config['app']['page_404'] ?? ''); ?>" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/404_tip')); ?></div>
                </div>
            </div>

            <div class="layui-tab-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/player_sort')); ?></label>
                        <div class="layui-input-inline">
                            <input type="radio" name="app[player_sort]" value="0" title="<?php echo e(__('admin.add')); ?>" <?php if((($config['app']['player_sort'] ?? 0) == 0)): echo 'checked'; endif; ?>>
                            <input type="radio" name="app[player_sort]" value="1" title="<?php echo e(__('admin/system/config/global')); ?>" <?php if((($config['app']['player_sort'] ?? 0) == 1)): echo 'checked'; endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/player_sort_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/encrypt')); ?></label>
                        <div class="layui-input-inline">
                            <select style="width:150px;" name="app[encrypt]">
                                <option value="0"><?php echo e(__('admin/system/config/encrypt_not')); ?></option>
                                <option value="1" <?php if((($config['app']['encrypt'] ?? 0) == 1)): echo 'selected'; endif; ?>>escape</option>
                                <option value="2" <?php if((($config['app']['encrypt'] ?? 0) == 2)): echo 'selected'; endif; ?>>base64</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/search_hot')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[search_hot]" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>" value="<?php echo e($config['app']['search_hot'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/art_extend_class')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[art_extend_class]" placeholder="" value="<?php echo e($config['app']['art_extend_class'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_class')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_class]" placeholder="" value="<?php echo e($config['app']['vod_extend_class'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_state')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_state]" placeholder="" value="<?php echo e($config['app']['vod_extend_state'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_version')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_version]" placeholder="" value="<?php echo e($config['app']['vod_extend_version'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_area')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_area]" placeholder="" value="<?php echo e($config['app']['vod_extend_area'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_lang')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_lang]" placeholder="" value="<?php echo e($config['app']['vod_extend_lang'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_year')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_year]" placeholder="" value="<?php echo e($config['app']['vod_extend_year'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/vod_extend_weekday')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_weekday]" placeholder="" value="<?php echo e($config['app']['vod_extend_weekday'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/actor_extend_area')); ?></label>
                        <div class="layui-input-block">
                            <input type="text" name="app[actor_extend_area]" placeholder="" value="<?php echo e($config['app']['actor_extend_area'] ?? ''); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/filter_words')); ?></label>
                        <div class="layui-input-block">
                            <textarea name="app[filter_words]" class="layui-textarea" placeholder="<?php echo e(__('admin/system/config/filter_words_tip')); ?>"><?php echo e($config['app']['filter_words'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/config/extra_var')); ?></label>
                        <div class="layui-input-block">
                            <textarea name="app[extra_var]" class="layui-textarea" placeholder="<?php echo e(__('admin/system/config/extra_var_tip')); ?>"><?php echo e($config['app']['extra_var'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>


            <div class="layui-tab-item">
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/collect_timespan')); ?></label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[collect_timespan]" placeholder="" value="<?php echo e($config['app']['collect_timespan'] ?? ''); ?>" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/collect_timespan_tip')); ?></div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/pagesize')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="app[pagesize]" placeholder="<?php echo e(__('admin/system/config/pagesize_tip')); ?>" value="<?php echo e($config['app']['pagesize'] ?? ''); ?>" class="layui-input w150">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/makesize')); ?></label>
                    <div class="layui-input-block">
                        <input type="text" name="app[makesize]" placeholder="<?php echo e(__('admin/system/config/makesize_tip')); ?>" value="<?php echo e($config['app']['makesize'] ?? ''); ?>" class="layui-input w150">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/admin_login_verify')); ?></label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[admin_login_verify]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((($config['app']['admin_login_verify'] ?? '0') === '0')): echo 'checked'; endif; ?>>
                        <input type="radio" name="app[admin_login_verify]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((($config['app']['admin_login_verify'] ?? '0') !== '0')): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/editor')); ?></label>
                    <div class="layui-input-inline">
                        <select style="width:150px;" name="app[editor]">
                            <?php $editorOptions = ['ueditor' => 'UEditor', 'markdown' => 'Markdown', 'wangEditor' => 'wangEditor']; ?>
                            <?php $__currentLoopData = $editorOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php if((($config['app']['editor'] ?? '') == $key)): echo 'selected'; endif; ?>><?php echo e($vo); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/config/editor_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin/system/config/lang')); ?></label>
                    <div class="layui-input-inline" >
                        <select class="w150" name="app[lang]">
                            <?php $__currentLoopData = $langs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($vo); ?>" <?php if((($config['app']['lang'] ?? '') == $vo)): echo 'selected'; endif; ?>><?php echo e($vo); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

                <div class="layui-form-item center">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                        <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="<?php echo e(asset('static')); ?>/js/jquery.cookie.js"></script>
<script type="text/javascript">
    layui.use(['form','upload', 'layer'], function(){
        // 操作对象
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer
            , upload = layui.upload;

        form.on('radio(cache_type)',function(data){
            $('.row_cache_server').hide();
           if(data.value=='memcache' || data.value=='redis' || data.value=='memcached'){
               $('.row_cache_server').show();
           }
        });

        element.on('tab(tb1)', function(){
            $.cookie('config_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('config_tab') !=null ) {
            element.tabChange('tb1', $.cookie('config_tab'));
        }

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(route('admin.upload.upload')); ?>?flag=site"
            ,method: 'post'
            ,before: function(input) {
                layer.msg("<?php echo e(__('admin.upload_ing')); ?>", {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                    return false;
                }
                layer.closeAll();
                var input = $(obj).parent().parent().find('.upload-input');
                if ($(obj).attr('lay-type') == 'image') {
                    input.siblings('img').attr('src', res.data.file).show();
                }
                input.val(res.data.file);

                if(res.data.thumb_class !=''){
                    $('.'+ res.data.thumb_class).val(res.data.thumb[0].file);
                }
            }
        });

        $('.upload-input').hover(function (e){
            var e = window.event || e;
            var imgsrc = $(this).val();
            if(imgsrc.trim()==""){ return; }
            var left = e.clientX+document.body.scrollLeft+20;
            var top = e.clientY+document.body.scrollTop+20;
            $(".showpic").css({left:left,top:top,display:""});
            $(".showpic_img").attr("src", mac_url_img(imgsrc));
        },function (e){
            $(".showpic").css("display","none");
        });


    });

    function test_cache(){
        var type = $("input[name='app[cache_type]']:checked").val();
        var host = $("input[name='app[cache_host]']").val();
        var port = $("input[name='app[cache_port]']").val();
        var user_name =  $("input[name='app[cache_username]']").val();
        var password = $("input[name='app[cache_password]']").val();
        var db = $("input[name='app[cache_db]']").val();
        layer.msg("<?php echo e(__('admin.wait_submit')); ?>",{time:500000});
        $.ajax({
            url: "<?php echo e(url('system/test_cache')); ?>",
            type: "post",
            dataType: "json",
            data: {type:type,host:host,port:port,username:user_name,password:password,db:db},
            beforeSend: function () {
            },
            error:function(r){
                layer.msg("<?php echo e(__('admin/system/config/test_err')); ?>",{time:1800});
            },
            success: function (r) {
                layer.msg(r.msg,{time:1800});
            },
            complete: function () {
            }
        });
    }


</script>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\system\config.blade.php ENDPATH**/ ?>