@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>{{ $info->admin_id ? 'Edit Admin' : 'Add Admin' }}</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.admin.info', $info->admin_id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="admin_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="admin_name" name="admin_name" value="{{ old('admin_name', $info->admin_name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="admin_pwd" class="form-label">Password</label>
                    <input type="password" class="form-control" id="admin_pwd" name="admin_pwd" placeholder="{{ $info->admin_id ? 'Leave blank to keep unchanged' : 'Required' }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="admin_status" id="status1" value="1" {{ $info->admin_status == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="status1">Enabled</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="admin_status" id="status0" value="0" {{ $info->admin_status == 0 ? 'checked' : '' }}>
                        <label class="form-check-label" for="status0">Disabled</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    @foreach($menus as $groupName => $items)
                        <div class="card mb-2">
                            <div class="card-header">{{ $groupName }}</div>
                            <div class="card-body">
                                @foreach($items as $key => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="admin_auth[]" value="{{ $key }}" 
                                            {{ strpos($info->admin_auth, ",$key,") !== false ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.admin.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
