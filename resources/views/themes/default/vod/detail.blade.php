@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    @if(session('status'))<p>{{ session('status') }}</p>@endif
    @if(session('error'))<p class="muted">{{ session('error') }}</p>@endif
    <h1>{{ $video->title }}</h1>
    <p class="muted">{{ $video->year }} / {{ $video->area }} / {{ $video->lang }} @if($video->remarks) · {{ $video->remarks }} @endif · 评分 {{ $video->stat->score ?? $video->score }}@if((int)($video->points ?? 0) > 0) · 点播 {{ $video->points }} 积分 @endif</p>
    <p>
        <a href="{{ $video->play_url }}">立即播放</a>
        <a href="{{ vod_url('down', ['id' => $video->id]) }}">下载</a>
        @auth('member')
            <button type="button" id="fav-btn" data-on="{{ $favorited ? '1' : '0' }}">{{ $favorited ? '取消收藏' : '收藏' }}</button>
        @else
            <a href="{{ url('/member/login') }}">登录后收藏</a>
        @endauth
    </p>
    <div class="desc">@vodSubstr(['name' => $video->description, 'len' => 400])</div>

    <h2>线路</h2>
    @vodSource(['type' => 'play'])
        <h3>{{ $item->name }}</h3>
        <div class="eps">
            @foreach($item->episodes as $ep)
                <a href="{{ $ep->play_url }}">{{ $ep->display_name }}</a>
            @endforeach
        </div>
    @endvodSource
    @vodSource(['type' => 'down'])
        <h3>{{ $item->name }}</h3>
        <div class="eps">
            @foreach($item->episodes as $ep)
                <a href="{{ $ep->url }}" target="_blank" rel="nofollow">下载 {{ $ep->display_name }}</a>
            @endforeach
        </div>
    @endvodSource

    <p>评分：
        @for($i=8; $i<=10; $i++)
            <a href="javascript:;" class="score-link" data-score="{{ $i }}">{{ $i }}分</a>
        @endfor
    </p>

    <h2>评论</h2>
    <form method="post" action="{{ url('/vod/'.$video->id.'/comment') }}">
        @csrf
        @guest('member')
            <p><input name="author_name" placeholder="昵称"></p>
        @endguest
        <p><textarea name="content" rows="4" style="width:100%;background:#0b0d12;color:#e8eaed;border:1px solid #2a2f3a;" required></textarea></p>
        <p><button type="submit">发表评论</button></p>
    </form>
    @vodComment
        <p><strong>{{ $item->author_name }}</strong> · {{ date('Y-m-d H:i', (int)$item->created_at) }}<br>{{ $item->content }}</p>
    @endvodComment

    <h2>报错</h2>
    <form method="post" action="{{ url('/vod/'.$video->id.'/report') }}">
        @csrf
        <p><input name="content" placeholder="无法播放 / 地址失效…" style="width:70%"></p>
        <p><button type="submit">提交报错</button></p>
    </form>

    <p>@vodPrev  @vodNext</p>
    <script>
        document.querySelectorAll('.score-link').forEach(function(a){
            a.addEventListener('click', function(){
                fetch(@json(url('/vod/'.$video->id.'/score')), {
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':@json(csrf_token())},
                    body:'score='+this.dataset.score
                }).then(r=>r.json()).then(function(res){ alert(res.msg||'ok'); });
            });
        });
        var fav = document.getElementById('fav-btn');
        if (fav) {
            fav.addEventListener('click', function(){
                fetch(@json(url('/vod/'.$video->id.'/favorite')), {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':@json(csrf_token())}
                }).then(r=>r.json()).then(function(res){
                    alert(res.msg||'ok');
                    if(res.data && res.data.favorited){ fav.textContent='取消收藏'; } else { fav.textContent='收藏'; }
                });
            });
        }
    </script>
@endsection
