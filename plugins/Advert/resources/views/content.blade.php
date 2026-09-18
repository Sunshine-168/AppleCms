@php
    $ads = app(\Plugins\Advert\Services\AdvertService::class)->slot('content');
@endphp
@if($ads->isNotEmpty())
<div class="plugin-ad-content">
    @foreach($ads as $ad)
        @include('advert::item', ['ad' => $ad])
    @endforeach
</div>
@endif
