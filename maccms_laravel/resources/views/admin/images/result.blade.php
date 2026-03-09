@include('admin.public.head')

<div class="page-container p10">
    <div class="layui-card">
        <div class="layui-card-header">图片同步结果</div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">
                当前模型：{{ $tab }}，处理页码：{{ $page }}，每页 {{ $limit }} 条，成功 {{ $successCount }} 张，失败 {{ $failedCount }} 张。
            </blockquote>

            <table class="layui-table" lay-size="sm">
                <thead>
                <tr>
                    <th width="80">ID</th>
                    <th>名称</th>
                    <th width="90">处理数</th>
                    <th width="90">成功</th>
                    <th width="90">失败</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $result)
                    <tr>
                        <td>{{ $result['id'] }}</td>
                        <td>{{ $result['name'] }}</td>
                        <td>{{ $result['processed'] }}</td>
                        <td>{{ $result['success'] }}</td>
                        <td>{{ $result['failed'] }}</td>
                        <td>{{ $result['message'] ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <a href="{{ route('admin.images.opt', ['tab' => $tab]) }}" class="layui-btn">返回图片同步</a>
        </div>
    </div>
</div>

@include('admin.public.foot')
