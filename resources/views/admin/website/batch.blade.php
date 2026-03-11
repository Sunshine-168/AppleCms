@include('../../../application/admin/view/public/head')
<div class="page-container p10">

    <form class="layui-form" method="post" action="">

        <div class="my-toolbar-box">

            <div class="center mb10">

                    <div class="layui-input-inline w150">
                        <select name="type">
                            <option value="">{{ lang('select_type') }}</option>
                            @foreach($type_tree as $vo)
                            @if($vo.type_mid eq 11)
                            <option value="{{ $vo.type_id }}" @if(condition="$param['type'] eq $vo.type_id")selected @endif>{{ $vo.type_name }}</option>
                            @foreach($vo.child as $ch)
                            <option value="{{ $ch.type_id }}" @if(condition="$param['type'] eq $ch.type_id")selected @endif>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;{{ $ch.type_name }}</option>
                            @endforeach
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="status">
                            <option value="">{{ lang('select_status') }}</option>
                            <option value="0" @if(condition="$param['status'] eq '0'")selected @endif>{{ lang('reviewed_not') }}</option>
                            <option value="1" @if(condition="$param['status'] eq '1'")selected @endif>{{ lang('reviewed') }}</option>
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="level">
                            <option value="">{{ lang('select_level') }}</option>
                            <option value="9" @if(condition="$param['level'] eq '9'")selected @endif>{{ lang('level') }}9-{{ lang('slide') }}</option>
                            <option value="1" @if(condition="$param['level'] eq '1'")selected @endif>{{ lang('level') }}1</option>
                            <option value="2" @if(condition="$param['level'] eq '2'")selected @endif>{{ lang('level') }}2</option>
                            <option value="3" @if(condition="$param['level'] eq '3'")selected @endif>{{ lang('level') }}3</option>
                            <option value="4" @if(condition="$param['level'] eq '4'")selected @endif>{{ lang('level') }}4</option>
                            <option value="5" @if(condition="$param['level'] eq '5'")selected @endif>{{ lang('level') }}5</option>
                            <option value="6" @if(condition="$param['level'] eq '6'")selected @endif>{{ lang('level') }}6</option>
                            <option value="7" @if(condition="$param['level'] eq '7'")selected @endif>{{ lang('level') }}7</option>
                            <option value="8" @if(condition="$param['level'] eq '8'")selected @endif>{{ lang('level') }}8</option>
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="lock">
                            <option value="">{{ lang('select_lock') }}</option>
                            <option value="0" @if(condition="$param['lock'] eq '0'")selected @endif>{{ lang('unlock') }}</option>
                            <option value="1" @if(condition="$param['lock'] eq '1'")selected @endif>{{ lang('lock') }}</option>
                        </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="pic">
                            <option value="">{{ lang('select_pic') }}</option>
                            <option value="1" @if(condition="$param['pic'] eq '1'")selected@endif>{{ lang('pic_empty') }}</option>
                            <option value="2" @if(condition="$param['pic'] eq '2'")selected@endif>{{ lang('pic_remote') }}</option>
                            <option value="3" @if(condition="$param['pic'] eq '3'")selected@endif>{{ lang('pic_sync_err') }}</option>
                        </select>
                    </div>

                    <div class="layui-input-inline">
                        <input type="text" autocomplete="off" placeholder="{{ lang('wd') }}" class="layui-input" name="wd" value="{{ $param['wd']|mac_filter_xss }}">
                    </div>

            </div>

        </div>

        <fieldset class="layui-elem-field">
            <legend>{{ lang('del_multi') }}</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_del">{{ lang('del_data') }}</label>
                        <div class="layui-input-inline" style="width: 100px;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <button type="button" class="layui-btn btn_submit">{{ lang('del_multi') }}</button>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
        <legend>{{ lang('multi_set') }}</legend>
        <div class="layui-field-box">

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_level" title="{{ lang('level') }}">{{ lang('level') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_level">
                            <option value="">{{ lang('select_level') }}</option>
                            <option value="9" >{{ lang('level') }}9-{{ lang('slide') }}</option>
                            <option value="1" >{{ lang('level') }}1</option>
                            <option value="2" >{{ lang('level') }}2</option>
                            <option value="3" >{{ lang('level') }}3</option>
                            <option value="4" >{{ lang('level') }}4</option>
                            <option value="5" >{{ lang('level') }}5</option>
                            <option value="6" >{{ lang('level') }}6</option>
                            <option value="7" >{{ lang('level') }}7</option>
                            <option value="8" >{{ lang('level') }}8</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_lock">{{ lang('lock') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_lock">
                            <option value="">{{ lang('select_opt') }}</option>
                            <option value="0" >{{ lang('unlock') }}</option>
                            <option value="1" >{{ lang('lock') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_status">{{ lang('status') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_status">
                            <option value="">{{ lang('select_status') }}</option>
                            <option value="0" >{{ lang('reviewed_not') }}</option>
                            <option value="1" >{{ lang('reviewed') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_hits">{{ lang('hits') }}</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_min" required  placeholder="{{ lang('min_val') }}" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_max" required  placeholder="{{ lang('max_val') }}" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <button type="submit" class="layui-btn btn_submit">{{ lang('start_exec') }}</button>
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