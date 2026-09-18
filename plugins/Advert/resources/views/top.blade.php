@php
    $ads = app(\Plugins\Advert\Services\AdvertService::class)->slot('top');
@endphp
@if($ads->isNotEmpty())
<div class="plugin-ad-fixed is-top">
    @foreach($ads as $ad)
        @include('advert::item', ['ad' => $ad])
    @endforeach
</div>
<script>document.body.classList.add('has-ad-top');</script>
@endif
