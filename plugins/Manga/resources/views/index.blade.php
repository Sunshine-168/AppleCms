@extends('themes.default.layout')
@section('content')
    <h1>漫画</h1>
    @if(isset($types) && $types->isNotEmpty())
        <p class="muted">
            <a href="{{ url('/manga') }}">全部</a>
            @foreach($types as $type)
                <a href="{{ url('/manga?type='.$type->id) }}"@if((int) ($typeId ?? 0) === (int) $type->id) style="font-weight:600"@endif>{{ $type->name }}</a>
            @endforeach
        </p>
    @endif
    @if($list->isEmpty())
        <p class="muted">还没有上架的漫画。后台启用「漫画」插件后，在内容折叠菜单里添加。</p>
    @else
        <div class="grid">
            @foreach($list as $row)
                <a class="card" href="{{ url('/manga/'.$row->id) }}">
                    @if($row->cover)
                        <img src="{{ $row->cover }}" alt="{{ $row->title }}">
                    @endif
                    <div class="meta">
                        <h3>{{ $row->title }}</h3>
                        <div class="muted">{{ $row->remarks ?: $row->author }}</div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="pager">{{ $list->links() }}</div>
    @endif
@endsection
