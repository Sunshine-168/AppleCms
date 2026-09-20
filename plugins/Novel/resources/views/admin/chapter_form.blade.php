<form class="admin-form tag-form" data-desk="chapters">
    <input type="hidden" name="desk" value="chapters">
    <h3>{{ admin_t('ui.section_basic') }}</h3>
    <label>{{ admin_t('ui.works') }}</label>
    <select name="novel_id" required>
        <option value="">{{ admin_t('novel.select_work') }}</option>
        @foreach($works as $work)
            <option value="{{ $work->id }}">{{ $work->title }}</option>
        @endforeach
    </select>
    <label>{{ admin_t('ui.chapter_name') }}</label>
    <input type="text" name="name" required placeholder="{{ admin_t('novel.ph_chapter') }}">
    <label>{{ admin_t('ui.body_text') }}</label>
    <textarea name="content" rows="12" placeholder="{{ admin_t('ui.ph_chapter_body_opt') }}"></textarea>
    <p class="muted field-hint">{{ admin_t('ui.novel_chapter_hint') }}</p>

    <h3>{{ admin_t('ui.publish') }}</h3>
    <label>{{ admin_t('ui.sort') }}</label>
    <input type="number" name="sort" value="0" min="0">
    <label>{{ admin_t('ui.access_perm') }}</label>
    <select name="vip">
        <option value="0">{{ admin_t('ui.free') }}</option>
        <option value="1">{{ admin_t('ui.vip_readable') }}</option>
    </select>
</form>
