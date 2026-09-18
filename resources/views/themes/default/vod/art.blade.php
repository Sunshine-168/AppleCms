@extends('themes.default.layout')

@section('content')
    @php
        $type = $type ?? null;
        $prev = $prev ?? null;
        $next = $next ?? null;
        $related = $related ?? collect();
        $artTags = $artTags ?? collect();
        $when = (int) ($art->published_at ?? 0) > 0 ? (int) $art->published_at : (int) $art->created_at;
        $tags = [];
        if ($artTags instanceof \Illuminate\Support\Collection && $artTags->isNotEmpty()) {
            foreach ($artTags as $item) {
                $tags[] = ['name' => (string) $item->name, 'url' => (string) $item->url];
            }
        } else {
            foreach (array_values(array_filter(array_map('trim', explode(',', (string) ($art->tag ?? ''))))) as $name) {
                $tags[] = ['name' => $name, 'url' => vod_url('arts').'?wd='.urlencode($name)];
            }
        }
        $bits = [];
        if ($when > 0) {
            $bits[] = date('Y-m-d H:i', $when);
        }
        $bits[] = ((int) $art->hits).' 次';
        if (trim((string) ($art->source ?? '')) !== '') {
            $bits[] = (string) $art->source;
        }
        if (trim((string) ($art->author ?? '')) !== '') {
            $bits[] = (string) $art->author;
        }
    @endphp
    <nav class="breadcrumb">
        <a href="{{ vod_url('home') }}">首页</a>
        / <a href="{{ vod_url('arts') }}">资讯</a>
        @if($type)
            / <a href="{{ $type->url }}">{{ $type->name }}</a>
        @endif
        / <span>{{ $art->title }}</span>
    </nav>
    <h1>{{ $art->title }}</h1>
    @if(trim((string) $art->cover) !== '')
        <p class="art-cover"><img src="{{ $art->cover }}" alt=""></p>
    @endif
    <p class="muted">{{ implode(' · ', $bits) }}</p>
    <article class="desc">{!! $art->content !!}</article>
    @if($tags !== [])
        <p class="art-tags">
            @foreach($tags as $tag)
                <a href="{{ $tag['url'] }}">{{ $tag['name'] }}</a>
            @endforeach
        </p>
    @endif
    <p class="art-near">
        @if($prev)
            <a href="{{ $prev->url }}">上一篇：{{ $prev->title }}</a>
        @endif
        @if($next)
            <a href="{{ $next->url }}">下一篇：{{ $next->title }}</a>
        @endif
    </p>
    @if($related && count($related))
        <h2>相关阅读</h2>
        <ul class="list-plain">
            @foreach($related as $item)
                <li><a href="{{ $item->url }}">{{ $item->title }}</a></li>
            @endforeach
        </ul>
    @endif
    <h2>评论</h2>
    @if(session('error'))
        <p class="muted">{{ session('error') }}</p>
    @endif
    @if(session('status'))
        <p class="muted">{{ session('status') }}</p>
    @endif
    <form method="post" action="{{ url('/art/'.$art->id.'/comment') }}">
        @csrf
        @guest('member')
            <p><input name="author_name" placeholder="昵称"></p>
        @endguest
        <p><textarea name="content" rows="4" style="width:100%;background:#0b0d12;color:#e8eaed;border:1px solid #2a2f3a;" required></textarea></p>
        <p><button type="submit">发表评论</button></p>
    </form>
    @vodComment(['id' => $art->id, 'mid' => 2])
        <p><strong>{{ $item->author_name }}</strong> · {{ date('Y-m-d H:i', (int)$item->created_at) }}
            <a href="javascript:;" class="comment-like" data-id="{{ $item->id }}">赞{{ (int)($item->comment_up ?? 0) > 0 ? ' '.$item->comment_up : '' }}</a>
            <a href="javascript:;" class="comment-report" data-id="{{ $item->id }}">举报</a>
            <br>{{ $item->content }}
        </p>
    @endvodComment
    <script>
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
