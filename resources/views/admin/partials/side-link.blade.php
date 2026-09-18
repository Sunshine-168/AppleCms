@php
    $url = (string) ($url ?? '');
    $icon = trim((string) ($icon ?? ''));
    $label = (string) ($label ?? '');
    $active = (bool) ($active ?? false);
    $force = (bool) ($force ?? false);
    $allowed = is_object($nav ?? null) ? ($nav->allowed ?? []) : [];
    $founder = (int) session('admin_uid', 0) === 1;
    $path = explode('?', explode('#', $url)[0])[0];
    $ok = $force || $founder || ($url !== '' && (isset($allowed[$url]) || ($path !== '' && isset($allowed[$path]))));
    if ($ok && is_object($nav ?? null)) {
        $nav->shown[$url] = true;
    }
@endphp
@if($ok)
<a href="{{ $url }}" @class(['active' => $active, 'no-icon' => $icon === ''])>
    @if($icon !== '')
        <i class="fas fa-{{ $icon }}" aria-hidden="true"></i>
    @endif
    <span>{{ $label }}</span>
</a>
@endif
