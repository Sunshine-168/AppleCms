<form class="admin-form tag-form" data-desk="types">
    <input type="hidden" name="desk" value="types">
    <label>{{ admin_t('ui.name') }}</label>
    <input type="text" name="name" required autofocus placeholder="{{ admin_t('ui.ph_type_novel') }}">
    <p class="muted field-hint">{{ admin_t('ui.type_name_front_hint') }}</p>
    <label>{{ admin_t('ui.alias') }}</label>
    <input type="text" name="slug" placeholder="{{ admin_t('ui.ph_slug_from_name') }}">
    <p class="muted field-hint">{{ admin_t('ui.slug_auto_hint') }}</p>
    <label>{{ admin_t('ui.sort') }}</label>
    <input name="sort" type="number" value="0" min="0">
    <p class="muted field-hint">{{ admin_t('ui.sort_front_hint') }}</p>
    <label>{{ admin_t('ui.status') }}</label>
    <select name="status">
        <option value="1">{{ admin_t('ui.show') }}</option>
        <option value="0">{{ admin_t('ui.hide') }}</option>
    </select>
</form>
