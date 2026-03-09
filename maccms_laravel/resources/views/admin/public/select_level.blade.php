<form class="layui-form m10" method="post" action="{{ $url }}">
    <input type="hidden" name="col" value="{{ $col }}">
    <input type="hidden" name="ids" value="{{ $ids }}">

    <div class="layui-input-inline w150">
        <select name="val">
            <option value="">{{ __('admin.select_level') }}</option>
            <option value="0">{{ __('admin.cancel_level') }}</option>
            @foreach($level_list as $vo)
            <option value="{{ $vo }}">{{ __('admin.level') }}{{ $vo }}</option>
            @endforeach
        </select>
    </div>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
    </div>
</form>

