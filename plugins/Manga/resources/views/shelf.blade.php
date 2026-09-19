@extends('themes.default.layout')
@section('content')
    <div class="list-head manga-head">
        <h1>书架</h1>
        <p class="muted">收藏的作品集中在这里，有更新会标出来。</p>
    </div>
    @include('manga::partials.subnav')
    @php
        $updated = $list->filter(static fn ($row) => (bool) ($row->has_update ?? false))->count();
    @endphp
    @if($list->isEmpty())
        <div class="list-empty">
            <p>书架是空的</p>
            <p class="muted">在漫画详情页点收藏即可加入。</p>
            <p><a class="btn-link" href="{{ url('/manga') }}">去逛逛漫画</a></p>
        </div>
    @else
        @if($updated > 0)
            <p class="manga-active-filters muted">有 {{ $updated }} 部作品更新了新章节。</p>
        @endif
        <div class="grid">
            @foreach($list as $row)
                <div class="manga-shelf-item">
                    @include('manga::partials.card', ['row' => $row])
                    <form method="post" action="{{ url('/manga/'.$row->id.'/favor') }}" class="inline-form manga-shelf-remove">
                        @csrf
                        <button type="submit" class="btn-link">移出书架</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
    @include('manga::partials.continue')
@endsection
