<form class="tag-form" data-desk="types">
    <input type="hidden" name="desk" value="types">
    <label>名称</label>
    <input name="name" required autofocus>
    <label>别名</label>
    <input name="slug" placeholder="可选">
    <label>排序</label>
    <input name="sort" type="number" value="0" min="0">
    <label>状态</label>
    <select name="status">
        <option value="1">显示</option>
        <option value="0">隐藏</option>
    </select>
</form>
