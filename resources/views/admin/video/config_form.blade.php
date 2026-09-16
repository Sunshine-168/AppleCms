@extends('admin.layouts.inner')
@section('title', $title)

@section('content')
    @if(! empty($hint))
        <p class="hint">{{ $hint }}</p>
    @endif
    <form id="site-form">
        @foreach($fields as $field)
            <label>{{ $field['label'] }}</label>
            @if(($field['type'] ?? 'text') === 'textarea')
                <textarea name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}">{{ $site[$field['name']] ?? '' }}</textarea>
            @elseif(($field['type'] ?? '') === 'select')
                <select name="{{ $field['name'] }}">
                    @foreach(($field['options'] ?? []) as $val => $lab)
                        <option value="{{ $val }}" @selected((string)($site[$field['name']] ?? '') === (string)$val)>{{ $lab }}</option>
                    @endforeach
                </select>
            @else
                <input type="text" name="{{ $field['name'] }}" value="{{ $site[$field['name']] ?? '' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
            @endif
        @endforeach
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">保存</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
