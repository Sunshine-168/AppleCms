@extends('themes.default.layout')
@section('content')
    @php
        $tags = $manga->tagNames();
        $related = $related ?? collect();
        $relatedTitle = (string) ($relatedTitle ?? '相关漫画');
        $comments = $comments ?? collect();
        $commentCount = (int) ($commentCount ?? $comments->count());
        $favored = (bool) ($favored ?? false);
        $favorCount = (int) ($favorCount ?? 0);
        $continueId = (int) ($continueId ?? 0);
        $first = $manga->chapters->first();
        $last = $manga->chapters->last();
        $listUrl = $listUrl ?? fn (array $over = []) => url('/manga');
        $cover = trim((string) ($manga->cover ?? ''));
        $authors = $manga->authorNames();
        $chapterCount = $manga->chapters->count();
        $latestEp = $manga->chapters->last();
        $latestName = $latestEp ? (string) ($latestEp->name ?: ('第'.$latestEp->id.'话')) : '';
        $updatedAt = (int) ($manga->updated_at ?? 0);
        $collapseAt = 15;
        $collapsed = $chapterCount > $collapseAt;
    @endphp

    <p class="breadcrumb"><a href="{{ url('/manga') }}">漫画</a> / {{ $manga->title }}</p>
    @include('manga::partials.subnav')

    <div class="detail-layout manga-detail">
        <div class="detail-cover{{ $cover === '' ? ' is-empty' : '' }}">
            @if($cover !== '')
                <img src="{{ $cover }}" alt="{{ $manga->title }}"
                     onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
            @endif
            <div class="detail-cover-empty">暂无封面</div>
        </div>
        <div class="detail-main">
            <h1 class="detail-title">{{ $manga->title }}</h1>
            <ul class="detail-tags">
                <li>{{ $manga->serializeLabel() }}</li>
                @if($manga->type)<li><a href="{{ $listUrl(['type' => $manga->type_id]) }}">{{ $manga->type->name }}</a></li>@endif
                @if((int) ($manga->recommend ?? 0) === 1)<li class="is-hi">推荐</li>@endif
                <li>人气 {{ $manga->hits }}</li>
                <li>收藏 {{ $favorCount }}</li>
                <li>{{ $commentCount }} 条评论</li>
            </ul>

            @if($authors !== [])
                <p class="detail-actors">
                    <span class="muted">作者</span>
                    @foreach($authors as $i => $authorName)
                        @if($i > 0)<span class="muted"> / </span>@endif
                        <a href="{{ $listUrl(['author' => $authorName]) }}">{{ $authorName }}</a>
                    @endforeach
                </p>
            @endif

            @if($tags !== [])
                <p class="detail-actors">
                    <span class="muted">标签</span>
                    @foreach($tags as $i => $tag)
                        @if($i > 0)<span class="muted"> · </span>@endif
                        <a href="{{ $listUrl(['tag' => $tag]) }}">{{ $tag }}</a>
                    @endforeach
                </p>
            @endif

            @if($manga->remarks)
                <p class="muted">{{ $manga->remarks }}</p>
            @endif

            @if($manga->content)
                <div class="desc detail-desc">{!! nl2br(e($manga->content)) !!}</div>
            @endif

            <div class="detail-actions manga-actions">
                @if($first)
                    <a class="btn-play" href="{{ url('/manga/'.$manga->id.'/'.$first->id) }}">开始阅读</a>
                    @if($continueId > 0 && $continueId !== (int) $first->id)
                        <a class="btn-ghost" href="{{ url('/manga/'.$manga->id.'/'.$continueId) }}">继续阅读</a>
                    @else
                        <a class="btn-ghost" id="manga-continue" hidden href="{{ url('/manga/'.$manga->id.'/'.$first->id) }}">继续阅读</a>
                    @endif
                @endif
                @if($last && $first && (int) $last->id !== (int) $first->id)
                    <a class="btn-ghost" href="{{ url('/manga/'.$manga->id.'/'.$last->id) }}">最新 {{ $last->name ?: ('第'.$last->id.'话') }}</a>
                @endif
                @auth('member')
                    <form method="post" action="{{ url('/manga/'.$manga->id.'/favor') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn-ghost">{{ $favored ? '移出书架' : '加入书架' }}</button>
                    </form>
                @else
                    <a class="btn-ghost" href="{{ url('/member/login') }}">登录后加入书架</a>
                @endauth
            </div>
        </div>
    </div>

    <section class="play-panel manga-chapter-panel">
        <div class="sec-head">
            <h2>章节</h2>
            @if($chapterCount > 0)
                <span class="muted">共 {{ $chapterCount }} 话</span>
            @endif
        </div>
        @if($chapterCount > 0)
            <p class="manga-eps-status muted">
                {{ $manga->serializeLabel() }}
                @if($latestName !== '') · 最新 {{ $latestName }}@endif
                @if($updatedAt > 0) · {{ date('Y-m-d', $updatedAt) }}@endif
                <button type="button" class="btn-link" id="manga-rev">倒序</button>
            </p>
        @endif
        @if($manga->chapters->isEmpty())
            <div class="list-empty" style="margin:8px 0">
                <p>还没有章节</p>
                <p><a class="btn-link" href="{{ url('/manga') }}">去逛逛漫画</a></p>
            </div>
        @else
            <div class="eps manga-eps{{ $collapsed ? ' is-collapsed' : '' }}" id="manga-eps" data-collapse-at="{{ $collapseAt }}">
                @foreach($manga->chapters as $ep)
                    <a href="{{ url('/manga/'.$manga->id.'/'.$ep->id) }}">{{ $ep->name ?: ('第'.$ep->id.'话') }}{{ (int) ($ep->vip ?? 0) === 1 ? ' ·VIP' : '' }}</a>
                @endforeach
            </div>
            @if($collapsed)
                <p class="manga-eps-more"><button type="button" class="btn-link" id="manga-eps-expand">展开全部章节</button></p>
            @endif
        @endif
    </section>

    @if($related->isNotEmpty())
        <section class="home-sec">
            <div class="sec-head"><h2>{{ $relatedTitle }}</h2></div>
            <div class="grid">
                @foreach($related as $item)
                    @include('manga::partials.card', ['row' => $item])
                @endforeach
            </div>
        </section>
    @endif

    <section class="home-sec detail-engage">
        <div class="sec-head"><h2>评论</h2><span class="muted">{{ $commentCount }}</span></div>
        <div class="detail-comment-box">
            <form method="post" action="{{ url('/manga/'.$manga->id.'/comment') }}" class="comment-form manga-comment-form">
                @csrf
                @guest('member')
                    <label class="auth-field">
                        <span>昵称</span>
                        <input name="author_name" placeholder="怎么称呼你" maxlength="32">
                    </label>
                @endguest
                <label class="auth-field">
                    <span>内容</span>
                    <textarea name="content" rows="4" required maxlength="2000" placeholder="写评论…"></textarea>
                </label>
                <div class="auth-actions">
                    <button type="submit" class="btn-play btn-sm">发表评论</button>
                </div>
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
    var expand = document.getElementById('manga-eps-expand');
    if (expand && eps) {
        expand.addEventListener('click', function () {
            eps.classList.remove('is-collapsed');
            var wrap = expand.closest('.manga-eps-more');
            if (wrap) wrap.remove();
            else expand.remove();
        });
    }
})();
</script>
@endpush
