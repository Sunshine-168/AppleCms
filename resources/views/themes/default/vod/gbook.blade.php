@extends('themes.default.layout')

@section('content')
@php
    $gbooks = app(\App\Services\Video\Tags\GuestbookTag::class)->get(['num' => 40]);
    $nick = auth('member')->user()->name ?? '';
@endphp
    @vodBreadcrumb(['last' => '留言本'])

    <div class="gbook-page">
        <header class="gbook-hero">
            <h1>留言本</h1>
            <p class="muted">有问题、想催更、申请友链，直接写在这里。站长看到会回复。</p>
        </header>

        <div class="gbook-layout">
            <section class="gbook-compose">
                <div class="sec-head"><h2>写留言</h2></div>
                <form class="gbook-form" method="post" action="{{ url('/gbook') }}">
                    @csrf
                    <label class="gbook-field">
                        <span>昵称</span>
                        <input name="author_name" placeholder="怎么称呼你" value="{{ $nick }}" maxlength="40" @auth('member') readonly @endauth>
                    </label>
                    <label class="gbook-field">
                        <span>内容</span>
                        <textarea name="content" rows="5" placeholder="说点什么吧…" required maxlength="1000"></textarea>
                    </label>
                    <div class="gbook-actions">
                        <button type="submit" class="btn-play">提交留言</button>
                        <span class="muted gbook-tip">文明发言，广告/人身攻击会被删除</span>
                    </div>
                </form>
            </section>

            <section class="gbook-feed">
                <div class="sec-head">
                    <h2>最近留言</h2>
                    @if($gbooks->isNotEmpty())
                        <span class="muted">{{ $gbooks->count() }} 条</span>
                    @endif
                </div>

                @forelse($gbooks as $item)
                    <article class="gbook-item">
                        <header class="gbook-item-head">
                            <strong>{{ $item->author_name ?: '访客' }}</strong>
                            <time class="muted">{{ date('Y-m-d H:i', (int) $item->created_at) }}</time>
                        </header>
                        <p class="gbook-item-body">{{ $item->content }}</p>
                        @if(trim((string) ($item->reply ?? '')) !== '')
                            <div class="gbook-reply">
                                <span class="gbook-reply-label">站长回复</span>
                                <p>{{ $item->reply }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="list-empty">
                        <p>还没有留言</p>
                        <p class="muted">来当第一条吧。</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
