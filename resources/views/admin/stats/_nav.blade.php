<p class="toolbar">
    <a class="btn btn-muted{{ ($active ?? '') === 'traffic' ? ' is-on' : '' }}" href="{{ route('admin.stats.index') }}">{{ admin_t('page.traffic') }}</a>
    <a class="btn btn-muted{{ ($active ?? '') === 'spiders' ? ' is-on' : '' }}" href="{{ route('admin.stats.spiders') }}">{{ admin_t('page.spiders') }}</a>
    <a class="btn btn-muted{{ ($active ?? '') === 'logs' ? ' is-on' : '' }}" href="{{ route('admin.stats.logs') }}">{{ admin_t('page.detail') }}</a>
</p>
