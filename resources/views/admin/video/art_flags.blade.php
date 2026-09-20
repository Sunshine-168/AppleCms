@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
    $flags = is_array($flags ?? null) ? $flags : [];
@endphp

@section('plain')
<div class="card card-panel art-flag-index">
    <div class="card-header">
        <span>{{ admin_t('nav.art_flags') }}</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/arts">{{ admin_t('ui.back_arts') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.art_flags_lead') }}</p>
        @if(! $ready)
            <p class="muted">{{ admin_t('ui.migrate_first') }}</p>
        @else
            <ul class="flag-board">
                @foreach($flags as $flag)
                    <li>
                        <a href="{{ $flag['url'] }}">{{ $flag['label'] }}</a>
                        <em>{{ admin_t('ui.topic_arts_n', ['n' => (int) ($flag['art_count'] ?? 0)]) }}</em>
                        <span class="muted">{{ $flag['hint'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
