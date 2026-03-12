<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="<?php echo e(asset('static')); ?>/js/jquery.jscolor.js"></script>
<?php echo $__env->make('../../../application/admin/view/public/editor', ['flag' => 'role_editor'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>
    
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="role_id" value="<?php echo e($info.role_id); ?>">

        <div class="layui-tab">
            <ul class="layui-tab-title ">
                <li class="layui-this"><?php echo e(__('admin.base_info')); ?></a></li>
                <li><?php echo e(__('admin.other_info')); ?></li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">
                    
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.param')); ?>：</label>
                    <div class="layui-input-inline w150">
                            <select name="role_level">
                                <option value="0"><?php echo e(__('admin.select_level')); ?></option>
                                <option value="9" <?php if($info.role_level == 9): ?>selected@endif><?php echo e(__('admin.level')); ?>9-<?php echo e(__('admin.slide')); ?></option>
                                <option value="1" <?php if($info.role_level == 1): ?>selected@endif><?php echo e(__('admin.level')); ?>1</option>
                                <option value="2" <?php if($info.role_level == 2): ?>selected@endif><?php echo e(__('admin.level')); ?>2</option>
                                <option value="3" <?php if($info.role_level == 3): ?>selected@endif><?php echo e(__('admin.level')); ?>3</option>
                                <option value="4" <?php if($info.role_level == 4): ?>selected@endif><?php echo e(__('admin.level')); ?>4</option>
                                <option value="5" <?php if($info.role_level == 5): ?>selected@endif><?php echo e(__('admin.level')); ?>5</option>
                                <option value="6" <?php if($info.role_level == 6): ?>selected@endif><?php echo e(__('admin.level')); ?>6</option>
                                <option value="7" <?php if($info.role_level == 7): ?>selected@endif><?php echo e(__('admin.level')); ?>7</option>
                                <option value="8" <?php if($info.role_level == 8): ?>selected@endif><?php echo e(__('admin.level')); ?>8</option>

                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                            <select name="role_status">
                                <option value="1"><?php echo e(__('admin.reviewed')); ?></option>
                                <option value="0" <?php if(condition="$info.role_status == '0'"): ?>selected@endif><?php echo e(__('admin.reviewed_not')); ?></option>
                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="role_lock">
                            <option value="0"><?php echo e(__('admin.unlock')); ?></option>
                            <option value="1" <?php if($info.role_lock == 1): ?>selected@endif><?php echo e(__('admin.lock')); ?></option>
                        </select>
                    </div>

                    <div class="layui-input-inline">
                        <input type="checkbox" name="uptime" title="<?php echo e(__('admin.update_time')); ?>" value="1" checked class="layui-checkbox checkbox-ids" lay-skin="primary">
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.vod_name')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" class="layui-input" value="<?php echo e($data.vod_name); ?>" readonly="readonly" placeholder="" name="">
                        </div>
                        <label class="layui-form-label"><?php echo e(__('admin.vod_id')); ?>：</label>
                        <div class="layui-input-inline w70">
                            <input type="text" class="layui-input" value="<?php echo e($info.role_rid); ?>" readonly="readonly" placeholder="" name="role_rid">
                        </div>
                        <label class="layui-form-label"><?php echo e(__('admin.vod')); ?><?php echo e(__('admin.type')); ?>：</label>
                        <div class="layui-input-inline w70">
                            <input type="text" class="layui-input" value="<?php echo e($data.type.type_name); ?>" readonly="readonly" placeholder="" name="">
                        </div>
                    </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.role_name')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="text" class="layui-input" value="<?php echo e($info.role_name); ?>" placeholder="" name="role_name">
                    </div>
                    <label class="layui-form-label"><?php echo e(__('admin.actor_name')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="text" class="layui-input" value="<?php echo e($info.role_actor); ?>" placeholder="" name="role_actor">
                    </div>
                    <label class="layui-form-label"><?php echo e(__('admin.sort')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="text" class="layui-input" value="<?php echo e($info.role_sort); ?>" placeholder="" name="role_sort">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.en')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="text" class="layui-input" value="<?php echo e($info.role_en); ?>" placeholder="" name="role_en">
                    </div>
                    <label class="layui-form-label"><?php echo e(__('admin.letter')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="text" class="layui-input" value="<?php echo e($info.role_letter); ?>" placeholder="" name="role_letter">
                    </div>
                    <label class="layui-form-label"><?php echo e(__('admin.color')); ?>：</label>
                    <div class="layui-input-inline w200">
                        <input type="text" class="layui-input color" value="<?php echo e($info.role_color); ?>" placeholder="" name="role_color">
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.remarks')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" class="layui-input" value="<?php echo e($info.role_remarks); ?>" placeholder="" name="role_remarks">
                        </div>
                    </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(__('admin.pic')); ?>：</label>
                    <div class="layui-input-inline w400 upload">
                        <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo e($info.role_pic); ?>" placeholder="" id="role_pic" name="role_pic">
                    </div>
                    <div class="layui-input-inline ">
                        <button type="button" class="layui-btn layui-upload" lay-data="" id="upload1"><?php echo e(__('admin.upload_pic')); ?></button>
                    </div>
                </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.content')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea id="role_content" name="role_content" type="text/plain" style="width:99%;height:300px"><?php echo e($info.role_content|mac_url_content_img); ?></textarea>
                        </div>
                    </div>
                    
        </div>


                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.up')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_up); ?>" placeholder="" id="role_up" name="role_up">
                            </div>
                            <label class="layui-form-label"><?php echo e(__('admin.hate')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_down); ?>" placeholder="" id="role_down" name="role_down">
                            </div>
                            <button class="layui-btn" type="button" id="btn_rnd"><?php echo e(__('admin.rnd_make')); ?></button>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.hits')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_hits); ?>" placeholder="" id="role_hits" name="role_hits">
                            </div>
                            <label class="layui-form-label"><?php echo e(__('admin.hits_month')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_hits_month); ?>" placeholder="" id="role_hits_month" name="role_hits_month" >
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.hits_week')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_hits_week); ?>" placeholder="" id="role_hits_week" name="role_hits_week">
                            </div>
                            <label class="layui-form-label"><?php echo e(__('admin.hits_day')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input " value="<?php echo e($info.role_hits_day); ?>" placeholder="" id="role_hits_day" name="role_hits_day">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.score')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_score); ?>" placeholder="" id="role_score" name="role_score">
                            </div>
                            <label class="layui-form-label"><?php echo e(__('admin.score_all')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_score_all); ?>" placeholder="" id="role_score_all" name="role_score_all">
                            </div>
                            <label class="layui-form-label"><?php echo e(__('admin.score_num')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_score_num); ?>" placeholder="" id="role_score_num" name="role_score_num">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(__('admin.tpl')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_tpl); ?>" placeholder="" name="role_tpl">
                            </div>
                            <label class="layui-form-label"><?php echo e(__('admin.jumpurl')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.role_jumpurl); ?>" placeholder="" name="role_jumpurl">
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

    layui.use(['form','upload', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;;

        // 验证
        form.verify({
            role_name: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.name_empty')); ?>";
                }
            }
        });

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(route('admin.upload.upload')); ?>?flag=role"
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

        $("#btn_rnd").click(function(){
            $("#role_hits").val( rndNum(5000,9999) );
            $("#role_hits_month").val( rndNum(1000,4999) );
            $("#role_hits_week").val( rndNum(300,999) );
            $("#role_hits_day").val( rndNum(1,299) );
            $("#role_up").val( rndNum(1,999) );
            $("#role_down").val( rndNum(1,999) );
            $("#role_score").val( rndNum(10) );
            $("#role_score_all").val( rndNum(1000) );
            $("#role_score_num").val( rndNum(100) );
        });

        var ue = editor_getEditor('role_content');
    });
    
</script>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\role\info.blade.php ENDPATH**/ ?>