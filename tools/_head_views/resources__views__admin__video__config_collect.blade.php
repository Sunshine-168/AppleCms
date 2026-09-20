fatal: path 'resources\views\admin\video\config_collect.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.config_collect'))

@php
    $s = $site ?? [];
    $on = fn (string $k, string $d = '0') => (string) ($s[$k] ?? $d) === '1';
    $toTemp = $on('collect_to_temp');
@endphp

@section('plain')
<div class="card card-panel collect-config-index">
    <div class="card-header">
        <span>内容接入</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_temps">待审入库</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/audits">入库审核</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">入库接口</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/hub">试试接口</a>
        </div>
    </div>
    <div class="card-body">
            <p class="muted recycle-lead">片子从资源站进来时按这里处理。漫画采集源写入插件库，不走待审入库。改完点保存，<strong>下次采集</strong>才生效，已经进库的片子不会改。</p>
        <form class="admin-form settings-page collect-config-form" id="site-form">
            <h3>入库方式</h3>
            <input type="hidden" name="collect_to_temp" id="collect_to_temp" value="{{ $toTemp ? '1' : '0' }}">
            <div class="ingest-modes" id="ingest-modes">
                <button type="button" class="ingest-mode{{ $toTemp ? '' : ' is-on' }}" data-value="0">
                    <strong>直接进片库</strong>
                    <span>采到的片子马上出现在影片列表。适合信得过的资源站。</span>
                </button>
                <button type="button" class="ingest-mode{{ $toTemp ? ' is-on' : '' }}" data-value="1">
                    <strong>先待审再转入</strong>
                    <span>新片先停在待审入库，核对封面和分类后再进片库。</span>
                </button>
            </div>
            <p class="muted field-hint ingest-temp-hint" id="ingest-temp-hint" @if(! $toTemp) hidden @endif>打开后去「<a href="/admin/video/collect_temps">待审入库</a>」处理停住的片子。现在采集是直接进库的话，那里通常是空的。</p>

            <h3>进库以后</h3>
            <div class="theme-nav-toggles">
            <input type="hidden" name="collect_in_status" value="0">
            <label class="inline">
                <input type="checkbox" name="collect_in_status" value="1" @checked($on('collect_in_status', '1'))>
                采集后直接上架
            </label>
            <input type="hidden" name="collect_sync_pic" value="0">
            <label class="inline">
                <input type="checkbox" name="collect_sync_pic" value="1" @checked($on('collect_sync_pic', '1'))>
                同步封面地址
            </label>
            <input type="hidden" name="collect_pic_local" value="0">
            <label class="inline">
                <input type="checkbox" name="collect_pic_local" value="1" @checked($on('collect_pic_local'))>
                把封面下载到本站
            </label>
            </div>
            <p class="muted field-hint">关掉「采集后直接上架」则新片是待审状态，要在影片列表里再上架。下载封面会占磁盘，适合资源站图床不稳定的情况。也可事后在「<a href="/admin/video/tools/images">远程图片</a>」里补。</p>
            <div class="settings-two">
                <div>
                    <label for="collect_hits_min">随机人气下限</label>
                    <input id="collect_hits_min" type="number" name="collect_hits_min" min="0" value="{{ $s['collect_hits_min'] ?? 0 }}">
                </div>
                <div>
                    <label for="collect_hits_max">随机人气上限</label>
                    <input id="collect_hits_max" type="number" name="collect_hits_max" min="0" value="{{ $s['collect_hits_max'] ?? 0 }}">
                </div>
            </div>
            <p class="muted field-hint">只作用于新建影片。都填 0 表示不随机。</p>

            <h3>地区 / 语言对照</h3>
            <p class="muted field-hint">资源站写法和本站筛选不一致时，在这里对照。每行一条，<code>大陆=中国</code> 或 <code>大陆,中国</code>。对照后的名字要能在「<a href="/admin/video/settings?tab=more">站点设置 → 更多 → 前台筛选</a>」里找到。</p>
            <label for="collect_areawords">地区</label>
            <textarea id="collect_areawords" name="collect_areawords" rows="5" placeholder="大陆=中国">{{ $s['collect_areawords'] ?? '' }}</textarea>
            <label for="collect_langwords">语言</label>
            <textarea id="collect_langwords" name="collect_langwords" rows="5" placeholder="国语=普通话">{{ $s['collect_langwords'] ?? '' }}</textarea>

            <h3>站外推送</h3>
            <p class="muted field-hint">别的程序 POST 片子进本站，密钥和地址在「<a href="/admin/video/config/interface">入库接口</a>」。采集资源站请用采集源，不要跟这个接口混用。</p>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.partials.site-save')

@push('scripts')
<script>
(function () {
    var hidden = document.getElementById('collect_to_temp');
    var hint = document.getElementById('ingest-temp-hint');
    var wrap = document.getElementById('ingest-modes');
    if (wrap && hidden) {
        wrap.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-value]');
            if (!btn) return;
            hidden.value = btn.getAttribute('data-value') || '0';
            wrap.querySelectorAll('.ingest-mode').forEach(function (item) {
                item.classList.toggle('is-on', item === btn);
            });
            if (hint) hint.hidden = hidden.value !== '1';
        });
    }
})();
</script>
@endpush