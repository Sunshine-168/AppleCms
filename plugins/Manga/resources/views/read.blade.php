@extends('themes.default.layout')
@if($next ?? null)
@push('head')
    <link rel="prefetch" href="{{ url('/manga/'.$manga->id.'/'.$next->id) }}">
@endpush
@endif
@section('content')
    @php
        $prev = $prev ?? null;
        $next = $next ?? null;
        $epName = $chapter->name ?: ('第'.$chapter->id.'话');
        $pics = array_values(array_filter(array_map('strval', $pics ?? [])));
        $picCount = count($pics);
        $catalogUrl = url('/manga/'.$manga->id);
        $gate = is_array($gate ?? null) ? $gate : ['locked' => false, 'vip' => false, 'total' => $picCount, 'trysee' => 0, 'need_login' => false, 'need_vip' => false];
        $totalPics = (int) ($gate['total'] ?? $picCount);
        $favored = (bool) ($favored ?? false);
    @endphp
    <p class="breadcrumb">
        <a href="{{ url('/manga') }}">漫画</a> /
        <a href="{{ $catalogUrl }}">{{ $manga->title }}</a> /
        {{ $epName }}
        @if(!empty($gate['vip'])) <span class="manga-badge">VIP</span>@endif
    </p>
    <div class="manga-read-bar">
        <h1 class="manga-read-title">{{ $manga->title }} <span class="muted">· {{ $epName }}</span></h1>
        <nav class="manga-read-nav is-sticky" id="manga-toolbar" aria-label="阅读工具">
            @if($prev)
                <a class="btn-ghost" id="manga-prev" href="{{ url('/manga/'.$manga->id.'/'.$prev->id) }}">上一话</a>
            @else
                <span class="muted manga-read-edge">没有上一话</span>
            @endif
            <span class="manga-read-tools">
                <a class="btn-ghost" href="{{ $catalogUrl }}" id="manga-catalog">目录</a>
                @auth('member')
                    <form method="post" action="{{ url('/manga/'.$manga->id.'/favor') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn-ghost">{{ $favored ? '移出书架' : '加入书架' }}</button>
                    </form>
                @else
                    <a class="btn-ghost" href="{{ url('/member/login') }}">登录收藏</a>
                @endauth
                <button type="button" class="btn-ghost" id="manga-mode" aria-pressed="false">页漫</button>
                <button type="button" class="btn-ghost" id="manga-night">夜间</button>
                <span class="muted manga-read-prog" id="manga-progress">{{ $picCount > 0 ? '1 / '.$picCount : '0 / 0' }}@if(!empty($gate['locked']) && $totalPics > $picCount) · 共 {{ $totalPics }} 页@endif</span>
            </span>
            @if($next)
                <a class="btn-ghost" id="manga-next" href="{{ url('/manga/'.$manga->id.'/'.$next->id) }}">下一话</a>
            @else
                <span class="muted manga-read-edge">没有下一话</span>
            @endif
        </nav>
    </div>
    @if(!empty($gate['locked']))
        <div class="flash" style="margin:0 0 16px">
            @if(!empty($gate['need_login']))
                <p>本话为 VIP 章节。可试看前 {{ (int) ($gate['trysee'] ?? 0) }} 页，<a href="{{ url('/member/login') }}">登录</a>并开通会员组后可读全文。</p>
            @else
                <p>本话为 VIP 章节。可试看前 {{ (int) ($gate['trysee'] ?? 0) }} 页，请到 <a href="{{ url('/member') }}">会员中心</a> 开通对应会员组后继续阅读。</p>
            @endif
            @if(app(\App\Support\Plugins\PluginManager::class)->isEnabled('pay'))
                <p class="muted"><a href="{{ url('/pay') }}">去充值 / 开通</a></p>
            @endif
        </div>
    @endif
    @if($manga->chapters->isNotEmpty())
        <details class="manga-chapter-drawer">
            <summary>本话目录</summary>
            <div class="eps">
                @foreach($manga->chapters as $ep)
                    <a href="{{ url('/manga/'.$manga->id.'/'.$ep->id) }}"@if((int) $ep->id === (int) $chapter->id) class="on"@endif>{{ $ep->name ?: ('第'.$ep->id.'话') }}{{ (int) ($ep->vip ?? 0) === 1 ? ' ·VIP' : '' }}</a>
                @endforeach
            </div>
        </details>
    @endif
    <div class="manga-read is-scroll" id="manga-read" data-mode="scroll">
        @forelse($pics as $i => $src)
            <figure class="manga-page" data-page="{{ $i }}">
                <img src="{{ $src }}" alt="" loading="{{ $i < 2 ? 'eager' : 'lazy' }}" data-src="{{ $src }}">
                <button type="button" class="btn-link manga-retry" hidden>加载失败，点此重试</button>
            </figure>
        @empty
            <p class="muted">这一话还没有图片地址。</p>
        @endforelse
    </div>
    @if(!empty($gate['locked']) && $totalPics > $picCount)
        <div class="manga-vip-lock" style="text-align:center;padding:24px 12px;margin:16px 0;border-top:1px dashed #333">
            <p>试看结束（已显示 {{ $picCount }} / {{ $totalPics }} 页）</p>
            @if(!empty($gate['need_login']))
                <p><a class="btn-link" href="{{ url('/member/login') }}">登录后开通会员继续读</a></p>
            @else
                <p><a class="btn-link" href="{{ url('/member') }}">开通会员组继续读</a></p>
            @endif
        </div>
    @endif
    <p class="manga-read-nav">
        @if($prev)
            <a href="{{ url('/manga/'.$manga->id.'/'.$prev->id) }}">上一话</a>
        @else
            <span class="muted">没有上一话</span>
        @endif
        <a href="{{ $catalogUrl }}">目录</a>
        @if($next)
            <a href="{{ url('/manga/'.$manga->id.'/'.$next->id) }}">下一话</a>
        @else
            <span class="muted">没有下一话</span>
        @endif
    </p>
    <button type="button" class="manga-gotop" id="manga-gotop" hidden>顶部</button>
