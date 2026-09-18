@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
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
        @include('themes.default.partials.share-link')
    </p>
    <div class="desc">@vodSubstr(['name' => $video->description, 'len' => 400])</div>
    @php $roleList = $roles ?? collect(); @endphp
    @if($roleList && count($roleList))
        <h2>角色</h2>
        <ul>
            @foreach($roleList as $role)
                <li><a href="{{ $role->url ?? vod_url('role', ['id' => $role->id]) }}">{{ $role->name }}</a></li>
            @endforeach
        </ul>
        <p><a href="{{ vod_url('roles') }}">全部角色</a></p>
    @endif
    @php $plotList = $plots ?? $video->plots ?? collect(); @endphp
    @if($plotList && count($plotList))
        <h2>分集剧情</h2>
        <ul>
            @foreach($plotList as $plot)
                <li><a href="{{ vod_url('plot', ['id' => $plot->id]) }}">第{{ $plot->episode_num }}集 {{ $plot->title }}</a></li>
            @endforeach
        </ul>
        <p><a href="{{ vod_url('plots') }}?video_id={{ $video->id }}">全部剧情</a></p>
    @endif

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
        <p><strong>{{ $item->author_name }}</strong> · {{ date('Y-m-d H:i', (int)$item->created_at) }}
            <a href="javascript:;" class="comment-like" data-id="{{ $item->id }}">赞{{ (int)($item->comment_up ?? 0) > 0 ? ' '.$item->comment_up : '' }}</a>
            <a href="javascript:;" class="comment-report" data-id="{{ $item->id }}">举报</a>
            <br>{{ $item->content }}
        </p>
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
