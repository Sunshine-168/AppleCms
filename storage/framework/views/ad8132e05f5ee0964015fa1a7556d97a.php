<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerpolicy="no-referrer"></div>

    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.user.info', ['id' => $info->user_id ?: null])); ?>">
        <?php echo csrf_field(); ?>
        <input id="user_id" name="user_id" type="hidden" value="<?php echo e($info->user_id); ?>">

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('access')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e(old('user_name', $info->user_name)); ?>" id="user_name" name="user_name">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('pass')); ?>：</label>
            <div class="layui-input-block">
                <input type="password" class="layui-input" value="" id="user_pwd" name="user_pwd" placeholder="<?php echo e($info->user_id ? '留空则不修改' : ''); ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('status')); ?>：</label>
            <div class="layui-input-block">
                <input name="user_status" type="radio" value="0" title="<?php echo e(__('disable')); ?>" <?php if((string) old('user_status', $info->user_status ?? 1) !== '1'): echo 'checked'; endif; ?>>
                <input name="user_status" type="radio" value="1" title="<?php echo e(__('enable')); ?>" <?php if((string) old('user_status', $info->user_status ?? 1) === '1'): echo 'checked'; endif; ?>>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('group')); ?>：</label>
            <div class="layui-input-inline">
                <select name="group_id" lay-filter="group_id">
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($group->group_id); ?>" <?php if((string) old('group_id', $info->group_id) === (string) $group->group_id): echo 'selected'; endif; ?>><?php echo e($group->group_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('portrait')); ?>：</label>
            <div class="layui-input-inline w500 upload">
                <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo e(old('user_portrait', $info->user_portrait)); ?>" id="user_portrait" name="user_portrait">
            </div>
            <?php if($info->user_id): ?>
                <div class="layui-input-inline">
                    <button type="button" class="layui-btn layui-upload" id="upload1"><?php echo e(__('upload_pic')); ?></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('nickname')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="user_nick_name" value="<?php echo e(old('user_nick_name', $info->user_nick_name)); ?>">
            </div>
        </div>

        <div class="layui-form-item rowTime" <?php if((int) old('group_id', $info->group_id ?? 0) <= 2): ?> style="display:none;" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(__('admin/user/time_end')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="user_end_time" id="user_end_time" value="<?php echo e(old('user_end_time', !empty($info->user_end_time) ? date('Y-m-d H:i:s', (int) $info->user_end_time) : '')); ?>" placeholder="yyyy-MM-dd HH:mm:ss">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('points')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e(old('user_points', $info->user_points ?? 0)); ?>" id="user_points" name="user_points">
            </div>
            <label class="layui-form-label"><?php echo e(__('phone')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e(old('user_phone', $info->user_phone)); ?>" id="user_phone" name="user_phone">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">QQ：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e(old('user_qq', $info->user_qq)); ?>" id="user_qq" name="user_qq">
            </div>
            <label class="layui-form-label">email：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e(old('user_email', $info->user_email)); ?>" id="user_email" name="user_email">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin/user/find_question')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e(old('user_question', $info->user_question)); ?>" id="user_question" name="user_question">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin/user/find_answer')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e(old('user_answer', $info->user_answer)); ?>" id="user_answer" name="user_answer">
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(__('btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    layui.use(['form', 'layer', 'upload', 'laydate'], function () {
        var form = layui.form,
            layer = layui.layer,
            $ = layui.jquery,
            laydate = layui.laydate,
            upload = layui.upload;

        form.verify({
            user_name: function (value) {
                if (value === "") {
                    return "<?php echo e(__('admin/user/access_empty')); ?>";
                }
            }
        });

        laydate.render({
            elem: '#user_end_time',
            type: 'datetime'
        });

        form.on('select(group_id)', function(data) {
            $('.rowTime').hide();
            if (parseInt(data.value, 10) > 2) {
                $('.rowTime').show();
            }
        });

        <?php if($info->user_id): ?>
        upload.render({
            elem: '#upload1',
            url: <?php echo json_encode(route('admin.upload.upload', ['flag' => 'user', 'user_id' => $info->user_id])) ?>,
            method: 'post',
            before: function() {
                layer.msg("<?php echo e(__('upload_ing')); ?>", {time: 3000000});
            },
            done: function(res) {
                if (parseInt(res.code, 10) !== 1) {
                    layer.msg(res.msg || '上传失败');
                    return false;
                }
                layer.closeAll();
                $('#user_portrait').val(res.data.path || '');
            }
        });
        <?php endif; ?>

        $('.upload-input').hover(function (e) {
            var imgsrc = $(this).val();
            if ($.trim(imgsrc) === "") { return; }
            var left = e.clientX + document.body.scrollLeft + 20;
            var top = e.clientY + document.body.scrollTop + 20;
            $(".showpic").css({left: left, top: top, display: ""});
            $(".showpic_img").attr("src", mac_url_img(imgsrc) + '?r=' + Math.random());
        }, function (){
            $(".showpic").css("display", "none");
        });
    });
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\user\info.blade.php ENDPATH**/ ?>