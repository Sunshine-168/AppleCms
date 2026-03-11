@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">{{ $title }}</div>
        <div class="card-body">
            <div class="alert alert-info">{{ $summary }}</div>

            @if(!empty($items))
                <ul class="list-group mb-3">
                    @foreach($items as $item)
                        <li class="list-group-item">{{ $item }}</li>
                    @endforeach
                </ul>
            @endif

            <a href="{{ route('admin.annex.index') }}" class="btn btn-primary">返回附件管理</a>
        </div>
    </div>
</div>
@endsection
