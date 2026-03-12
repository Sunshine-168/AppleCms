<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="__STATIC__/js/jquery.jscolor.js"></script>
{include file="../../../application/admin/view/public/editor" flag="website_editor"/}

<div class="page-container p10">
    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>
    
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="website_id" value="<?php echo e($info.website_id); ?>">

        <div class="layui-tab">
            <ul class="layui-tab-title ">
                <li class="layui-this"><?php echo e(lang('base_info')); ?></a></li>
                <li><?php echo e(lang('other_info')); ?></li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">
                    
                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('param')); ?>：</label>
                    <div class="layui-input-inline w150">
                            <select name="type_id" lay-filter="type_id">
                                <option value=""><?php echo e(lang('select_type')); ?></option>
                                <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($vo.type_mid eq 11): ?>
                                    <option value="<?php echo e($vo.type_id); ?>" <?php if($info.type_id eq $vo.type_id): ?>selected@endif><?php echo e($vo.type_name); ?></option>
                                    <?php $__currentLoopData = $$vo.child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ch.type_id); ?>" <?php if($info.type_id eq $ch.type_id): ?>selected@endif>&nbsp;|&nbsp;&nbsp;&nbsp;|—<?php echo e($ch.type_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                            <select name="website_level">
                                <option value="0"><?php echo e(lang('select_level')); ?></option>
                                <option value="9" <?php if($info.website_level eq 9): ?>selected@endif><?php echo e(lang('level')); ?>9-<?php echo e(lang('slide')); ?></option>
                                <option value="1" <?php if($info.website_level eq 1): ?>selected@endif><?php echo e(lang('level')); ?>1</option>
                                <option value="2" <?php if($info.website_level eq 2): ?>selected@endif><?php echo e(lang('level')); ?>2</option>
                                <option value="3" <?php if($info.website_level eq 3): ?>selected@endif><?php echo e(lang('level')); ?>3</option>
                                <option value="4" <?php if($info.website_level eq 4): ?>selected@endif><?php echo e(lang('level')); ?>4</option>
                                <option value="5" <?php if($info.website_level eq 5): ?>selected@endif><?php echo e(lang('level')); ?>5</option>
                                <option value="6" <?php if($info.website_level eq 6): ?>selected@endif><?php echo e(lang('level')); ?>6</option>
                                <option value="7" <?php if($info.website_level eq 7): ?>selected@endif><?php echo e(lang('level')); ?>7</option>
                                <option value="8" <?php if($info.website_level eq 8): ?>selected@endif><?php echo e(lang('level')); ?>8</option>

                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                            <select name="website_status">
                                <option value="1" ><?php echo e(lang('reviewed')); ?></option>
                                <option value="0" <?php if(condition="$info.website_status eq '0'"): ?>selected@endif><?php echo e(lang('reviewed_not')); ?></option>
                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="website_lock">
                            <option value="0"><?php echo e(lang('unlock')); ?></option>
                            <option value="1" <?php if($info.website_lock eq 1): ?>selected@endif><?php echo e(lang('lock')); ?></option>
                        </select>
                    </div>

                    <div class="layui-input-inline">
                        <input type="checkbox" name="uptime" title="<?php echo e(lang('update_time')); ?>" value="1" checked class="layui-checkbox checkbox-ids" lay-skin="primary">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('name')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.website_name); ?>" placeholder="" name="website_name">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('sub')); ?>：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo e($info.website_sub); ?>" placeholder="" name="website_sub">
                    </div>

                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"><?php echo e(lang('en')); ?>：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.website_en); ?>" placeholder="" name="website_en">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('letter')); ?>：</label>
                    <div class="layui-input-inline w70">
                        <input type="text" class="layui-input" value="<?php echo e($info.website_letter); ?>" placeholder="" name="website_letter">
                    </div>
                    <label class="layui-form-label"><?php echo e(lang('color')); ?>：</label>
                    <div class="layui-input-inline w70">
                        <input type="text" class="layui-input color" value="<?php echo e($info.website_color); ?>" placeholder="" name="website_color">
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('jumpurl')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.website_jumpurl); ?>" placeholder="" name="website_jumpurl">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('area')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.website_area); ?>" placeholder="" name="website_area">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('lang')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.website_lang); ?>" placeholder="" name="website_lang">
                        </div>
                    </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">TAG：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo e($info.website_tag); ?>" placeholder="" name="website_tag">
                    </div>
                    <div class="layui-input-inline w120">
                        <input type="checkbox" name="uptag" title="<?php echo e(lang('auto_make')); ?>" value="1" class="layui-checkbox checkbox-ids" lay-skin="primary">
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('class')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.website_class); ?>" placeholder="" id="website_class" name="website_class">
                        </div>
                        <div class="layui-input-inline w500 website_class_label">

                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('remarks')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.website_remarks); ?>" placeholder="" name="website_remarks">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">LOGO：</label>
                        <div class="layui-input-inline w500 upload">
                            <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo e($info.website_logo); ?>" placeholder="" id="website_logo" name="website_logo">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'upload-thumb'}}" id="upload3"><?php echo e(lang('upload_pic')); ?></button>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('pic')); ?>：</label>
                        <div class="layui-input-inline w500 upload">
                            <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo e($info.website_pic); ?>" placeholder="" id="website_pic" name="website_pic">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:1,thumb_class:'upload-thumb'}}" id="upload1"><?php echo e(lang('upload_pic')); ?></button>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label "><?php echo e(lang('pic_screenshot')); ?>：</label>
                        <div class="layui-input-inline w400 ">
                            <div class="layui-btn-group">
                                <button type="button" class="layui-btn screenshot"><i class="layui-icon layui-icon-upload"></i> <?php echo e(lang('upload_pic')); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <textarea id="website_pic_screenshot" name="website_pic_screenshot" placeholder="<?php echo e(lang('screenshot_tip')); ?>" type="text/plain" style="width:100%;height:150px;"><?php echo e($info.website_pic_screenshot|mac_str_correct=###,'#',chr(13)); ?></textarea>
                            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
                                <legend><?php echo e(lang('screenshot_preview')); ?></legend>
                            </fieldset>
                            <div class="screenshot_list">
                                <?php $__currentLoopData = $$info.website_pic_screenshot_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div data-src="<?php echo e($vo['url']); ?>"><a href="javascript:;" class="del_screenshot"><?php echo e(lang('del')); ?></a>
                                    <img src="<?php echo e($vo['url']|mac_url_img); ?>" alt="" class="layui-upload-img screenshot-img">
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('blurb')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea name="website_blurb" cols="" rows="3" class="layui-textarea"  placeholder="<?php echo e(lang('blurb_auto_tip')); ?>" style="height:40px;"><?php echo e($info.website_blurb); ?></textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('content')); ?>：</label>
                        <div class="layui-input-block">
                            <textarea id="website_content" name="website_content" type="text/plain" style="width:99%;height:300px"><?php echo e($info.website_content|mac_url_content_img); ?></textarea>
                        </div>
                    </div>
                    
        </div>


                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(lang('up')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_up); ?>" placeholder="" id="website_up" name="website_up">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('hate')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_down); ?>" placeholder="" id="website_down" name="website_down">
                            </div>
                            <button class="layui-btn" type="button" id="btn_rnd"><?php echo e(lang('rnd_make')); ?></button>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(lang('hits')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_hits); ?>" placeholder="" id="website_hits" name="website_hits">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('hits_month')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_hits_month); ?>" placeholder="" id="website_hits_month" name="website_hits_month" >
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(lang('hits_week')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_hits_week); ?>" placeholder="" id="website_hits_week" name="website_hits_week">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('hits_day')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input " value="<?php echo e($info.website_hits_day); ?>" placeholder="" id="website_hits_day" name="website_hits_day">
                            </div>
                        </div>


                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(lang('score')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_score); ?>" placeholder="" id="website_score" name="website_score">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('score_all')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_score_all); ?>" placeholder="" id="website_score_all" name="website_score_all">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('score_num')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_score_num); ?>" placeholder="" id="website_score_num" name="website_score_num">
                            </div>
                        </div>


                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(lang('admin/website/referer')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_referer); ?>" placeholder="" id="website_referer" name="website_referer">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('admin/website/referer_month')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_referer_month); ?>" placeholder="" id="website_referer_month" name="website_referer_month">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php echo e(lang('admin/website/referer_week')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_referer_week); ?>" placeholder="" id="website_referer_week" name="website_referer_week">
                            </div>
                            <label class="layui-form-label"><?php echo e(lang('admin/website/referer_day')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_referer_day); ?>" placeholder="" id="website_referer_day" name="website_referer_day">
                            </div>
                        </div>


                        <div class="layui-form-item">

                            <label class="layui-form-label"><?php echo e(lang('tpl')); ?>：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo e($info.website_tpl); ?>" placeholder="" name="website_tpl">
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
    var UPLOAD_IMG_KEY="<?php echo e($GLOBALS['config']['upload']['img_key']); ?>";UPLOAD_IMG_API="<?php echo e($GLOBALS['config']['upload']['img_api']); ?>";

    layui.use(['form','upload', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;;

        // 验证
        form.verify({
            website_name: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('name_empty')); ?>";
                }
            }
        });

        $(document).on("click", ".extend", function(){
            $id = $(this).attr('data-id');
            if($id == 'website_class'||$id == 'website_keywords'){
                $val = $("input[id='"+$id+"']").val();
                if($val!=''){
                    $val = $val+',';
                }
                if($val.indexOf($(this).text())>-1){
                    return;
                }
                $("input[id='"+$id+"']").val($val+$(this).text());
            }else{
                $("input[id='"+$id+"']").val($(this).text());
            }
        });


        form.on('select(type_id)', function(data){
            getExtend(data.value);
        });

        //多图片上传
        upload.render({
            elem: '.screenshot'
            ,url: "<?php echo e(route('admin.upload.upload')); ?>?flag=website_screenshot"
            ,multiple: true
            ,before: function(obj){
                obj.preview(function(index, file, result){

                });
            }
            ,done: function(res){
                var val = res.data.file;
                var input = $("#website_pic_screenshot")
                var content = input.val();
                if(content!=''){
                    content += '\r\n';
                }
                content += val;
                input.val(content);
                $('.screenshot_list').append('<div data-src="'+val+'"><a href="javascript:;" class="del_screenshot"><?php echo e(lang(\'del\')); ?></a><img src="'+mac_url_img(val)+'" alt="" class="layui-upload-img screenshot-img"></div>');
            }
        });
        //监听文本框
        $('#website_pic_screenshot').keyup(function(e){
            let html = ``;
            var textArr = $(this).val().split(/[(\r\n)\r\n]+/);
            textArr.forEach((item,index)=>{
                if(!item){
                    textArr.splice(index,1);
                }else{
                    if(item.indexOf('$')>-1){
                        item = item.substring(item.indexOf('$')+1);
                    }
                    html += `<div data-src="${item}"><a href="javascript:;" class="del_screenshot"><?php echo e(lang('del')); ?></a><img src="${mac_url_img(item)}"" alt="" class="layui-upload-img screenshot-img"></div>`;
                }
            });
            $('.screenshot_list').html(html);
        });

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(route('admin.upload.upload')); ?>?flag=website"
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
            $("#website_hits").val( rndNum(5000,9999) );
            $("#website_hits_month").val( rndNum(1000,4999) );
            $("#website_hits_week").val( rndNum(300,999) );
            $("#website_hits_day").val( rndNum(1,299) );
            $("#website_up").val( rndNum(1,999) );
            $("#website_down").val( rndNum(1,999) );
            $("#website_score").val( rndNum(10) );
            $("#website_score_all").val( rndNum(1000) );
            $("#website_score_num").val( rndNum(100) );
        });
        $(document).on('click', '.del_screenshot', function() {
            var src = $(this).parent().attr('data-src');
            var input = $("#website_pic_screenshot")
            var content = input.val();
            console.log(content);
            var snsArr = content.split(/[(\r\n)\r\n]+/);
            snsArr.forEach((item,index)=>{
                if(!item || item == src){
                    snsArr.splice(index,1);//删除
                }
            });
            $(this).parent().remove();
            input.val(snsArr.join('\r\n'));//重新赋值
            $.get("<?php echo e(url('annex/del')); ?>", {ids:src}, function(res){});
        });
        var ue = editor_getEditor('website_content');
    });

    function getExtend(id){
        $.post("<?php echo e(url('type/extend')); ?>", {id:id}, function(res) {

            if (res.code == 1) {
                $.each(res.data, function(key, value){
                    $('.website_'+key+"_label").html('');
                    if(value != ''){
                        $.each(value, function(key2, value2){
                            $(".website_"+key+"_label").append('<a class="layui-btn layui-btn-xs extend" href="javascript:;" data-id="website_'+key+'">'+value2+'</a>');
                        });
                    }
                });
            }
        });
    }

    <?php if($info.website_id gt 0): ?>
    setTimeout(function () {
        getExtend('<?php echo e($info.type_id); ?>')
    },1000);
    <?php endif; ?>
    
</script>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\website\info.blade.php ENDPATH**/ ?>