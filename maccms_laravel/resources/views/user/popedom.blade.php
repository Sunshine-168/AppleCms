@extends('user.layout')

@section('title', '权限查看')

@section('user_content')
<div class="card">
    <div class="card-header">权限查看</div>
    <div class="card-body">
        @forelse($tree as $node)
            <div class="mb-4">
                <h5>{{ $node['type']->type_name }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>分类</th>
                                @foreach($node['popedom'] as $label => $allowed)
                                    <th>{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $node['type']->type_name }}</td>
                                @foreach($node['popedom'] as $allowed)
                                    <td>{{ $allowed ? '是' : '否' }}</td>
                                @endforeach
                            </tr>
                            @foreach($node['children'] as $child)
                                <tr>
                                    <td>{{ $child['type']->type_name }}</td>
                                    @foreach($child['popedom'] as $allowed)
                                        <td>{{ $allowed ? '是' : '否' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p>暂无权限数据。</p>
        @endforelse
    </div>
</div>
@endsection
