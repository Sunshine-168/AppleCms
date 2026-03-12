<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="__STATIC__/js/jquery.jscolor.js"></script>
{include file="../../../application/admin/view/public/editor" flag="topic_editor"/}
<div class="page-container p10">

    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>

    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="topic_id" value="<?php echo e($info.topic_id); ?>">

        <div class="layui-tab">
            <ul class="layui-tab-title ">
                <li class="layui-this"><?php echo e(lang('base_info')); ?></a></li>
                <li><?php echo e(lang('other_info')); ?></li>
            </ul>
        <div class="layui-tab-content">

            <div class="layui-tab-item layui-show">

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('param')); ?>：</label>
                    <div class="layui-input-inline ">
                        <select name="topic_status">
                            <option value="1" ><?php echo e(lang('reviewed')); ?></option>
                            <option value="0" <?php if(condition="$info.topic_status eq '0'"): ?>selected@endif><?php echo e(lang('reviewed_not')); ?></option>
                        </select>
                    </div>
                    <div class="layui-input-inline ">
                        <select name="topic_level">
                            <option value="0"><?php echo e(lang('select_level')); ?></option>
                            <option value="9" <?php if(condition="$info['topic_level'] eq 9"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>9-<?php echo e(lang('slide')); ?></option>
                            <option value="1" <?php if(condition="$info['topic_level'] eq 1"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>1</option>
                            <option value="2" <?php if(condition="$info['topic_level'] eq 2"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>2</option>
                            <option value="3" <?php if(condition="$info['topic_level'] eq 3"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>3</option>
                            <option value="4" <?php if(condition="$info['topic_level'] eq 4"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>4</option>
                            <option value="5" <?php if(condition="$info['topic_level'] eq 5"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>5</option>
                            <option value="6" <?php if(condition="$info['topic_level'] eq 6"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>6</option>
                            <option value="7" <?php if(condition="$info['topic_level'] eq 7"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>7</option>
                            <option value="8" <?php if(condition="$info['topic_level'] eq 8"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>8</option>

                        </select>
                    </div>
                    <div class="layui-input-inline w110">
                        <input type="checkbox" name="uptime" title="<?php echo e(lang('update_time')); ?>" value="1" checked class="layui-checkbox checkbox-ids" lay-skin="primary">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('name')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" lay-verify="topic_name" value="<?php echo e($info.topic_name); ?>" placeholder="" name="topic_name">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('sub')); ?>：</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_sub); ?>" placeholder="" name="topic_sub">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('en')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" lay-verify="topic_en" value="<?php echo e($info.topic_en); ?>" placeholder="" name="topic_en">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('letter')); ?>：</label>
                    <div class="layui-input-inline w70">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_letter); ?>" placeholder="" name="topic_letter">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('color')); ?>：</label>
                    <div class="layui-input-inline w70">
                        <input type="text" class="layui-input color" value="<?php echo e($info.topic_color); ?>" placeholder="" name="topic_color">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('remarks')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_remarks); ?>" placeholder="" name="topic_remarks">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_sort); ?>" placeholder="" name="topic_sort">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('class')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_type); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="topic_type">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('tpl')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" lay-verify="topic_tpl" value="<?php echo e($info.topic_tpl|mac_default='detail.html'); ?>" placeholder="" name="topic_tpl">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">TAG：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_tag); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="topic_tag">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('admin/topic/vod_include')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_rel_vod); ?>" placeholder="<?php echo e(lang('vod')); ?>id,<?php echo e(lang('multi_separate_tip')); ?>" name="topic_rel_vod">
                    </div>
                    <div class="layui-input-inline">
                        <a class="layui-btn j-iframe" data-href="<?php echo e(url('vod/data')); ?>?select=1&input=topic_rel_vod" href="javascript:;" title="<?php echo e(lang('search_data')); ?>"><?php echo e(lang('search_data')); ?></a>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('admin/topic/art_include')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_rel_art); ?>" placeholder="<?php echo e(lang('art')); ?>id,<?php echo e(lang('multi_separate_tip')); ?>" name="topic_rel_art">
                    </div>
                    <div class="layui-input-inline">
                        <a class="layui-btn j-iframe" data-href="<?php echo e(url('art/data')); ?>?select=1&input=topic_rel_art" href="javascript:;" title="<?php echo e(lang('search_data')); ?>"><?php echo e(lang('search_data')); ?></a>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('pic')); ?>：</label>
                    <div class="layui-input-inline w500 upload">
                        <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo e($info.topic_pic); ?>" placeholder="" id="topic_pic" name="topic_pic">
                    </div>
                    <div class="layui-input-inline ">
                        <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:1,thumb_class:'upload-thumb'}}" id="upload1"><?php echo e(lang('upload_pic')); ?></button>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('pic_thumb')); ?>：</label>
                    <div class="layui-input-inline w500 upload">
                        <input type="text" class="layui-input upload-input upload-thumb" style="max-width:100%;" value="<?php echo e($info.topic_pic_thumb); ?>" placeholder="" id="topic_pic_thumb" name="topic_pic_thumb">
                    </div>
                    <div class="layui-input-inline ">
                        <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:''}}" id="upload2"><?php echo e(lang('upload_pic')); ?></button>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('slide')); ?>：</label>
                    <div class="layui-input-inline w500 upload">
                        <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo e($info.topic_pic_slide); ?>" placeholder="" id="topic_pic_slide" name="topic_pic_slide">
                    </div>
                    <div class="layui-input-inline ">
                        <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:''}}" id="upload3"><?php echo e(lang('upload_pic')); ?></button>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('blurb')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea name="topic_blurb" cols="" rows="3" class="layui-textarea"  placeholder="<?php echo e(lang('blurb_auto_tip')); ?>" style="height:40px;"><?php echo e($info.topic_blurb); ?></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('content')); ?>：</label>
                    <div class="layui-input-block">
                        <textarea id="topic_content" name="topic_content" type="text/plain" style="width:99%;height:300px"><?php echo e($info.topic_content|mac_url_content_img); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="layui-tab-item">

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('seo_key')); ?>：</label>
                    <div class="layui-input-block w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_key); ?>" placeholder="" name="topic_key">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('seo_des')); ?>：</label>
                    <div class="layui-input-block w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_des); ?>" placeholder="" name="topic_des">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('seo_title')); ?>：</label>
                    <div class="layui-input-block w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_title); ?>" placeholder="" name="topic_title">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('up')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_up); ?>" placeholder="" id="topic_up" name="topic_up">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('hate')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_down); ?>" placeholder="" id="topic_down" name="topic_down">
                    </div>
                    <button class="layui-btn" type="button" id="btn_rnd"><?php echo e(lang('rnd_make')); ?></button>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('hits')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_hits); ?>" placeholder="" id="topic_hits" name="topic_hits">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('hits_month')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_hits_month); ?>" placeholder="" id="topic_hits_month" name="topic_hits_month" >
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('hits_week')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_hits_week); ?>" placeholder="" id="topic_hits_week" name="topic_hits_week">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('hits_day')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input " value="<?php echo e($info.topic_hits_day); ?>" placeholder="" id="topic_hits_day" name="topic_hits_day">
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('score')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_score); ?>" placeholder="" id="topic_score" name="topic_score">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('score_all')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_score_all); ?>" placeholder="" id="topic_score_all" name="topic_score_all">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('score_num')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.topic_score_num); ?>" placeholder="" id="topic_score_num" name="topic_score_num">
                    </div>
                </div>
            </div>

        </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child=""><?php echo e(lang('btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(lang('btn_reset')); ?></button>
            </div>
        </div>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<script type="text/javascript">

    layui.use(['form','upload', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;;

        // 验证
        form.verify({
            topic_name: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('name_empty')); ?>";
                }
            },
            topic_tpl: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('admin/topic/tpl_empty')); ?>";
                }
            }
        });

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(route('admin.upload.upload')); ?>?flag=topic"
            ,method: 'post'
            ,before: function(input) {
                layer.msg("<?php echo e(lang('upload_ing')); ?>", {time:3000000});
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

        $("#btn_rnd").click(function(){
            $("#topic_hits").val( rndNum(5000,9999) );
            $("#topic_hits_month").val( rndNum(1000,4999) );
            $("#topic_hits_week").val( rndNum(300,999) );
            $("#topic_hits_day").val( rndNum(1,299) );
            $("#topic_up").val( rndNum(1,999) );
            $("#topic_down").val( rndNum(1,999) );
            $("#topic_score").val( rndNum(10) );
            $("#topic_score_all").val( rndNum(1000) );
            $("#topic_score_num").val( rndNum(100) );
        });


        var ue = editor_getEditor('topic_content');

    });

</script>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\topic\info.blade.php ENDPATH**/ ?>