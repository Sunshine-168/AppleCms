<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <div class="layui-tab layui-tab-brief">
        <!--添加采集点 start-->
        <div class="layui-tab-content">
            <form class="layui-form" name="myform" method="post" id="myform">
                <input type="hidden" name="data[nodeid]" value="<?php echo e($data.nodeid); ?>">
                <div class="layui-tab layui-tab-card" style="min-height: 430px;">
                    <ul class="layui-tab-title">
                        <li class="layui-this"><?php echo e(__('admin.admin/cj/rule_url')); ?></li>
                        <li><?php echo e(__('admin.admin/cj/rule_content')); ?></li>
                        <li><?php echo e(__('admin.admin/cj/rule_diy')); ?></li>
                        <li><?php echo e(__('admin.admin/cj/adv_config')); ?></li>
                    </ul>
                    <div class="layui-tab-content">
                        <!--网址规则 start-->
                        <div class="layui-tab-item layui-show">
                            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                                <legend><?php echo e(__('admin.base_info')); ?></legend>
                            </fieldset>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/rule_name')); ?>：</label>
                                <div class="layui-input-block" style="width: 60%">
                                    <input type="text" name="data[name]" placeholder="" value="<?php echo e($data.name); ?>" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/page_charset')); ?>：</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="data[sourcecharset]" value="GBK" title="GBK" <?php if(condition="$data['sourcecharset'] == 'GBK'"): ?>checked='checked'<?php endif; ?>>
                                    <input type="radio" name="data[sourcecharset]" value="UTF-8" title="UTF-8" <?php if(condition="$data['sourcecharset'] == 'UTF-8'"): ?>checked='checked'<?php endif; ?>>
                                    <input type="radio" name="data[sourcecharset]" value="BIG5" title="BIG5" <?php if(condition="$data['sourcecharset'] == 'BIG5'"): ?>checked='checked'<?php endif; ?>>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/cj_model')); ?>：</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="data[mid]" value="1" title="<?php echo e(__('admin.vod')); ?>" <?php if(condition="$data['mid'] != '2'"): ?>checked='checked'<?php endif; ?>>
                                    <input type="radio" name="data[mid]" value="2" title="<?php echo e(__('admin.art')); ?>" <?php if(condition="$data['mid'] == '2'"): ?>checked='checked'<?php endif; ?>>
                                </div>
                            </div>

                            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                                <legend><?php echo e(__('admin.admin/cj/url_collect')); ?></legend>
                            </fieldset>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/url_type')); ?>：</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="data[sourcetype]" id="_1" value="1" lay-filter="sourcetype" title="<?php echo e(__('admin.admin/cj/sequence_url')); ?>" <?php if(condition="$data['sourcetype'] == 1"): ?>checked='checked'<?php endif; ?>>
                                    <input type="radio" name="data[sourcetype]" id="_2" value="2" lay-filter="sourcetype" title="<?php echo e(__('admin.admin/cj/multi_url')); ?>" <?php if(condition="$data['sourcetype'] == 2"): ?>checked='checked'<?php endif; ?>>
                                    <input type="radio" name="data[sourcetype]" id="_3" value="3" lay-filter="sourcetype" title="<?php echo e(__('admin.admin/cj/one_url')); ?>" <?php if(condition="$data['sourcetype'] == 3"): ?>checked='checked'<?php endif; ?>>
                                </div>
                            </div>
                            <div id="url_type_1" <?php if(condition="$data['sourcetype'] != 1"): ?>style="display:none"<?php endif; ?>>
                                <div class="layui-form-item">
                                    <label class="layui-form-label"><?php echo e(__('admin.admin/cj/cj_url')); ?>：</label>
                                    <div class="layui-input-inline" style="width: 60%;">
                                        <input type="text" name="urlpage1" id="urlpage_1" placeholder="http://..." value="<?php echo e($data.urlpage); ?>" class="layui-input">
                                        <div class="layui-form-mid layui-word-aux">
                                            (如：http://www.phpcms.cn/help/rumen/(*).html,<?php echo e(__('admin.use')); ?>(*)<?php echo e(__('admin.admin/cj/wildcard_tip')); ?>。
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label"><?php echo e(__('admin.admin/cj/page_num_config')); ?>：</label>
                                    <div class="layui-form-mid"><?php echo e(__('admin.start')); ?></div>
                                    <div class="layui-input-inline" style="width: 60px;">
                                        <input type="text" name="data[pagesize_start]" value="<?php echo e($data.pagesize_start); ?>" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid"> <?php echo e(__('admin.end')); ?></div>
                                    <div class="layui-input-inline" style="width: 60px;">
                                        <input type="text" name="data[pagesize_end]" value="<?php echo e($data.pagesize_end); ?>" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/page_num_increment')); ?></div>
                                    <div class="layui-input-inline" style="width: 60px;">
                                        <input type="text" name="data[par_num]" value="<?php echo e($data.par_num); ?>" class="layui-input">
                                    </div>
                                    <div class="layui-input-inline" style="width:10%;">
                                        <a class="layui-btn" onclick="testUrl();" href="javascript:;"><?php echo e(__('admin.test')); ?></a>
                                    </div>
                                </div>
                            </div>
                            <!--多个网址-->
                            <div id="url_type_2" class="layui-form-item" <?php if(condition="$data['sourcetype'] != 2"): ?>style="display:none"<?php endif; ?>>
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/cj_url')); ?>：</label>
                                <div class="layui-input-inline" style="width: 60%;">
                                    <textarea class="layui-textarea" name="urlpage2" id="urlpage_2"><?php echo e($data.urlpage); ?></textarea>
                                    <div class="layui-form-mid layui-word-aux">
                                        <?php echo e(__('admin.admin/cj/one_per_line')); ?>

                                    </div>
                                </div>
                            </div>
                            <!--单一网址-->
                            <div id="url_type_3" class="layui-form-item" <?php if(condition="$data['sourcetype'] != 3"): ?>style="display:none"<?php endif; ?>>
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/cj_url')); ?>：</label>
                                <div class="layui-input-inline" style="width: 60%;">
                                    <input type="text" name="urlpage3" id="urlpage_3" placeholder="http://..." value="<?php echo e($data.urlpage); ?>" class="layui-input">
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/url_config')); ?>：</label>
                                <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/url_must_contain')); ?></div>
                                <div class="layui-input-inline" style="width: 160px;">
                                    <input type="text" name="data[url_contain]" value="<?php echo e($data.url_contain); ?>" class="layui-input">
                                </div>
                                <div class="layui-form-mid"> <?php echo e(__('admin.admin/cj/url_not_contain')); ?></div>
                                <div class="layui-input-inline" style="width: 160px;">
                                    <input type="text" name="data[url_except]" value="<?php echo e($data.url_except); ?>" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/collect_interval')); ?>：</label>
                                <div class="layui-input-inline">
                                    <textarea name="data[url_start]" class="layui-textarea"><?php echo e($data.url_start); ?></textarea>
                                </div>
                                <div class="layui-form-mid"><?php echo e(__('admin.to')); ?></div>
                                <div class="layui-input-inline">
                                    <textarea name="data[url_end]" class="layui-textarea"><?php echo e($data.url_end); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!--网址规则 end-->
                            <!--内容规则 start-->
                        <div class="layui-tab-item">
                            <blockquote class="layui-elem-quote layui-text" style="margin:20px 0;border-left-color: #ff5722;">
                                    <?php echo e(__('admin.admin/cj/wildcard_prompt')); ?>

                            </blockquote>
                            <div class="layui-btn-group">
                                    <a class="layui-btn" href="javascript:void(0);" onclick="showAll(this);"><?php echo e(__('admin.expand_all')); ?></a>
                                    <a class="layui-btn" href="javascript:void(0);" onclick="hideAll(this);"><?php echo e(__('admin.fold_all')); ?></a>
                            </div>
                            <div class="layui-collapse" lay-filter="lay_state" style="margin: 20px 0;">
                                    <div class="layui-colla-item">
                                        <h2 class="layui-colla-title"><?php echo e(__('admin.admin/cj/title_rule')); ?></h2>
                                        <div class="layui-colla-content layui-show">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/match_rule')); ?>：</label>
                                                <div class="layui-input-inline w300">
                                                    <textarea name="data[title_rule]" id="title_rule" class="layui-textarea"><?php echo e($data.title_rule); ?></textarea>
                                                    <div class="layui-form-mid layui-word-aux">
                                                        <?php echo e(__('admin.use')); ?>"<a href="javascript:insertText('title_rule', '[内容]')"> [内容] </a>"<?php echo e(__('admin.admin/cj/wildcard_tip')); ?>

                                                    </div>
                                                </div>
                                                <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/filter_rule')); ?>：</div>
                                                <div class="layui-input-inline w300">
                                                    <textarea name="data[title_html_rule]" id="title_html_rule" class="layui-textarea"><?php echo e($data.title_html_rule); ?></textarea>
                                                    <div class="layui-form-mid layui-word-aux">
                                                        <input type="button" value="<?php echo e(__('admin.select')); ?>" class="layui-btn layui-btn-xs" onclick="add_tag('title_html_rule')">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <div class="layui-colla-item">
                                    <h2 class="layui-colla-title"><?php echo e(__('admin.admin/cj/type_rule')); ?></h2>
                                    <div class="layui-colla-content layui-show">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label"><?php echo e(__('admin.admin/cj/match_rule')); ?>：</label>
                                            <div class="layui-input-inline w300">
                                                <textarea name="data[type_rule]" id="type_rule" class="layui-textarea"><?php echo e($data.type_rule); ?></textarea>
                                                <div class="layui-form-mid layui-word-aux">
                                                    <?php echo e(__('admin.use')); ?>"<a href="javascript:insertText('content_rule', '[内容]')"> [内容] </a>"<?php echo e(__('admin.admin/cj/wildcard_tip')); ?>

                                                </div>
                                            </div>
                                            <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/filter_rule')); ?>：</div>
                                            <div class="layui-input-inline w300">
                                                <textarea name="data[type_html_rule]" id="type_html_rule" class="layui-textarea"><?php echo e($data.type_html_rule); ?></textarea>
                                                <div class="layui-form-mid layui-word-aux">
                                                    <input type="button" value="<?php echo e(__('admin.select')); ?>" class="layui-btn layui-btn-xs" onclick="add_tag('type_html_rule')">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                    <div class="layui-colla-item">
                                        <h2 class="layui-colla-title"><?php echo e(__('admin.admin/cj/content_rule')); ?></h2>
                                        <div class="layui-colla-content layui-show">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/match_rule')); ?>：</label>
                                                <div class="layui-input-inline w300">
                                                    <textarea name="data[content_rule]" id="content_rule" class="layui-textarea"><?php echo e($data.content_rule); ?></textarea>
                                                    <div class="layui-form-mid layui-word-aux">
                                                        <?php echo e(__('admin.use')); ?>"<a href="javascript:insertText('content_rule', '[内容]')"> [内容] </a>"<?php echo e(__('admin.admin/cj/wildcard_tip')); ?>

                                                    </div>
                                                </div>
                                                <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/filter_rule')); ?>：</div>
                                                <div class="layui-input-inline w300">
                                                    <textarea name="data[content_html_rule]" id="content_html_rule" class="layui-textarea"><?php echo e($data.content_html_rule); ?></textarea>
                                                    <div class="layui-form-mid layui-word-aux">
                                                        <input type="button" value="<?php echo e(__('admin.select')); ?>" class="layui-btn layui-btn-xs" onclick="add_tag('content_html_rule')">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="layui-colla-item">
                                        <h2 class="layui-colla-title"><?php echo e(__('admin.admin/cj/page_mode')); ?></h2>
                                        <div class="layui-colla-content layui-show">
                                            <div class="layui-form-item">
                                                <input type="radio" name="data[content_page_rule]" id="_1" value="1" title="<?php echo e(__('admin.admin/cj/list_all_mode')); ?>" lay-filter="content_page_rule" <?php if(condition="$data['content_page_rule'] != 2"): ?>checked="checked"<?php endif; ?>>
                                                <input type="radio" name="data[content_page_rule]" id="_2" value="2" title="<?php echo e(__('admin.admin/cj/next_page_mode')); ?>" lay-filter="content_page_rule" <?php if(condition="$data['content_page_rule'] == 2"): ?>checked="checked"<?php endif; ?>>
                                            </div>

                                            <div class="layui-form-item" id="nextpage" <?php if(condition="$data['content_page_rule'] != '2'"): ?>style="display:none"<?php endif; ?>>
                                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/next_page_rule')); ?>：</label>
                                                <div class="layui-input-inline w600">
                                                    <input type="text" name="data[content_nextpage]" class="layui-input" value="<?php echo e($data.content_nextpage); ?>">
                                                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/cj/next_page_tip')); ?></div>
                                                </div>
                                            </div>
                                            <div class="layui-form-item">
                                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/match_rule')); ?>：</label>
                                                从 <textarea rows="5" cols="40" name="data[content_page_start]" id="content_page_start"><?php echo e($data.content_page_start); ?></textarea> <?php echo e(__('admin.to')); ?>

                                                <textarea rows="5" cols="40" name="data[content_page_end]" id="content_page_end"><?php echo e($data.content_page_end); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <!--内容规则 end-->
                        <!--自定义规则 start-->
                        <div class="layui-tab-item" id="customize_config">

                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <a class="layui-btn layui-btn-sm layui-btn-normal" href="javascript:;" onclick="add_caiji()"><?php echo e(__('admin.admin/cj/add_group')); ?></a>
                                </div>
                            </div>

                            <?php $__currentLoopData = $$data.customize_config; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="layui-form-item mt10">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/rule_name')); ?>：</label>
                                <div class="layui-input-inline"><input type="text" name="data[customize_config][name][]" placeholder="" value="<?php echo e($vo.name); ?>" class="layui-input"></div>
                                <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/rule_name_en')); ?>：</div>
                                <div class="layui-input-inline"><input type="text" name="data[customize_config][en_name][]" placeholder="" value="<?php echo e($vo.en_name); ?>" class="layui-input"></div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/match_rule')); ?>：</label>
                                <div class="layui-input-inline">
                                    <textarea name="data[customize_config][rule][]" id="role_'+caiji+'" class="layui-textarea"><?php echo e($vo.rule); ?></textarea>
                                    <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.use')); ?>"<a href="javascript:insertText('title_rule', '[内容]')"> [内容] </a>"<?php echo e(__('admin.admin/cj/wildcard_tip')); ?>    </div>
                                </div>
                                <div class="layui-form-mid"><?php echo e(__('admin.admin/cj/filter_rule')); ?>：</div>
                                <div class="layui-input-inline">
                                    <textarea name="data[customize_config][html_rule][]" id="content_html_rule_'+caiji+'" class="layui-textarea"><?php echo e($vo.html_rule); ?></textarea>
                                    <div class="layui-form-mid layui-word-aux"><a class="layui-btn layui-btn-xs" href="javascript:;" onclick="add_tag('content_html_rule_'+caiji+'')"><?php echo e(__('admin.select')); ?></a></div>
                                </div>
                            </div>
                            <hr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </div>
                        <!--自定义规则 end-->
                        <!--高级配置 start-->
                        <div class="layui-tab-item">

                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/content_page')); ?>：</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="data[content_page]" value="0" title="<?php echo e(__('admin.admin/cj/no_page')); ?>">
                                    <div class="layui-unselect layui-form-radio layui-form-radioed">
                                        <i class="layui-anim layui-icon"></i>
                                        <div><?php echo e(__('admin.admin/cj/no_page')); ?></div>
                                    </div>
                                    <input type="radio" name="data[content_page]" value="1" title="<?php echo e(__('admin.admin/cj/original_page')); ?>" checked>
                                    <div class="layui-unselect layui-form-radio layui-form-radioed">
                                        <i class="layui-anim layui-icon"></i>
                                        <div><?php echo e(__('admin.admin/cj/original_page')); ?></div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php echo e(__('admin.admin/cj/import_sort')); ?>：</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="data[coll_order]" value="1" title="<?php echo e(__('admin.admin/cj/same_to_site')); ?>">
                                    <div class="layui-unselect layui-form-radio layui-form-radioed">
                                        <i class="layui-anim layui-icon"></i>
                                        <div><?php echo e(__('admin.admin/cj/same_to_site')); ?></div>
                                    </div>
                                    <input type="radio" name="data[coll_order]" value="2" title="<?php echo e(__('admin.admin/cj/opposite_to_site')); ?>" checked>
                                    <div class="layui-unselect layui-form-radio layui-form-radioed">
                                        <i class="layui-anim layui-icon"></i>
                                        <div><?php echo e(__('admin.admin/cj/opposite_to_site')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--高级配置 end-->
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block w150" style="margin:20px auto;">
                        <button type="submit" name="dosubmit" id="dosubmit" class="layui-btn layui-btn-fluid"><?php echo e(__('admin.btn_save')); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <!--添加采集点 end-->
    </div>
</div>
<style>
    .ib{display: inline-block;}
</style>
<div id="html_rule_show" class="aui_content" style="display:none; padding: 20px 25px;">
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_1" value="<p([^>]*)>(.*)</p>[|]"> &lt;p&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_2" value="<a([^>]*)>(.*)</a>[|]"> &lt;a&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_3" value="<script([^>]*)>(.*)</script>[|]"> &lt;script&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_4" value="<iframe([^>]*)>(.*)</iframe>[|]"> &lt;iframe&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_5" value="<table([^>]*)>(.*)</table>[|]"> &lt;table&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_6" value="<span([^>]*)>(.*)</span>[|]"> &lt;span&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_7" value="<b([^>]*)>(.*)</b>[|]"> &lt;b&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_8" value="<img([^>]*)>[|]"> &lt;img&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_9" value="<object([^>]*)>(.*)</object>[|]"> &lt;object&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_10" value="<embed([^>]*)>(.*)</embed>[|]"> &lt;embed&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_11" value="<param([^>]*)>(.*)</param>[|]"> &lt;param&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_12" value="<div([^>]*)>[|]"> &lt;div&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_13" value="</div>[|]"> &lt;/div&gt;</label>
    <label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_14" value="<!--([^>]*)-->[|]"> &lt;!-- --&gt;</label>
    <br>
    <div class="bk15"></div>
    <center><input type="button" value="<?php echo e(__('admin.check_all')); ?>" class="button" onclick="selectall('html_rule')"> <input type="button" class="button" value="<?php echo e(__('admin.check_other')); ?>" onclick="anti_selectall('html_rule')"></center>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    layui.use(['element','form','upload','layer'],function () {
        // 操作对象
        var element = layui.element;
        form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;

        form.on('radio(sourcetype)',function (data) {
            var num = 4;
            for (var i=1; i<=num; i++){
                if (data.value==i){
                    $('#url_type_'+i).show();
                } else {
                    $('#url_type_'+i).hide();
                }
            }
        });
        form.on('radio(content_page_rule)',function (data) {
            $('#nextpage').hide();
            if(data.value==2){
                $('#nextpage').show();
            }
        });



        //监听折叠
        element.on('collapse(lay_state)', function(data){
            //layer.msg('展开状态：'+ data.show);
        });
    });

    function selectall(obj) {
        $("input[name='"+obj+"']").each(function(i,n){
            this.checked = true;
        });
    }
    function anti_selectall(obj) {
        $("input[name='"+obj+"']").each(function(i,n){
            if (this.checked) {
                this.checked = false;
            } else {
                this.checked = true;
            }});
    }
    //折叠面板
    function showAll(_this) {
        $(_this).parents(".layui-btn-group").siblings(".layui-collapse").children(".layui-colla-item").children(".layui-colla-content").addClass("layui-show");
    }
    function hideAll(_this) {
        $(_this).parents(".layui-btn-group").siblings(".layui-collapse").children(".layui-colla-item").children(".layui-colla-content").removeClass("layui-show");
    }

    //  包含内容
    function insertText(id, text) {
        $('#' + id).focus();
        var str = document.selection.createRange();
        str.text = text;
    }

    function add_tag(id) {
        var index = layer.open({
            type: 1
            ,title: "<?php echo e(__('admin.admin/cj/filter_rule')); ?>" //不显示标题栏
            ,closeBtn: 1
            ,area: '600px;'
            ,shade: 0.8
            ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
            ,btn: ["<?php echo e(__('admin.add')); ?>", "<?php echo e(__('admin.cancel')); ?>"]
            ,btnAlign: 'c'
            ,moveType: 1 //拖拽模式，0或者1
            ,content: $('#html_rule_show')
            ,yes: function(layero){
                var str = '';
                $("input[name='html_rule']:checked").each(function(){
                    str+=$(this).val()+"\n";
                });
                alert(str);
                $("#"+id).val(str);
                layer.close(index);
            }
        });
    }

    var caiji=0;
    function add_caiji()
    {
        $('#customize_config').append('<div class="layui-form-item mt10"><label class="layui-form-label"><?php echo e(__('admin.rule_name')); ?>：</label><div class="layui-input-inline"><input type="text" name="data[customize_config][name][]" placeholder="" value="" class="layui-input"></div><div class="layui-form-mid"><?php echo e(__('admin.rule_name_en')); ?>：</div><div class="layui-input-inline"><input type="text" name="data[customize_config][en_name][]" placeholder="" value="" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label"><?php echo e(__('admin.admin/cj/match_rule')); ?>：</label><div class="layui-input-inline"><textarea name="data[customize_config][rule][]" id="role_'+caiji+'" class="layui-textarea"></textarea><div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.use')); ?>"<a href="javascript:insertText(\'title_rule\', \'[内容]\')"> [内容] </a>"<?php echo e(__('admin.admin/cj/wildcard_tip')); ?>    </div></div><div class="layui-form-mid"><?php echo e(__('admin.admin/cj/filter_rule')); ?>：</div><div class="layui-input-inline"><textarea name="data[customize_config][html_rule][]" id="content_html_rule_'+caiji+'" class="layui-textarea"></textarea><div class="layui-form-mid layui-word-aux"><a class="layui-btn layui-btn-xs" href="javascript:;" onclick="add_tag(\'content_html_rule_'+caiji+'\')"><?php echo e(__('admin.select')); ?></a></div></div></div><hr>');
        caiji++;
    }

    function testUrl() {
        var data = $('#myform').serialize();

        layer.open({
            type: 2
            ,title: 'test'
            ,closeBtn: 1
            ,area: ['500px;','400px']
            ,shade: 0.8
            ,id: 'LAY_testUrl' //设定一个id，防止重复弹出
            ,btn: ["<?php echo e(__('admin.close')); ?>"]
            ,btnAlign: 'c'
            ,moveType: 1 //拖拽模式，0或者1
            ,content: '<?php echo e(route('admin.cj.show_url')); ?>?call=1&' +data
        });
    }


</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\info.blade.php ENDPATH**/ ?>