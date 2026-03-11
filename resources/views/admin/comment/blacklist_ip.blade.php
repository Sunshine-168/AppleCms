@include('../../../application/admin/view/public/head')
<div class="page-container p10">


  <form class="layui-form layui-form-pane" method="post" action="{{ url('comment/blacklist_ip') }}">
    <div class="layui-form-item">
      <label class="layui-form-label">{{ __('admin.blacklist_ip') }}</label>
      <div class="layui-input-block">
        <textarea style="height: 500px" name="ip" placeholder="{{ __('admin.index/blacklist_placeholder_ip') }}" class="layui-textarea" >{{ $black_ip_list }}</textarea>
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