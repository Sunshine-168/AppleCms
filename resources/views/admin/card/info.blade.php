@include('admin.public.head')
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.card.info', ['id' => $info->card_id ?: null]) }}">
        @csrf
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/card/make_num') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ old('num', 10) }}" lay-verify="num" name="num">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.money') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ old('money', '') }}" lay-verify="money" name="money">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.points') }}：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="{{ old('point', '') }}" lay-verify="point" name="point">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.card_no') }}{{ __('admin.rule') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="role_no" value="" title="{{ __('admin.mixing') }}" @checked(old('role_no', '') === '')>
                <input type="radio" name="role_no" value="letter" title="{{ __('admin.abc') }}" @checked(old('role_no') === 'letter')>
                <input type="radio" name="role_no" value="num" title="{{ __('admin.number') }}" @checked(old('role_no') === 'num')>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.pass') }}{{ __('admin.rule') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="role_pwd" value="" title="{{ __('admin.mixing') }}" @checked(old('role_pwd', '') === '')>
                <input type="radio" name="role_pwd" value="letter" title="{{ __('admin.abc') }}" @checked(old('role_pwd') === 'letter')>
                <input type="radio" name="role_pwd" value="num" title="{{ __('admin.number') }}" @checked(old('role_pwd') === 'num')>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>
@include('admin.public.foot')

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        var form = layui.form, layer = layui.layer, $ = layui.jquery;

        form.verify({
            num: function (value) {
                if (value === "") {
                    return "{{ __('admin.admin/card/please_input_make_num') }}";
                }
            },
            money: function (value) {
                if (value === "") {
                    return "{{ __('admin.admin/card/please_input_money') }}";
                }
            },
            point: function (value) {
                if (value === "") {
                    return "{{ __('admin.admin/card/please_input_points') }}";
                }
            }
        });
    });
</script>