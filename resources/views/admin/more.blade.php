@extends('admin.layouts.inner')
@section('title', admin_t('more.title'))

@php
    $catalog = $catalog ?? [];
    $plugins = $plugins ?? [];
    $pluginTotal = (int) ($pluginTotal ?? 0);
@endphp

@section('plain')
<div class="card card-panel more-index">
    <div class="card-header">
        <span>{{ admin_t('more.title') }} <em id="more-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/plugins">{{ admin_t('more.plugins_cta') }}</a>
    </div>
    <div class="card-body">
        <form class="filter-bar more-find" id="more-search" onsubmit="return false;">
            <input type="search" id="more-q" name="q" placeholder="{{ admin_t('more.search') }}" autocomplete="off" aria-label="{{ admin_t('more.search') }}">
        </form>
        <div class="queue-chips" id="more-chips">
            <button type="button" class="chip active" data-block="all">{{ admin_t('more.all') }}</button>
            @foreach($catalog as $block)
                <button type="button" class="chip" data-block="{{ $block['id'] }}">{{ admin_t($block['title']) }}</button>
            @endforeach
        </div>
        <p class="muted recycle-lead">{{ admin_t('more.lead') }}</p>

        @if($pluginTotal > 0)
            <div class="more-plugins">
                <div class="more-plugins-head">
                    <strong>{{ admin_t('more.plugins_on') }}</strong>
                    <span class="muted">{{ count($plugins) }}/{{ $pluginTotal }}</span>
                </div>
                @if(count($plugins) > 0)
                    <div class="more-plugin-row">
                        @foreach($plugins as $plugin)
                            <a class="chip" href="{{ route('admin.plugins.show', $plugin['id']) }}">{{ $plugin['name'] }}</a>
                        @endforeach
                    </div>
                @else
                    <p class="muted">{{ admin_t('more.plugins_none') }}</p>
                @endif
            </div>
        @endif

        @foreach($catalog as $block)
            @php $fold = ! empty($block['fold']); @endphp
            <section class="more-block" data-block="{{ $block['id'] }}" @if($fold) data-fold="1" @endif>
                @if($fold)
                    <details class="more-fold">
                        <summary>
                            <span>{{ admin_t($block['title']) }}</span>
                            @if(! empty($block['hint']))
                                <span class="muted">{{ admin_t($block['hint']) }}</span>
                            @endif
                        </summary>
                        <div class="more-grid">
                            @foreach($block['items'] as $item)
                                @include('admin.partials.more-tile', ['item' => $item])
                            @endforeach
                        </div>
                    </details>
                @else
                    <h2>
                        <span>{{ admin_t($block['title']) }}</span>
                        @if(! empty($block['hint']))
                            <span class="muted">{{ admin_t($block['hint']) }}</span>
                        @endif
                    </h2>
                    <div class="more-grid">
                        @foreach($block['items'] as $item)
                            @include('admin.partials.more-tile', ['item' => $item])
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        <p class="muted more-empty" id="more-empty" hidden>{{ admin_t('more.empty') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var qEl = document.getElementById('more-q');
    var countEl = document.getElementById('more-count');
    var emptyEl = document.getElementById('more-empty');
    var chips = document.querySelectorAll('#more-chips .chip');
    var blockFilter = 'all';

    function textOf(el) {
        return (el.getAttribute('data-q') || '').toLowerCase();
    }
    function apply() {
        var q = ((qEl && qEl.value) || '').trim().toLowerCase();
        var shown = 0;
        document.querySelectorAll('.more-block').forEach(function (sec) {
            var id = sec.getAttribute('data-block') || '';
            var fold = sec.getAttribute('data-fold') === '1';
            var blockOk = blockFilter === 'all' || blockFilter === id;
            var any = 0;
            sec.querySelectorAll('.more-tile').forEach(function (tile) {
                var hit = blockOk && (!q || textOf(tile).indexOf(q) !== -1);
                tile.hidden = !hit;
                if (hit) any++;
            });
            sec.hidden = any === 0;
            shown += any;
            if (fold) {
                var box = sec.querySelector('details');
                if (box) box.open = any > 0 && (q !== '' || blockFilter === id);
            }
        });
        if (countEl) countEl.textContent = shown ? shown : '';
        if (emptyEl) emptyEl.hidden = shown > 0;
        chips.forEach(function (chip) {
            chip.classList.toggle('active', (chip.getAttribute('data-block') || '') === blockFilter);
        });
    }
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            blockFilter = chip.getAttribute('data-block') || 'all';
            apply();
        });
    });
    if (qEl) qEl.addEventListener('input', apply);
    apply();
})();
</script>
@endpush
