@php
    $groups = $groups ?? ['other' => [], 'known' => []];
    $ui = $ui ?? [];
    $last = $last ?? null;
    $others = $groups['other'] ?? [];
    $knowns = $groups['known'] ?? [];
    $scanned = is_array($last) && trim((string) ($last['scanned_at'] ?? '')) !== '';
@endphp
@if($others === [] && $scanned)
    <p class="muted">{{ $ui['none_other'] ?? '' }}</p>
@endif
@if($others !== [])
    <h4>{{ $ui['other'] ?? '' }}</h4>
@endif
@foreach($others as $group)
    <article class="safety-hit is-other">
        <div class="safety-hit-head">
            <strong>{{ $group['file'] ?? '' }}</strong>
            <span class="badge badge-warn">{{ $ui['other'] ?? '' }}</span>
        </div>
        @foreach(($group['hits'] ?? []) as $hit)
            <div class="safety-hit-line">
                <code>{{ $hit['line'] ?? '' }}</code>
                <span class="safety-hit-fn">{{ $hit['needle_label'] ?? ($hit['needle'] ?? '') }}</span>
                @if(($hit['known_hint'] ?? '') !== '')
                    <span class="muted">{{ $hit['known_hint'] }}</span>
                @endif
            </div>
            @if(($hit['snippet'] ?? '') !== '')
                <pre class="safety-snippet">{{ $hit['snippet'] }}</pre>
            @endif
        @endforeach
    </article>
@endforeach
@if($knowns !== [])
    <h4>{{ $ui['known'] ?? '' }}</h4>
    @foreach($knowns as $group)
        <article class="safety-hit is-known">
            <div class="safety-hit-head">
                <strong>{{ $group['file'] ?? '' }}</strong>
                <span class="badge">{{ $ui['known'] ?? '' }}</span>
            </div>
            @foreach(($group['hits'] ?? []) as $hit)
                <div class="safety-hit-line">
                    <code>{{ $hit['line'] ?? '' }}</code>
                    <span class="safety-hit-fn">{{ $hit['needle_label'] ?? ($hit['needle'] ?? '') }}</span>
                    @if(($hit['known_hint'] ?? '') !== '')
                        <span class="muted">{{ $hit['known_hint'] }}</span>
                    @endif
                </div>
                @if(($hit['snippet'] ?? '') !== '')
                    <pre class="safety-snippet">{{ $hit['snippet'] }}</pre>
                @endif
            @endforeach
        </article>
    @endforeach
@endif
