<form class="admin-form tag-form" data-desk="works">
    <input type="hidden" name="desk" value="works">
    <h3>{{ admin_t('ui.section_basic') }}</h3>
    <label>{{ admin_t('ui.name') }}</label>
    <input type="text" name="title" required autofocus>
    <label>{{ admin_t('ui.author_model') }}</label>
    <input type="text" name="author" placeholder="{{ admin_t('ui.ph_author_name') }}">
    <label>{{ admin_t('ui.tags') }}</label>
    <input type="text" name="tags" placeholder="{{ admin_t('ui.pick_tag_ph_short') }}">
    <p class="muted field-hint">{{ admin_t('ui.tags_sync_hint_short') }}</p>
    <label>{{ admin_t('ui.types') }}</label>
    <select name="type_id">
        <option value="0">{{ admin_t('ui.uncategorized') }}</option>
        @foreach($types as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
        @endforeach
    </select>
    <label>{{ admin_t('ui.cover') }}</label>
    <input type="text" name="cover" placeholder="{{ admin_t('ui.ph_image_url') }}">
    <label>{{ admin_t('ui.remarks') }}</label>
    <input type="text" name="remarks">
    <label>{{ admin_t('ui.intro') }}</label>
    <textarea name="content" rows="4"></textarea>

    <h3>{{ admin_t('ui.publish') }}</h3>
    <label>{{ admin_t('ui.status') }}</label>
    <select name="status">
        <option value="1">{{ admin_t('ui.on') }}</option>
        <option value="0">{{ admin_t('ui.off') }}</option>
    </select>
    <label>{{ admin_t('ui.audit') }}</label>
    <select name="yid">
        <option value="0">{{ admin_t('ui.audited') }}</option>
        <option value="1">{{ admin_t('ui.pending') }}</option>
    </select>
    <label>{{ admin_t('ui.hits') }}</label>
    <input name="hits" type="number" value="0" min="0">
    <label>{{ admin_t('ui.sort') }}</label>
    <input name="sort" type="number" value="0" min="0">
</form>
