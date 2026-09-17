@php
    $url = (string) ($url ?? '');
    $icon = (string) ($icon ?? 'circle');
    $label = (string) ($label ?? '');
    $active = (bool) ($active ?? false);
    $force = (bool) ($force ?? false);
    $allowed = is_object($nav ?? null) ? ($nav->allowed ?? []) : [];
    $founder = (int) session('admin_uid', 0) === 1;
    $ok = $force || $founder || ($url !== '' && isset($allowed[$url]));
    if ($ok && is_object($nav ?? null)) {
        $nav->shown[$url] = true;
    }
@endphp
@if($ok)
<a href="{{ $url }}" @class(['active' => $active])>
    <i class="fas fa-{{ $icon }}" aria-hidden="true"></i>
    <span>{{ $label }}</span>
</a>
@endif
