@php
    $href = url('/ads/go/'.$ad->id);
    $label = trim((string) ($ad->title !== '' ? $ad->title : $ad->name));
@endphp
@if((string) $ad->type === 'image' && trim((string) $ad->image) !== '')
    <a class="plugin-ad-img" href="{{ $href }}" rel="nofollow"><img src="{{ $ad->image }}" alt="{{ $label }}"></a>
@else
    <a class="plugin-ad-text" href="{{ $href }}" rel="nofollow">{{ $label }}</a>
@endif
