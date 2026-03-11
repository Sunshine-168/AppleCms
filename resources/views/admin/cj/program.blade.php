@include('../../../application/admin/view/public/head')
<div class="page-container p10">
    <form class="layui-form layui-form-pane" action="">
        <input type="hidden" name="id" value="{{ $param.id }}">
        <fieldset class="layui-elem-field">
            <legend>{{ __('admin.admin/cj/label_data_rel') }}</legend>
        </fieldset>

                    <table class="layui-table" lay-size="sm" style="width:600px;">
                        <thead>
                        <tr>
                            <th width="100">{{ __('admin.admin/cj/data_column') }}</th>
                            <th width="100">{{ __('admin.admin/cj/label_column') }}</th>
                            <th width="100">{{ __('admin.admin/cj/processing_function') }}</th>
                        </tr>
                        </thead>

                        @foreach($column_list as $vo)
                        <tr>
                            <td><input type="hidden" name="model_field[]" value="{{ $vo.Field }}">{{ $vo.Field }}</td>
                            <td><select name="node_field[]">
                                <option value="">{{ __('admin.select_please') }}</option>
                                @foreach($node_field as $k => $vo2)
                                <option value="{{ $key }}" @if(condition="$program_config['map'][$vo.Field] == $key")selected@endif>{{ $vo2 }}</option>
                                @endforeach
                            </select>
                            </td>
                            <td><select name="funcs[]"><option value="" >{{ __('admin.select_please') }}</option><option value="trim" @if(condition="$program_config['funcs'][$vo.Field] == 'trim'")selected@endif>{{ __('admin.admin/cj/trim_space') }}</option></select></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>

@include('../../../application/admin/view/public/foot')
<script type="text/javascript">

</script>

</body>
</html>