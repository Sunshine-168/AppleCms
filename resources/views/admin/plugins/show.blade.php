@extends('admin.layouts.inner')
@section('title', $plugin['name'])

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $plugin['name'] }}</span>
        <div class="plugin-card-actions">
            <a class="btn btn-muted btn-sm" href="{{ route('admin.plugins') }}">{{ admin_t('plugin.back') }}</a>
            @include('admin.plugins._toggle', ['plugin' => $plugin])
            @if(! empty($plugin['uploaded']))
                <button type="button" class="btn btn-muted btn-sm js-plugin-uninstall" data-id="{{ $plugin['id'] }}">{{ admin_t('plugin.uninstall') }}</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="hint" style="margin-top:0">{{ $plugin['description'] }}</p>
        <p class="muted">v{{ $plugin['version'] }} · {{ admin_t('plugin.group_'.$plugin['group']) }} · {{ admin_t('plugin.cap_'.$plugin['capability']) }}</p>
    </div>
</div>

@forelse($plugin['pages'] as $page)
<div class="card card-panel">
    <div class="card-header"><span>{{ $page['title'] }}</span></div>
    <div class="card-body">
        @if($page['hint'] !== '')
            <p class="hint">{{ $page['hint'] }}</p>
        @endif
        <form class="js-plugin-form" id="site-form">
            @foreach($page['fields'] as $field)
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
                    <input type="{{ preg_match('/(secret|password|_key)$/i', (string) $field['name']) ? 'password' : 'text' }}" name="{{ $field['name'] }}" value="{{ $site[$field['name']] ?? '' }}" placeholder="{{ $field['placeholder'] ?? '' }}" autocomplete="off">
                @endif
                @if(! empty($field['hint']))
                    <p class="muted field-hint">{{ $field['hint'] }}</p>
                @endif
            @endforeach
            <div class="form-actions">
                <button type="button" class="btn" id="site-save">{{ admin_t('plugin.save') }}</button>
            </div>
        </form>
    </div>
</div>
@empty
    @if($plugin['manage'] === [])
        <div class="card card-panel">
            <div class="card-body">
                <p class="muted" style="margin:0">{{ admin_t('plugin.no_fields') }}</p>
            </div>
        </div>
    @endif
@endforelse

@if($plugin['manage'] !== [])
<div class="card card-panel">
    <div class="card-header"><span>{{ admin_t('plugin.manage') }}</span></div>
    <div class="card-body">
        @if($plugin['enabled'])
            <div class="plugin-manage">
                @foreach($plugin['manage'] as $link)
                    <a class="btn btn-sm" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </div>
        @else
            <p class="muted" style="margin:0 0 10px">{{ admin_t('plugin.manage_need_on') }}</p>
            <div class="plugin-manage">
                @foreach($plugin['manage'] as $link)
                    <span class="btn btn-muted btn-sm" aria-disabled="true">{{ $link['label'] }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif
@endsection

@include('admin.plugins._boot')
@include('admin.partials.site-save')
