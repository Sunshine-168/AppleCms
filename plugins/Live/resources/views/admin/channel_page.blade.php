@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑频道' : '新增频道')

@php
    $channel = is_array($channel ?? null) ? $channel : [];
    $isEdit = (bool) ($isEdit ?? false);
    $categories = $categories ?? collect();
    $hasRecommend = (bool) ($hasRecommend ?? false);
    $title = (string) ($channel['title'] ?? '');
    $sub = (string) ($channel['sub'] ?? '');
    $slug = (string) ($channel['slug'] ?? '');
    $cover = trim((string) ($channel['cover'] ?? ''));
    $urls = (string) ($channel['urls'] ?? '');
    $cateId = (int) ($channel['cate_id'] ?? 0);
    $hits = (int) ($channel['hits'] ?? 0);
    $recommend = (int) ($channel['recommend'] ?? 0);
    $sort = (int) ($channel['sort'] ?? 0);
    $status = (string) ($channel['status'] ?? '1');
    $remarks = (string) ($channel['remarks'] ?? '');
    $content = (string) ($channel['content'] ?? '');
    $id = (int) ($channel['id'] ?? 0);
    $frontUrl = trim((string) ($channel['front_url'] ?? ''));
    $back = ((string) $status === '0' && ! $isEdit) ? '/admin/video/lives?desk=pending' : '/admin/video/lives';
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑频道' : '新增频道' }}@if($isEdit && $title !== '') <em>{{ $title }}</em>@endif</span>
        <div>
            @if($isEdit && $frontUrl !== '')
                <a class="btn btn-muted btn-sm" href="{{ $frontUrl }}" target="_blank" rel="noopener">前台</a>
            @endif
            <a class="btn btn-muted btn-sm" href="{{ $back }}">返回频道</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">IPTV 频道目录，不是用户直播间。播放地址用苹果风格：高清$https://…m3u8，多线路用 # 或换行分隔。推荐用 HLS（.m3u8）。</p>
        <form class="tag-form" id="live-channel-form" style="max-width:720px">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="channels">
            <input type="hidden" name="play_from" value="hls">

            <h3>基本信息</h3>
            <label for="ch-title">频道名</label>
            <input id="ch-title" type="text" name="title" value="{{ $title }}" required autofocus placeholder="如 CCTV-1 综合">
            <label for="ch-sub">副标题</label>
            <input id="ch-sub" type="text" name="sub" value="{{ $sub }}" placeholder="可选，如 高清 / 卫视">
            <label for="ch-cate">分类</label>
            <select id="ch-cate" name="cate_id">
                <option value="0">未分类</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($cateId === (int) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">没有合适分类？去 <a href="/admin/video/live-categories/create" target="_blank" rel="noopener">新建分类</a>。</p>

            <label for="ch-cover">封面</label>
            <div class="field-inline">
                <input id="ch-cover" type="text" name="cover" value="{{ $cover }}" placeholder="图片地址，可空">
                <button type="button" class="btn btn-sm" id="ch-cover-pick">上传</button>
            </div>
            <img class="img-preview" id="ch-cover-preview" alt="" @if($cover === '') style="display:none" @else src="{{ $cover }}" @endif>
            <p class="muted field-hint">列表卡片用。可粘贴地址或点上传。</p>

            <h3>播放</h3>
            <label for="ch-urls">播放地址</label>
            <textarea id="ch-urls" name="urls" rows="6" placeholder="高清$https://example.com/live.m3u8&#10;备用$https://example.com/backup.m3u8">{{ $urls }}</textarea>
            <p class="muted field-hint">格式：线路名$地址。多线路用 # 或每行一条。推荐 .m3u8（HLS）；其它直链也会尝试用浏览器播放。</p>

            <h3>展示</h3>
            @if($hasRecommend)
                <label for="ch-rec">推荐等级</label>
                <input id="ch-rec" type="number" name="recommend" min="0" max="9" value="{{ $recommend }}">
                <p class="muted field-hint">0 不推荐；1–9 越大越靠前，会出现在前台「推荐」。</p>
            @endif
            <label for="ch-sort">排序</label>
            <input id="ch-sort" type="number" name="sort" value="{{ $sort }}">
            <p class="muted field-hint">同推荐等级下，数字越大越靠前。</p>
            <label for="ch-hits">人气</label>
            <input id="ch-hits" type="number" name="hits" min="0" value="{{ $hits }}">
            <p class="muted field-hint">打开播放页会自动累加，一般不用手改。</p>
            <label for="ch-status">状态</label>
            <select id="ch-status" name="status">
                <option value="1" @selected($status === '1')>上架</option>
                <option value="0" @selected($status === '0')>待审 / 下架</option>
            </select>
            <label for="ch-slug">网址标识</label>
            <input id="ch-slug" type="text" name="slug" value="{{ $slug }}" placeholder="可空，按名称生成">
            <label for="ch-remarks">备注</label>
            <input id="ch-remarks" type="text" name="remarks" value="{{ $remarks }}" placeholder="前台一行说明，可空">
            <label for="ch-content">简介</label>
            <textarea id="ch-content" name="content" rows="4" placeholder="可选">{{ $content }}</textarea>

            <div class="form-actions">
                <button type="submit" class="btn" id="ch-save">保存</button>
                <a class="btn btn-muted" href="{{ $back }}">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('live-channel-form');
    var isEdit = !!String(form.id.value || '').trim();
    U.bindImageField(form, { input: '#ch-cover', btn: '#ch-cover-pick', preview: '#ch-cover-preview' });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.title || '').trim()) { U.toast('请填写频道名', 'err'); return; }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/lives/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return; }
            U.toast('已保存', 'ok');
            var id = (res.data && res.data.id) || data.id;
            if (!isEdit && id) location.href = '/admin/video/live-channels/' + encodeURIComponent(id) + '/edit';
            else location.href = '/admin/video/lives';
        }).catch(function () { U.loading(false); U.toast('保存失败', 'err'); });
    });
})();
</script>
@endpush
