@include('admin.public.head')
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.system.configemail') }}">
        @csrf
        <blockquote class="layui-elem-quote layui-quote-nm">
            {{ __('admin.admin/system/configemail/tip') }}
        </blockquote>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/type') }}：</label>
            <div class="layui-input-inline">
                <select class="w150" id="ac" name="email[type]">
                    <option value="phpmailer" selected>Phpmailer</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/time') }}：</label>
            <div class="layui-input-inline">
                <input type="text" name="email[time]" value="{{ data_get($config, 'email.time', '') }}" class="layui-input w200">
            </div>
            <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configemail/time_tip') }}</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/nick') }}：</label>
            <div class="layui-input-inline">
                <input type="text" id="nick" name="email[nick]" value="{{ data_get($config, 'email.nick', '') }}" class="layui-input w200">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/test') }}：</label>
            <div class="layui-input-inline">
                <input type="text" id="test" name="email[test]" value="{{ data_get($config, 'email.test', '') }}" class="layui-input w200">
            </div>
            <button type="button" class="layui-btn layui-btn-normal" onclick="test_email()">{{ __('admin.admin/system/configemail/btn_test') }}</button>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">SMTP Host：</label>
            <div class="layui-input-inline">
                <input type="text" name="email[phpmailer][host]" value="{{ data_get($config, 'email.phpmailer.host', '') }}" class="layui-input w200">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Port：</label>
            <div class="layui-input-inline">
                <input type="text" name="email[phpmailer][port]" value="{{ data_get($config, 'email.phpmailer.port', '') }}" class="layui-input w200">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Secure：</label>
            <div class="layui-input-inline w200">
                <select name="email[phpmailer][secure]">
                    <option value="" @selected(data_get($config, 'email.phpmailer.secure', '') === '')>默认</option>
                    <option value="tsl" @selected(data_get($config, 'email.phpmailer.secure', '') === 'tsl')>TSL</option>
                    <option value="ssl" @selected(data_get($config, 'email.phpmailer.secure', '') === 'ssl')>SSL</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Username：</label>
            <div class="layui-input-inline">
                <input type="text" name="email[phpmailer][username]" value="{{ data_get($config, 'email.phpmailer.username', '') }}" class="layui-input w200">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Password：</label>
            <div class="layui-input-inline">
                <input type="password" name="email[phpmailer][password]" value="{{ data_get($config, 'email.phpmailer.password', '') }}" class="layui-input w200">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/test_title') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="email[tpl][test_title]" value="{{ data_get($config, 'email.tpl.test_title', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/test_body') }}：</label>
            <div class="layui-input-block">
                <textarea name="email[tpl][test_body]" class="layui-textarea">{{ data_get($config, 'email.tpl.test_body', '') }}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/user_reg_title') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="email[tpl][user_reg_title]" value="{{ data_get($config, 'email.tpl.user_reg_title', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/user_reg_body') }}：</label>
            <div class="layui-input-block">
                <textarea name="email[tpl][user_reg_body]" class="layui-textarea">{{ data_get($config, 'email.tpl.user_reg_body', '') }}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/user_bind_title') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="email[tpl][user_bind_title]" value="{{ data_get($config, 'email.tpl.user_bind_title', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/user_bind_body') }}：</label>
            <div class="layui-input-block">
                <textarea name="email[tpl][user_bind_body]" class="layui-textarea">{{ data_get($config, 'email.tpl.user_bind_body', '') }}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/user_findpass_title') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="email[tpl][user_findpass_title]" value="{{ data_get($config, 'email.tpl.user_findpass_title', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configemail/user_findpass_body') }}：</label>
            <div class="layui-input-block">
                <textarea name="email[tpl][user_findpass_body]" class="layui-textarea">{{ data_get($config, 'email.tpl.user_findpass_body', '') }}</textarea>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript">
    layui.use(['layer'], function () {
        window.layer = layui.layer;
    });

    function test_email() {
        var type = $('#ac').val();
        var test = $('#test').val();
        var nick = $('#nick').val();

        layer.msg("{{ __('admin.wait_submit') }}", {time: 500000});
        $.ajax({
            url: @json(route('admin.system.test_email')),
            type: 'post',
            dataType: 'json',
            data: {type: type, nick: nick, test: test, _token: @json(csrf_token())},
            error: function() {
                layer.msg("{{ __('admin.admin/system/configemail/test_err') }}", {time: 1800});
            },
            success: function (r) {
                layer.msg(r.msg, {time: 1800});
            }
        });
    }
</script>