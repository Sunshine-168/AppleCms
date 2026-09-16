@extends('admin.layouts.inner')
@section('title', admin_t('more.title'))

@section('plain')
    <div class="card card-panel">
        <div class="card-header">
            <span>{{ admin_t('more.title') }}</span>
        </div>
        <div class="card-body">
            <p class="muted" style="margin:0">{{ admin_t('more.lead') }}</p>
        </div>
    </div>

    @foreach($catalog as $block)
        <div class="card card-panel">
            <div class="card-header">
                <span>{{ admin_t($block['title']) }}</span>
                @if(! empty($block['hint']))
                    <span class="muted">{{ admin_t($block['hint']) }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="tool-grid">
                    @foreach($block['items'] as $item)
                        <a href="{{ $item['url'] }}">{{ admin_t($item['label']) }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@endsection
