@extends('admin.layouts.inner')
@section('title', admin_t('plugin.title'))

@section('plain')
    <div class="card card-panel">
        <div class="card-header">
            <span>{{ admin_t('plugin.title') }}</span>
            <div>
                <button type="button" class="btn btn-primary btn-sm" id="plugin-upload-btn">{{ admin_t('plugin.upload') }}</button>
            </div>
        </div>
        <div class="card-body">
            <p class="muted" style="margin:0">{{ admin_t('plugin.lead') }}</p>
        </div>
    </div>

    <div class="plugin-grid">
        @forelse($plugins as $plugin)
            <div class="plugin-card{{ $plugin['enabled'] ? ' is-on' : '' }}">
                <div class="plugin-card-head">
                    <strong>{{ $plugin['name'] }}</strong>
                    <span class="badge badge-{{ $plugin['capability'] === 'ready' ? 'ok' : ($plugin['capability'] === 'config' ? 'search' : 'off') }}">{{ admin_t('plugin.cap_'.$plugin['capability']) }}</span>
                </div>
                <p class="muted">{{ $plugin['description'] }}</p>
                <div class="plugin-card-foot">
                    <span class="muted">v{{ $plugin['version'] }} · {{ admin_t('plugin.group_'.$plugin['group']) }}</span>
                    <div class="plugin-card-actions">
                        <a class="btn btn-muted btn-sm" href="{{ route('admin.plugins.show', $plugin['id']) }}">{{ admin_t('plugin.configure') }}</a>
                        @include('admin.plugins._toggle', ['plugin' => $plugin])
                        @if(! empty($plugin['uploaded']))
                            <button type="button" class="btn btn-muted btn-sm js-plugin-uninstall" data-id="{{ $plugin['id'] }}">{{ admin_t('plugin.uninstall') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="muted">{{ admin_t('plugin.empty') }}</p>
        @endforelse
    </div>

    <template id="plugin-upload-tpl">
        <div class="plugin-upload">
            <p class="hint">{{ admin_t('plugin.upload_hint') }}</p>
            <div class="plugin-upload-pick">
                <button type="button" class="btn btn-muted btn-sm js-plugin-pick">{{ admin_t('plugin.upload_pick') }}</button>
                <span class="muted js-plugin-zip-name">{{ admin_t('plugin.upload_none') }}</span>
                <input type="file" accept=".zip,application/zip,application/x-zip-compressed" hidden>
            </div>
        </div>
    </template>
    @include('admin.plugins._boot')
@endsection
