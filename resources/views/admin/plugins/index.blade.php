@extends('admin.layouts.inner')
@section('title', admin_t('plugin.title'))

@section('plain')
    <div class="card card-panel">
        <div class="card-header"><span>{{ admin_t('plugin.title') }}</span></div>
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
                    </div>
                </div>
            </div>
        @empty
            <p class="muted">{{ admin_t('plugin.empty') }}</p>
        @endforelse
    </div>
@endsection
