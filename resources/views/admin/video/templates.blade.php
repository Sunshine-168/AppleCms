@extends('admin.layouts.inner')
@section('title', admin_t('nav.templates'))

@php
    $groups = $groups ?? [];
    $theme = $theme ?? ['slug' => 'default', 'title' => '默认模板'];
    $codeEditor = (bool) ($codeEditor ?? false);
    $hasFiles = false;
    foreach ($groups as $g) {
        if (! empty($g['files'])) { $hasFiles = true; break; }
    }
@endphp

@if($codeEditor)
    @push('styles')
        @include('code-editor::head')
    @endpush
@endif

@section('plain')
<div class="card card-panel desk-board tpl-board">
    <div class="card-header">
        <span>模板</span>
        <div>
            @include('admin.video.partials.theme-desks', ['desk' => 'files'])
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">改当前主题的页面文件。@if($codeEditor) 绿色是 Blade，橙色是 <code>@@vod</code>，紫色是 <code>@@php</code>。括号里的参数会另外上色。Ctrl+F 查找，Ctrl+S 直接保存。@else 打开「<a href="/admin/plugins/code_editor">代码编辑器</a>」插件可高亮 Blade 标签。@endif</p>
    </div>
</div>
<div class="split-side tpl-index">
    <div class="card card-panel">
        <div class="card-header"><span>{{ $theme['title'] }}</span></div>
        <div class="card-body">
            @if($hasFiles)
                <input type="search" id="tpl-search" class="tpl-search" placeholder="搜页面，如 首页、播放" autocomplete="off" aria-label="搜索模板">
                <div class="file-list" id="tpl-files" data-theme="{{ $theme['slug'] }}">
                    @foreach($groups as $group)
                        <div class="tpl-group" data-group="{{ $group['key'] }}" data-fold>
                            <button type="button" class="tpl-group-toggle" aria-expanded="true" aria-controls="tpl-group-{{ $group['key'] }}">{{ $group['label'] }}</button>
                            <div class="tpl-group-body" id="tpl-group-{{ $group['key'] }}" role="group">
                                @foreach($group['files'] as $f)
                                    <a href="#" class="tpl-file" data-path="{{ $f['path'] }}" data-label="{{ $f['label'] }}">
                                        {{ $f['label'] }}
                                        <small>{{ $f['path'] }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="muted" id="tpl-filter-empty" hidden>没有匹配的页面。</p>
            @else
                <div class="list-empty">
                    <p>这个主题还没有可改的页面。</p>
                    <p class="muted">把 Blade 文件放到 <code>resources/views/themes/{{ $theme['slug'] }}</code>。</p>
                </div>
            @endif
        </div>
    </div>
    <div class="card card-panel">
        <div class="card-header">
            <span id="tpl-title">模板</span>
            <div>
                <button type="button" class="btn btn-muted btn-sm" id="tpl-find" hidden title="Ctrl+F">查找</button>
                <button type="button" class="btn btn-muted btn-sm" id="tpl-attach">插入附件</button>
                <button type="button" class="btn btn-muted btn-sm" id="tpl-rollback" disabled>回滚</button>
                <button type="button" class="btn btn-muted btn-sm" id="tpl-backup" disabled>备份</button>
                <button type="button" class="btn btn-sm" id="tpl-save" disabled>保存</button>
            </div>
        </div>
        <div class="card-body">
            <p class="muted field-hint" id="tpl-meta">
                <span id="tpl-meta-main">从左边点一个页面开始改。保存会先备份，改错了可以回滚。</span>
                <span id="tpl-meta-pos" class="tpl-meta-pos" hidden></span>
            </p>
            <div class="list-empty" id="tpl-empty">
                <p>还没有打开文件。</p>
                <p class="muted">先点「首页」或「整站头尾」。这是 Blade 模板，不是可视化排版。需要图片时点「插入附件」。</p>
            </div>
            <div id="tpl-find-bar" class="tpl-find-bar" hidden>
                <label for="tpl-find-q">查找：</label>
                <input type="search" id="tpl-find-q" autocomplete="off" spellcheck="false">
                <span class="muted">可用 /正则/</span>
            </div>
            <textarea id="tpl-content" class="tpl-editor" hidden spellcheck="false"></textarea>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($codeEditor)
    @include('code-editor::scripts')
@endif
<script>
(function () {
    var U = AdminUi;
    var current = '';
    var saved = '';
    var backupAt = 0;
    var eol = '\n';
    var applying = false;
    var loadSeq = 0;
    var saving = false;
    var titleEl = document.getElementById('tpl-title');
    var metaMain = document.getElementById('tpl-meta-main');
    var metaPos = document.getElementById('tpl-meta-pos');
    var emptyEl = document.getElementById('tpl-empty');
    var editor = document.getElementById('tpl-content');
    var saveBtn = document.getElementById('tpl-save');
    var backupBtn = document.getElementById('tpl-backup');
    var rollbackBtn = document.getElementById('tpl-rollback');
    var findBtn = document.getElementById('tpl-find');
    var findBar = document.getElementById('tpl-find-bar');
    var findInput = document.getElementById('tpl-find-q');
    var usingCode = false;
    var code = window.TplCodeEditor ? window.TplCodeEditor.mount(editor, {
        onSave: function () { saveFile(true); },
        onShow: function () { sizeEditor(); }
    }) : null;
    if (code) code.show(false);

    function sniffEol(text) {
        text = String(text == null ? '' : text);
        if (text.indexOf('\r\n') >= 0) return '\r\n';
        if (text.indexOf('\r') >= 0) return '\r';
        return '\n';
    }
    function normalize(text) {
        return String(text == null ? '' : text).replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    }
    function encode(text) {
        text = normalize(text);
        return eol === '\n' ? text : text.replace(/\n/g, eol);
    }
    function getContent() {
        return normalize(code ? code.get() : editor.value);
    }
    function setContent(value) {
        applying = true;
        value = normalize(value);
        if (code) code.set(value);
        else editor.value = value;
        applying = false;
    }
    function dirty() {
        return current !== '' && getContent() !== saved;
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '';
        var d = new Date(ts * 1000);
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function cursorBit() {
        if (!code || !current) return '';
        var c = code.cursor();
        return (c.line + 1) + '行 ' + (c.ch + 1) + '列';
    }
    function paintPos() {
        var pos = cursorBit();
        metaPos.hidden = !pos;
        metaPos.textContent = pos;
    }
    function updateChrome() {
        var label = (document.querySelector('.tpl-file.active') || {}).getAttribute('data-label') || current || '模板';
        titleEl.title = label;
        titleEl.textContent = label + (dirty() ? ' *' : '');
        saveBtn.disabled = !current;
        backupBtn.disabled = !current;
        rollbackBtn.disabled = !current || backupAt < 1;
        findBtn.hidden = !current;
    }
    function setMeta(label, path, bak) {
        backupAt = parseInt(bak, 10) || 0;
        var bits = [path];
        if (backupAt) bits.push('上次备份 ' + fmtTime(backupAt));
        else bits.push('还没有备份');
        metaMain.textContent = bits.join(' · ');
        paintPos();
        updateChrome();
    }
    function sizeEditor() {
        if (code && code.size) code.size();
    }
    function showEditor(on) {
        emptyEl.hidden = on;
        if (code) {
            editor.hidden = true;
            code.show(on);
            usingCode = !!on;
        } else {
            editor.hidden = !on;
            usingCode = false;
        }
        if (!on) closePlainFind();
    }
    function markDirtyTitle() {
        updateChrome();
        paintPos();
    }
    function markActive(path) {
        document.querySelectorAll('.tpl-file').forEach(function (x) {
            x.classList.toggle('active', x.getAttribute('data-path') === path);
            x.classList.toggle('is-loading', false);
        });
        expandGroupForPath(path, true);
    }
    function openFile(a) {
        var path = a.getAttribute('data-path');
        var label = a.getAttribute('data-label') || path;
        if (path === current) {
            if (code) code.focus();
            else editor.focus();
            return;
        }
        if (current && dirty() && !U.confirm('当前文件还没保存，换过去会丢掉改动。确定？')) return;
        var seq = ++loadSeq;
        a.classList.add('is-loading');
        U.get('/admin/video/templates/read', {path: path}).then(function (res) {
            a.classList.remove('is-loading');
            if (seq !== loadSeq) return;
            if (!res || res.code !== 0) {
                U.toast((res && (res.msg || res.message)) || '读取失败', 'err');
                return;
            }
            var raw = (res.data && res.data.content) || '';
            eol = sniffEol(raw);
            current = path;
            saved = normalize(raw);
            try {
                setContent(saved);
                showEditor(true);
            } catch (err) {
                usingCode = false;
                editor.hidden = false;
                if (code) code.show(false);
                editor.value = saved;
                U.toast('高亮没加上，已用文本框打开', 'err');
            }
            markActive(path);
            setMeta((res.data && res.data.label) || label, path, res.data && res.data.backup_at);
            var url = new URL(window.location.href);
            url.searchParams.set('path', path);
            history.replaceState(null, '', url);
        }).catch(function () {
            a.classList.remove('is-loading');
            if (seq !== loadSeq) return;
            U.toast('读取失败', 'err');
        });
    }

    document.querySelectorAll('.tpl-file').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            openFile(a);
        });
    });
    function onEditorInput() { if (!applying) markDirtyTitle(); }
    if (code) {
        code.onChange(onEditorInput);
        code.onCursor(paintPos);
    } else {
        editor.addEventListener('input', onEditorInput);
    }
    window.addEventListener('beforeunload', function (e) {
        if (!dirty()) return;
        e.preventDefault();
        e.returnValue = '';
    });
    window.addEventListener('resize', function () {
        if (current) sizeEditor();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && findBar && !findBar.hidden) {
            closePlainFind();
            e.preventDefault();
            if (!editor.hidden) editor.focus();
            return;
        }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'f' || e.key === 'F')) {
            if (!current || document.querySelector('.ui-mask')) return;
            if (e.target && e.target.closest && e.target.closest('.CodeMirror')) return;
            e.preventDefault();
            openFind();
            return;
        }
        if (!(e.ctrlKey || e.metaKey) || (e.key !== 's' && e.key !== 'S')) return;
        e.preventDefault();
        saveFile(true);
    });

    var search = document.getElementById('tpl-search');
    var filterEmpty = document.getElementById('tpl-filter-empty');
    var filesRoot = document.getElementById('tpl-files');
    var foldKey = 'laravideo.tpl.fold.' + ((filesRoot && filesRoot.getAttribute('data-theme')) || 'default');
    function readFoldMap() {
        try {
            var raw = localStorage.getItem(foldKey);
            var data = raw ? JSON.parse(raw) : {};
            return data && typeof data === 'object' && !Array.isArray(data) ? data : {};
        } catch (e) {
            return {};
        }
    }
    function writeFoldKey(key, expanded) {
        if (!key) return;
        var data = readFoldMap();
        data[key] = !!expanded;
        try { localStorage.setItem(foldKey, JSON.stringify(data)); } catch (e) {}
    }
    function applyFold(box, expanded, persist) {
        if (!box) return;
        var btn = box.querySelector('.tpl-group-toggle');
        box.classList.toggle('is-folded', !expanded);
        if (btn) btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        if (persist) writeFoldKey(box.getAttribute('data-group'), expanded);
    }
    function restoreFolds() {
        var data = readFoldMap();
        document.querySelectorAll('[data-group]').forEach(function (box) {
            applyFold(box, data[box.getAttribute('data-group')] !== false, false);
        });
    }
    function expandGroupForPath(path, persist) {
        if (!path) return;
        document.querySelectorAll('.tpl-file').forEach(function (a) {
            if (a.getAttribute('data-path') !== path) return;
            applyFold(a.closest('[data-group]'), true, persist);
        });
    }
    document.querySelectorAll('.tpl-group-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var box = btn.closest('[data-group]');
            if (!box) return;
            applyFold(box, box.classList.contains('is-folded'), true);
        });
    });
    restoreFolds();
    function filterFiles() {
        if (!search) return;
        var q = (search.value || '').trim().toLowerCase();
        var hit = false;
        document.querySelectorAll('[data-group]').forEach(function (box) {
            var any = false;
            box.querySelectorAll('.tpl-file').forEach(function (a) {
                var hay = ((a.getAttribute('data-label') || '') + ' ' + (a.getAttribute('data-path') || '')).toLowerCase();
                var on = !q || hay.indexOf(q) >= 0;
                a.hidden = !on;
                if (on) any = true;
            });
            box.hidden = !any;
            if (any && q) applyFold(box, true, false);
            if (any) hit = true;
        });
        if (!q) {
            restoreFolds();
            expandGroupForPath(current, false);
        }
        if (filterEmpty) filterEmpty.hidden = !q || hit;
    }
    if (search) {
        search.addEventListener('input', filterFiles);
        search.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || !search.value) return;
            search.value = '';
            filterFiles();
            e.preventDefault();
        });
    }

    function saveFile(quiet) {
        if (!current) {
            U.toast('请先选一个页面', 'err');
            return;
        }
        if (saving) return;
        if (!dirty()) {
            U.toast('没有改动，不用保存');
            return;
        }
        if (!quiet && !U.confirm('保存会覆盖这个页面，并先做一份备份。确定？')) return;
        saving = true;
        var payload = encode(getContent());
        var snap = normalize(payload);
        U.post('/admin/video/templates/save', {path: current, content: payload}).then(function (res) {
            if (res && res.code === 0) {
                saved = snap;
                var label = (document.querySelector('.tpl-file.active') || {}).getAttribute('data-label') || current;
                setMeta(label, current, res.data && res.data.backup_at);
            }
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        }).catch(function () {
            U.toast('保存失败', 'err');
        }).then(function () {
            saving = false;
        });
    }
    function closePlainFind() {
        if (findBar) findBar.hidden = true;
    }
    function parseFindQuery(raw) {
        raw = String(raw || '');
        if (!raw) return null;
        var isRE = raw.match(/^\/(.*)\/([a-z]*)$/);
        if (isRE) {
            try {
                return new RegExp(isRE[1], (isRE[2].indexOf('i') === -1 ? '' : 'i') + 'g');
            } catch (e) {
                return null;
            }
        }
        var flags = raw === raw.toLowerCase() ? 'gi' : 'g';
        return new RegExp(raw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), flags);
    }
    function findInTextarea(rev) {
        if (!findInput || editor.hidden) return;
        var re = parseFindQuery(findInput.value);
        if (!re) return;
        var text = editor.value || '';
        if (!text) return;
        var from = rev ? editor.selectionStart : editor.selectionEnd;
        var match = null;
        var m;
        if (rev) {
            re.lastIndex = 0;
            while ((m = re.exec(text))) {
                if (m.index >= from) break;
                match = m;
                if (!m[0].length) re.lastIndex++;
            }
            if (!match) {
                re.lastIndex = 0;
                while ((m = re.exec(text))) {
                    match = m;
                    if (!m[0].length) re.lastIndex++;
                }
            }
        } else {
            re.lastIndex = from;
            match = re.exec(text);
            if (!match && from > 0) {
                re.lastIndex = 0;
                match = re.exec(text);
            }
        }
        if (!match || !match[0].length) return;
        editor.focus();
        editor.setSelectionRange(match.index, match.index + match[0].length);
    }
    function openFind() {
        if (!current) return;
        if (usingCode && code && code.find) {
            closePlainFind();
            code.find();
            return;
        }
        if (!findBar || !findInput) return;
        findBar.hidden = false;
        findInput.focus();
        findInput.select();
    }
    U.on('#tpl-save', 'click', function () { saveFile(false); });
    U.on('#tpl-find', 'click', function () { openFind(); });
    if (findInput) {
        findInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                findInTextarea(e.shiftKey);
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                closePlainFind();
                if (!editor.hidden) editor.focus();
            }
        });
    }
    U.on('#tpl-backup', 'click', function () {
        if (!current) { U.toast('请先选一个页面', 'err'); return; }
        U.post('/admin/video/templates/backup', {path: current}).then(function (res) {
            if (res && res.code === 0) {
                var label = (document.querySelector('.tpl-file.active') || {}).getAttribute('data-label') || current;
                setMeta(label, current, res.data && res.data.backup_at);
            }
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
    U.on('#tpl-rollback', 'click', function () {
        if (!current) { U.toast('请先选一个页面', 'err'); return; }
        if (!U.confirm('回到最近一次备份？现在编辑器里没保存的改动会丢掉。')) return;
        U.post('/admin/video/templates/rollback', {path: current}).then(function (res) {
            if (res && res.code === 0) {
                U.get('/admin/video/templates/read', {path: current}).then(function (r) {
                    if (r && r.code === 0) {
                        saved = normalize((r.data && r.data.content) || '');
                        setContent(saved);
                        var label = (r.data && r.data.label) || current;
                        setMeta(label, current, r.data && r.data.backup_at);
                    }
                });
            }
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });

    function isImage(row) {
        var mime = String((row && row.mime) || '');
        var url = String((row && row.url) || (row && row.path) || '');
        if (parseInt(row && row.type, 10) === 1) return true;
        if (mime.indexOf('image/') === 0) return true;
        return /\.(jpe?g|png|gif|webp|svg|ico)(\?|$)/i.test(url);
    }
    function fileUrl(row) {
        var url = String((row && row.url) || (row && row.path) || '').trim();
        if (!url) return '';
        if (/^(https?:)?\/\//i.test(url) || url.charAt(0) === '/') return url;
        return '/' + url;
    }
    function fileExt(row) {
        var s = String((row && row.name) || (row && row.url) || '');
        var m = s.match(/\.([a-z0-9]+)(\?|$)/i);
        return m ? m[1].toUpperCase() : '文件';
    }
    function snippet(row) {
        var url = fileUrl(row);
        if (!url) return '';
        if (isImage(row)) return '<img src="' + url + '" alt="">';
        return url;
    }
    function insertAtCursor(text) {
        if (!text) return;
        if (!current) {
            copyText(text);
            U.toast('已复制，打开页面后可粘贴到光标处', 'ok');
            return;
        }
        if (code) {
            code.insert(text);
            markDirtyTitle();
            U.toast('已插入到光标处', 'ok');
            return;
        }
        editor.focus();
        var start = editor.selectionStart;
        var end = editor.selectionEnd;
        var val = editor.value;
        editor.value = val.slice(0, start) + text + val.slice(end);
        editor.selectionStart = editor.selectionEnd = start + text.length;
        editor.dispatchEvent(new Event('input'));
        U.toast('已插入到光标处', 'ok');
    }
    function copyText(text) {
        if (!text) return Promise.resolve();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).catch(function () {});
        }
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        return Promise.resolve();
    }
    function uploadFiles(files, after) {
        files = Array.prototype.slice.call(files || []).filter(Boolean);
        if (!files.length) return Promise.resolve();
        U.loading(true);
        var chain = Promise.resolve();
        files.forEach(function (file) {
            chain = chain.then(function () {
                return U.upload(file, '/admin/system/attachments/upload').then(function (res) {
                    if (!res || res.code !== 0) {
                        U.toast((res && res.msg) || (file.name + ' 失败'), 'err');
                        return;
                    }
                    if (after) after(res.data || {});
                    else U.toast(file.name + ' 已上传', 'ok');
                });
            });
        });
        return chain.then(function () { U.loading(false); });
    }
    function bindDrop(el, onFiles) {
        if (!el) return;
        el.addEventListener('dragover', function (e) {
            e.preventDefault();
            el.classList.add('is-drag');
        });
        el.addEventListener('dragleave', function () { el.classList.remove('is-drag'); });
        el.addEventListener('drop', function (e) {
            e.preventDefault();
            el.classList.remove('is-drag');
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                onFiles(e.dataTransfer.files);
            }
        });
    }
    function openPicker() {
        var active = document.querySelector('.tpl-file.active');
        var currentLabel = (active && active.getAttribute('data-label')) || current;
        var hint = current
            ? '点缩略图插到光标处。图片写成 img 标签，其它文件只写地址。'
            : '还没打开页面，点插入只会复制地址。先在左边打开一个模板。';
        U.dialog({
            title: current ? ('插入到「' + currentLabel + '」') : '插入附件',
            wide: true,
            hideOk: true,
            hideFoot: true,
            content: '<p class="muted tpl-picker-lead">' + hint + '</p>'
                + '<div class="tpl-picker-drop" id="tpl-picker-drop">把 Logo、海报拖到这里，或点击上传（图片，最大 10MB）'
                + '<input type="file" id="tpl-picker-input" multiple hidden accept="image/*,.svg,.ico,.webp,.css"></div>'
                + '<div class="tpl-picker-bar"><input type="search" id="tpl-picker-q" placeholder="搜文件名，如 logo、海报" autocomplete="off">'
                + '<a href="/admin/system/attachments" target="_blank">去附件库</a>'
                + '<button type="button" class="btn btn-muted btn-sm" id="tpl-picker-reload">刷新</button></div>'
                + '<div id="tpl-picker-grid" class="tpl-picker-grid"></div>'
                + '<div id="tpl-picker-pager"></div>',
            onOpen: function (body) {
                var state = { page: 1, keyword: '' };
                var grid = body.querySelector('#tpl-picker-grid');
                var pagerEl = body.querySelector('#tpl-picker-pager');
                var drop = body.querySelector('#tpl-picker-drop');
                var input = body.querySelector('#tpl-picker-input');
                var q = body.querySelector('#tpl-picker-q');
                function load() {
                    U.get('/admin/system/attachments/list', {
                        page: state.page,
                        limit: 18,
                        keyword: state.keyword
                    }).then(function (res) {
                        var d = (res && res.data) || {};
                        var list = Array.isArray(d) ? d : (d.data || []);
                        var total = d.total != null ? d.total : list.length;
                        if (!list.length) {
                            grid.innerHTML = state.keyword
                                ? '<div class="list-empty tpl-picker-empty"><p>没有叫这个名字的文件。</p><p class="muted">换个词，或把文件拖到上面上传。</p></div>'
                                : '<div class="list-empty tpl-picker-empty"><p>还没有可用的图。</p><p class="muted">把海报或图标拖到上面，插入时会写进当前模板。</p></div>';
                            pagerEl.innerHTML = '';
                            return;
                        }
                        grid.innerHTML = list.map(function (row) {
                            var url = U.escape(fileUrl(row));
                            var name = U.escape(row.name || url);
                            var thumb = isImage(row)
                                ? '<div class="tpl-picker-thumb"><img src="' + url + '" alt=""></div>'
                                : '<div class="tpl-picker-file"><span>' + U.escape(fileExt(row)) + '</span></div>';
                            return '<div class="tpl-picker-card" data-id="' + U.escape(row.id) + '" title="' + name + '">'
                                + thumb
                                + '<span class="entry-row-title">' + name + '</span>'
                                + '<p class="muted">' + U.escape(row.size_text || '') + '</p>'
                                + '<div class="tpl-picker-actions">'
                                + '<button type="button" class="btn btn-sm js-insert">插入</button>'
                                + '<button type="button" class="btn btn-muted btn-sm js-copy">复制地址</button>'
                                + '</div></div>';
                        }).join('');
                        grid._rows = list;
                        Array.prototype.forEach.call(grid.querySelectorAll('.tpl-picker-thumb img'), function (img) {
                            function markBroken() {
                                var thumb = img.closest('.tpl-picker-thumb');
                                if (thumb) thumb.classList.add('is-broken');
                            }
                            img.addEventListener('error', markBroken);
                            if (img.complete && !img.naturalWidth) markBroken();
                        });
                        U.pager(pagerEl, {
                            page: d.current_page || state.page,
                            last: d.last_page || Math.max(1, Math.ceil(total / 18)),
                            total: total
                        }, function (p) {
                            state.page = p;
                            load();
                        });
                    });
                }
                function afterUpload(row) {
                    insertAtCursor(snippet(row));
                    state.page = 1;
                    load();
                }
                drop.addEventListener('click', function () { input.click(); });
                input.addEventListener('change', function () {
                    uploadFiles(input.files, afterUpload).then(function () { input.value = ''; });
                });
                bindDrop(drop, function (files) { uploadFiles(files, afterUpload); });
                body.querySelector('#tpl-picker-reload').addEventListener('click', function () { load(); });
                var timer = 0;
                q.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        state.keyword = (q.value || '').trim();
                        state.page = 1;
                        load();
                    }, 250);
                });
                grid.addEventListener('click', function (e) {
                    var card = e.target.closest('.tpl-picker-card');
                    if (!card) return;
                    var row = (grid._rows || []).filter(function (r) {
                        return String(r.id) === String(card.getAttribute('data-id'));
                    })[0];
                    if (!row) return;
                    if (e.target.closest('.js-copy')) {
                        e.stopPropagation();
                        copyText(fileUrl(row)).then(function () { U.toast('已复制地址', 'ok'); });
                        return;
                    }
                    insertAtCursor(snippet(row));
                });
                load();
            }
        });
    }
    U.on('#tpl-attach', 'click', openPicker);
    bindDrop(code ? code.wrap : editor, function (files) {
        uploadFiles(files, function (row) { insertAtCursor(snippet(row)); });
    });

    var boot = new URLSearchParams(location.search).get('path');
    if (boot) {
        expandGroupForPath(boot, true);
        document.querySelectorAll('.tpl-file').forEach(function (a) {
            if (a.getAttribute('data-path') === boot) openFile(a);
        });
    }
})();
</script>
@endpush