@endsection
@push('scripts')
<script>
(function () {
    var mangaId = @json((int) $manga->id);
    var chapterId = @json((int) $chapter->id);
    var epName = @json($epName, JSON_UNESCAPED_UNICODE);
    var title = @json((string) $manga->title);
    var catalogUrl = @json($catalogUrl, JSON_UNESCAPED_UNICODE);
    var total = @json($picCount, JSON_UNESCAPED_UNICODE);
    var pages = Array.prototype.slice.call(document.querySelectorAll('#manga-read .manga-page'));
    var readEl = document.getElementById('manga-read');
    var modeBtn = document.getElementById('manga-mode');
    var progressEl = document.getElementById('manga-progress');
    var prev = document.getElementById('manga-prev');
    var next = document.getElementById('manga-next');
    var pageIndex = 0;
    var modeKey = 'manga_read_mode';
    var histKey = 'manga_history';
    var progKey = 'manga_progress';
    var nightKey = 'manga_night';

    function saveHistory(page) {
        try {
            var map = JSON.parse(localStorage.getItem(histKey) || '{}');
            map[String(mangaId)] = {
                chapter: chapterId,
                name: epName,
                title: title,
                page: page,
                at: Date.now()
            };
            localStorage.setItem(histKey, JSON.stringify(map));
            var prog = JSON.parse(localStorage.getItem(progKey) || '{}');
            prog[String(mangaId)] = { chapter: chapterId, page: page, at: Date.now() };
            localStorage.setItem(progKey, JSON.stringify(prog));
        } catch (e) {}
    }

    function setProgress(i) {
        pageIndex = Math.max(0, Math.min(total - 1, i));
        if (progressEl) {
            progressEl.textContent = total > 0 ? ((pageIndex + 1) + ' / ' + total) : '0 / 0';
        }
        saveHistory(pageIndex);
    }

    function applyMode(mode) {
        mode = mode === 'page' ? 'page' : 'scroll';
        if (!readEl) return;
        readEl.setAttribute('data-mode', mode);
        readEl.classList.toggle('is-page', mode === 'page');
        readEl.classList.toggle('is-scroll', mode === 'scroll');
        if (modeBtn) {
            modeBtn.textContent = mode === 'page' ? '条漫' : '页漫';
            modeBtn.setAttribute('aria-pressed', mode === 'page' ? 'true' : 'false');
            modeBtn.title = mode === 'page' ? '切换到条漫（竖滑）' : '切换到页漫（左右翻）';
        }
        if (mode === 'page') {
            showPage(pageIndex);
        } else {
            pages.forEach(function (el) { el.hidden = false; });
        }
        try { localStorage.setItem(modeKey, mode); } catch (e) {}
    }

    function showPage(i) {
        if (total < 1) return;
        setProgress(i);
        pages.forEach(function (el, idx) {
            el.hidden = idx !== pageIndex;
        });
        var img = pages[pageIndex] && pages[pageIndex].querySelector('img');
        if (img && img.dataset.src && (!img.getAttribute('src') || img.naturalWidth === 0)) {
            img.src = img.dataset.src;
        }
        // Prefetch next page in-chapter
        var n = pages[pageIndex + 1];
        if (n) {
            var nimg = n.querySelector('img');
            if (nimg && nimg.dataset.src) {
                var pre = new Image();
                pre.src = nimg.dataset.src;
            }
        }
        window.scrollTo({ top: 0 });
    }

    function goPage(delta) {
        if (total < 1) return;
        var nextIdx = pageIndex + delta;
        if (nextIdx < 0) {
            if (prev) location.href = prev.href;
            return;
        }
        if (nextIdx >= total) {
            if (next) location.href = next.href;
            return;
        }
        showPage(nextIdx);
    }

    // Restore page within chapter
    try {
        var prog = JSON.parse(localStorage.getItem(progKey) || '{}');
        var row = prog[String(mangaId)];
        if (row && Number(row.chapter) === chapterId && Number(row.page) >= 0) {
            pageIndex = Math.min(total - 1, Math.max(0, Number(row.page) || 0));
        }
    } catch (e) {}
    setProgress(pageIndex);

    var savedMode = 'scroll';
    try { savedMode = localStorage.getItem(modeKey) || 'scroll'; } catch (e) {}
    applyMode(savedMode);
    if (modeBtn) {
        modeBtn.addEventListener('click', function () {
            applyMode(readEl.getAttribute('data-mode') === 'page' ? 'scroll' : 'page');
        });
    }

    // Image retry
    pages.forEach(function (fig) {
        var img = fig.querySelector('img');
        var retry = fig.querySelector('.manga-retry');
        if (!img) return;
        img.addEventListener('error', function () {
            if (retry) retry.hidden = false;
        });
        img.addEventListener('load', function () {
            if (retry) retry.hidden = true;
        });
        if (retry) {
            retry.addEventListener('click', function () {
                var src = img.dataset.src || img.getAttribute('src') || '';
                if (!src) return;
                img.src = src + (src.indexOf('?') >= 0 ? '&' : '?') + '_r=' + Date.now();
                retry.hidden = true;
            });
        }
    });

    // Scroll mode: track visible page
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            if (!readEl || readEl.getAttribute('data-mode') !== 'scroll') return;
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                var i = Number(en.target.getAttribute('data-page') || 0);
                setProgress(i);
            });
        }, { rootMargin: '-35% 0px -45% 0px', threshold: 0.01 });
        pages.forEach(function (el) { io.observe(el); });
    }

    var nightBtn = document.getElementById('manga-night');
    function applyNight(on) {
        document.body.classList.toggle('manga-night', !!on);
        if (nightBtn) nightBtn.textContent = on ? '日间' : '夜间';
    }
    applyNight(localStorage.getItem(nightKey) === '1');
    if (nightBtn) {
        nightBtn.addEventListener('click', function () {
            var on = !document.body.classList.contains('manga-night');
            localStorage.setItem(nightKey, on ? '1' : '0');
            applyNight(on);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.target && /input|textarea|select/i.test(e.target.tagName)) return;
        var m = readEl ? readEl.getAttribute('data-mode') : 'scroll';
        if (e.key === 'ArrowLeft' || e.key === 'a' || e.key === 'A') {
            e.preventDefault();
            if (m === 'page') goPage(-1);
            else if (prev) location.href = prev.href;
        } else if (e.key === 'ArrowRight' || e.key === 'd' || e.key === 'D') {
            e.preventDefault();
            if (m === 'page') goPage(1);
            else if (next) location.href = next.href;
        } else if (e.key === 'n' || e.key === 'N') {
            if (nightBtn) nightBtn.click();
        } else if (e.key === 'm' || e.key === 'M') {
            if (modeBtn) modeBtn.click();
        } else if (e.key === 'c' || e.key === 'C') {
            location.href = catalogUrl;
        }
    });

    // Click sides in page mode
    if (readEl) {
        readEl.addEventListener('click', function (e) {
            if (readEl.getAttribute('data-mode') !== 'page') return;
            if (e.target.closest('a,button')) return;
            var rect = readEl.getBoundingClientRect();
            var x = e.clientX - rect.left;
            if (x < rect.width * 0.35) goPage(-1);
            else if (x > rect.width * 0.65) goPage(1);
        });
    }

    var topBtn = document.getElementById('manga-gotop');
    window.addEventListener('scroll', function () {
        if (topBtn) topBtn.hidden = window.scrollY < 400;
    });
    if (topBtn) topBtn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

    saveHistory(pageIndex);
})();
</script>
@endpush
