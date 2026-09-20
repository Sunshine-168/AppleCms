@extends('admin.layouts.inner')
@section('title', admin_t('page.wizard'))

@php
    $jsLang = [
        'wiz_group_list' => admin_t('ui.wiz_group_list'),
        'wiz_group_page' => admin_t('ui.wiz_group_page'),
        'wiz_group_format' => admin_t('ui.wiz_group_format'),
        'yes' => admin_t('ui.yes'),
        'more_conds' => admin_t('ui.more_conds'),
        'gen_fail' => admin_t('ui.gen_fail'),
        'no_snippet_yet' => admin_t('ui.no_snippet_yet'),
        'copied' => admin_t('ui.copied'),
        'copy_manually' => admin_t('ui.copy_manually'),
        'fail' => admin_t('ui.fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel wizard-index" id="wizard-index">
    <div class="card-header">
        <span>{{ admin_t('page.wizard') }}</span>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.wizard_lead_1') }}「{{ admin_t('page.templates') }}」{{ admin_t('ui.wizard_lead_2') }} <code>{maccms:vod}</code>{{ admin_t('ui.wizard_lead_3') }}「{{ admin_t('nav.tags') }}」{{ admin_t('ui.wizard_lead_4') }}</p>
        <div class="wiz-picker">
            <div class="queue-chips" id="wiz-group-chips"></div>
            <div class="queue-chips" id="wiz-tag-chips"></div>
        </div>
        <p class="muted field-hint" id="wiz-hint"></p>
        <div class="wiz-work">
            <div class="wiz-left">
                <form class="wiz-form" id="wiz-form" onsubmit="return false;"></form>
                <div class="wiz-actions">
                    <button type="button" class="btn btn-muted btn-sm" id="wiz-try">{{ admin_t('ui.wiz_try') }}</button>
                    <span class="muted" id="wiz-try-out"></span>
                </div>
                <ul class="wiz-samples" id="wiz-samples" hidden></ul>
                <div class="wiz-vars" id="wiz-vars" hidden>
                    <h3>{{ admin_t('ui.wiz_loop_fields') }}</h3>
                    <ul id="wiz-var-list"></ul>
                </div>
            </div>
            <div class="wiz-right">
                <div class="wiz-snippet-head">
                    <label class="wiz-snippet-label" for="wiz-snippet">{{ admin_t('ui.wiz_snippet') }}</label>
                    <button type="button" class="btn btn-sm" id="wiz-copy">{{ admin_t('ui.copy') }}</button>
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
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var catalog = @json($catalog, JSON_UNESCAPED_UNICODE);
    var groups = [
        {id: 'list', label: L.wiz_group_list},
        {id: 'page', label: L.wiz_group_page},
        {id: 'format', label: L.wiz_group_format}
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
            html += '<label class="wiz-check"><input id="' + id + '" type="checkbox" name="' + esc(field.name) + '" value="1"> ' + esc(L.yes) + '</label>';
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
            html += '<details class="wiz-more"><summary>' + esc(L.more_conds) + '</summary><div class="wiz-more-grid">';
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
                U.toast((res && res.msg) || L.gen_fail, 'err');
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
        if (!text) { U.toast(L.no_snippet_yet, 'err'); return; }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { U.toast(L.copied, 'ok'); }).catch(function () {
                snippet.select();
                U.toast(L.copy_manually, 'err');
            });
        } else {
            snippet.select();
            U.toast(L.copy_manually, 'err');
        }
    });
    U.on('#wiz-try', 'click', function () {
        tryOut.textContent = '…';
        samples.hidden = true;
        U.post('/admin/video/wizard/try', payload()).then(function (res) {
            tryOut.textContent = (res && res.msg) || L.fail;
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.fail, 'err');
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
