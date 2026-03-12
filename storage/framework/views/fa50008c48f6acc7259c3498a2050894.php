<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(url('data')); ?>">
                <input type="hidden" value="<?php echo e($param.select|mac_filter_xss); ?>" name="select">
                <input type="hidden" value="<?php echo e($param.input|mac_filter_xss); ?>" name="input">
                <div class="layui-input-inline w150">
                    <select name="type">
                        <option value=""><?php echo e(lang('select_type')); ?></option>
                        <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($vo.type_mid eq 11): ?>
                        <option value="<?php echo e($vo.type_id); ?>" <?php if(condition="$param['type'] eq $vo.type_id"): ?>selected <?php endif; ?>><?php echo e($vo.type_name); ?></option>
                        <?php $__currentLoopData = $vo.child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ch.type_id); ?>" <?php if(condition="$param['type'] eq $ch.type_id"): ?>selected <?php endif; ?>>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;<?php echo e($ch.type_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value=""><?php echo e(lang('select_status')); ?></option>
                        <option value="0" <?php if(condition="$param['status'] eq '0'"): ?>selected <?php endif; ?>><?php echo e(lang('reviewed_not')); ?></option>
                        <option value="1" <?php if(condition="$param['status'] eq '1'"): ?>selected <?php endif; ?>><?php echo e(lang('reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value=""><?php echo e(lang('select_level')); ?></option>
                        <option value="9" <?php if(condition="$param['level'] eq '9'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>9-<?php echo e(lang('slide')); ?></option>
                        <option value="1" <?php if(condition="$param['level'] eq '1'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>1</option>
                        <option value="2" <?php if(condition="$param['level'] eq '2'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>2</option>
                        <option value="3" <?php if(condition="$param['level'] eq '3'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>3</option>
                        <option value="4" <?php if(condition="$param['level'] eq '4'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>4</option>
                        <option value="5" <?php if(condition="$param['level'] eq '5'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>5</option>
                        <option value="6" <?php if(condition="$param['level'] eq '6'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>6</option>
                        <option value="7" <?php if(condition="$param['level'] eq '7'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>7</option>
                        <option value="8" <?php if(condition="$param['level'] eq '8'"): ?>selected <?php endif; ?>><?php echo e(lang('level')); ?>8</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="lock">
                        <option value=""><?php echo e(lang('select_lock')); ?></option>
                        <option value="0" <?php if(condition="$param['lock'] eq '0'"): ?>selected <?php endif; ?>><?php echo e(lang('unlock')); ?></option>
                        <option value="1" <?php if(condition="$param['lock'] eq '1'"): ?>selected <?php endif; ?>><?php echo e(lang('lock')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="pic">
                        <option value=""><?php echo e(lang('select_pic')); ?></option>
                        <option value="1" <?php if(condition="$param['pic'] eq '1'"): ?>selected@endif><?php echo e(lang('pic_empty')); ?></option>
                        <option value="2" <?php if(condition="$param['pic'] eq '2'"): ?>selected@endif><?php echo e(lang('pic_remote')); ?></option>
                        <option value="3" <?php if(condition="$param['pic'] eq '3'"): ?>selected@endif><?php echo e(lang('pic_sync_err')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="order">
                        <option value=""><?php echo e(lang('select_sort')); ?></option>
                        <option value="website_time" <?php if(condition="$param['order'] eq 'website_time'"): ?>selected@endif><?php echo e(lang('update_time')); ?></option>
                        <option value="website_id" <?php if(condition="$param['order'] eq 'website_id'"): ?>selected@endif><?php echo e(lang('id')); ?></option>
                        <option value="website_hits" <?php if(condition="$param['order'] eq 'website_hits'"): ?>selected@endif><?php echo e(lang('hits')); ?></option>
                        <option value="website_hits_month" <?php if(condition="$param['order'] eq 'website_hits_month'"): ?>selected@endif><?php echo e(lang('hits_month')); ?></option>
                        <option value="website_hits_week" <?php if(condition="$param['order'] eq 'website_hits_week'"): ?>selected@endif><?php echo e(lang('hits_week')); ?></option>
                        <option value="website_hits_day" <?php if(condition="$param['order'] eq 'website_hits_day'"): ?>selected@endif><?php echo e(lang('hits_day')); ?></option>
                    </select>
                </div>

                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(lang('wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']|mac_filter_xss); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(lang('btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" data-full="1" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=website&col=type_id&tpl=select_type&url=website/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('type')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=website&col=website_level&tpl=select_level&url=website/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('level')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=website&col=website_hits&tpl=select_hits&url=website/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('hits')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=website&col=website_status&tpl=select_status&url=website/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=website&col=website_lock&tpl=select_lock&url=website/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('lock')); ?></a>
            <a class="layui-btn layui-btn-primary j-iframe" data-href="<?php echo e(url('images/opt?tab=website')); ?>" href="javascript:;" title="<?php echo e(lang('pic_sync')); ?>"><i class="layui-icon">&#xe620;</i><?php echo e(lang('pic_sync')); ?></a>
            <a class="layui-btn layui-btn-primary j-iframe" data-checkbox="true" data-href="<?php echo e(url('make/make?ac=info&tab=website')); ?>" href="javascript:;" title="<?php echo e(lang('make_page')); ?>"><i class="layui-icon">&#xe620;</i><?php echo e(lang('make_page')); ?></a>
            <?php if($param.select eq 1): ?>
            <a data-href="" onclick="parent.onSelectResult('<?php echo e($param.input|mac_filter_xss); ?>', $('.checkbox-ids:checked'))" class="layui-btn layui-btn-normal"><?php echo e(lang('select_return')); ?></a>
            <?php endif; ?>
            <?php if(condition="$param['repeat'] neq ''"): ?>
            <a data-href="<?php echo e(url('del')); ?>?repeat=1&retain=min" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del_auto_keep_min')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>?repeat=1&retain=max" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del_auto_keep_max')); ?></a>
            <?php endif; ?>
        </div>

    </div>


    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="50"><?php echo e(lang('id')); ?></th>
                <th ><?php echo e(lang('name')); ?></th>
                <th width="350"><?php echo e(lang('url')); ?></th>
                <th width="50"><?php echo e(lang('hits')); ?></th>
                <th width="50"><?php echo e(lang('referer')); ?></th>
                <th width="30"><?php echo e(lang('level')); ?></th>
                <th width="30"><?php echo e(lang('browse')); ?></th>
                <th width="120"><?php echo e(lang('update_time')); ?></th>
                <th width="170"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.website_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.website_id); ?></td>
                <td>[<?php echo e($vo.type.type_name); ?>] <a target="_blank" class="layui-badge-rim " href="<?php echo e(mac_url_website_detail($vo)); ?>"><?php echo e($vo.website_name|htmlspecialchars); ?></a> <?php if($vo.website_status eq 0): ?> <span class="layui-badge"><?php echo e(lang('reviewed_not')); ?></span><?php endif; ?> <?php if($vo.website_lock eq 1): ?> <span class="layui-badge"><?php echo e(lang('lock')); ?></span><?php endif; ?></td>
                <td><a class="layui-badge-rim " href="<?php echo e($vo.website_jumpurl); ?>" target="_blank"><?php echo e($vo.website_jumpurl); ?></a></td>
                <td><?php echo e($vo.website_hits); ?></td>
                <td><?php echo e($vo.website_referer); ?></td>
                <td><a data-href="<?php echo e(url('index/select')); ?>?tab=website&col=website_level&tpl=select_level&url=website/field&ids=<?php echo e($vo.website_id); ?>" data-width="270" data-height="100" class=" j-select"><span class="layui-badge layui-bg-orange"><?php echo e($vo.website_level); ?></span></a></td>
                <td><?php if($vo.ismake eq 1): ?><a target="_blank" class="layui-badge layui-bg-green " href="<?php echo e(mac_url_website_detail($vo)); ?>">Y</a>{else/}<a class="layui-badge" href="<?php echo e(url('make/make?ac=info&tab=website')); ?>?ids=<?php echo e($vo.website_id); ?>&ref=1">N</a><?php endif; ?></td>
                <td><?php echo e($vo.website_time|mac_day='color'); ?></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('visit/index?mid=11').'?wd='.parse_url($vo['website_jumpurl'])['host']); ?>" href="javascript:;" title=""><?php echo e(lang('referer')); ?></a>
                    <a class="layui-badge-rim j-ajax" data-href="<?php echo e(url('index/check_back_link')); ?>?url=<?php echo e($vo['website_jumpurl']); ?>" refresh="no" href="javascript:;" title="<?php echo e(lang('detect')); ?>"><?php echo e(lang('detect')); ?></a>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info?id='.$vo['website_id'])); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['website_id'])); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div id="pages" class="center"></div>
    </form>
</div>




<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    var curUrl="<?php echo e(url('website/data',$param)); ?>";
    layui.use(['laypage', 'layer','form'], function() {
        var laypage = layui.laypage
                , layer = layui.layer,
                form = layui.form;

        laypage.render({
            elem: 'pages'
            ,count: <?php echo e($total); ?>

            ,limit: <?php echo e($limit); ?>

            ,curr: <?php echo e($page); ?>

            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj,first){
                if(!first){
                    location.href = curUrl.replace('%7Bpage%7D',obj.curr).replace('%7Blimit%7D',obj.limit);
                }
            }
        });


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\website\index.blade.php ENDPATH**/ ?>