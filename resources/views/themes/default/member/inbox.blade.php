@extends('themes.default.layout')
@section('content')
    <h1>站内信</h1>
    @forelse($messages as $m)
        <p><strong>{{ $m->title }}</strong> · {{ $m->is_read ? '已读' : '未读' }}</p>
        <p class="desc">{{ $m->content }}</p>
    @empty
        <p class="muted">暂无站内信</p>
    @endforelse
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
