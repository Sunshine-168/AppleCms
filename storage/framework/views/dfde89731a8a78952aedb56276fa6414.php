<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
    .layui-form-pane .layui-form-label { width:140px; }
    .layui-form-pane .layui-input-block { margin-left:140px; }
</style>
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.system.configcollect')); ?>">
        <?php echo csrf_field(); ?>
        <?php
            $ruleHas = static function ($rule, string $key): bool {
                if (is_array($rule)) {
                    return in_array($key, $rule, true);
                }
                $rule = (string) $rule;
                return $rule !== '' && strpos($rule, $key) !== false;
            };
        ?>
        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                <li class="layui-this" lay-id="configcollect_1"><?php echo e(__('admin.admin/system/configcollect/vod')); ?></li>
                <li lay-id="configcollect_2"><?php echo e(__('admin.admin/system/configcollect/art')); ?></li>
                <li lay-id="configcollect_3"><?php echo e(__('admin.admin/system/configcollect/actor')); ?></li>
                <li lay-id="configcollect_4"><?php echo e(__('admin.admin/system/configcollect/role')); ?></li>
                <li lay-id="configcollect_5"><?php echo e(__('admin.admin/system/configcollect/website')); ?></li>
                <li lay-id="configcollect_6"><?php echo e(__('admin.admin/system/configcollect/comment')); ?></li>
                <li lay-id="configcollect_7"><?php echo e(__('admin.admin/system/configcollect/words')); ?></li>
            </ul>

            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/status')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[vod][status]" value="0" title="<?php echo e(__('admin.reviewed_not')); ?>" <?php if((string) data_get($config, 'collect.vod.status', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][status]" value="1" title="<?php echo e(__('admin.reviewed')); ?>" <?php if((string) data_get($config, 'collect.vod.status', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/hits_rnd')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][hits_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['vod']['hits_start']); ?>" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][hits_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['vod']['hits_end']); ?>" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/updown_rnd')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][updown_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['vod']['updown_start']); ?>" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][updown_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['vod']['updown_end']); ?>" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/score_rnd')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][score]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.score', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][score]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.score', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/sync_pic')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][pic]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.pic', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][pic]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.pic', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/auto_tag')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][tag]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.tag', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][tag]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.tag', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/class_filter')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][class_filter]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.class_filter', '0') === '0'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][class_filter]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.class_filter', '0') !== '0'): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configcollect/class_filter_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psename')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psename]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.psename', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][psename]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.psename', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configcollect/psename_tip')); ?></div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psernd')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psernd]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.psernd', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][psernd]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.psernd', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psesyn')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psesyn]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.psesyn', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][psesyn]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.psesyn', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/pseplayer')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][pseplayer]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.pseplayer', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][pseplayer]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.pseplayer', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psearea')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psearea]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.psearea', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][psearea]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.psearea', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/pselang')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][pselang]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.vod.pselang', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][pselang]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.vod.pselang', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/urlrole')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][urlrole]" value="0" title="<?php echo e(__('admin.replace')); ?>" <?php if((string) data_get($config, 'collect.vod.urlrole', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[vod][urlrole]" value="1" title="<?php echo e(__('admin.merge')); ?>" <?php if((string) data_get($config, 'collect.vod.urlrole', '0') === '1'): echo 'checked'; endif; ?>>
                        <!-- <input type="radio" name="collect[vod][urlrole]" value="2" title="<?php echo e(__('admin.admin/system/configcollect/urlrole/use_more')); ?>" <?php if((string) data_get($config, 'collect.vod.urlrole') === '2'): echo 'checked'; endif; ?>> -->
                    </div>
                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configcollect/urlrole_tip')); ?></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin.admin/system/configcollect/inrule')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="a" title="<?php echo e(__('admin.name')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'a')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="b" title="<?php echo e(__('admin.type')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'b')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="c" title="<?php echo e(__('admin.years')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'c')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="d" title="<?php echo e(__('admin.area')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'd')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="e" title="<?php echo e(__('admin.lang')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'e')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="f" title="<?php echo e(__('admin.actor')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'f')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="g" title="<?php echo e(__('admin.director')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'g')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="h" title="<?php echo e(__('admin.douban_id')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.inrule', ''), 'h')): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">
                        <?php echo e(__('admin.admin/system/configcollect/uprule')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="a" title="<?php echo e(__('admin.playurl')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'a')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="b" title="<?php echo e(__('admin.downurl')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'b')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="c" title="<?php echo e(__('admin.serial')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'c')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="d" title="<?php echo e(__('admin.remarks')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'd')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="e" title="<?php echo e(__('admin.director')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'e')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="f" title="<?php echo e(__('admin.actor')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'f')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="g" title="<?php echo e(__('admin.years')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'g')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="h" title="<?php echo e(__('admin.area')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'h')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="i" title="<?php echo e(__('admin.lang')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'i')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="j" title="<?php echo e(__('admin.pic')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'j')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="k" title="<?php echo e(__('admin.content')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'k')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="l" title="TAG" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'l')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="m" title="<?php echo e(__('admin.sub')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'm')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="n" title="<?php echo e(__('admin.class')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'n')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="o" title="<?php echo e(__('admin.writer')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'o')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="p" title="<?php echo e(__('admin.version')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'p')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="q" title="<?php echo e(__('admin.state')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'q')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="r" title="<?php echo e(__('admin.blurb')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'r')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="s" title="<?php echo e(__('admin.tv')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 's')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="t" title="<?php echo e(__('admin.weekday')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 't')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="u" title="<?php echo e(__('admin.total')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'u')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="v" title="<?php echo e(__('admin.isend')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'v')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="w" title="<?php echo e(__('admin.plot')); ?>" <?php if($ruleHas(data_get($config, 'collect.vod.uprule', ''), 'w')): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/filter')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][filter]" class="layui-textarea" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>"><?php echo e($config['collect']['vod']['filter']); ?></textarea>
                    </div>
                </div>
            </div>

                <div class="layui-tab-item">

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/status')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][status]" value="0" title="<?php echo e(__('admin.reviewed_not')); ?>" <?php if((string) data_get($config, 'collect.art.status', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[art][status]" value="1" title="<?php echo e(__('admin.reviewed')); ?>" <?php if((string) data_get($config, 'collect.art.status', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/hits_rnd')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][hits_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['art']['hits_start']); ?>" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][hits_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['art']['hits_end']); ?>" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/updown_rnd')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][updown_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['art']['updown_start']); ?>" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][updown_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['art']['updown_end']); ?>" class="layui-input">
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/score_rnd')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][score]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.art.score', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[art][score]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.art.score', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/sync_pic')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][pic]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.art.pic', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[art][pic]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.art.pic', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/auto_tag')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][tag]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.art.tag', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[art][tag]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.art.tag', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psernd')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][psernd]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.art.psernd', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[art][psernd]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.art.psernd', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psesyn')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][psesyn]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.art.psesyn', '0') !== '1'): echo 'checked'; endif; ?>>
                        <input type="radio" name="collect[art][psesyn]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.art.psesyn', '0') === '1'): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/inrule')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[art][inrule][]" value="a" title="<?php echo e(__('admin.name')); ?>" checked disabled>
                        <input type="checkbox" lay-skin="primary" name="collect[art][inrule][]" value="b" title="<?php echo e(__('admin.type')); ?>" <?php if($ruleHas(data_get($config, 'collect.art.inrule', ''), 'b')): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/uprule')); ?>：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="a" title="<?php echo e(__('admin.content')); ?>" <?php if($ruleHas(data_get($config, 'collect.art.uprule', ''), 'a')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="b" title="<?php echo e(__('admin.author')); ?>" <?php if($ruleHas(data_get($config, 'collect.art.uprule', ''), 'b')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="c" title="<?php echo e(__('admin.from')); ?>" <?php if($ruleHas(data_get($config, 'collect.art.uprule', ''), 'c')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="d" title="<?php echo e(__('admin.pic')); ?>" <?php if($ruleHas(data_get($config, 'collect.art.uprule', ''), 'd')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="e" title="TAG" <?php if($ruleHas(data_get($config, 'collect.art.uprule', ''), 'e')): echo 'checked'; endif; ?>>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="f" title="<?php echo e(__('admin.blurb')); ?>" <?php if($ruleHas(data_get($config, 'collect.art.uprule', ''), 'f')): echo 'checked'; endif; ?>>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/filter')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[art][filter]" class="layui-textarea" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>"><?php echo e($config['collect']['art']['filter']); ?></textarea>
                    </div>
                </div>

            </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/status')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][status]" value="0" title="<?php echo e(__('admin.reviewed_not')); ?>" <?php if((string) data_get($config, 'collect.actor.status', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[actor][status]" value="1" title="<?php echo e(__('admin.reviewed')); ?>" <?php if((string) data_get($config, 'collect.actor.status', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/hits_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][hits_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['actor']['hits_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][hits_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['actor']['hits_end']); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/updown_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][updown_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['actor']['updown_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][updown_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['actor']['updown_end']); ?>" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/score_rnd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][score]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.actor.score', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[actor][score]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.actor.score', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/sync_pic')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][pic]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.actor.pic', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[actor][pic]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.actor.pic', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psernd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][psernd]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.actor.psernd', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[actor][psernd]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.actor.psernd', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psesyn')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][psesyn]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.actor.psesyn', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[actor][psesyn]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.actor.psesyn', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/inrule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[actor][inrule][]" value="a" title="<?php echo e(__('admin.actor_name')); ?>" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][inrule][]" value="c" title="<?php echo e(__('admin.type')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.inrule', ''), 'c')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][inrule][]" value="b" title="<?php echo e(__('admin.sex')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.inrule', ''), 'b')): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/uprule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="a" title="<?php echo e(__('admin.content')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.uprule', ''), 'a')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="b" title="<?php echo e(__('admin.blurb')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.uprule', ''), 'b')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="c" title="<?php echo e(__('admin.remarks')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.uprule', ''), 'c')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="d" title="<?php echo e(__('admin.works')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.uprule', ''), 'd')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="e" title="<?php echo e(__('admin.pic')); ?>" <?php if($ruleHas(data_get($config, 'collect.actor.uprule', ''), 'e')): echo 'checked'; endif; ?>>

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/filter')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[actor][filter]" class="layui-textarea" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>"><?php echo e($config['collect']['actor']['filter']); ?></textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/status')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][status]" value="0" title="<?php echo e(__('admin.reviewed_not')); ?>" <?php if((string) data_get($config, 'collect.role.status', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[role][status]" value="1" title="<?php echo e(__('admin.reviewed')); ?>" <?php if((string) data_get($config, 'collect.role.status', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/hits_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][hits_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['role']['hits_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][hits_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['role']['hits_end']); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/updown_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][updown_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['role']['updown_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][updown_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['role']['updown_end']); ?>" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/score_rnd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][score]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.role.score', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[role][score]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.role.score', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/sync_pic')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][pic]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.role.pic', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[role][pic]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.role.pic', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psernd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][psernd]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.role.psernd', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[role][psernd]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.role.psernd', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psesyn')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][psesyn]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.role.psesyn', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[role][psesyn]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.role.psesyn', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/inrule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="a" title="<?php echo e(__('admin.role_name')); ?>" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="b" title="<?php echo e(__('admin.vod_name')); ?><?php echo e(__('admin.or')); ?><?php echo e(__('admin.douban_id')); ?>" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="c" title="<?php echo e(__('admin.actor_name')); ?>" <?php if($ruleHas(data_get($config, 'collect.role.inrule', ''), 'c')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="d" title="<?php echo e(__('admin.director')); ?>" <?php if($ruleHas(data_get($config, 'collect.role.inrule', ''), 'd')): echo 'checked'; endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configcollect/inrule_tip_role')); ?></div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/uprule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[role][uprule][]" value="a" title="<?php echo e(__('admin.content')); ?>" <?php if($ruleHas(data_get($config, 'collect.role.uprule', ''), 'a')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[role][uprule][]" value="b" title="<?php echo e(__('admin.remarks')); ?>" <?php if($ruleHas(data_get($config, 'collect.role.uprule', ''), 'b')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[role][uprule][]" value="c" title="<?php echo e(__('admin.pic')); ?>" <?php if($ruleHas(data_get($config, 'collect.role.uprule', ''), 'c')): echo 'checked'; endif; ?>>

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/filter')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[role][filter]" class="layui-textarea" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>"><?php echo e($config['collect']['role']['filter']); ?></textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/status')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][status]" value="0" title="<?php echo e(__('admin.reviewed_not')); ?>" <?php if((string) data_get($config, 'collect.website.status', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[website][status]" value="1" title="<?php echo e(__('admin.reviewed')); ?>" <?php if((string) data_get($config, 'collect.website.status', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/hits_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][hits_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['website']['hits_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][hits_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['website']['hits_end']); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/updown_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][updown_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['website']['updown_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][updown_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['website']['updown_end']); ?>" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/score_rnd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][score]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.website.score', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[website][score]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.website.score', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/sync_pic')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][pic]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.website.pic', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[website][pic]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.website.pic', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psernd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][psernd]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.website.psernd', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[website][psernd]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.website.psernd', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psesyn')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][psesyn]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.website.psesyn', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[website][psesyn]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.website.psesyn', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/inrule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[website][inrule][]" value="a" title="<?php echo e(__('admin.name')); ?>" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[website][inrule][]" value="b" title="<?php echo e(__('admin.type')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.inrule', ''), 'b')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[website][inrule][]" value="c" title="<?php echo e(__('admin.jumpurl')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.inrule', ''), 'c')): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/uprule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="a" title="<?php echo e(__('admin.content')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.uprule', ''), 'a')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="b" title="<?php echo e(__('admin.blurb')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.uprule', ''), 'b')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="c" title="<?php echo e(__('admin.remarks')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.uprule', ''), 'c')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="d" title="<?php echo e(__('admin.jumpurl')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.uprule', ''), 'd')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="e" title="<?php echo e(__('admin.pic')); ?>" <?php if($ruleHas(data_get($config, 'collect.website.uprule', ''), 'e')): echo 'checked'; endif; ?>>

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/filter')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[website][filter]" class="layui-textarea" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>"><?php echo e($config['collect']['website']['filter']); ?></textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/status')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[comment][status]" value="0" title="<?php echo e(__('admin.reviewed_not')); ?>" <?php if((string) data_get($config, 'collect.comment.status', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[comment][status]" value="1" title="<?php echo e(__('admin.reviewed')); ?>" <?php if((string) data_get($config, 'collect.comment.status', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/updown_rnd')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[comment][updown_start]" placeholder="<?php echo e(__('admin.min_val')); ?>" value="<?php echo e($config['collect']['comment']['updown_start']); ?>" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[comment][updown_end]" placeholder="<?php echo e(__('admin.max_val')); ?>" value="<?php echo e($config['collect']['comment']['updown_end']); ?>" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psernd')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[comment][psernd]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.comment.psernd', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[comment][psernd]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.comment.psernd', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/psesyn')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[comment][psesyn]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'collect.comment.psesyn', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="collect[comment][psesyn]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'collect.comment.psesyn', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/inrule')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[comment][inrule][]" value="a" title="<?php echo e(__('admin.rel_name')); ?><?php echo e(__('admin.or')); ?><?php echo e(__('admin.douban_id')); ?>" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[comment][inrule][]" value="b" title="<?php echo e(__('admin.comment_content')); ?>" <?php if($ruleHas(data_get($config, 'collect.comment.inrule', ''), 'b')): echo 'checked'; endif; ?>>
                            <input type="checkbox" lay-skin="primary" name="collect[comment][inrule][]" value="c" title="<?php echo e(__('admin.comment_name')); ?>" <?php if($ruleHas(data_get($config, 'collect.comment.inrule', ''), 'c')): echo 'checked'; endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configcollect/inrule_tip_comment')); ?></div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/uprule')); ?>：</label>
                        <div class="layui-input-block">


                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/filter')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[comment][filter]" class="layui-textarea" placeholder="<?php echo e(__('admin.multi_separate_tip')); ?>"><?php echo e($config['collect']['comment']['filter']); ?></textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">
                <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
                    <?php echo __('admin.admin/system/configcollect/words_tip'); ?>

                </blockquote>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/vod_namewords')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][namewords]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.vod.namewords', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/vod_thesaurus')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][thesaurus]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.vod.thesaurus', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/vod_playerwords')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][playerwords]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.vod.playerwords', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/vod_areawords')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][areawords]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.vod.areawords', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/vod_langwords')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][langwords]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.vod.langwords', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/vod_words')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][words]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.vod.words', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/art_thesaurus')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[art][thesaurus]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.art.thesaurus', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/art_words')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[art][words]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.art.words', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/actor_thesaurus')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[actor][thesaurus]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.actor.thesaurus', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/actor_words')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[actor][words]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.actor.words', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/role_thesaurus')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[role][thesaurus]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.role.thesaurus', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/role_words')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[role][words]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.role.words', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/website_thesaurus')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[website][thesaurus]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.website.thesaurus', ''))); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/website_words')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[website][words]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.website.words', ''))); ?></textarea>
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/comment_thesaurus')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[comment][thesaurus]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.comment.thesaurus', ''))); ?></textarea>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configcollect/comment_words')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[comment][words]" class="layui-textarea"><?php echo e(mac_replace_text((string) data_get($config, 'collect.comment.words', ''))); ?></textarea>
                        </div>
                    </div>

            </div>
            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript" src="<?php echo e(asset('static')); ?>/js/jquery.cookie.js"></script>
<script type="text/javascript">
    layui.use(['element', 'form', 'layer'], function() {
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer;


        element.on('tab(tb1)', function(){
            $.cookie('configcollect_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configcollect_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configcollect_tab'));
        }

    });
</script>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views/admin/system/configcollect.blade.php ENDPATH**/ ?>