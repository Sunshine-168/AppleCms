<form class="layui-form m10" method="post" action="{{ $url }}">
    <input type="hidden" name="col" value="{{ $col }}">
    <input type="hidden" name="ids" value="{{ $ids }}">

    <div class="layui-input-inline w150">
        <select name="val">
            <option value="">{{ __('admin.select_opt') }}</option>
            <option value="0" >{{ __('admin.unlock') }}</option>
            <option value="1" >{{ __('admin.lock') }}</option>
        </select>
    </div>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit="" refresh="{{ $refresh }}" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
    </div>
</form>

