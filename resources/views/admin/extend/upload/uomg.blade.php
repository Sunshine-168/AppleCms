<div class="layui-form-item upload_mode mode_Uomg" @if(condition="$config['upload']['mode'] != 'Uomg'")style="display:none;" @endif>
<label class="layui-form-label">优启梦图床：</label>
<div class="layui-input-block">
    <a href="http://api.uomg.com/" target="_blank" class="layui-btn layui-btn-primary">目前免费不用申请 http://api.uomg.com/</a>
</div>
</div>
<div class="layui-form-item upload_mode mode_Uomg" @if(condition="$config['upload']['mode'] != 'Uomg'")style="display:none;" @endif>
<label class="layui-form-label">OPENID：</label>
<div class="layui-input-inline w200">
    <input type="text" name="upload[api][uomg][openid]" placeholder="" value="{{ $config['upload']['api']['uomg']['openid'] }}" class="layui-input"  >
</div>
<label class="layui-form-label">操作员名：</label>
<div class="layui-input-inline w200">
    <input type="text" name="upload[api][uomg][key]" placeholder="" value="{{ $config['upload']['api']['uomg']['key'] }}" class="layui-input"  >
</div>
<label class="layui-form-label">类型：</label>
<div class="layui-input-inline w200">
    <select class="w150" name="upload[api][uomg][type]">
        <option value="ali" @if(condition="$config['upload']['api']['uomg']['type'] == 'ali'")selected @endif>阿里巴巴</option>
        <option value="baidu" @if(condition="$config['upload']['api']['uomg']['type'] == 'baidu'")selected @endif>百度识图</option>
        <option value="sina" @if(condition="$config['upload']['api']['uomg']['type'] == 'sina'")selected @endif>新浪微博</option>
        <option value="sogou" @if(condition="$config['upload']['api']['uomg']['type'] == 'sogou'")selected @endif>搜狗图片</option>
        <option value="juejin" @if(condition="$config['upload']['api']['uomg']['type'] == 'juejin'")selected @endif>掘金社区</option>
        <option value="360" @if(condition="$config['upload']['api']['uomg']['type'] == '360'")selected @endif>奇虎360</option>
        <option value="jd" @if(condition="$config['upload']['api']['uomg']['type'] == 'jd'")selected @endif>京东商城</option>
    </select>
</div>
</div>