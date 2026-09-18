@php
    $ads = app(\Plugins\Advert\Services\AdvertService::class)->slot('player');
@endphp
@if($ads->isNotEmpty())
<div class="plugin-ad-player">
    @foreach($ads as $ad)
        @include('advert::item', ['ad' => $ad])
    @endforeach
</div>
@endif
