@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>Config: {{ $name }}</h3>

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
            <form action="{{ route('admin.addon.config', $name) }}" method="POST">
                @csrf
                
                @foreach($config as $item)
                <div class="mb-3">
                    <label class="form-label">{{ $item['title'] }} ({{ $item['name'] }})</label>
                    
                    @if($item['type'] == 'string' || $item['type'] == 'text')
                        <input type="text" class="form-control" name="row[{{ $item['name'] }}]" value="{{ $item['value'] }}">
                    @elseif($item['type'] == 'radio')
                        <div>
                            @foreach($item['content'] as $k => $v)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="row[{{ $item['name'] }}]" value="{{ $k }}" {{ $item['value'] == $k ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $v }}</label>
                            </div>
                            @endforeach
                        </div>
                    @elseif($item['type'] == 'select')
                        <select class="form-select" name="row[{{ $item['name'] }}]">
                            @foreach($item['content'] as $k => $v)
                            <option value="{{ $k }}" {{ $item['value'] == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    @endif
                    
                    @if(isset($item['tip']))
                    <div class="form-text">{{ $item['tip'] }}</div>
                    @endif
                </div>
                @endforeach
                
                <button type="submit" class="btn btn-primary">Save Config</button>
                <a href="{{ route('admin.addon.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
