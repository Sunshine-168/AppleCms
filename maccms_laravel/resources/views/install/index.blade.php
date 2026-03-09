@include('install.head')
<div class="install-box">
    <div class="protocol-box">
        <div class="title">{{ __('install.user_agreement_title') }}</div>
        <div class="protocol">
            <p>
                {{ __('install.user_agreement') }}
            </p>
        </div>
    </div>
    <form class="layui-form layui-form-pane" action="" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('install.lang') }}</label>
            <div class="layui-input-inline w200 ">
                <select class="" name="lang" lay-filter="lang" style="z-index:99999;">
                    <option value="">{{ __('install.select_lang') }}</option>
                    @foreach($langs as $key => $name)
                    <option value="{{ $key }}" @if($lang == $key)selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="layui-form-mid layui-word-aux">{{ __('install.lang_tip') }}</div>
        </div>
    </form>
    <div class="step-btns">
        <a href="{{ route('install.step2') }}" class="layui-btn layui-btn-big layui-btn-normal">{{ __('install.user_agreement_agree') }}</a>
    </div>
</div>
@include('install.foot')
<script type="text/javascript">
    var test=0;
    layui.define(['element', 'form'], function(exports) {
        var $ = layui.jquery, layer = layui.layer, form = layui.form;
        form.on('select(lang)',function(data){
            if(data.value !='') {
                location.href = "{{ route('install.index') }}?lang=" + (data.value);
            }
        });
    });
</script>
