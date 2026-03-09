@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <form class="layui-form" method="post" action="">

        <div class="my-toolbar-box">

            <div class="center mb10">

                    <div class="layui-input-inline w150">
                        <select name="type">
                            <option value="">{{ __('admin.select_type') }}</option>
                            @foreach($type_tree as $vo)
                            @if($vo.type_mid == 2)
                            <option value="{{ $vo.type_id }}" @if(condition="$param['type'] == $vo.type_id")selected @endif>{{ $vo.type_name }}</option>
                            @foreach($vo.child as $ch)
                            <option value="{{ $ch.type_id }}" @if(condition="$param['type'] == $ch.type_id")selected @endif>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;{{ $ch.type_name }}</option>
                            @endforeach
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="status">
                            <option value="">{{ __('admin.select_status') }}</option>
                            <option value="0" @if(condition="$param['status'] == '0'")selected @endif>{{ __('admin.reviewed_not') }}</option>
                            <option value="1" @if(condition="$param['status'] == '1'")selected @endif>{{ __('admin.reviewed') }}</option>
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="level">
                            <option value="">{{ __('admin.select_level') }}</option>
                            <option value="9" @if(condition="$param['level'] == '9'")selected @endif>{{ __('admin.level') }}9-{{ __('admin.slide') }}</option>
                            <option value="1" @if(condition="$param['level'] == '1'")selected @endif>{{ __('admin.level') }}1</option>
                            <option value="2" @if(condition="$param['level'] == '2'")selected @endif>{{ __('admin.level') }}2</option>
                            <option value="3" @if(condition="$param['level'] == '3'")selected @endif>{{ __('admin.level') }}3</option>
                            <option value="4" @if(condition="$param['level'] == '4'")selected @endif>{{ __('admin.level') }}4</option>
                            <option value="5" @if(condition="$param['level'] == '5'")selected @endif>{{ __('admin.level') }}5</option>
                            <option value="6" @if(condition="$param['level'] == '6'")selected @endif>{{ __('admin.level') }}6</option>
                            <option value="7" @if(condition="$param['level'] == '7'")selected @endif>{{ __('admin.level') }}7</option>
                            <option value="8" @if(condition="$param['level'] == '8'")selected @endif>{{ __('admin.level') }}8</option>
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="lock">
                            <option value="">{{ __('admin.select_lock') }}</option>
                            <option value="0" @if(condition="$param['lock'] == '0'")selected @endif>{{ __('admin.unlock') }}</option>
                            <option value="1" @if(condition="$param['lock'] == '1'")selected @endif>{{ __('admin.lock') }}</option>
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="pic">
                            <option value="">{{ __('admin.select_pic') }}</option>
                            <option value="1" @if(condition="$param['pic'] == '1'")selected@endif>{{ __('admin.pic_empty') }}</option>
                            <option value="2" @if(condition="$param['pic'] == '2'")selected@endif>{{ __('admin.pic_remote') }}</option>
                            <option value="3" @if(condition="$param['pic'] == '3'")selected@endif>{{ __('admin.pic_sync_err') }}</option>
                        </select>
                    </div>

                    <div class="layui-input-inline">
                        <input type="text" autocomplete="off" placeholder="{{ __('admin.wd') }}" class="layui-input" name="wd" value="{{ $param['wd'] }}">
                    </div>

            </div>

        </div>

        <fieldset class="layui-elem-field">
            <legend>{{ __('admin.del_multi') }}</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_del">{{ __('admin.del_data') }}</label>
                        <div class="layui-input-inline" style="width: 100px;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <button type="button" class="layui-btn btn_submit">{{ __('admin.del_multi') }}</button>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
        <legend>{{ __('admin.multi_set') }}</legend>
        <div class="layui-field-box">

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_level" title="{{ __('admin.level') }}">{{ __('admin.level') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_level">
                            <option value="">{{ __('admin.select_level') }}</option>
                            <option value="9" >{{ __('admin.level') }}9-{{ __('admin.slide') }}</option>
                            <option value="1" >{{ __('admin.level') }}1</option>
                            <option value="2" >{{ __('admin.level') }}2</option>
                            <option value="3" >{{ __('admin.level') }}3</option>
                            <option value="4" >{{ __('admin.level') }}4</option>
                            <option value="5" >{{ __('admin.level') }}5</option>
                            <option value="6" >{{ __('admin.level') }}6</option>
                            <option value="7" >{{ __('admin.level') }}7</option>
                            <option value="8" >{{ __('admin.level') }}8</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_lock">{{ __('admin.lock') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_lock">
                            <option value="">{{ __('admin.select_opt') }}</option>
                            <option value="0" >{{ __('admin.unlock') }}</option>
                            <option value="1" >{{ __('admin.lock') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_status">{{ __('admin.status') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_status">
                            <option value="">{{ __('admin.select_status') }}</option>
                            <option value="0" >{{ __('admin.reviewed') }}</option>
                            <option value="1" >{{ __('admin.reviewed') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_hits">{{ __('admin.hits') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_min" required  placeholder="{{ __('admin.min_val') }}" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_max" required  placeholder="{{ __('admin.max_val') }}" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">{{ __('admin.page_limit') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="limit" required  placeholder="" autocomplete="off" value="100" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <button type="submit" class="layui-btn btn_submit">{{ __('admin.start_exec') }}</button>
            </div>

        </div>
    </fieldset>
    </form>
</div>

<script type="text/javascript">
    layui.use(['form'], function () {

    });

    $('.btn_submit').click(function(){
        $('form').submit();
    })
</script>
</body>
</html>