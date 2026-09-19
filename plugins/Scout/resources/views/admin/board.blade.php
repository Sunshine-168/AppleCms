@extends('admin.layouts.inner')
@section('title', '全文搜索')

@php
    $status = $status ?? [];
    $options = $options ?? [];
@endphp

@section('plain')
<div class="card card-panel desk-board" id="scout-board">
    <div class="card-header">
        <span>全文搜索 <em>Laravel Scout</em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/scout">参数页</a>
            <button type="button" class="btn btn-sm" id="scout-sync-btn">重建索引</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">启用后，前台只带关键词的影片搜索走 Scout；复杂筛选仍用数据库。驱动默认 database，无需另装引擎。</p>

        <div class="stat-grid dash" style="margin:0 0 16px">
            <div class="stat-card">
                <div class="stat-label">状态</div>
                <div class="stat-value">{{ !empty($status['search_enabled']) ? '已启用' : '未启用' }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">驱动</div>
                <div class="stat-value">{{ $status['driver'] ?? 'database' }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">影片</div>
                <div class="stat-value">{{ (int) ($status['video_count'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">资讯</div>
                <div class="stat-value">{{ (int) ($status['art_count'] ?? 0) }}</div>
            </div>
        </div>

        <form class="admin-form settings-page" id="scout-form" style="max-width:560px">
            <input type="hidden" name="desk" value="settings">
            <label>启用 Scout 搜影片</label>
            <select name="scout_search_enabled">
                <option value="0" @selected(($options['scout_search_enabled'] ?? '1') === '0')>否（用 LIKE）</option>
                <option value="1" @selected(($options['scout_search_enabled'] ?? '1') === '1')>是</option>
            </select>
            <label>驱动</label>
            <select name="scout_driver">
                <option value="database" @selected(($options['scout_driver'] ?? '') === 'database')>database（推荐）</option>
                <option value="collection" @selected(($options['scout_driver'] ?? '') === 'collection')>collection（内存）</option>
                <option value="meilisearch" @selected(($options['scout_driver'] ?? '') === 'meilisearch')>meilisearch</option>
            </select>
            <label>Meilisearch 主机</label>
            <input type="text" name="scout_meili_host" value="{{ $options['scout_meili_host'] ?? '' }}" placeholder="http://127.0.0.1:7700">
            <label>Meilisearch Key</label>
            <input type="text" name="scout_meili_key" value="" placeholder="{{ !empty($options['scout_meili_key_set']) ? '已保存，留空不改' : '可选' }}">
            <p class="muted field-hint">改驱动或接 Meilisearch 后请点「重建索引」。命令行：<code>php artisan scout:site-sync</code></p>
            <p><button type="button" class="btn btn-sm" id="scout-save-btn">保存</button></p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    U.on('#scout-save-btn', 'click', function () {
        var data = U.formData(document.getElementById('scout-form'));
        data.action = 'settings';
        U.loading(true);
        U.post('/admin/video/scout/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return; }
            U.toast('已保存', 'ok');
        }).catch(function () { U.loading(false); U.toast('保存失败', 'err'); });
    });
    U.on('#scout-sync-btn', 'click', function () {
        if (!U.confirm('重建影片与资讯索引？大数据量可能稍慢。')) return;
        U.loading(true);
        U.post('/admin/video/scout/sync', {}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '重建失败', 'err'); return; }
            U.toast((res.msg || '已重建') + (res.data ? (' · 片 ' + (res.data.videos || 0) + ' / 文 ' + (res.data.arts || 0)) : ''), 'ok');
        }).catch(function () { U.loading(false); U.toast('重建失败', 'err'); });
    });
})();
</script>
@endpush
