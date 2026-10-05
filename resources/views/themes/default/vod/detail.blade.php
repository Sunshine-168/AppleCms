@extends('themes.default.layout')

@section('content')
@php
    $cover = trim((string) ($video->cover ?? ''));
    $score = $video->stat->score ?? $video->score ?? 0;
    $desc = trim(strip_tags((string) ($video->description ?? '')));
    $actors = $video->actors ?? collect();
    $tags = $video->tags ?? collect();
@endphp
    @vodBreadcrumb
    <div class="detail-layout">
        <div class="detail-cover{{ $cover === '' ? ' is-empty' : '' }}">
            @if($cover !== '')
                <img src="{{ $cover }}" alt="{{ $video->title }}"
                     onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
            @endif
            <div class="detail-cover-empty">暂无封面</div>
        </div>
        <div class="detail-main">
            <h1 class="detail-title">{{ $video->title }}</h1>
            <ul class="detail-tags">
                @if($video->type)<li><a href="{{ $video->type->url }}">{{ $video->type->name }}</a></li>@endif
                @if($video->year)<li>{{ $video->year }}</li>@endif
                @if($video->area)<li>{{ $video->area }}</li>@endif
                @if($video->lang)<li>{{ $video->lang }}</li>@endif
                @if($video->remarks)<li class="is-hi">{{ $video->remarks }}</li>@endif
                @if((float) $score > 0)<li class="is-score">{{ $score }} 分</li>@endif
                @if((int) ($video->points ?? 0) > 0)<li>点播 {{ $video->points }} 积分</li>@endif
            </ul>

            @if($actors->isNotEmpty())
                <p class="detail-actors">
                    <span class="muted">主演</span>
                    @foreach($actors->take(8) as $actor)
                        <a href="{{ $actor->url }}">{{ $actor->name }}</a>@if(! $loop->last)<span class="muted"> / </span>@endif
                    @endforeach
                </p>
            @endif

            @if($tags->isNotEmpty())
                <p class="detail-actors">
                    <span class="muted">标签</span>
                    @foreach($tags->take(10) as $tag)
                        <a href="{{ $tag->url ?? vod_url('tag', ['slug' => $tag->slug ?? $tag->id]) }}">{{ $tag->name }}</a>@if(! $loop->last)<span class="muted"> · </span>@endif
                    @endforeach
                </p>
            @endif

            <div class="detail-actions">
                <a class="btn-play" href="{{ $video->play_url }}">立即播放</a>
                <a class="btn-ghost" href="{{ vod_url('down', ['id' => $video->id]) }}">下载</a>
                @auth('member')
                    <button type="button" class="btn-ghost" id="fav-btn" data-on="{{ $favorited ? '1' : '0' }}">{{ $favorited ? '取消收藏' : '收藏' }}</button>
                @else
                    <a class="btn-ghost" href="{{ url('/member/login') }}">登录后收藏</a>
                @endauth
                @include('themes.default.partials.share-link')
            </div>

            @if($desc !== '' && $desc !== '暂无简介')
                <div class="desc detail-desc">{{ \Illuminate\Support\Str::limit($desc, 400) }}</div>
            @endif
        </div>
    </div>

    @php $roleList = $roles ?? collect(); @endphp
    @if($roleList && count($roleList))
        <section class="home-sec">
            <div class="sec-head"><h2>角色</h2><a class="more" href="{{ vod_url('roles') }}">全部</a></div>
            <ul class="chip-list">
                @foreach($roleList as $role)
                    <li><a href="{{ $role->url ?? vod_url('role', ['id' => $role->id]) }}">{{ $role->name }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif

    @php $plotList = $plots ?? $video->plots ?? collect(); @endphp
    @if($plotList && count($plotList))
        <section class="home-sec">
            <div class="sec-head"><h2>分集剧情</h2><a class="more" href="{{ vod_url('plots') }}?video_id={{ $video->id }}">全部</a></div>
            <ul class="list-plain">
                @foreach($plotList as $plot)
                    <li><a href="{{ vod_url('plot', ['id' => $plot->id]) }}">第{{ $plot->episode_num }}集 {{ $plot->title }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="home-sec">
        <div class="sec-head"><h2>播放线路</h2></div>
        <div class="source-row">
            @vodSource(['type' => 'play'])
                <div class="source-item">
                    <span class="source-name">{{ $item->display_name ?? $item->name }}</span>
                    @foreach($item->episodes as $ep)
                        <a href="{{ $ep->play_url }}">{{ $ep->display_name }}</a>
                    @endforeach
                </div>
            @endvodSource
        </div>
        <div class="source-row">
            @vodSource(['type' => 'down'])
                <div class="source-item">
                    <span class="source-name">下载 · {{ $item->display_name ?? $item->name }}</span>
                    @foreach($item->episodes as $ep)
                        <a href="{{ $ep->url }}" target="_blank" rel="nofollow">{{ $ep->display_name }}</a>
                    @endforeach
                </div>
            @endvodSource
        </div>
    </section>

    <section class="home-sec">
        <div class="sec-head"><h2>猜你喜欢</h2></div>
        <div class="grid">
            @vod(['typeid' => $video->type_id, 'num' => 12, 'order' => 'hits'])
                @if((int) $item->id !== (int) $video->id)
                    @include('themes.default.partials.vod-card')
                @endif
            @endvod
        </div>
    </section>

    <p class="score-row muted">给这部片打分：
        @for($i=8; $i<=10; $i++)
            <a href="javascript:;" class="score-link" data-score="{{ $i }}">{{ $i }}分</a>
        @endfor
    </p>

    <section class="home-sec detail-engage">
        <div class="sec-head"><h2>评论</h2></div>
        <div class="detail-comment-box">
            <form class="comment-form" method="post" action="{{ url('/vod/'.$video->id.'/comment') }}">
                @csrf
                @guest('member')
                    <label class="auth-field">
                        <span>昵称</span>
                        <input name="author_name" placeholder="怎么称呼你" maxlength="40">
                    </label>
                @endguest
                <label class="auth-field">
                    <span>内容</span>
                    <textarea name="content" rows="4" required placeholder="说点什么…" maxlength="1000"></textarea>
                </label>
                <div class="auth-actions">
                    <button type="submit" class="btn-play btn-sm">发表评论</button>
                </div>
            </form>
            <div class="comment-list">
                @vodComment
                    <article class="comment-item">
                        <header class="comment-item-head">
                            <strong>{{ $item->author_name }}</strong>
                            <time class="muted">{{ date('Y-m-d H:i', (int)$item->created_at) }}</time>
                            <span class="comment-item-acts">
                                <a href="javascript:;" class="comment-like" data-id="{{ $item->id }}">赞{{ (int)($item->comment_up ?? 0) > 0 ? ' '.$item->comment_up : '' }}</a>
                                <a href="javascript:;" class="comment-report" data-id="{{ $item->id }}">举报</a>
                            </span>
                        </header>
                        <p class="comment-item-body">{{ $item->content }}</p>
                    </article>
                @endvodComment
            </div>
        </div>
    </section>

    <section class="home-sec detail-engage">
        <div class="sec-head"><h2>报错</h2></div>
        <form class="report-form detail-comment-box" method="post" action="{{ url('/vod/'.$video->id.'/report') }}">
            @csrf
            <div class="field-inline">
                <input name="content" placeholder="无法播放 / 地址失效…" required>
                <button type="submit" class="btn-ghost">提交</button>
            </div>
        </form>
    </section>

    <p class="near-nav">@vodPrev  @vodNext</p>
    <script>
        document.querySelectorAll('.score-link').forEach(function(a){
            a.addEventListener('click', function(){
                fetch(@json(url('/vod/'.$video->id.'/score')), {
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    body:'score='+this.dataset.score
                }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(res){
                    vodResult(res, '评分失败');
                }).catch(function(){ vodToast('网络异常，请重试', 'err'); });
            });
        });
        var fav = document.getElementById('fav-btn');
        if (fav) {
            fav.addEventListener('click', function(){
                fetch(@json(url('/vod/'.$video->id.'/favorite')), {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(res){
                    if (vodResult(res, '收藏失败') && res.data && res.data.favorited) {
                        fav.textContent='取消收藏';
                    } else if (res && Number(res.code) === 0) {
                        fav.textContent='收藏';
                    }
                }).catch(function(){ vodToast('网络异常，请重试', 'err'); });
            });
        }
        document.querySelectorAll('.comment-like').forEach(function(a){
            a.addEventListener('click', function(){
                var el = this;
                fetch('/comment/' + this.dataset.id + '/like', {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(res){
                    if (vodResult(res, '点赞失败') && res.data && typeof res.data.comment_up !== 'undefined') {
                        el.textContent = '赞 ' + res.data.comment_up;
                    }
                }).catch(function(){ vodToast('网络异常，请重试', 'err'); });
            });
        });
        document.querySelectorAll('.comment-report').forEach(function(a){
            a.addEventListener('click', function(){
                fetch('/comment/' + this.dataset.id + '/report', {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(res){
                    vodResult(res, '举报失败');
                }).catch(function(){ vodToast('网络异常，请重试', 'err'); });
            });
        });
    </script>
@endsection
