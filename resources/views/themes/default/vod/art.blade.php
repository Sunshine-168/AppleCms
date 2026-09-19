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

    <article class="art-detail">
        <header class="art-detail-head">
            <h1>{{ $art->title }}</h1>
            <p class="muted">{{ implode(' · ', $bits) }}</p>
        </header>
        @if(trim((string) $art->cover) !== '')
            <p class="art-cover"><img src="{{ $art->cover }}" alt=""></p>
        @endif
        @if(trim((string) ($art->blurb ?? '')) !== '')
            <p class="art-lead">{{ $art->blurb }}</p>
        @endif
        <div class="art-body desc">{!! $art->content !!}</div>
        @if($tags !== [])
            <p class="art-tags">
                @foreach($tags as $tag)
                    <a href="{{ $tag['url'] }}">{{ $tag['name'] }}</a>
                @endforeach
            </p>
        @endif
    </article>

    <p class="art-near">
        @if($prev)
            <a href="{{ $prev->url }}">上一篇：{{ $prev->title }}</a>
        @endif
        @if($next)
            <a href="{{ $next->url }}">下一篇：{{ $next->title }}</a>
        @endif
    </p>
    @if($related && count($related))
        <section class="home-sec">
            <div class="sec-head"><h2>相关阅读</h2></div>
            <ul class="list-plain">
                @foreach($related as $item)
                    <li><a href="{{ $item->url }}">{{ $item->title }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="home-sec detail-engage">
        <div class="sec-head"><h2>评论</h2></div>
        <div class="detail-comment-box">
            <form class="comment-form" method="post" action="{{ url('/art/'.$art->id.'/comment') }}">
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
                @vodComment(['id' => $art->id, 'mid' => 2])
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
