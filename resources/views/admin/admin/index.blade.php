@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>Admin Management</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('admin.admin.info') }}" class="btn btn-primary">Add Admin</a>
        </div>
        <div class="col-md-6 text-end">
            <form action="" method="GET" class="d-inline-flex">
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
                        <th>Name</th>
                        <th>Status</th>
                        <th>Last Login Time</th>
                        <th>Last Login IP</th>
                        <th>Login Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($list as $admin)
                    <tr>
                        <td>{{ $admin->admin_id }}</td>
                        <td>{{ $admin->admin_name }}</td>
                        <td>
                            <span class="badge {{ $admin->admin_status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $admin->status_text }}
                            </span>
                        </td>
                        <td>{{ $admin->admin_last_login_time ? date('Y-m-d H:i:s', $admin->admin_last_login_time) : '-' }}</td>
                        <td>{{ $admin->admin_last_login_ip ? long2ip($admin->admin_last_login_ip) : '-' }}</td>
                        <td>{{ $admin->admin_login_num }}</td>
                        <td>
                            <a href="{{ route('admin.admin.info', $admin->admin_id) }}" class="btn btn-sm btn-info">Edit</a>
                            <button onclick="delAdmin({{ $admin->admin_id }})" class="btn btn-sm btn-danger">Delete</button>
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
function delAdmin(id) {
    if(confirm('Are you sure you want to delete this admin?')) {
        fetch('{{ route("admin.admin.del") }}?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
@endsection
