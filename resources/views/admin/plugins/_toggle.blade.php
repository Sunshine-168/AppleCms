<form method="post" action="{{ route('admin.plugins.toggle', $plugin['id']) }}">
    @csrf
    <input type="hidden" name="enabled" value="{{ $plugin['enabled'] ? 0 : 1 }}">
    <button type="submit" class="btn btn-sm{{ $plugin['enabled'] ? ' btn-muted' : ' btn-primary' }}">
        {{ $plugin['enabled'] ? admin_t('plugin.turn_off') : admin_t('plugin.turn_on') }}
    </button>
</form>
