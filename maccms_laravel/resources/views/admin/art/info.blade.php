@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>{{ $info->art_id ? 'Edit Article' : 'Add Article' }}</h3>

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
            <form action="{{ route('admin.art.info', $info->art_id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type_id" class="form-label">Type</label>
                        <select name="type_id" id="type_id" class="form-select" required>
                            <option value="">Select Type</option>
                            @foreach($type_tree as $type)
                                <option value="{{ $type->type_id }}" {{ (old('type_id', $info->type_id) == $type->type_id) ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="art_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="art_name" name="art_name" value="{{ old('art_name', $info->art_name) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="art_sub" class="form-label">Sub Name</label>
                    <input type="text" class="form-control" id="art_sub" name="art_sub" value="{{ old('art_sub', $info->art_sub) }}">
                </div>

                <div class="mb-3">
                    <label for="art_en" class="form-label">En Name</label>
                    <input type="text" class="form-control" id="art_en" name="art_en" value="{{ old('art_en', $info->art_en) }}">
                </div>

                <div class="mb-3">
                    <label for="art_pic" class="form-label">Picture URL</label>
                    <input type="text" class="form-control" id="art_pic" name="art_pic" value="{{ old('art_pic', $info->art_pic) }}">
                </div>

                <div class="mb-3">
                    <label for="art_content" class="form-label">Content</label>
                    <textarea class="form-control" id="art_content" name="art_content" rows="10">{{ old('art_content', $info->art_content) }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="art_level" class="form-label">Level</label>
                        <select name="art_level" class="form-select">
                            <option value="0" {{ old('art_level', $info->art_level) == 0 ? 'selected' : '' }}>None</option>
                            <option value="1" {{ old('art_level', $info->art_level) == 1 ? 'selected' : '' }}>Level 1</option>
                            <option value="2" {{ old('art_level', $info->art_level) == 2 ? 'selected' : '' }}>Level 2</option>
                            <option value="3" {{ old('art_level', $info->art_level) == 3 ? 'selected' : '' }}>Level 3</option>
                            <option value="4" {{ old('art_level', $info->art_level) == 4 ? 'selected' : '' }}>Level 4</option>
                            <option value="5" {{ old('art_level', $info->art_level) == 5 ? 'selected' : '' }}>Level 5</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="art_hits" class="form-label">Hits</label>
                        <input type="number" class="form-control" name="art_hits" value="{{ old('art_hits', $info->art_hits ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="art_up" class="form-label">Up</label>
                        <input type="number" class="form-control" name="art_up" value="{{ old('art_up', $info->art_up ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="art_down" class="form-label">Down</label>
                        <input type="number" class="form-control" name="art_down" value="{{ old('art_down', $info->art_down ?? 0) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="art_status" name="art_status" value="1" {{ old('art_status', $info->art_status ?? 1) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="art_status">Enabled</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="art_lock" name="art_lock" value="1" {{ old('art_lock', $info->art_lock) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="art_lock">Locked</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.art.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
