@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Actor Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.actor.info') }}" class="btn btn-primary">Add Actor</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-auto">
                    <select name="type" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($type_tree as $type)
                            <option value="{{ $type->type_id }}" {{ request('type') == $type->type_id ? 'selected' : '' }}>
                                {{ $type->type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" name="wd" class="form-control" placeholder="Search name..." value="{{ request('wd') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </div>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Sex</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($list as $actor)
                    <tr>
                        <td>{{ $actor->actor_id }}</td>
                        <td>{{ $actor->actor_name }}</td>
                        <td>{{ $actor->actor_sex }}</td>
                        <td>{{ $actor->type ? $actor->type->type_name : '-' }}</td>
                        <td>
                            <span class="badge {{ $actor->actor_status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $actor->status_text }}
                            </span>
                        </td>
                        <td>{{ date('Y-m-d H:i', $actor->actor_time) }}</td>
                        <td>
                            <a href="{{ route('admin.actor.info', $actor->actor_id) }}" class="btn btn-sm btn-info">Edit</a>
                            <button onclick="delActor({{ $actor->actor_id }})" class="btn btn-sm btn-danger">Delete</button>
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
function delActor(id) {
    if(confirm('Are you sure?')) {
        // Implement AJAX delete
        fetch('{{ route("admin.actor.del") }}?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
@endsection
