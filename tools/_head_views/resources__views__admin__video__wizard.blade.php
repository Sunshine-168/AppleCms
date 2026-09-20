fatal: path 'resources\views\admin\video\wizard.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.wizard'))

@section('plain')
<div class="card card-panel wizard-index" id="wizard-index">
    <div class="card-header">
        <span>标签向导</span>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">生成主题里能跑的 Blade 标签。选一种，改条件，复制到「模板编辑」里贴。没有苹果的 <code>{maccms:vod}</code>。给片子打标签请去「标签」。</p>
        <div class="wiz-picker">
            <div class="queue-chips" id="wiz-group-chips"></div>
            <div class="queue-chips" id="wiz-tag-chips"></div>
        </div>
        <p class="muted field-hint" id="wiz-hint"></p>
        <div class="wiz-work">
            <div class="wiz-left">
                <form class="wiz-form" id="wiz-form" onsubmit="return false;"></form>
                <div class="wiz-actions">
                    <button type="button" class="btn btn-muted btn-sm" id="wiz-try">试一下会捞几条</button>
                    <span class="muted" id="wiz-try-out"></span>
                </div>
                <ul class="wiz-samples" id="wiz-samples" hidden></ul>
                <div class="wiz-vars" id="wiz-vars" hidden>
                    <h3>循环里能用的字段</h3>
                    <ul id="wiz-var-list"></ul>
                </div>
            </div>
            <div class="wiz-right">
                <div class="wiz-snippet-head">
                    <label class="wiz-snippet-label" for="wiz-snippet">生成的标签</label>
                    <button type="button" class="btn btn-sm" id="wiz-copy">复制</button>
                </div>
                <textarea id="wiz-snippet" class="wiz-snippet" rows="12" spellcheck="false"></textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var catalog = @json($catalog);
    var groups = [
        {id: 'list', label: '列表'},
        {id: 'page', label: '当前页'},
        {id: 'format', label: '格式化'}
    ];
    var currentGroup = 'list';
    var current = 'vod';
    var form = document.getElementById('wiz-form');
    var hint = document.getElementById('wiz-hint');
    var snippet = document.getElementById('wiz-snippet');
    var tryBtn = document.getElementById('wiz-try');
    var tryOut = document.getElementById('wiz-try-out');
    var varsBox = document.getElementById('wiz-vars');
    var varList = document.getElementById('wiz-var-list');
    var samples = document.getElementById('wiz-samples');
    var atSign = String.fromCharCode(64);

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
        var wrap = document.getElementById('wiz-group-chips');
        wrap.innerHTML = groups.map(function (g) {
            return '<button type="button" class="chip" data-group="' + esc(g.id) + '">' + esc(g.label) + '</button>';
        }).join('');
    }
    function renderTagChips() {
        var wrap = document.getElementById('wiz-tag-chips');
        var tags = catalog.filter(function (t) { return t.group === currentGroup; });
        wrap.innerHTML = tags.map(function (t) {
            return '<button type="button" class="chip" data-tag="' + esc(t.name) + '">' + esc(t.title) + '</button>';
        }).join('');
        markTagChips();
    }
    function renderFieldHtml(field) {
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
    }
    function renderFields(tag) {
        hint.innerHTML = '<strong>' + esc(tag.title) + '</strong> <code>' + atSign + esc(tag.name) + '</code>' +
            (tag.hint ? ' — ' + esc(tag.hint) : '');
        var basic = [];
        var advanced = [];
        (tag.fields || []).forEach(function (field) {
            if (field.advanced) advanced.push(field);
            else basic.push(field);
        });
        var html = basic.map(renderFieldHtml).join('');
        if (advanced.length) {
            html += '<details class="wiz-more"><summary>更多条件</summary><div class="wiz-more-grid">';
            html += advanced.map(renderFieldHtml).join('');
            html += '</div></details>';
        }
        form.innerHTML = html || '<p class="muted"></p>';
        varList.innerHTML = (tag.vars || []).map(function (v) {
            return '<li><code>' + esc(v.code) + '</code> ' + esc(v.label) + '</li>';
        }).join('');
        varsBox.hidden = !(tag.vars && tag.vars.length);
        tryBtn.hidden = !tag.tryable;
        markTagChips();
    }
    function markGroupChips() {
        U.qa('#wiz-group-chips .chip').forEach(function (chip) {
            chip.classList.toggle('active', chip.getAttribute('data-group') === currentGroup);
        });
    }
    function markTagChips() {
        U.qa('#wiz-tag-chips .chip').forEach(function (chip) {
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
        var tag = findTag(name);
        if (!tag) return;
        current = name;
        currentGroup = tag.group || currentGroup;
        tryOut.textContent = '';
        samples.hidden = true;
        samples.innerHTML = '';
        renderTagChips();
        markGroupChips();
        renderFields(tag);
        refreshSnippet();
    }
    function selectGroup(id) {
        currentGroup = id;
        markGroupChips();
        var tags = catalog.filter(function (t) { return t.group === id; });
        renderTagChips();
        if (tags.length) selectTag(tags[0].name);
    }

    renderGroups();
    renderTagChips();
    markGroupChips();
    selectTag('vod');

    document.querySelector('.wiz-picker').addEventListener('click', function (e) {
        var groupChip = e.target.closest('[data-group]');
        if (groupChip) {
            selectGroup(groupChip.getAttribute('data-group'));
            return;
        }
        var tagChip = e.target.closest('[data-tag]');
        if (tagChip) selectTag(tagChip.getAttribute('data-tag'));
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