<form class="layui-form m10" method="post" action="{{ $url }}">
    <input type="hidden" name="col" value="{{ $col }}">
    <input type="hidden" name="ids" value="{{ $ids }}">

    <div class="layui-input-inline w150">
        <select name="val">
            <option value="">{{ __('admin.select_type') }}</option>
            @foreach($type_tree as $vo)
            @if($vo.type_mid == $mid)
            <option value="{{ $vo.type_id }}" >{{ $vo.type_name }}</option>
            @foreach($vo.child as $ch)
            <option value="{{ $ch.type_id }}" @if($ch.type_id == $val)selected@endif>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;{{ $ch.type_name }}</option>
            @endforeach
            @endif
            @endforeach
        </select>
    </div>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit="" refresh="{{ $refresh }}" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
    </div>
</form>

