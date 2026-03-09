@include('admin.public.head')
<div class="page-container p10">

    <fieldset class="layui-elem-field">
        <legend>{{ __('admin.base_info') }}</legend>
        <div class="layui-field-box">
            {{ $url_list }} <br>{{ __('admin.sum') }}：{{ $total }} {{ __('admin.data') }}，{{ __('admin.duplicate_data') }}：{{ $re }}{{ __('admin.data') }}，{{ __('admin.distinct_into') }}{{ $total-$re }}{{ __('admin.data') }}。
        </div>
    </fieldset>

    <table class="layui-table" lay-size="sm">
    <thead>
      <tr>
        <th width="50">{{ __('admin.serial_num') }}</th>
		<th>{{ __('admin.link') }}</th>
        <th>{{ __('admin.name') }}</th>
      </tr> 
    </thead>
    <tbody>
        @foreach($url as $index => $v)
          <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $v['url'] ?? '' }}</td>
              <td>{{ $v['title'] ?? '' }}</td>
          </tr>
        @endforeach
    </tbody>
  </table>
</div>
<script>
var total_page = {{ $total_page }};
var page = {{ $param['page'] }};
var id = {{ $param['id'] }};
if (total_page > page) {
    var url = "{{ route('admin.cj.col_url', ['id' => $param['id']]) }}";
	page += 1;
    //location.href= url + '?page='+page+'&id='+id;
} else {
	//alert('采集完成');
}
</script>
@include('admin.public.foot')