<form class="tag-form" data-desk="works">
    <input type="hidden" name="desk" value="works">
    <h3>基本</h3>
    <label>名称</label>
    <input name="title" required autofocus>
    <label>作者</label>
    <input name="author" placeholder="作者 / 笔名">
    <label>标签</label>
    <input name="tags" placeholder="逗号分隔，如 都市,穿越">
    <p class="muted field-hint">多个标签用逗号隔开，前台可按标签筛选。</p>
    <label>分类</label>
    <select name="type_id">
        <option value="0">未分类</option>
        @foreach($types as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
        @endforeach
    </select>
    <label>封面</label>
    <input name="cover" placeholder="图片地址">
    <label>备注</label>
    <input name="remarks">
    <label>简介</label>
    <textarea name="content" rows="5"></textarea>

    <h3>发布</h3>
    <label>连载</label>
    <select name="serialize">
        <option value="0">连载</option>
        <option value="1">完结</option>
    </select>
    <label>状态</label>
    <select name="status">
        <option value="1">上架</option>
        <option value="0">下架</option>
    </select>
    <label>审核</label>
    <select name="yid">
        <option value="0">已审</option>
        <option value="1">待审</option>
    </select>
    <label>人气</label>
    <input name="hits" type="number" value="0" min="0">
    <label>排序</label>
    <input name="sort" type="number" value="0" min="0">
</form>
