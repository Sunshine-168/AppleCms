@extends('themes.default.layout')
@section('content')
<p class="breadcrumb"><a href="{{ url('/novel') }}">小说</a> / {{ $novel->title }}</p>
@include('novel::partials.subnav')
<div class="detail-layout manga-detail">
    <div class="detail-cover{{ $novel->cover ? '' : ' is-empty' }}">
        @if($novel->cover)<img src="{{ $novel->cover }}" alt="{{ $novel->title }}">@endif
        <div class="detail-cover-empty">暂无封面</div>
    </div>
    <div class="detail-main">
        <h1>{{ $novel->title }}</h1>
        <p class="muted">{{ $novel->author ?: '佚名' }} · {{ (int) $novel->serialize === 1 ? '完结' : '连载' }} · 人气 {{ $novel->hits }} · 收藏 {{ (int) ($favor_count ?? 0) }}</p>
        @if(! empty($tag_list))
            <p class="muted">
                @foreach($tag_list as $tag)
                    <a href="{{ url('/novel?tag='.urlencode($tag)) }}">{{ $tag }}</a>@unless($loop->last) · @endunless
                @endforeach
            </p>
        @endif
        <div class="desc">{!! nl2br(e($novel->content)) !!}</div>
        @auth('member')
            <form method="post" action="{{ url('/novel/'.$novel->id.'/favor') }}">
                @csrf
                <button class="btn-ghost">{{ ! empty($favored) ? '移出书架' : '加入书架' }}</button>
            </form>
        @else
            <p class="muted"><a href="{{ url('/member/login') }}">登录</a> 后可加入书架</p>
        @endauth
    </div>
</div>
<section class="play-panel">
    <h2>章节</h2>
    <div class="eps">
        @forelse($novel->chapters as $ep)
            <a href="{{ url('/novel/'.$novel->id.'/'.$ep->id) }}">{{ $ep->name }}{{ $ep->vip ? ' · VIP' : '' }}</a>
        @empty
            <p class="muted">还没有章节。</p>
        @endforelse
    </div>
</section>
@php $comments = $comments ?? collect(); $commentCount = (int) ($commentCount ?? $comments->count()); @endphp
<section class="home-sec detail-engage">
    <div class="sec-head"><h2>评论</h2><span class="muted">{{ $commentCount }}</span></div>
    <div class="detail-comment-box">
        <form method="post" action="{{ url('/novel/'.$novel->id.'/comment') }}" class="comment-form">
            @csrf
            @guest('member')
                <label class="auth-field"><span>昵称</span><input name="author_name" placeholder="怎么称呼你" maxlength="32"></label>
            @endguest
            <label class="auth-field"><span>内容</span><textarea name="content" rows="4" required maxlength="2000" placeholder="写评论…"></textarea></label>
            <div class="auth-actions"><button type="submit" class="btn-play btn-sm">发表评论</button></div>
        </form>
        @if($comments->isEmpty())
            <p class="muted" style="margin:12px 0 0">还没有评论，来当第一条。</p>
        @else
            <div class="comment-list">
                @foreach($comments as $item)
                    <article class="comment-item">
                        <header class="comment-item-head">
                            <strong>{{ $item->author_name ?: '游客' }}</strong>
                            <time class="muted">{{ (int) $item->created_at > 0 ? date('Y-m-d H:i', (int) $item->created_at) : '' }}</time>
                        </header>
                        <p class="comment-item-body">{{ $item->content }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
