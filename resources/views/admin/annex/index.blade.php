@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>Annex Management</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('admin.annex.file') }}" class="btn btn-primary">File Explorer</a>
        </div>
        <div class="col-md-6 text-end">
            <form action="" method="GET" class="d-inline-flex">
                <input type="text" name="wd" class="form-control me-2" placeholder="Search file..." value="{{ request('wd') }}">
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
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($list as $item)
                    <tr>
                        <td>{{ $item->annex_id }}</td>
                        <td>
                            <a href="{{ asset($item->annex_file) }}" target="_blank">{{ $item->annex_file }}</a>
                        </td>
                        <td>{{ $item->annex_type }}</td>
                        <td>{{ $item->annex_size }}</td>
                        <td>{{ date('Y-m-d H:i:s', $item->annex_time) }}</td>
                        <td>
                            <button onclick="delAnnex({{ $item->annex_id }})" class="btn btn-sm btn-danger">Delete</button>
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
function delAnnex(id) {
    if(confirm('Are you sure you want to delete this record?')) {
        fetch('{{ route("admin.annex.del") }}?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
@endsection
