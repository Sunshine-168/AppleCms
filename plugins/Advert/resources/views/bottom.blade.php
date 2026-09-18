@php
    $ads = app(\Plugins\Advert\Services\AdvertService::class)->slot('bottom');
@endphp
@if($ads->isNotEmpty())
<div class="plugin-ad-fixed is-bottom">
    @foreach($ads as $ad)
        @include('advert::item', ['ad' => $ad])
    @endforeach
</div>
<script>document.body.classList.add('has-ad-bottom');</script>
@endif
