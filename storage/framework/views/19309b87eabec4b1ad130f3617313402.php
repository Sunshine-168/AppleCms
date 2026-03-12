<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>
    
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="type_id" value="<?php echo e($info.type_id); ?>">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
        <blockquote class="layui-elem-quote layui-quote-nm">
            <?php echo e(lang('admin/type/tip')); ?>

        </blockquote>

        <div class="layui-form-item">
            <label class="layui-form-label"> <?php echo e(lang('genre')); ?>：</label>
            <div class="layui-input-inline ">
                    <select id="type_mid" name="type_mid" lay-filter="type_mid">
                        <option value="1" <?php if(condition="$info['type_mid'] == '1' || ($info.type_id eq 0 && $infop['type_mid'] == '1')"): ?>selected <?php endif; ?>><?php echo e(lang('vod')); ?></option>
                        <option value="2" <?php if(condition="$info['type_mid'] == '2' || ($info.type_id eq 0 && $infop['type_mid'] == '2')"): ?>selected <?php endif; ?>><?php echo e(lang('art')); ?></option>
                        <option value="8" <?php if(condition="$info['type_mid'] == '8' || ($info.type_id eq 0 && $infop['type_mid'] == '8')"): ?>selected <?php endif; ?>><?php echo e(lang('actor')); ?></option>
                        <option value="11" <?php if(condition="$info['type_mid'] == '11' || ($info.type_id eq 0 && $infop['type_mid'] == '11')"): ?>selected <?php endif; ?>><?php echo e(lang('website')); ?></option>
                    </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/type/parent_type')); ?>：</label>
            <div class="layui-input-inline ">
                    <select name="type_pid">
                        <option value="0"><?php echo e(lang('admin/type/top_type')); ?></option>
                        <?php $__currentLoopData = $parent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo.type_id); ?>" <?php if($info.type_pid eq $vo.type_id || $pid eq $vo.type_id): ?>selected <?php endif; ?>><?php echo e($vo.type_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('status')); ?>：</label>
            <div class="layui-input-block">
                <input name="type_status" type="radio" id="rad-1" value="0" title="<?php echo e(lang('disable')); ?>" <?php if(condition="$info['type_status'] neq 1"): ?>checked <?php endif; ?>>
                <input name="type_status" type="radio" id="rad-2" value="1" title="<?php echo e(lang('enable')); ?>" <?php if(condition="$info['type_status'] eq 1"): ?>checked <?php endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <input type="text" class="layui-input" value="<?php echo e($info.type_sort); ?>" placeholder="" id="type_sort" name="type_sort">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('name')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" lay-verify="type_name" value="<?php echo e($info.type_name); ?>" placeholder="" id="type_name" name="type_name">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('en')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_en); ?>" placeholder="" id="type_en" name="type_en">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/type/type_tpl')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" lay-verify="type_tpl" value="<?php echo e($info.type_tpl); ?>" placeholder="" id="type_tpl" name="type_tpl">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/type/show_tpl')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" lay-verify="type_tpl_list" value="<?php echo e($info.type_tpl_list); ?>" placeholder="" id="type_tpl_list" name="type_tpl_list">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/type/detail_tpl')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_tpl_detail); ?>" placeholder="" id="type_tpl_detail" name="type_tpl_detail">
            </div>
        </div>

        <div class="layui-form-item vod-list">
            <label class="layui-form-label"><?php echo e(lang('admin/type/play_tpl')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_tpl_play); ?>" placeholder="" id="type_tpl_play" name="type_tpl_play">
            </div>
        </div>
        <div class="layui-form-item vod-list">
            <label class="layui-form-label"><?php echo e(lang('admin/type/down_tpl')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_tpl_down); ?>" placeholder="" id="type_tpl_down" name="type_tpl_down">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('seo_title')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_title); ?>" placeholder="" id="type_title" name="type_title">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('seo_key')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_key); ?>" placeholder="" id="type_key" name="type_key">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('seo_des')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_des); ?>" placeholder="" id="type_des" name="type_des">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/type/logo')); ?>：</label>
            <div class="layui-input-inline w600">
                <input type="text" name="type_logo" placeholder="" value="<?php echo e($info['type_logo']); ?>" class="layui-input upload-input">
            </div>
            <div class="layui-input-inline ">
                <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'type_logo'}}" id="upload1"><?php echo e(lang('upload_pic')); ?></button>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/type/pic')); ?></label>
            <div class="layui-input-inline w600">
                <input type="text" name="type_pic" placeholder="" value="<?php echo e($info['type_pic']); ?>" class="layui-input upload-input">
            </div>
            <div class="layui-input-inline ">
                <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'type_pic'}}" id="upload1"><?php echo e(lang('upload_pic')); ?></button>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('jumpurl')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_jumpurl); ?>" placeholder="" id="type_jumpurl" name="type_jumpurl">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('class')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.class); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[class]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?> >
            <label class="layui-form-label"><?php echo e(lang('extend_area')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.area); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[area]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(lang('extend_lang')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.lang); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[lang]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(lang('extend_year')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.year); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[year]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(lang('admin/type/extend_star')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.star); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[star]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(lang('admin/type/extend_director')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.director); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[director]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(lang('admin/type/extend_state')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.state); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[state]">
            </div>
        </div>
        <div class="layui-form-item vod-list" <?php if(condition="$info.type_mid neq '1'"): ?> style="display:none" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(lang('admin/type/extend_version')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.type_extend.version); ?>" placeholder="<?php echo e(lang('multi_separate_tip')); ?>" name="type_extend[version]">
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(lang('btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(lang('btn_reset')); ?></button>
            </div>
        </div>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    function selectOnChange(id)
    {
        var flag = id;
        var type_tpl = 'type.html';
        var type_tpl_list = 'show.html';
        var type_tpl_detail = 'detail.html';
        var type_tpl_play = 'play.html';
        var type_tpl_down = 'down.html';

        if(flag != 1){
            $(".vod-list").hide();
            type_tpl_play = '';
            type_tpl_down = '';
        }
        else{
            $(".vod-list").show();
        }
        if($('input[name="type_id"]').val() ==''){
            $('input[name="type_tpl"]').val(type_tpl);
            $('input[name="type_tpl_list"]').val(type_tpl_list);
            $('input[name="type_tpl_detail"]').val(type_tpl_detail);
            $('input[name="type_tpl_play"]').val(type_tpl_play);
            $('input[name="type_tpl_down"]').val(type_tpl_down);
        }
    }
    layui.use(['form','upload', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , upload = layui.upload
                , $ = layui.jquery;

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(route('admin.upload.upload')); ?>?flag=type"
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

        // 验证
        form.verify({
            type_name: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('name_empty')); ?>";
                }
            },
            type_tpl: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('admin/type/tpl_empty')); ?>";
                }
            }
        });


        form.on('select(type_mid)', function(data){
            selectOnChange(data.value);
        });


        selectOnChange(<?php echo e($info.type_mid); ?>);
    });
</script>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\type\info.blade.php ENDPATH**/ ?>