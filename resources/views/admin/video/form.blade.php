@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑影片' : '新增影片')

@php
    $video = is_array($video ?? null) ? $video : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $collects = is_array($collects ?? null) ? $collects : [];
    $areas = is_array($areas ?? null) ? $areas : [];
    $langs = is_array($langs ?? null) ? $langs : [];
    $years = is_array($years ?? null) ? $years : [];
    $publishAt = $publishAt ?? '';
    $title = (string) ($video['title'] ?? '');
    $cover = trim((string) ($video['cover'] ?? ''));
    $banner = trim((string) ($video['banner'] ?? ''));
    $typeId = (string) ($video['type_id'] ?? '');
    $collectId = (string) ($video['collect_source_id'] ?? '');
    $status = (string) ($video['status'] ?? ($isEdit ? '1' : '2'));
@endphp

@section('plain')
<div class="card card-panel video-form-page">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑影片' : '新增影片' }}@if($isEdit) <em>{{ $title }}</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">返回列表</a>
            @if($isEdit)
                <a class="btn btn-muted btn-sm" href="/admin/video/sources?video_id={{ (int) ($video['id'] ?? 0) }}">播放线路</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                改资料不会动播放地址。线路和剧集在右边「播放线路」里加。
            @else
                标题必填。没有播放地址时先存成草稿，保存后再去加线路，避免前台点开播不了。
            @endif
        </p>

        <form class="video-form" id="video-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($video['id'] ?? 0) : '' }}">

            <h3>这部片子</h3>
            <label for="video-title">标题</label>
            <input id="video-title" type="text" name="title" value="{{ $title }}" placeholder="前台列表和播放页上的名字" required>
            <label for="video-subtitle">副标题</label>
            <input id="video-subtitle" type="text" name="subtitle" value="{{ $video['subtitle'] ?? '' }}" placeholder="可空，如英文名、别名">
            <label for="video-type">分类</label>
            <select id="video-type" name="type_id">
                <option value="">先不选</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}" @selected($typeId === (string) $type['id'])>{{ $type['name'] }}</option>
                @endforeach
            </select>
            @if($types === [])
                <p class="muted field-hint">还没有分类。<a href="/admin/video/types">先去建一个</a>，前台按分类找片。</p>
            @else
                <p class="muted field-hint">电影、电视剧分开建。没有合适的去「<a href="/admin/video/types">分类</a>」加。</p>
            @endif
            <label for="video-remarks">更新备注</label>
            <input id="video-remarks" type="text" name="remarks" value="{{ $video['remarks'] ?? '' }}" placeholder="如 更新至12集、已完结">
            <p class="muted field-hint">出现在列表里，告诉访客更到哪了。</p>

            <h3>封面</h3>
            <label for="video-cover">海报</label>
            <div class="settings-file-preview video-cover-preview-box">
                <div class="settings-file-thumb video-cover-thumb{{ $cover === '' ? ' is-empty' : '' }}" id="cover-thumb">
                    <img id="cover-img" src="{{ $cover }}" alt="" @if($cover === '') hidden @endif>
                    <span class="settings-file-empty muted" id="cover-empty" @if($cover !== '') hidden @endif>还没有海报</span>
                </div>
                <div class="field-inline">
                    <input id="video-cover" type="text" name="cover" value="{{ $cover }}" placeholder="图片地址，或点上传">
                    <button type="button" class="btn btn-muted" id="cover-upload">上传</button>
                </div>
            </div>
            <p class="muted field-hint">竖图，列表和详情页用。没有图时列表会标「无封面」。</p>

            <label for="video-banner">横幅</label>
            <div class="settings-file-preview">
                <div class="settings-file-thumb video-banner-thumb{{ $banner === '' ? ' is-empty' : '' }}" id="banner-thumb">
                    <img id="banner-img" src="{{ $banner }}" alt="" @if($banner === '') hidden @endif>
                    <span class="settings-file-empty muted" id="banner-empty" @if($banner !== '') hidden @endif>可空</span>
                </div>
                <div class="field-inline">
                    <input id="video-banner" type="text" name="banner" value="{{ $banner }}" placeholder="宽图地址，可空">
                    <button type="button" class="btn btn-muted" id="banner-upload">上传</button>
                </div>
            </div>
            <p class="muted field-hint">首页大图用。没有就只显示海报。</p>

            <h3>资料</h3>
            <div class="settings-two">
                <div>
                    <label for="video-year">年份</label>
                    <input id="video-year" type="text" name="year" value="{{ $video['year'] ?? '' }}" list="video-year-list" placeholder="2024">
                    @if($years !== [])
                        <datalist id="video-year-list">
                            @foreach($years as $year)
                                <option value="{{ $year }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>
                <div>
                    <label for="video-area">地区</label>
                    <input id="video-area" type="text" name="area" value="{{ $video['area'] ?? '' }}" list="video-area-list" placeholder="如 大陆">
                    @if($areas !== [])
                        <datalist id="video-area-list">
                            @foreach($areas as $area)
                                <option value="{{ $area }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>
            </div>
            <div class="settings-two">
                <div>
                    <label for="video-lang">语言</label>
                    <input id="video-lang" type="text" name="lang" value="{{ $video['lang'] ?? '' }}" list="video-lang-list" placeholder="如 国语">
                    @if($langs !== [])
                        <datalist id="video-lang-list">
                            @foreach($langs as $lang)
                                <option value="{{ $lang }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>
                <div>
                    <label for="video-weekday">更新周期</label>
                    <input id="video-weekday" type="text" name="weekday" value="{{ $video['weekday'] ?? '' }}" placeholder="一,三,五">
                </div>
            </div>
            <p class="muted field-hint">地区、语言、年份会进分类页筛选。词库在「<a href="/admin/video/settings?tab=more">站点设置 → 更多</a>」。</p>
            <label for="video-director">导演</label>
            <input id="video-director" type="text" name="director" value="{{ $video['director'] ?? '' }}">
            <label for="video-actors">主演</label>
            <input id="video-actors" type="text" name="actors_text" value="{{ $video['actors_text'] ?? '' }}" placeholder="逗号分开，如 张三,李四">
            <p class="muted field-hint">保存时会写进演员库，可在演员页继续补资料。</p>
            <label for="video-tags">标签</label>
            <input id="video-tags" type="text" name="tags_text" value="{{ $video['tags_text'] ?? '' }}" placeholder="逗号分开，如 动作,犯罪">

            <h3>简介</h3>
            <label for="video-desc">剧情</label>
            <textarea id="video-desc" name="description" rows="8" placeholder="一两段即可，不要整集台词">{{ $video['description'] ?? '' }}</textarea>
            @includeIf('ai_content::form_button')

            @if($isEdit)
                <h3>角色</h3>
                @php $roleRows = is_array($roles ?? null) ? $roles : []; @endphp
                @if($roleRows === [])
                    <p class="muted field-hint">还没有挂角色。去「<a href="/admin/video/roles">角色库</a>」把影片 ID 填成 {{ (int) ($video['id'] ?? 0) }}。</p>
                @else
                    <ul class="muted">
                        @foreach($roleRows as $role)
                            <li>{{ $role['name'] ?? '' }} @if((int)($role['status'] ?? 1) !== 1)（停用）@endif</li>
                        @endforeach
                    </ul>
                    <p class="muted field-hint"><a href="/admin/video/roles">角色库</a> 里改，前台详情页会列出启用的。</p>
                @endif
            @endif

            <h3>上架</h3>
            <label for="video-status">状态</label>
            <select id="video-status" name="status">
                <option value="2" @selected($status === '2')>草稿（前台看不到）</option>
                <option value="1" @selected($status === '1')>上架</option>
                <option value="0" @selected($status === '0')>下架</option>
                <option value="4" @selected($status === '4')>定时发布</option>
                <option value="3" @selected($status === '3')>未通过</option>
            </select>
            <div class="video-publish-row" id="video-publish-row" @if($status !== '4') hidden @endif>
                <label for="video-publish-at">定时发布时间</label>
                <input id="video-publish-at" type="datetime-local" name="publish_at" value="{{ $publishAt }}">
                <p class="muted field-hint">到点会自动改成上架。时间按服务器时区。</p>
            </div>
            <div class="settings-two">
                <div>
                    <label for="video-points">点播积分</label>
                    <input id="video-points" type="number" name="points" min="0" value="{{ $video['points'] ?? 0 }}">
                    <p class="muted field-hint">0 表示免费。会员组里也可以单独设。</p>
                </div>
                <div>
                    <label for="video-score">评分</label>
                    <input id="video-score" type="number" name="score" min="0" max="10" step="0.1" value="{{ $video['score'] ?? 0 }}">
                </div>
            </div>
            <label for="video-sort">排序</label>
            <input id="video-sort" type="number" name="sort" value="{{ $video['sort'] ?? 0 }}">
            <p class="muted field-hint">数字越大越靠前。一般不用改。</p>
            <input type="hidden" name="is_recommend" value="0">
            <input type="hidden" name="is_hot" value="0">
            <input type="hidden" name="lock" value="0">
            <div class="video-form-checks">
                <label class="inline">
                    <input type="checkbox" name="is_recommend" value="1" @checked((string) ($video['is_recommend'] ?? '0') === '1')>
                    推荐到首页
                </label>
                <label class="inline">
                    <input type="checkbox" name="is_hot" value="1" @checked((string) ($video['is_hot'] ?? '0') === '1')>
                    标成热门
                </label>
                <label class="inline">
                    <input type="checkbox" name="lock" value="1" @checked((string) ($video['lock'] ?? '0') === '1')>
                    锁定，采集不要覆盖
                </label>
            </div>

            <details class="settings-details" @if(trim((string) ($video['collect_id'] ?? '')) !== '' || $collectId !== '') open @endif>
                <summary>采集对照</summary>
                <p class="muted field-hint">手动加片一般不用填。对得上资源站时，重复采集不会再插一条。</p>
                <label for="video-collect-source">采集源</label>
                <select id="video-collect-source" name="collect_source_id">
                    <option value="">无</option>
                    @foreach($collects as $src)
                        <option value="{{ $src['id'] }}" @selected($collectId === (string) $src['id'])>
                            {{ $src['name'] }}@if((string) ($src['status'] ?? '1') === '0')（停用）@endif
                        </option>
                    @endforeach
                </select>
                <label for="video-collect-id">采集 ID</label>
                <input id="video-collect-id" type="text" name="collect_id" value="{{ $video['collect_id'] ?? '' }}">
            </details>

            <div class="form-actions">
                <button type="submit" class="btn" id="video-save">保存</button>
                <button type="button" class="btn btn-muted" id="video-save-play">保存并加播放地址</button>
                <a class="btn btn-muted" href="/admin/video">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('video-form');
    var statusEl = document.getElementById('video-status');
    var pubRow = document.getElementById('video-publish-row');
    var isEdit = !!String(form.id.value || '').trim();

    function showStatus() {
        var timed = statusEl && statusEl.value === '4';
        if (pubRow) pubRow.hidden = !timed;
    }
    if (statusEl) statusEl.addEventListener('change', showStatus);
    showStatus();

    function bindImage(inputId, btnId, imgId, emptyId, thumbId) {
        var input = document.getElementById(inputId);
        var btn = document.getElementById(btnId);
        var img = document.getElementById(imgId);
        var empty = document.getElementById(emptyId);
        var thumb = document.getElementById(thumbId);
        function sync(url) {
            url = String(url || '').trim();
            if (!img || !empty || !thumb) return;
            if (!url) {
                img.removeAttribute('src');
                img.hidden = true;
                empty.hidden = false;
                thumb.classList.add('is-empty');
                return;
            }
            img.hidden = false;
            empty.hidden = true;
            thumb.classList.remove('is-empty');
            if (img.getAttribute('src') !== url) img.src = url;
        }
        if (input) input.addEventListener('input', function () { sync(input.value); });
        if (btn) btn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        input.value = res.data.url;
                        sync(res.data.url);
                        U.toast('上传成功', 'ok');
                    } else U.toast((res && res.msg) || '上传失败', 'err');
                });
            });
        });
    }
    bindImage('video-cover', 'cover-upload', 'cover-img', 'cover-empty', 'cover-thumb');
    bindImage('video-banner', 'banner-upload', 'banner-img', 'banner-empty', 'banner-thumb');

    function save(goPlay) {
        var data = U.formData(form);
        if (!String(data.title || '').trim()) {
            U.toast('请填写标题', 'err');
            document.getElementById('video-title').focus();
            return;
        }
        U.loading(true);
        U.post('/admin/video/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast('已保存', 'ok');
            if (goPlay && id) {
                location.href = '/admin/video/sources?video_id=' + encodeURIComponent(id);
                return;
            }
            if (!isEdit && id) {
                location.href = '/admin/video/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save(false);
    });
    document.getElementById('video-save-play').addEventListener('click', function () { save(true); });
})();
</script>
@endpush
