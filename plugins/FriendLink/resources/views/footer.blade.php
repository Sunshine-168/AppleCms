@php
    $links = app(\Plugins\FriendLink\Services\FriendLinkService::class);
    $listed = $links->ready() ? $links->listed() : collect();
    $allowApply = $links->ready() && $links->options()['allow_apply'] === 1;
@endphp
@if($listed->isNotEmpty() || $allowApply)
<div class="flink">
    @foreach($listed as $item)
        <a href="{{ url('/links/go/'.$item->id) }}" target="_blank" rel="nofollow">{{ $item->name }}</a>
    @endforeach
    @if($allowApply)
        <a href="{{ url('/links/apply') }}">申请友链</a>
    @endif
</div>
@endif
@if($links->ready())
<script>
(function () {
    var ref = document.referrer || '';
    if (!ref) return;
    var token = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
    fetch(@json(url('/links/hit')), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({referer: ref})
    }).catch(function () {});
})();
</script>
@endif
