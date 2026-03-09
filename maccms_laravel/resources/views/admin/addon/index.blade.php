@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>Addon Management</h3>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Author</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($localAddons as $addon)
                    <tr>
                        <td>{{ $addon['name'] }}</td>
                        <td>{{ $addon['title'] }}</td>
                        <td>{{ $addon['intro'] }}</td>
                        <td>{{ $addon['author'] }}</td>
                        <td>{{ $addon['version'] }}</td>
                        <td>
                            @if($addon['state'] == 1)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.addon.config', $addon['name']) }}" class="btn btn-sm btn-info">Config</a>
                            @if($addon['state'] == 1)
                                <button onclick="changeState('{{ $addon['name'] }}', 'disable')" class="btn btn-sm btn-warning">Disable</button>
                            @else
                                <button onclick="changeState('{{ $addon['name'] }}', 'enable')" class="btn btn-sm btn-success">Enable</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No addons found. Place addons in the <code>addons/</code> directory.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function changeState(name, action) {
    if(confirm('Are you sure you want to ' + action + ' this addon?')) {
        fetch('{{ route("admin.addon.state") }}?name=' + name + '&action=' + action)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
@endsection
