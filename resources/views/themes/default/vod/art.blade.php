@extends('themes.default.layout')

@section('content')
    @php
        $type = $type ?? null;
        $prev = $prev ?? null;
        $next = $next ?? null;
        $related = $related ?? collect();
        $artTags = $artTags ?? collect();
        $when = (int) ($art->published_at ?? 0) > 0 ? (int) $art->published_at : (int) $art->created_at;
        $tags = [];
        if ($artTags instanceof \Illuminate\Support\Collection && $artTags->isNotEmpty()) {
            foreach ($artTags as $item) {
                $tags[] = ['name' => (string) $item->name, 'url' => (string) $item->url];
            }
        } else {
            foreach (array_values(array_filter(array_map('trim', explode(',', (string) ($art->tag ?? ''))))) as $name) {
                $tags[] = ['name' => $name, 'url' => vod_url('arts').'?wd='.urlencode($name)];
            }
        }
        $bits = [];
        if ($when > 0) {
            $bits[] = date('Y-m-d H:i', $when);
        }
        $bits[] = ((int) $art->hits).' 次';
        if (trim((string) ($art->source ?? '')) !== '') {
            $bits[] = (string) $art->source;
        }
        if (trim((string) ($art->author ?? '')) !== '') {
            $bits[] = (string) $art->author;
        }
    @endphp
    <nav class="breadcrumb">
        <a href="{{ vod_url('home') }}">首页</a>
        / <a href="{{ vod_url('arts') }}">资讯</a>
        @if($type)
            / <a href="{{ $type->url }}">{{ $type->name }}</a>
        @endif
        / <span>{{ $art->title }}</span>
    </nav>
    <h1>{{ $art->title }}</h1>
    @if(trim((string) $art->cover) !== '')
        <p class="art-cover"><img src="{{ $art->cover }}" alt=""></p>
    @endif
    <p class="muted">{{ implode(' · ', $bits) }}</p>
    <article class="desc">{!! $art->content !!}</article>
    @if($tags !== [])
        <p class="art-tags">
            @foreach($tags as $tag)
                <a href="{{ $tag['url'] }}">{{ $tag['name'] }}</a>
            @endforeach
        </p>
    @endif
    <p class="art-near">
        @if($prev)
            <a href="{{ $prev->url }}">上一篇：{{ $prev->title }}</a>
        @endif
        @if($next)
            <a href="{{ $next->url }}">下一篇：{{ $next->title }}</a>
        @endif
    </p>
    @if($related && count($related))
        <h2>相关阅读</h2>
        <ul class="list-plain">
            @foreach($related as $item)
                <li><a href="{{ $item->url }}">{{ $item->title }}</a></li>
            @endforeach
        </ul>
    @endif
@endsection
