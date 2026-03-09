@include('../../../application/admin/view/public/head')
<div class="page-container p10">


  <form class="layui-form layui-form-pane" method="post" action="{{ url('comment/blacklist') }}">
    <div class="layui-form-item">
      <label class="layui-form-label">{{ __('admin.blacklist_keywords') }}</label>
      <div class="layui-input-block">
        <textarea style="height: 500px" name="keywords" placeholder="{{ __('admin.index/blacklist_placeholder') }}" class="layui-textarea" >{{ $black_keyword_list }}</textarea>
      </div>
    </div>
    <div class="layui-form-item center">
      <div class="layui-input-block">
        <button type="submit" class="layui-btn" lay-submit>{{ __('admin.save') }}</button>
        <button type="reset" class="layui-btn layui-btn-primary">{{ __('admin.btn_reset') }}</button>
      </div>
    </div>
  </form>
</div>


@include('../../../application/admin/view/public/foot')


</body>
</html>