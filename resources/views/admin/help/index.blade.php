@extends('admin.layouts.inner')
@section('title', admin_t('page.help'))

@php
    $topic = $topic ?? 'use';
    $urls = $urls ?? [];
    $hl = function (string $key, string $label) use ($urls): string {
        $url = $urls[$key] ?? null;

        return $url ? '<a href="'.e($url).'">'.e($label).'</a>' : e($label);
    };
    $groups = [
        admin_t('guide.group_use') => [
            'use' => admin_t('guide.topic_use'),
            'admin' => admin_t('guide.topic_admin'),
            'templates' => admin_t('guide.topic_templates'),
            'tags' => admin_t('guide.topic_tags'),
        ],
        admin_t('guide.group_install') => [
            'env' => admin_t('guide.topic_env'),
            'schedule' => admin_t('guide.topic_schedule'),
            'laravel' => admin_t('guide.topic_laravel'),
            'docker' => admin_t('guide.topic_docker'),
        ],
    ];
    $helpData = match ($topic) {
        'env' => [
            'compact' => false,
            'scheduleUrl' => route('admin.help', ['topic' => 'schedule']),
        ],
        'laravel' => [
            'deployUrl' => route('admin.help', ['topic' => 'env']),
            'webInstallUrl' => url('/install'),
        ],
        default => [],
    };
@endphp

@section('plain')
<div class="card card-panel help-index">
    <div class="card-header"><span>{{ admin_t('nav.help') }}</span></div>
    <div class="card-body">
        <div class="help-page" data-copy-label="{{ admin_t('guide.copy') }}" data-copied-label="{{ admin_t('guide.copied') }}">
            <p class="muted recycle-lead">{{ admin_t('guide.lead') }}</p>

            <div class="help-nav">
                @foreach($groups as $group => $items)
                    <div class="help-nav-group">
                        <span>{{ $group }}</span>
                        <div class="tabs">
                            @foreach($items as $key => $label)
                                <a class="{{ $topic === $key ? 'active' : '' }}" href="{{ route('admin.help', ['topic' => $key]) }}">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="help-body{{ in_array($topic, ['env', 'schedule', 'laravel', 'docker'], true) ? ' deploy-guide' : '' }}">
                @include(admin_help_view($topic), array_merge(['urls' => $urls, 'hl' => $hl], $helpData))
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var page = btn.closest('.help-page');
        var copyLabel = (page && page.getAttribute('data-copy-label')) || '复制';
        var copiedLabel = (page && page.getAttribute('data-copied-label')) || '已复制';
        var code = btn.parentElement && btn.parentElement.querySelector('code');
        if (!code) return;
        var text = code.innerText;
        function done() {
            btn.textContent = copiedLabel;
            setTimeout(function () { btn.textContent = copyLabel; }, 1500);
        }
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(ta);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(fallback);
        } else {
            fallback();
        }
    });
});
</script>
@endpush
