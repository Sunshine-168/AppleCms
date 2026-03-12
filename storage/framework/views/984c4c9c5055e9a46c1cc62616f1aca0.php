<div class="layui-tab-item">

    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
        <legend>微信设置 <a target="_blank" href="https://pay.weixin.qq.com/?maccms" class="layui-btn layui-btn-primary">点击进入注册</a></legend>
    </fieldset>

    <div class="layui-form-item">
        <label class="layui-form-label">APPID：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[weixin][appid]" placeholder="" value="<?php echo e($config['pay']['weixin']['appid']); ?>" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">商户号：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[weixin][mchid]" placeholder="" value="<?php echo e($config['pay']['weixin']['mchid']); ?>" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">商户密钥：</label>
        <div class="layui-input-inline w400">
            <input type="text" name="pay[weixin][appkey]" placeholder="" value="<?php echo e($config['pay']['weixin']['appkey']); ?>" class="layui-input">
        </div>
    </div>

</div><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\extend\pay\weixin.blade.php ENDPATH**/ ?>