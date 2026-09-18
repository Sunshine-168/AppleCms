@extends('admin.layouts.inner')
@section('title', admin_t('page.theme_config'))

@php
    $s = $site ?? [];
    $tab = $tab ?? 'base';
    $hasPlayView = (bool) ($hasPlayView ?? false);
    $tabs = [
        'base' => '基本设置',
        'home' => '首页配置',
        'page' => '页面配置',
        'nav' => '导航菜单',
        'other' => '其他设置',
        'seo' => 'SEO设置',
        'ads' => '广告设置',
    ];
    if (! $hasPlayView) {
        unset($tabs['page']);
    }
    $on = fn (string $k, string $d = '1') => (string) ($s[$k] ?? $d) === '1';
    $assets = [
        ['name' => 'theme_logo', 'label' => '顶部 Logo', 'empty' => '还没有 Logo', 'kind' => 'logo', 'hint' => '出现在前台页头。建议用透明底的横图。'],
        ['name' => 'theme_logo_foot', 'label' => '底部 Logo', 'empty' => '还没有底部 Logo', 'kind' => 'logo', 'hint' => '出现在页脚。留空则不显示。'],
        ['name' => 'theme_favicon', 'label' => '网站图标', 'empty' => '还没有图标', 'kind' => 'favicon', 'hint' => '浏览器标签上的小图标，建议正方形 png / ico。'],
        ['name' => 'theme_webapp', 'label' => 'webapp 图标', 'empty' => '还没有 webapp 图标', 'kind' => 'favicon', 'hint' => '添加到主屏幕时用的图标，建议正方形。'],
        ['name' => 'theme_lazy', 'label' => '懒加载占位', 'empty' => '还没有占位图', 'kind' => 'logo', 'hint' => '封面为空时用这张图顶上，不是独立的 JS 懒加载库。'],
    ];
    $navToggles = [
        ['name' => 'theme_nav_latest', 'label' => '最新'],
        ['name' => 'theme_nav_topic', 'label' => '专题'],
        ['name' => 'theme_nav_actor', 'label' => '演员'],
        ['name' => 'theme_nav_role', 'label' => '角色'],
        ['name' => 'theme_nav_art', 'label' => '资讯'],
        ['name' => 'theme_nav_website', 'label' => '网址导航'],
    ];
@endphp

