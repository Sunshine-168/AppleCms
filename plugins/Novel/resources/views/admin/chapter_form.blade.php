<form class="admin-form tag-form" data-desk="chapters">
    <input type="hidden" name="desk" value="chapters">
    <h3>基本</h3>
    <label>作品</label>
    <select name="novel_id" required>
        <option value="">选择作品</option>
        @foreach($works as $work)
            <option value="{{ $work->id }}">{{ $work->title }}</option>
        @endforeach
    </select>
    <label>章节名</label>
    <input type="text" name="name" required placeholder="如 第一章">
    <label>正文</label>
    <textarea name="content" rows="12" placeholder="章节正文（可选）"></textarea>
    <p class="muted field-hint">快捷添加只填章节名；正文、VIP 请在这里改。</p>

    <h3>发布</h3>
    <label>排序</label>
    <input type="number" name="sort" value="0" min="0">
    <label>权限</label>
    <select name="vip">
        <option value="0">免费</option>
        <option value="1">VIP</option>
    </select>
</form>
