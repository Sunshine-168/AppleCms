@php
    $item = $item ?? [];
    $q = admin_t((string) ($item['label'] ?? '')).' '.admin_t((string) ($item['hint'] ?? ''));
    if (! empty($item['keywords'])) {
        $q .= ' '.admin_t((string) $item['keywords']);
    }
    $tag = (string) ($item['tag'] ?? '');
@endphp
<a class="more-tile{{ $tag !== '' ? ' is-'.$tag : '' }}" href="{{ $item['url'] ?? '#' }}" data-q="{{ $q }}">
    <strong>{{ admin_t((string) ($item['label'] ?? '')) }}</strong>
    @if(! empty($item['hint']))
        <span>{{ admin_t((string) $item['hint']) }}</span>
    @endif
    @if($tag === 'plugin')
        <em class="more-tag">{{ admin_t('more.plugin_tag') }}</em>
    @elseif($tag === 'moved')
        <em class="more-tag">{{ admin_t('more.moved_tag') }}</em>
    @endif
</a>
