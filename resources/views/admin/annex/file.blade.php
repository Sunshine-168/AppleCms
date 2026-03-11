@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>File Explorer</h3>
    
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Current Path: <strong>{{ $path }}</strong>
                @if($path != 'upload')
                    <a href="{{ route('admin.annex.file', ['path' => $upPath]) }}" class="btn btn-sm btn-secondary ms-2">Go Up</a>
                @endif
            </div>
            <div>
                <span class="badge bg-info">Dirs: {{ $num_path }}</span>
                <span class="badge bg-primary">Files: {{ $num_file }}</span>
                <span class="badge bg-success">Size: {{ $sum_size }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Modified Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                    <tr>
                        <td>
                            @if($file['isfile'])
                                <i class="bi bi-file-earmark"></i>
                                <a href="{{ asset($file['path']) }}" target="_blank">{{ $file['name'] }}</a>
                            @else
                                <i class="bi bi-folder-fill text-warning"></i>
                                <a href="{{ route('admin.annex.file', ['path' => $file['path']]) }}">{{ $file['name'] }}</a>
                            @endif
                        </td>
                        <td>{{ isset($file['size']) ? $file['size'] : '-' }}</td>
                        <td>{{ date('Y-m-d H:i:s', $file['time']) }}</td>
                        <td>
                            <!-- File actions placeholder -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
