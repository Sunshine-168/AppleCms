@php
    $homeMangas = collect();
    try {
        $svc = app(\Plugins\Manga\Services\MangaService::class);
        if ($svc->ready()) {
            $homeMangas = $svc->homeList(6);
        }
    } catch (\Throwable) {
        $homeMangas = collect();
    }
@endphp
@if($homeMangas->isNotEmpty())
    <section class="home-sec">
        <div class="sec-head">
            <h2>漫画</h2>
            <a class="more" href="{{ url('/manga') }}">更多</a>
        </div>
        <div class="grid">
            @foreach($homeMangas as $row)
                @include('manga::partials.card', ['row' => $row])
            @endforeach
        </div>
    </section>
@endif
@include('manga::partials.continue')
