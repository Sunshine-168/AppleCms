<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
    table {
        table-layout: fixed;
    }


    td {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(route('admin.vod.index')); ?>">
                <input type="hidden" value="<?php echo e(htmlspecialchars($param['select'] ?? '')); ?>" name="select">
                <input type="hidden" value="<?php echo e(htmlspecialchars($param['input'] ?? '')); ?>" name="input">
                <div class="layui-input-inline w150">
                    <select name="type">
                        <option value=""><?php echo e(__('admin.select_type')); ?></option>
                        <?php $__currentLoopData = $type_tree ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($vo->type_mid == 1): ?>
                        <option value="<?php echo e($vo->type_id); ?>" <?php if(($param['type'] ?? '') == $vo->type_id): ?>selected <?php endif; ?>><?php echo e($vo->type_name); ?></option>
                        <?php $__currentLoopData = $vo->child ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ch->type_id); ?>" <?php if(($param['type'] ?? '') == $ch->type_id): ?>selected <?php endif; ?>>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;<?php echo e($ch->type_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value=""><?php echo e(__('admin.select_status')); ?></option>
                        <option value="0" <?php if(($param['status'] ?? '') == '0'): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed_not')); ?></option>
                        <option value="1" <?php if(($param['status'] ?? '') == '1'): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value=""><?php echo e(__('admin.select_level')); ?></option>
                        <option value="9" <?php if(($param['level'] ?? '') == '9'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>9-<?php echo e(__('admin.slide')); ?></option>
                        <option value="1" <?php if(($param['level'] ?? '') == '1'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>1</option>
                        <option value="2" <?php if(($param['level'] ?? '') == '2'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>2</option>
                        <option value="3" <?php if(($param['level'] ?? '') == '3'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>3</option>
                        <option value="4" <?php if(($param['level'] ?? '') == '4'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>4</option>
                        <option value="5" <?php if(($param['level'] ?? '') == '5'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>5</option>
                        <option value="6" <?php if(($param['level'] ?? '') == '6'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>6</option>
                        <option value="7" <?php if(($param['level'] ?? '') == '7'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>7</option>
                        <option value="8" <?php if(($param['level'] ?? '') == '8'): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>8</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="lock">
                        <option value=""><?php echo e(__('admin.select_lock')); ?></option>
                        <option value="0" <?php if(($param['lock'] ?? '') == '0'): ?>selected <?php endif; ?>><?php echo e(__('admin.unlock')); ?></option>
                        <option value="1" <?php if(($param['lock'] ?? '') == '1'): ?>selected <?php endif; ?>><?php echo e(__('admin.lock')); ?></option>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="weekday">
                        <option value=""><?php echo e(__('admin.admin/vod/select_weekday')); ?></option>
                        <?php $__currentLoopData = explode(',', config('maccms.app.vod_extend_weekday', '')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo2); ?>" <?php if(($param['weekday'] ?? '') == $vo2): ?>selected <?php endif; ?>><?php echo e($vo2); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="area">
                        <option value=""><?php echo e(__('admin.admin/vod/select_area')); ?></option>
                        <?php $__currentLoopData = explode(',', config('maccms.app.vod_extend_area', '')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo2); ?>" <?php if(($param['area'] ?? '') == $vo2): ?>selected <?php endif; ?>><?php echo e($vo2); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="lang">
                        <option value=""><?php echo e(__('admin.admin/vod/select_lang')); ?></option>
                        <?php $__currentLoopData = explode(',', config('maccms.app.vod_extend_lang', '')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo2); ?>" <?php if(($param['lang'] ?? '') == $vo2): ?>selected <?php endif; ?>><?php echo e($vo2); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>


                <div class="layui-input-inline w150">
                    <select name="server">
                        <option value=""><?php echo e(__('admin.admin/vod/select_server')); ?></option>
                        <?php $__currentLoopData = $server_list ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo->from ?? $vo['from']); ?>" <?php if(($param['server'] ?? '') == ($vo->from ?? $vo['from'])): ?>selected <?php endif; ?>><?php echo e($vo->show ?? $vo['show']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="player">
                        <option value=""><?php echo e(__('admin.admin/vod/select_player')); ?></option>
                        <option value="no" <?php if(($param['player'] ?? '') == 'no'): ?>selected@endif><?php echo e(__('admin.admin/vod/player_empty')); ?></option>
                        <?php $__currentLoopData = $player_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo.from); ?>" <?php if(condition="$param['player'] eq $vo.from"): ?>selected@endif><?php echo e($vo.show); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="downer">
                        <option value=""><?php echo e(__('admin.admin/vod/select_downer')); ?></option>
                        <option value="no" <?php if(($param['downer'] ?? '') == 'no'): ?>selected@endif><?php echo e(__('admin.admin/vod/downer_empty')); ?></option>
                        <?php $__currentLoopData = $downer_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vo.from); ?>" <?php if(condition="$param['downer'] eq $vo.from"): ?>selected@endif><?php echo e($vo.show); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <select name="isend">
                        <option value=""><?php echo e(__('admin.admin/vod/select_isend')); ?></option>
                        <option value="0" <?php if(($param['isend'] ?? '') == '0'): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/vod/no_end')); ?></option>
                        <option value="1" <?php if(($param['isend'] ?? '') == '1'): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/vod/is_end')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="copyright">
                        <option value=""><?php echo e(__('admin.admin/vod/select_copyright')); ?></option>
                        <option value="0" <?php if(condition="$param['copyright'] eq '0'"): ?>selected <?php endif; ?>><?php echo e(lang('close')); ?></option>
                        <option value="1" <?php if(condition="$param['copyright'] eq '1'"): ?>selected <?php endif; ?>><?php echo e(lang('open')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="plot">
                        <option value=""><?php echo e(__('admin.admin/vod/select_plot')); ?></option>
                        <option value="0" <?php if(($param['plot'] ?? '') == '0'): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/vod/no')); ?></option>
                        <option value="1" <?php if(($param['plot'] ?? '') == '1'): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/vod/have')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="role">
                        <option value=""><?php echo e(__('admin.admin/vod/select_role')); ?></option>
                        <option value="0" <?php if(($param['role'] ?? '') == '0'): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/vod/no')); ?></option>
                        <option value="1" <?php if(($param['role'] ?? '') == '1'): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/vod/have')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="order">
                        <option value=""><?php echo e(lang('select_sort')); ?></option>
                        <option value="vod_time" <?php if(condition="$param['order'] eq 'vod_time'"): ?>selected@endif><?php echo e(lang('update_time')); ?></option>
                        <option value="vod_id" <?php if(condition="$param['order'] eq 'vod_id'"): ?>selected@endif><?php echo e(lang('id')); ?></option>
                        <option value="vod_hits" <?php if(condition="$param['order'] eq 'vod_hits'"): ?>selected@endif><?php echo e(lang('hits')); ?></option>
                        <option value="vod_hits_month" <?php if(condition="$param['order'] eq 'vod_hits_month'"): ?>selected@endif><?php echo e(lang('hits_month')); ?></option>
                        <option value="vod_hits_week" <?php if(condition="$param['order'] eq 'vod_hits_week'"): ?>selected@endif><?php echo e(lang('hits_week')); ?></option>
                        <option value="vod_hits_day" <?php if(condition="$param['order'] eq 'vod_hits_day'"): ?>selected@endif><?php echo e(lang('hits_day')); ?></option>
                    </select>
                </div>


                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(lang('wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']|mac_restore_htmlfilter); ?>">
                </div>
                <input type="hidden" name="repeat" value="<?php echo e($param['repeat']|mac_filter_xss); ?>" />
                <button class="layui-btn mgl-20 j-search" ><?php echo e(lang('btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" data-full="1" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=type_id&tpl=select_type&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('type')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=vod_level&tpl=select_level&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('level')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=vod_hits&tpl=select_hits&url=vod/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('hits')); ?></a>            
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=vod_status&tpl=select_status&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=vod_lock&tpl=select_lock&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('lock')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=vod_copyright&tpl=select_copyright&url=vod/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('admin/vod/copyright')); ?></a>
            <a class="layui-btn layui-btn-primary j-iframe" data-href="<?php echo e(url('images/opt?tab=vod')); ?>" href="javascript:;" title="<?php echo e(lang('pic_sync')); ?>"><i class="layui-icon">&#xe620;</i><?php echo e(lang('pic_sync')); ?></a>
            <a class="layui-btn layui-btn-primary j-iframe" data-checkbox="true" data-href="<?php echo e(url('make/make?ac=info&tab=vod')); ?>" href="javascript:;" title="<?php echo e(lang('make_page')); ?>"><i class="layui-icon">&#xe620;</i><?php echo e(lang('make_page')); ?></a>
            <?php if($param.select eq 1): ?>
            <a data-href="" onclick="parent.onSelectResult('<?php echo e($param.input|mac_filter_xss); ?>', $('.checkbox-ids:checked'))" class="layui-btn layui-btn-normal"><?php echo e(lang('select_return')); ?></a>
            <?php endif; ?>

            <?php if(condition="$param['repeat'] neq ''"): ?>
            <a data-href="<?php echo e(url('del')); ?>?repeat=1&retain=min" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del_auto_keep_min')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>?repeat=1&retain=max" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del_auto_keep_max')); ?></a>
            <a data-href="<?php echo e(url('data')); ?>?repeat=1&cache=1" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe640;</i><?php echo e(lang('update_repeat_cache')); ?></a>
            <?php endif; ?>
        </div>

    </div>


    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="40"><?php echo e(lang('id')); ?></th>
                <th id="table_th_vod_name"><?php echo e(lang('name')); ?></th>
                <th width="50"><?php echo e(lang('hits')); ?></th>
                <th width="50"><?php echo e(lang('hits_week')); ?></th>
                <th width="40"><?php echo e(lang('score')); ?></th>
                <th width="30"><?php echo e(lang('level')); ?></th>
                <th width="30"><?php echo e(lang('browse')); ?></th>
                <th width="80"><?php echo e(lang('player')); ?></th>
                <th width="120"><?php echo e(lang('update_time')); ?></th>
                <th width="190"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.vod_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.vod_id); ?></td>
                <td>
                    [<?php echo e($vo.type.type_name); ?>] <a target="_blank" class="layui-badge-rim" href="<?php echo e(mac_url_vod_detail($vo)); ?>"><?php echo e($vo.vod_name|mac_filter_xss|mac_restore_htmlfilter); ?></a>
                    <?php if($vo.vod_status eq 0): ?> <span class="layui-badge"><?php echo e(lang('reviewed_not')); ?></span><?php endif; ?>
                    <?php if($vo.vod_lock eq 1): ?> <span class="layui-badge"><?php echo e(lang('lock')); ?></span><?php endif; ?>
                    <?php if(condition="$vo.vod_isend eq 0 && $vo.vod_serial neq ''"): ?> <span class="layui-badge layui-bg-blue"><?php echo e(lang('admin/vod/serialize')); ?><?php echo e($vo.vod_serial); ?></span><?php endif; ?>
                    <?php if(condition="$vo.vod_remarks neq ''"): ?> <span class="layui-badge layui-bg-orange"><?php echo e($vo.vod_remarks); ?></span><?php endif; ?>
                    <?php if($vo.vod_plot eq 1): ?> <span class="layui-badge layui-bg-cyan"><?php echo e(lang('plot')); ?></span><?php endif; ?>
                    <?php if($vo.vod_role eq 1): ?> <span class="layui-badge layui-bg-purple"><?php echo e(lang('admin/vod/role')); ?></span><?php endif; ?>
                    <?php if($vo.vod_copyright eq 1): ?> <span class="layui-badge layui-bg-black"><?php echo e(lang('admin/vod/copyright')); ?></span><?php endif; ?>
                </td>
                <td><?php echo e($vo.vod_hits); ?></td>
                <td><?php echo e($vo.vod_hits_week); ?></td>
                <td><?php echo e($vo.vod_score); ?></td>
                <td><a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=vod_level&tpl=select_level&url=vod/field&ids=<?php echo e($vo.vod_id); ?>" data-width="270" data-height="100" class=" j-select"><span class="layui-badge layui-bg-orange"><?php echo e($vo.vod_level); ?></span></a></td>
                <td><?php if($vo.ismake eq 1): ?><a target="_blank" class="layui-badge layui-bg-green " href="<?php echo e(mac_url_vod_detail($vo)); ?>">Y</a>{else/}<a class="layui-badge" href="<?php echo e(url('make/make?ac=info&tab=vod')); ?>?ids=<?php echo e($vo.vod_id); ?>&ref=1">N</a><?php endif; ?></td>
                <td><span title="<?php echo e($vo['vod_play_from']|str_replace='$$$',',',###); ?>-<?php echo e($vo['vod_down_from']|str_replace='$$$',',',###); ?>"><?php echo e($vo['vod_play_from']|str_replace='$$$',',',###); ?>-<?php echo e($vo['vod_down_from']|str_replace='$$$',',',###); ?></span></td>
                <td><?php echo e($vo.vod_time|mac_day='color'); ?></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info?id='.$vo['vod_id'])); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['vod_id'])); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
                    <a class="layui-badge-rim j-iframe" data-full="1"  data-href="<?php echo e(url('role/data?select=1&tab=vod&rid='.$vo['vod_id'])); ?>" href="javascript:;" title="<?php echo e(lang('role')); ?>"><?php echo e(lang('role')); ?></a>
                    <a class="layui-badge-rim j-iframe" data-full="1"  data-href="<?php echo e(url('iplot?id='.$vo['vod_id'])); ?>" href="javascript:;" title="<?php echo e(lang('plot')); ?>"><?php echo e(lang('plot')); ?></a>
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
    var curUrl="<?php echo e(url('vod/data',$param)); ?>";
    layui.use(['laypage', 'layer','form'], function() {
        var laypage = layui.laypage
                , layer = layui.layer,
                form = layui.form;
                $ = layui.jquery;

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

        // 小屏幕下名称问题
        if ($('body').width() <= 600) {
            $('#table_th_vod_name').attr('width', '300');
        }

    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\vod\index.blade.php ENDPATH**/ ?>