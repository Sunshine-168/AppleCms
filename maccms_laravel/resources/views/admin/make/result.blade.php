@include('admin.public.head')

<div class="page-container p10">
    <div class="layui-card">
        <div class="layui-card-header">{{ $title }}</div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">
                共处理 {{ count($results) }} 个文件，成功 {{ $successCount }} 个，失败 {{ $failedCount }} 个。
            </blockquote>

            <table class="layui-table" lay-size="sm">
                <thead>
                <tr>
                    <th width="90">状态</th>
                    <th>来源</th>
                    <th>目标文件</th>
                    <th>结果</th>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $result)
                    <tr>
                        <td>
                            @if($result['ok'])
                                <span style="color:#16b777;">成功</span>
                            @else
                                <span style="color:#ff5722;">失败</span>
                            @endif
                        </td>
                        <td>{{ $result['source'] ?: '-' }}</td>
                        <td>{{ $result['target'] ?: '-' }}</td>
                        <td>{{ $result['message'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <a href="{{ $backUrl }}" class="layui-btn">返回生成管理</a>
        </div>
    </div>
</div>

@include('admin.public.foot')
