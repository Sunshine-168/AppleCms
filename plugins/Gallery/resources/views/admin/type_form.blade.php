<form class="admin-form tag-form" data-desk="types">
    <input type="hidden" name="desk" value="types">
    <label>名称</label>
    <input type="text" name="name" required autofocus placeholder="如 资讯、公告">
    <p class="muted field-hint">分类名会出现在前台筛选和后台列表。</p>
    <label>别名</label>
    <input type="text" name="slug" placeholder="可空，按名称生成">
    <p class="muted field-hint">英文、数字和短横线。留空则保存时自动生成。</p>
    <label>排序</label>
    <input name="sort" type="number" value="0" min="0">
    <p class="muted field-hint">数字越大越靠前。</p>
    <label>状态</label>
    <select name="status">
        <option value="1">显示</option>
        <option value="0">隐藏</option>
    </select>
</form>
