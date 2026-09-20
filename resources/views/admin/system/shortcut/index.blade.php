@extends('admin.layouts.inner')
@section('title', admin_t('page.shortcut'))

@php
    $groups = $groups ?? [];
    $todoTotal = (int) ($todo_total ?? 0);
@endphp

@section('plain')
<div class="card card-panel shortcut-index">
    <div class="card-header">
        <span>{{ admin_t('nav.shortcut') }}@if($todoTotal > 0) <em>· {{ $todoTotal }}</em>@endif</span>
        <div>
            <a class="btn btn-sm" href="/admin/plugins">{{ admin_t('nav.plugins') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/more">{{ admin_t('nav.catalog') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.shortcut_lead_before') }}「<a href="/admin/plugins">{{ admin_t('nav.plugins') }}</a>」。{{ admin_t('ui.shortcut_lead_mid') }}「<a href="/admin/more">{{ admin_t('nav.catalog') }}</a>」{{ admin_t('ui.shortcut_lead_end') }}</p>

        @foreach($groups as $group)
            <section class="shortcut-block">
                <h2>
                    <span>{{ $group['title'] }}</span>
                    @if(! empty($group['hint']))
                        <span class="muted">{{ $group['hint'] }}</span>
                    @endif
                </h2>
                <div class="more-grid">
                    @foreach($group['items'] as $item)
                        @php $count = (int) ($item['count'] ?? 0); @endphp
                        <a class="more-tile{{ $count > 0 ? ' has-count' : '' }}" href="{{ $item['url'] }}">
                            <strong>
                                {{ $item['title'] }}
                                @if($count > 0)
                                    <em>{{ $count }}</em>
                                @endif
                            </strong>
                            <span>{{ $item['desc'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
