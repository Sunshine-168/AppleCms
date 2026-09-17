@php
    $current = \App\Support\AdminNav::currentModule();
@endphp
<nav class="mod-nav {{ $class ?? '' }}" aria-label="{{ admin_t('nav.workspaces') }}">
    @foreach(\App\Support\AdminNav::modules() as $mod)
        <a href="{{ $mod['home'] }}" @class(['is-on' => ($mod['id'] ?? '') === $current])>{{ admin_t($mod['label']) }}</a>
    @endforeach
</nav>
