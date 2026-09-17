@extends('themes.default.layout')
@section('content')
    @vodBreadcrumb(['last' => '留言本'])
    <h1>留言本</h1>
    <form method="post" action="{{ url('/gbook') }}">
        @csrf
        <p><input name="author_name" placeholder="昵称" value="{{ auth('member')->user()->name ?? '' }}"></p>
        <p><textarea name="content" rows="4" placeholder="留言内容" required></textarea></p>
        <p><button type="submit">提交</button></p>
    </form>
    <div class="desc">
        @vodGbook
            <p><strong>{{ $item->author_name }}</strong> · {{ date('Y-m-d H:i', (int) $item->created_at) }}</p>
            <p>{{ $item->content }}</p>
            @if($item->reply)<p class="muted">回复：{{ $item->reply }}</p>@endif
        @endvodGbook
    </div>
@endsection
