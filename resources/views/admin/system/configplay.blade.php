@include('admin.public.head')
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.system.configplay') }}">
        @csrf
        <div class="page-tip-blue">
            @php
                $tip = (string) __('admin.admin/system/configplay/tip');
                $tip = preg_replace('/<br>\s+/u', '<br>', $tip) ?? $tip;
            @endphp
            {!! $tip !!}
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/width') }}：</label>
            <div class="layui-input-inline w150">
                <input type="text" name="play[width]" placeholder="{{ __('admin.admin/system/configplay/width_tip') }}" value="{{ data_get($play, 'width', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/height') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[height]" placeholder="{{ __('admin.admin/system/configplay/height_tip') }}" value="{{ data_get($play, 'height', '') }}" class="layui-input w150">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/widthmob') }}：</label>
            <div class="layui-input-inline w150">
                <input type="text" name="play[widthmob]" placeholder="{{ __('admin.admin/system/configplay/width_tip') }}" value="{{ data_get($play, 'widthmob', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/heightmob') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[heightmob]" placeholder="{{ __('admin.admin/system/configplay/height_tip') }}" value="{{ data_get($play, 'heightmob', '') }}" class="layui-input w150">
            </div>
        </div>
        <div class="layui-form-item" style="display:none;">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/widthpop') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[widthpop]" placeholder="{{ __('admin.admin/system/configplay/width_tip') }}" value="{{ data_get($play, 'widthpop', '') }}" class="layui-input w150">
            </div>
        </div>
        <div class="layui-form-item" style="display:none;">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/heightpop') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[heightpop]" placeholder="{{ __('admin.admin/system/configplay/height_tip') }}" value="{{ data_get($play, 'heightpop', '') }}" class="layui-input w150">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/second') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[second]" placeholder="{{ __('admin.admin/system/configplay/second_tip') }}" value="{{ data_get($play, 'second', '') }}" class="layui-input w150">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/prestrain') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[prestrain]" placeholder="{{ __('admin.admin/system/configplay/prestrain_tip') }}" value="{{ data_get($play, 'prestrain', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/buffer') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[buffer]" placeholder="{{ __('admin.admin/system/configplay/buffer_tip') }}" value="{{ data_get($play, 'buffer', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/parse') }}：</label>
            <div class="layui-input-block">
                <input type="text" name="play[parse]" placeholder="{{ __('admin.admin/system/configplay/parse_tip') }}" value="{{ data_get($play, 'parse', '') }}" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/autofull') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="play[autofull]" value="0" title="{{ __('admin.close') }}" @checked((string) data_get($play, 'autofull', '0') !== '1')>
                <input type="radio" name="play[autofull]" value="1" title="{{ __('admin.open') }}" @checked((string) data_get($play, 'autofull', '0') === '1')>
            </div>
        </div>
        <div class="layui-form-item" style="display:none;">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/showtop') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="play[showtop]" value="0" title="{{ __('admin.close') }}" @checked((string) data_get($play, 'showtop', '0') !== '1')>
                <input type="radio" name="play[showtop]" value="1" title="{{ __('admin.open') }}" @checked((string) data_get($play, 'showtop', '0') === '1')>
            </div>
        </div>
        <div class="layui-form-item" style="display:none;">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/showlist') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="play[showlist]" value="0" title="{{ __('admin.close') }}" @checked((string) data_get($play, 'showlist', '0') !== '1')>
                <input type="radio" name="play[showlist]" value="1" title="{{ __('admin.open') }}" @checked((string) data_get($play, 'showlist', '0') === '1')>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/flag') }}：</label>
            <div class="layui-input-block">
                <input type="radio" name="play[flag]" value="0" title="{{ __('admin.admin/system/configplay/flag_tip') }}" checked>
            </div>
        </div>
        <div class="layui-form-item" style="display:none;">
            <label class="layui-form-label">{{ __('admin.admin/system/configplay/colors') }}：</label>
            <div class="layui-input-block">
                <input type="text" id="mac_colors" name="play[colors]" value="{{ data_get($play, 'colors', '') }}" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                <button type="button" class="layui-btn layui-btn-normal" id="btnDef">{{ __('admin/database/import') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript">
    function setColor(v) {
        switch (v) {
            case 2:
                v = "EFF4F7,000000,666666,E4E4E4,000000,FF0000,FF0000,DBEBFE,458CE4,DBEBFE,FFFFFF,458CE4,DBEBFE,DBEBFE,fcfcfc";
                break;
            case 3:
                v = "D8CFDF,000000,666666,E4E4E4,000000,FF0000,FF0000,D8CFDF,926C92,BEAFC9,FFFFFF,926C92,BEAFC9,BEAFC9,fcfcfc";
                break;
            case 4:
                v = "D7E7B6,000000,666666,E4E4E4,000000,FF0000,FF0000,9EC14C,A3C656,BAD480,FFFFFF,A3C656,BAD480,BAD480,fcfcfc";
                break;
            default:
                v = "000000,F6F6F6,F6F6F6,333333,666666,FFFFF,FF0000,2c2c2c,ffffff,a3a3a3,2c2c2c,adadad,adadad,48486c,fcfcfc";
                break;
        }
        $("#mac_colors").val(v);
    }

    layui.use(['form', 'layer'], function(){
        var form = layui.form, layer = layui.layer;
        $('#btnDef').click(function(){
            $('input[name="play[width]"]').val('100%');
            $('input[name="play[height]"]').val('100%');
            $('input[name="play[widthmob]"]').val('100%');
            $('input[name="play[heightmob]"]').val('100%');
            $('input[name="play[widthpop]"]').val('600');
            $('input[name="play[heightpop]"]').val('500');
            $('input[name="play[second]"]').val('5');
            $('input[name="play[prestrain]"]').val('//union.maccms.la/html/prestrain.html');
            $('input[name="play[buffer]"]').val('//union.maccms.la/html/loading.html');
            $('input[name="play[parse]"]').val('');

        });
    });
</script>
