@extends('themes.default.layout')
@section('content')
    <div class="member-page">
        <div class="list-head">
            <h1>站内信</h1>
            <p class="muted"><a href="{{ url('/member') }}">返回会员中心</a></p>
        </div>

        @forelse($messages as $m)
            <article class="member-mail{{ $m->is_read ? '' : ' is-unread' }}">
                <header class="member-mail-head">
                    <strong>{{ $m->title }}</strong>
                    <span class="muted">{{ $m->is_read ? '已读' : '未读' }}</span>
                </header>
                <p class="member-mail-body">{{ $m->content }}</p>
            </article>
        @empty
            <div class="list-empty">
                <p>暂无站内信</p>
                <p><a class="btn-link" href="{{ url('/member') }}">返回会员中心</a></p>
            </div>
        @endforelse
    </div>
@endsection
