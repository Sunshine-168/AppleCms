@extends('admin.layouts.inner')
@section('title', admin_t('page.wizard'))

@section('plain')
<div class="card card-panel wizard-index" id="wizard-index">
    <div class="card-header">
        <span>标签向导</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/templates">模板编辑</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tags">影片标签</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">生成主题里能跑的 Blade 标签，复制到「模板编辑」。不改主题文件，也没有苹果的 <code>{maccms:vod}</code>。打标签请去标签页。</p>
        <div class="wiz-groups" id="wiz-groups"></div>
        <p class="muted field-hint" id="wiz-hint"></p>
        <form class="wiz-form" id="wiz-form" onsubmit="return false;"></form>
        <div class="wiz-actions">
            <button type="button" class="btn btn-sm" id="wiz-copy">复制</button>
            <button type="button" class="btn btn-muted btn-sm" id="wiz-try">试一下会捞几条</button>
            <span class="muted" id="wiz-try-out"></span>
        </div>
        <label class="wiz-snippet-label" for="wiz-snippet">生成的标签</label>
        <textarea id="wiz-snippet" class="wiz-snippet" rows="12" spellcheck="false"></textarea>
        <div class="wiz-vars" id="wiz-vars" hidden>
            <h3>循环里能用的字段</h3>
            <ul id="wiz-var-list"></ul>
        </div>
        <ul class="wiz-samples" id="wiz-samples" hidden></ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var catalog = @json($catalog);
    var groups = [
        {id: 'list', label: '拉列表'},
        {id: 'page', label: '当前页'},
        {id: 'format', label: '格式化'}
    ];
    var current = 'vod';
    var form = document.getElementById('wiz-form');
    var hint = document.getElementById('wiz-hint');
    var snippet = document.getElementById('wiz-snippet');
    var tryOut = document.getElementById('wiz-try-out');
    var varsBox = document.getElementById('wiz-vars');
    var varList = document.getElementById('wiz-var-list');
    var samples = document.getElementById('wiz-samples');

    function findTag(name) {
        for (var i = 0; i < catalog.length; i++) {
            if (catalog[i].name === name) return catalog[i];
        }
        return catalog[0] || null;
    }
    function esc(s) { return U.escape(String(s || '')); }
    function fieldVal(field) {
        var el = form.querySelector('[name="' + field.name + '"]');
        if (!el) return '';
        if (field.type === 'checkbox') return el.checked ? '1' : '';
        return el.value;
    }
    function payload() {
        var tag = findTag(current);
        var data = {tag: current};
        (tag.fields || []).forEach(function (field) {
            var v = fieldVal(field);
            if (v !== '') data[field.name] = v;
        });
        return data;
    }
    function renderGroups() {
        var wrap = document.getElementById('wiz-groups');
        wrap.innerHTML = groups.map(function (g) {
            var chips = catalog.filter(function (t) { return t.group === g.id; }).map(function (t) {
                return '<button type="button" class="chip" data-tag="' + esc(t.name) + '"><code>@@' + esc(t.name) + '</code> ' + esc(t.title) + '</button>';
            }).join('');
            return '<div class="wiz-group"><strong>' + esc(g.label) + '</strong><div class="queue-chips">' + chips + '</div></div>';
        }).join('');
    }
    function renderFields(tag) {
        hint.textContent = tag.hint || '';
        form.innerHTML = (tag.fields || []).map(function (field) {
            var id = 'wiz-f-' + field.name;
            var html = '<div class="wiz-field"><label for="' + id + '">' + esc(field.label) + '</label>';
            if (field.type === 'select') {
                var opts = field.options || {};
                html += '<select id="' + id + '" name="' + esc(field.name) + '">';
                Object.keys(opts).forEach(function (val) {
                    html += '<option value="' + esc(val) + '"' + (String(field.value || '') === String(val) ? ' selected' : '') + '>' + esc(opts[val]) + '</option>';
                });
                html += '</select>';
            } else if (field.type === 'checkbox') {
                html += '<label class="wiz-check"><input id="' + id + '" type="checkbox" name="' + esc(field.name) + '" value="1"> 是</label>';
            } else {
                html += '<input id="' + id + '" type="' + (field.type === 'number' ? 'number' : 'text') + '" name="' + esc(field.name) + '" value="' + esc(field.value || '') + '" autocomplete="off">';
            }
            if (field.hint) html += '<p class="muted field-hint">' + esc(field.hint) + '</p>';
            return html + '</div>';
        }).join('') || '<p class="muted"></p>';
        varList.innerHTML = (tag.vars || []).map(function (v) {
            return '<li><code>' + esc(v.code) + '</code> ' + esc(v.label) + '</li>';
        }).join('');
        varsBox.hidden = !(tag.vars && tag.vars.length);
        markChips();
    }
    function markChips() {
        U.qa('#wiz-groups .chip').forEach(function (chip) {
            chip.classList.toggle('active', chip.getAttribute('data-tag') === current);
        });
    }
    function refreshSnippet() {
        U.post('/admin/video/wizard/snippet', payload()).then(function (res) {
            if (!res || res.code !== 0) {
                snippet.value = '';
                U.toast((res && res.msg) || '生成失败', 'err');
                return;
            }
            snippet.value = (res.data && res.data.snippet) || '';
        });
    }
    function selectTag(name) {
        current = name;
        tryOut.textContent = '';
        samples.hidden = true;
        samples.innerHTML = '';
        renderFields(findTag(current));
        refreshSnippet();
    }

    renderGroups();
    selectTag('vod');

    document.getElementById('wiz-groups').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-tag]');
        if (!chip) return;
        selectTag(chip.getAttribute('data-tag'));
    });
    form.addEventListener('change', refreshSnippet);
    form.addEventListener('input', refreshSnippet);
    U.on('#wiz-copy', 'click', function () {
        var text = snippet.value || '';
        if (!text) { U.toast('还没有生成内容', 'err'); return; }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { U.toast('已复制', 'ok'); }).catch(function () {
                snippet.select();
                U.toast('请手动复制', 'err');
            });
        } else {
            snippet.select();
            U.toast('请手动复制', 'err');
        }
    });
    U.on('#wiz-try', 'click', function () {
        tryOut.textContent = '…';
        samples.hidden = true;
        U.post('/admin/video/wizard/try', payload()).then(function (res) {
            tryOut.textContent = (res && res.msg) || '失败';
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '失败', 'err');
                return;
            }
            var list = (res.data && res.data.samples) || [];
            samples.innerHTML = list.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('');
            samples.hidden = list.length === 0;
        });
    });
})();
</script>
@endpush
