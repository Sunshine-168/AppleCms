@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>Article Management</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('admin.art.info') }}" class="btn btn-primary">Add Article</a>
        </div>
        <div class="col-md-6 text-end">
            <form action="" method="GET" class="d-inline-flex">
                <select name="type" class="form-select me-2" style="width: 150px;">
                    <option value="">All Types</option>
                    @foreach($type_tree as $type)
                        <option value="{{ $type->type_id }}" {{ request('type') == $type->type_id ? 'selected' : '' }}>
                            {{ $type->type_name }}
                        </option>
                    @endforeach
                </select>
                <input type="text" name="wd" class="form-control me-2" placeholder="Search name..." value="{{ request('wd') }}">
                <button type="submit" class="btn btn-secondary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($list as $art)
                    <tr>
                        <td>{{ $art->art_id }}</td>
                        <td>{{ $art->type ? $art->type->type_name : 'Unknown' }}</td>
                        <td>
                            @if($art->art_pic)
                                <i class="bi bi-image text-success" title="Has Image"></i>
                            @endif
                            <a href="{{ route('admin.art.info', $art->art_id) }}">{{ $art->art_name }}</a>
                        </td>
                        <td>
                            @if($art->art_status == 1)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                            @if($art->art_lock == 1)
                                <span class="badge bg-warning text-dark">Locked</span>
                            @endif
                        </td>
                        <td>{{ date('Y-m-d H:i:s', $art->art_time) }}</td>
                        <td>
                            <a href="{{ route('admin.art.info', $art->art_id) }}" class="btn btn-sm btn-info">Edit</a>
                            <button onclick="delArt({{ $art->art_id }})" class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $list->links() }}
        </div>
    </div>
</div>

<script>
function delArt(id) {
    if(confirm('Are you sure you want to delete this article?')) {
        fetch('{{ route("admin.art.del") }}?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
@endsection
