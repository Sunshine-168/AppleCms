@extends('themes.default.layout')
@section('content')
    @php
        $tags = $manga->tagNames();
        $related = $related ?? collect();
        $comments = $comments ?? collect();
        $commentCount = (int) ($commentCount ?? $comments->count());
        $favored = (bool) ($favored ?? false);
        $first = $manga->chapters->first();
        $last = $manga->chapters->last();
    @endphp
    <p class="breadcrumb"><a href="{{ url('/manga') }}">漫画</a> / {{ $manga->title }}</p>
    @include('manga::partials.subnav')
    <div class="person">
        @if($manga->cover)
            <img src="{{ $manga->cover }}" alt="{{ $manga->title }}">
        @endif
        <div>
            <h1>{{ $manga->title }}</h1>
            <p class="manga-meta">
                <span>{{ $manga->serializeLabel() }}</span>
                @if($manga->type)<span>{{ $manga->type->name }}</span>@endif
                @if($manga->author)
                    <span>作者 <a href="{{ url('/manga?author='.urlencode((string) $manga->author)) }}">{{ $manga->author }}</a></span>
                @endif
                <span>人气 {{ $manga->hits }}</span>
                <span>{{ $commentCount }} 条评论</span>
                @if((int) ($manga->recommend ?? 0) === 1)<span>推荐</span>@endif
            </p>
            @if($manga->remarks)<p class="muted">{{ $manga->remarks }}</p>@endif
            @if($tags !== [])
                <p class="art-tags">
                    @foreach($tags as $tag)
                        <a href="{{ url('/manga?tag='.urlencode($tag)) }}">{{ $tag }}</a>
                    @endforeach
                </p>
            @endif
            @if($manga->content)<div class="desc">{!! nl2br(e($manga->content)) !!}</div>@endif
            <p class="manga-actions">
                @if($first)
                    <a class="btn-link" href="{{ url('/manga/'.$manga->id.'/'.$first->id) }}">开始阅读</a>
                    <a class="btn-link" id="manga-continue" hidden href="{{ url('/manga/'.$manga->id.'/'.$first->id) }}">继续阅读</a>
                @endif
                @if($last && $first && (int) $last->id !== (int) $first->id)
                    <a class="btn-link" href="{{ url('/manga/'.$manga->id.'/'.$last->id) }}">最新 {{ $last->name ?: ('第'.$last->id.'话') }}</a>
                @endif
                @auth('member')
                    <form method="post" action="{{ url('/manga/'.$manga->id.'/favor') }}" class="inline-form">
                        @csrf
                        <button type="submit">{{ $favored ? '移出书架' : '加入书架' }}</button>
                    </form>
                @else
                    <a href="{{ url('/member/login') }}">登录后收藏</a>
                @endauth
            </p>
        </div>
    </div>
    <h2>章节 <button type="button" class="btn-link" id="manga-rev">倒序</button></h2>
    @if($manga->chapters->isEmpty())
        <p class="muted">还没有章节。</p>
    @else
        <div class="eps" id="manga-eps">
            @foreach($manga->chapters as $ep)
                <a href="{{ url('/manga/'.$manga->id.'/'.$ep->id) }}">{{ $ep->name ?: ('第'.$ep->id.'话') }}</a>
            @endforeach
        </div>
    @endif
    @if($related->isNotEmpty())
        <h2>相关漫画</h2>
        <div class="grid">
            @foreach($related as $item)
                @include('manga::partials.card', ['row' => $item])
            @endforeach
        </div>
    @endif
    <h2>评论（{{ $commentCount }}）</h2>
    <form method="post" action="{{ url('/manga/'.$manga->id.'/comment') }}">
        @csrf
        @guest('member')
            <p><input name="author_name" placeholder="昵称"></p>
        @endguest
        <p><textarea name="content" rows="4" required placeholder="写评论"></textarea></p>
        <p><button type="submit">发表</button></p>
    </form>
    @if($comments->isEmpty())
        <p class="muted">还没有评论。</p>
    @else
        <ul class="list-plain manga-comments">
            @foreach($comments as $item)
                <li>
                    <strong>{{ $item->author_name ?: '游客' }}</strong>
                    <span class="muted">{{ (int) $item->created_at > 0 ? date('Y-m-d H:i', (int) $item->created_at) : '' }}</span>
                    <p>{{ $item->content }}</p>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
@push('scripts')
<script>
(function () {
    var key = 'manga_history';
    var id = @json((int) $manga->id);
    var first = @json($first ? (int) $first->id : 0);
    try {
        var map = JSON.parse(localStorage.getItem(key) || '{}');
        var row = map[String(id)];
        var link = document.getElementById('manga-continue');
        if (link && row && row.chapter && Number(row.chapter) !== first) {
            link.href = '/manga/' + id + '/' + row.chapter;
            if (row.name) link.textContent = '继续阅读 ' + row.name;
            link.hidden = false;
        }
    } catch (e) {}
    var btn = document.getElementById('manga-rev');
    var eps = document.getElementById('manga-eps');
    if (btn && eps) {
        btn.addEventListener('click', function () {
            var nodes = Array.prototype.slice.call(eps.children);
            nodes.reverse().forEach(function (n) { eps.appendChild(n); });
            btn.textContent = btn.textContent === '倒序' ? '正序' : '倒序';
        });
    }
})();
</script>
@endpush
