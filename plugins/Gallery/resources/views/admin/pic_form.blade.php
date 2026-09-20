<form class="admin-form tag-form" data-desk="pics">
    <input type="hidden" name="desk" value="pics">
    <h3>{{ admin_t('ui.section_basic') }}</h3>
    <label>{{ admin_t('gallery.title') }}</label>
    <select name="gallery_id" required>
        <option value="">{{ admin_t('gallery.select_gallery') }}</option>
        @foreach($works as $w)
            <option value="{{ $w->id }}">{{ $w->title }}</option>
        @endforeach
    </select>
    <label>{{ admin_t('ui.ph_pic') }}</label>
    <input type="text" name="url" placeholder="{{ admin_t('ui.ph_url_or_path') }}">
    <label>{{ admin_t('ui.title_label') }}</label>
    <input type="text" name="title" placeholder="{{ admin_t('ui.optional') }}">
    <label>{{ admin_t('ui.sort') }}</label>
    <input type="number" name="sort" value="0" min="0">
    <p class="muted field-hint">{{ admin_t('ui.pic_form_hint') }}</p>
</form>
