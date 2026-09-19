<form class="admin-form tag-form" data-desk="pics">
    <input type="hidden" name="desk" value="pics">
    <h3>基本</h3>
    <label>图集</label>
    <select name="gallery_id" required>
        <option value="">选择图集</option>
        @foreach($works as $w)
            <option value="{{ $w->id }}">{{ $w->title }}</option>
        @endforeach
    </select>
    <label>图片地址</label>
    <input type="text" name="url" placeholder="https:// 或 /upload/...">
    <label>标题</label>
    <input type="text" name="title" placeholder="可选">
    <label>排序</label>
    <input type="number" name="sort" value="0" min="0">
    <p class="muted field-hint">批量粘贴多行地址请用上方快捷添加；这里改单张。</p>
</form>