@section('plain')
<div class="card card-panel" id="theme-index">
    <div class="card-header"><span>主题配置</span></div>
    <div class="card-body">
        <p class="muted recycle-lead">改当前默认主题会真正用到的键。模板文件、广告位、标签向导仍是旁边的独立入口。</p>
        <form class="settings-page" id="theme-form">
            <input type="hidden" name="tab" value="theme">
            <div class="tabs settings-tabs" id="themeTabs">
                @foreach($tabs as $key => $label)
                    <button type="button" class="{{ $tab === $key ? 'active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>

            <div class="settings-pane{{ $tab === 'base' ? ' active' : '' }}" data-pane="base">
                <p class="muted field-hint">默认主题只有一套顶栏，没有深浅双套 Lottie。</p>
                @foreach($assets as $field)
                    @php
                        $val = trim((string) ($s[$field['name']] ?? ''));
                    @endphp
                    <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                    <div class="settings-file-preview{{ $field['kind'] === 'favicon' ? ' settings-file-preview--favicon' : '' }}" data-preview="{{ $field['name'] }}">
                        <div class="settings-file-thumb{{ $val === '' ? ' is-empty' : '' }}" data-thumb>
                            <img data-img src="{{ $val }}" alt="" @if($val === '') hidden @endif>
                            <span class="settings-file-empty muted" data-empty @if($val !== '') hidden @endif>{{ $field['empty'] }}</span>
                        </div>
                        <input id="{{ $field['name'] }}" type="text" name="{{ $field['name'] }}" value="{{ $val }}" placeholder="图片地址，或点上传" data-url>
                        <button type="button" class="btn btn-muted btn-sm" data-upload>上传图片</button>
                    </div>
                    <p class="muted field-hint">{{ $field['hint'] }}</p>
                @endforeach

                <label for="theme_head_code">头部代码</label>
                <textarea id="theme_head_code" name="theme_head_code" rows="5" placeholder="统计、验证等 HTML，会原样插到 &lt;head&gt;">{{ $s['theme_head_code'] ?? '' }}</textarea>
                <p class="muted field-hint">管理员专用，和站点设置里的统计代码一样原样输出，不会剥掉 HTML。</p>

                <label for="theme_foot_code">底部说明</label>
                <textarea id="theme_foot_code" name="theme_foot_code" rows="4" placeholder="页脚补充 HTML">{{ $s['theme_foot_code'] ?? '' }}</textarea>
                <p class="muted field-hint">出现在前台页脚，可写版权补充或统计。</p>
            </div>

            <div class="settings-pane{{ $tab === 'home' ? ' active' : '' }}" data-pane="home">
                <p class="muted field-hint">幻灯片在「幻灯」slot=<code>home</code>，到「<a href="/admin/video/slides">幻灯片</a>」里改。本页只改首页推荐条数。</p>
                <label for="theme_home_rec_num">首页推荐条数</label>
                <input id="theme_home_rec_num" type="number" name="theme_home_rec_num" min="1" max="100" value="{{ $s['theme_home_rec_num'] ?? 12 }}">
                <p class="muted field-hint">默认主题首页「推荐」区块的 <code>@@vod</code> 条数，默认 12。</p>
            </div>

            @if($hasPlayView)
            <div class="settings-pane{{ $tab === 'page' ? ' active' : '' }}" data-pane="page">
                <label for="theme_play_notice">播放页提示</label>
                <textarea id="theme_play_notice" name="theme_play_notice" rows="3" placeholder="出现在播放器上方，例如版权或线路说明">{{ $s['theme_play_notice'] ?? '' }}</textarea>
                <p class="muted field-hint">默认主题播放页会显示这段文字。留空则不显示。</p>
            </div>
            @endif

            <div class="settings-pane{{ $tab === 'nav' ? ' active' : '' }}" data-pane="nav">
                <p class="muted field-hint">顶部分类和专题仍由模板标签输出。下面开关控制默认主题写死的那几项，以及 4 条自定义链接。</p>
                @foreach($navToggles as $nav)
                    <input type="hidden" name="{{ $nav['name'] }}" value="0">
                    <label class="inline">
                        <input type="checkbox" name="{{ $nav['name'] }}" value="1" @checked($on($nav['name']))>
                        {{ $nav['label'] }}
                    </label>
                @endforeach
                <h3>自定义链接</h3>
                @for($i = 1; $i <= 4; $i++)
                    <div class="settings-two">
                        <div>
                            <label for="theme_nav_name{{ $i }}">名称 {{ $i }}</label>
                            <input id="theme_nav_name{{ $i }}" type="text" name="theme_nav_name{{ $i }}" value="{{ $s['theme_nav_name'.$i] ?? '' }}" maxlength="40">
                        </div>
                        <div>
                            <label for="theme_nav_url{{ $i }}">地址 {{ $i }}</label>
                            <input id="theme_nav_url{{ $i }}" type="text" name="theme_nav_url{{ $i }}" value="{{ $s['theme_nav_url'.$i] ?? '' }}" placeholder="https:// 或 /path">
                        </div>
                    </div>
                @endfor
                <p class="muted field-hint">名称和地址都填了才会出现在顶栏。地址只接受 http(s) 或以 / 开头的站点路径。</p>
            </div>

            <div class="settings-pane{{ $tab === 'other' ? ' active' : '' }}" data-pane="other">
                <label for="theme_primary">主色</label>
                <input id="theme_primary" type="text" name="theme_primary" value="{{ $s['theme_primary'] ?? '' }}" placeholder="#1b4f72" maxlength="7">
                <p class="muted field-hint">如 #1b4f72。留空则用模板自带配色。采集封面水印在「<a href="/admin/video/settings?tab=look">站点设置</a>」，不是主题文件。</p>
            </div>

            <div class="settings-pane{{ $tab === 'seo' ? ' active' : '' }}" data-pane="seo">
                <p class="muted field-hint">与站点设置「更多」同一套键，<code>@@vodSeo</code> 会用到。</p>
                <label for="seo_title_vod">影片页</label>
                <input id="seo_title_vod" type="text" name="seo_title_vod" value="{{ $s['seo_title_vod'] ?? '' }}" placeholder="{name} - {site}">
                <label for="seo_title_type">分类页</label>
                <input id="seo_title_type" type="text" name="seo_title_type" value="{{ $s['seo_title_type'] ?? '' }}" placeholder="{type} - {site}">
                <label for="seo_title_play">播放页</label>
                <input id="seo_title_play" type="text" name="seo_title_play" value="{{ $s['seo_title_play'] ?? '' }}" placeholder="{name} 在线播放 - {site}">
                <p class="muted field-hint">可用 <code>{name}</code> <code>{type}</code> <code>{site}</code>。</p>
            </div>

            <div class="settings-pane{{ $tab === 'ads' ? ' active' : '' }}" data-pane="ads">
                <p class="muted recycle-lead">广告不写在主题配置里。页头、页脚、播放页用 <code>@@vodAd</code>，位置分别是 <code>header</code>、<code>footer</code>、<code>play</code>。</p>
                <p><a class="btn btn-muted btn-sm" href="/admin/video/ads">广告位</a></p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="theme-save">保存设置</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var tabs = document.getElementById('themeTabs');
    if (tabs) {
        tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.getAttribute('data-tab') || 'base';
                tabs.querySelectorAll('[data-tab]').forEach(function (item) {
                    item.classList.toggle('active', item === btn);
                });
                document.querySelectorAll('.settings-pane').forEach(function (pane) {
                    pane.classList.toggle('active', pane.getAttribute('data-pane') === tab);
                });
                var url = new URL(window.location.href);
                if (tab === 'base') url.searchParams.delete('tab');
                else url.searchParams.set('tab', tab);
                history.replaceState(null, '', url);
            });
        });
    }

    function showPreview(box, url) {
        url = String(url || '').trim();
        var img = box.querySelector('[data-img]');
        var empty = box.querySelector('[data-empty]');
        var thumb = box.querySelector('[data-thumb]');
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

    document.querySelectorAll('[data-preview]').forEach(function (box) {
        var input = box.querySelector('[data-url]');
        if (input) {
            input.addEventListener('input', function () { showPreview(box, input.value); });
        }
        var up = box.querySelector('[data-upload]');
        if (up && window.AdminUi) {
            up.addEventListener('click', function () {
                AdminUi.pickFile('image/*').then(function (file) {
                    if (!file) return;
                    AdminUi.loading(true);
                    return AdminUi.upload(file).then(function (res) {
                        AdminUi.loading(false);
                        if (res && res.code === 0 && res.data && res.data.url) {
                            if (input) input.value = res.data.url;
                            showPreview(box, res.data.url);
                            AdminUi.toast('上传成功', 'ok');
                        } else {
                            AdminUi.toast((res && res.msg) || '上传失败', 'err');
                        }
                    });
                });
            });
        }
    });

    var save = document.getElementById('theme-save');
    var form = document.getElementById('theme-form');
    if (save && form) {
        var doSave = function () {
            AdminUi.post('/admin/video/theme', AdminUi.formData(form)).then(function (res) {
                AdminUi.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            });
        };
        save.addEventListener('click', doSave);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            doSave();
        });
    }
})();
</script>
@endpush
