@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>{{ $info->actor_id ? 'Edit Actor' : 'Add Actor' }}</h3>

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
            <form action="{{ route('admin.actor.info.save', $info->actor_id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="actor_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="actor_name" name="actor_name" value="{{ old('actor_name', $info->actor_name) }}" required>
                </div>
                
                <div class="mb-3">
                    <label for="type_id" class="form-label">Category</label>
                    <select class="form-select" id="type_id" name="type_id">
                        <option value="">Select Category</option>
                        @foreach($type_tree as $type)
                            <option value="{{ $type->type_id }}" {{ $info->type_id == $type->type_id ? 'selected' : '' }}>
                                {{ $type->type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="actor_sex" class="form-label">Sex</label>
                    <select class="form-select" id="actor_sex" name="actor_sex">
                        <option value="男" {{ $info->actor_sex == '男' ? 'selected' : '' }}>Male</option>
                        <option value="女" {{ $info->actor_sex == '女' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="actor_pic" class="form-label">Picture URL</label>
                    <input type="text" class="form-control" id="actor_pic" name="actor_pic" value="{{ old('actor_pic', $info->actor_pic) }}">
                </div>
                
                <div class="mb-3">
                    <label for="actor_content" class="form-label">Content</label>
                    <textarea class="form-control" id="actor_content" name="actor_content" rows="3">{{ old('actor_content', $info->actor_content) }}</textarea>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="actor_status" name="actor_status" value="1" {{ $info->actor_status == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="actor_status">Enabled</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.actor.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
