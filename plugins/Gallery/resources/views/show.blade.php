@extends('themes.default.layout')
@section('content')
<p class="breadcrumb"><a href="{{ url('/gallery') }}">图集</a> / {{ $gallery->title }}</p>
<div class="list-head">
    <h1>{{ $gallery->title }}</h1>
    <p class="muted">
        {{ $gallery->author ?: '佚名' }}
        @if(trim((string) $gallery->remarks) !== '')
            · {{ $gallery->remarks }}
        @endif
        · 人气 {{ $gallery->hits }} · 收藏 {{ (int) ($favor_count ?? 0) }}
    </p>
    @if(! empty($tag_list))
        <p class="muted">
            @foreach($tag_list as $tag)
                <a href="{{ url('/gallery?tag='.urlencode($tag)) }}">{{ $tag }}</a>@unless($loop->last) · @endunless
            @endforeach
        </p>
    @endif
</div>
@if($gallery->content)
    <div class="desc">{!! nl2br(e($gallery->content)) !!}</div>
@endif
@auth('member')
    <form method="post" action="{{ url('/gallery/'.$gallery->id.'/favor') }}" style="margin:0 0 16px">
        @csrf
        <button class="btn-ghost">{{ ! empty($favored) ? '取消收藏' : '收藏图集' }}</button>
    </form>
@else
    <p class="muted"><a href="{{ url('/member/login') }}">登录</a> 后可收藏</p>
@endauth
<div class="grid gallery-grid">
    @forelse($gallery->pics as $pic)
        <figure>
            <img src="{{ $pic->url }}" alt="{{ $pic->title ?: $gallery->title }}" loading="lazy" style="width:100%;height:auto">
            <figcaption>{{ $pic->title }}</figcaption>
        </figure>
    @empty
        <p class="muted">还没有图片。</p>
    @endforelse
</div>
@php $comments = $comments ?? collect(); $commentCount = (int) ($commentCount ?? $comments->count()); @endphp
<section class="home-sec detail-engage">
    <div class="sec-head"><h2>评论</h2><span class="muted">{{ $commentCount }}</span></div>
    <div class="detail-comment-box">
        <form method="post" action="{{ url('/gallery/'.$gallery->id.'/comment') }}" class="comment-form">
            @csrf
            @guest('member')
                <label class="auth-field"><span>昵称</span><input name="author_name" placeholder="怎么称呼你" maxlength="32"></label>
            @endguest
            <label class="auth-field"><span>内容</span><textarea name="content" rows="4" required maxlength="2000" placeholder="写评论…"></textarea></label>
            <div class="auth-actions"><button type="submit" class="btn-play btn-sm">发表评论</button></div>
        </form>
        @if($comments->isEmpty())
            <p class="muted" style="margin:12px 0 0">还没有评论。</p>
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
