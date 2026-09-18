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
    <div class="type-block">
        <h2>漫画 <a class="more" href="{{ url('/manga') }}">更多</a></h2>
        <div class="grid">
            @foreach($homeMangas as $row)
                @include('manga::partials.card', ['row' => $row])
            @endforeach
        </div>
    </div>
@endif
@include('manga::partials.continue')
