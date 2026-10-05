(function (global) {
    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function escape(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function flatten(data, prefix, out) {
        out = out || {};
        Object.keys(data || {}).forEach(function (k) {
            var key = prefix ? prefix + '[' + k + ']' : k;
            var v = data[k];
            if (v && typeof v === 'object' && !Array.isArray(v) && !(v instanceof File)) {
                flatten(v, key, out);
            } else {
                out[key] = v;
            }
        });
        return out;
    }

    function qs(data) {
        if (!data) return '';
        if (typeof data === 'string') return data;
        var p = new URLSearchParams();
        var flat = flatten(data);
        Object.keys(flat).forEach(function (k) {
            var v = flat[k];
            if (v === undefined || v === null) return;
            if (Array.isArray(v)) {
                v.forEach(function (item) { p.append(k + '[]', item); });
            } else {
                p.append(k, v);
            }
        });
        return p.toString();
    }

    function pickMsg(json, fallback) {
        if (json && typeof json === 'object') {
            if (json.msg) return String(json.msg);
            if (json.message) return String(json.message);
            if (json.errors) {
                var keys = Object.keys(json.errors);
                if (keys.length) {
                    var first = json.errors[keys[0]];
                    return Array.isArray(first) ? String(first[0] || '') : String(first);
                }
            }
        }
        return fallback || '请求失败';
    }

    function statusMsg(r) {
        if (!r) return '请求失败';
        if (r.status === 419) return '页面已过期，请刷新后再试';
        if (r.status === 403) return '没有权限做这项操作';
        if (r.status === 404) return '接口不存在';
        if (r.status === 429) return '操作太频繁，请稍后再试';
        if (r.status >= 500) return '服务器出错了，请稍后再试';
        return '请求失败';
    }

    function request(method, url, data) {
        var opts = {
            method: method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf(),
                Accept: 'application/json'
            }
        };
        if (method === 'GET') {
            var q = qs(data);
            if (q) url += (url.indexOf('?') >= 0 ? '&' : '?') + q;
        } else if (data instanceof FormData) {
            if (!data.has('_token')) data.append('_token', csrf());
            opts.body = data;
        } else {
            opts.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            var payload = Object.assign({}, data || {});
            if (!payload._token) payload._token = csrf();
            opts.body = qs(payload);
        }
        return fetch(url, opts).then(function (r) {
            return r.json().then(function (json) {
                if (!json || typeof json !== 'object') {
                    return { code: 1, msg: statusMsg(r) };
                }
                if (json.code === undefined) json.code = r.ok ? 0 : 1;
                json.msg = pickMsg(json, statusMsg(r));
                return json;
            }).catch(function () {
                return { code: 1, msg: statusMsg(r) };
            });
        }).catch(function () {
            return { code: 1, msg: '网络错误' };
        });
    }

    function toast(msg, type) {
        var old = document.querySelector('.ui-toast');
        if (old) old.remove();
        var el = document.createElement('div');
        el.className = 'ui-toast' + (type === 'ok' ? ' is-ok' : type === 'err' ? ' is-err' : '');
        el.textContent = msg || '';
        document.body.appendChild(el);
        if (type === 'err') return;
        setTimeout(function () { el.remove(); }, 2400);
    }

    function loading(on, msg) {
        var el = document.getElementById('ui-loading');
        if (!on) {
            if (el) el.remove();
            return;
        }
        var title = '';
        var text = '处理中…';
        var kind = '';
        if (typeof msg === 'string' && msg !== '') {
            text = msg;
        } else if (msg && typeof msg === 'object') {
            title = String(msg.title || '');
            text = String(msg.text || text);
            kind = String(msg.kind || '').replace(/[^a-z0-9_-]/gi, '');
        }
        if (!el) {
            el = document.createElement('div');
            el.id = 'ui-loading';
            document.body.appendChild(el);
        }
        if (!el.querySelector('.ui-loading-log')) {
            el.innerHTML = '<div class="ui-loading-card">'
                + '<span class="ui-loading-orbit" aria-hidden="true"><i></i></span>'
                + '<strong class="ui-loading-title"></strong>'
                + '<p class="ui-loading-text"></p>'
                + '<div class="ui-loading-stats" hidden></div>'
                + '<p class="ui-loading-meta" hidden></p>'
                + '<div class="ui-loading-log" hidden></div>'
                + '<div class="ui-loading-bar"><i></i></div>'
                + '</div>';
        }
        var done = !!(msg && typeof msg === 'object' && msg.done);
        el.className = 'ui-loading' + (kind ? ' is-' + kind : '') + (done ? ' is-done' : '');
        el.onclick = done ? function (e) { if (e.target === el) loading(false); } : null;
        var t = el.querySelector('.ui-loading-title');
        var p = el.querySelector('.ui-loading-text');
        var statsEl = el.querySelector('.ui-loading-stats');
        var metaEl = el.querySelector('.ui-loading-meta');
        if (t) {
            t.textContent = title;
            t.hidden = title === '';
        }
        if (p) p.textContent = text;
        var stats = (msg && typeof msg === 'object' && Array.isArray(msg.stats)) ? msg.stats : [];
        if (statsEl) {
            if (stats.length) {
                statsEl.hidden = false;
                statsEl.innerHTML = stats.map(function (s) {
                    var key = String((s && s.key) || '').replace(/[^a-z0-9_-]/gi, '');
                    var n = (s && s.n != null) ? s.n : 0;
                    var label = (s && s.label) || '';
                    return '<span class="ui-loading-stat is-' + key + '"><b>' + escape(n) + '</b>' + escape(label) + '</span>';
                }).join('');
            } else {
                statsEl.hidden = true;
                statsEl.innerHTML = '';
            }
        }
        var meta = (msg && typeof msg === 'object') ? String(msg.meta || '') : '';
        if (metaEl) {
            metaEl.textContent = meta;
            metaEl.hidden = meta === '';
        }
        var logEl = el.querySelector('.ui-loading-log');
        var logs = (msg && typeof msg === 'object' && Array.isArray(msg.logs)) ? msg.logs : [];
        if (logEl) {
            if (logs.length) {
                logEl.hidden = false;
                logEl.innerHTML = logs.map(function (row) {
                    var tone = String((row && row.tone) || '').replace(/[^a-z0-9_-]/gi, '');
                    var tag = (row && row.tag) || '';
                    var body = (row && row.text) || '';
                    return '<div class="ui-loading-log-row is-' + tone + '"><span class="tag">' + escape(tag) + '</span><span class="body">' + escape(body) + '</span></div>';
                }).join('');
                logEl.scrollTop = logEl.scrollHeight;
                if (p) p.hidden = true;
            } else {
                logEl.hidden = true;
                logEl.innerHTML = '';
                if (p) p.hidden = false;
            }
        }
    }

    function formData(form) {
        var el = typeof form === 'string' ? document.querySelector(form) : form;
        var obj = {};
        if (!el) return obj;
        new FormData(el).forEach(function (v, k) { obj[k] = v; });
        return obj;
    }

    function fillForm(form, data) {
        var el = typeof form === 'string' ? document.querySelector(form) : form;
        if (!el || !data) return;
        Array.prototype.forEach.call(el.elements, function (field) {
            if (!field.name) return;
            if (!(field.name in data)) return;
            var val = data[field.name];
            if (val === undefined || val === null) val = '';
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = String(field.value) === String(val);
            } else {
                field.value = String(val);
            }
        });
    }

    var lang = {};

    function setLang(map) {
        lang = map && typeof map === 'object' ? map : {};
    }

    function _(key, fallback) {
        var v = lang[key];
        return v != null && String(v) !== '' ? String(v) : (fallback == null ? key : fallback);
    }

    function dialog(opts) {
        opts = opts || {};
        var mask = document.createElement('div');
        mask.className = 'ui-mask';
        var box = document.createElement('div');
        box.className = 'ui-dialog' + (opts.wide ? ' is-wide' : '');
        if (opts.width) box.style.maxWidth = opts.width;
        var head = document.createElement('div');
        head.className = 'ui-dialog-head';
        var title = document.createElement('span');
        title.textContent = opts.title || '';
        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn btn-muted btn-sm';
        closeBtn.textContent = opts.closeText || _('close', '关闭');
        head.appendChild(title);
        head.appendChild(closeBtn);
        var body = document.createElement('div');
        body.className = 'ui-dialog-body';
        if (typeof opts.content === 'string') {
            var tmp = document.createElement('div');
            tmp.innerHTML = opts.content;
            stripAutofocus(tmp);
            while (tmp.firstChild) body.appendChild(tmp.firstChild);
        } else if (opts.content) {
            stripAutofocus(opts.content);
            body.appendChild(opts.content);
        }
        var foot = document.createElement('div');
        foot.className = 'ui-dialog-foot';
        var cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn btn-muted';
        cancel.textContent = opts.cancelText || _('cancel', '取消');
        var save = document.createElement('button');
        save.type = 'button';
        save.className = 'btn';
        save.textContent = opts.okText || _('save', '保存');
        if (opts.hideOk) save.style.display = 'none';
        foot.appendChild(cancel);
        if (!opts.hideOk) foot.appendChild(save);
        box.appendChild(head);
        box.appendChild(body);
        if (!opts.hideFoot) box.appendChild(foot);
        mask.appendChild(box);
        stripAutofocus(box);
        document.body.appendChild(mask);
        quietFocus(box.querySelector('input:not([type=hidden]), textarea, select'));
        function close() {
            if (mask.parentNode) mask.remove();
        }
        closeBtn.onclick = cancel.onclick = close;
        mask.addEventListener('click', function (e) {
            if (e.target === mask) close();
        });
        save.onclick = function () {
            var result = opts.onSave && opts.onSave(body, close);
            Promise.resolve(result).then(function (ok) {
                if (ok === false) return;
                close();
            });
        };
        if (opts.onOpen) opts.onOpen(body, close);
        return { el: body, close: close };
    }

    function pager(el, meta, onGo) {
        el = typeof el === 'string' ? document.querySelector(el) : el;
        if (!el) return;
        var page = parseInt(meta.page || meta.current_page || 1, 10) || 1;
        var last = parseInt(meta.last || meta.last_page || 1, 10) || 1;
        var total = parseInt(meta.total || 0, 10) || 0;
        var html = '<nav class="pagination" aria-label="' + escape(_('pager', '分页')) + '">';
        html += '<span>' + escape(String(_('n_items', '共:n条')).split(':n').join(String(total))) + '</span>';
        if (page > 1) html += '<a href="#" data-p="' + (page - 1) + '" rel="prev">' + escape(_('prev_page', '上一页')) + '</a>';
        html += '<span>' + page + '/' + last + '</span>';
        if (page < last) html += '<a href="#" data-p="' + (page + 1) + '" rel="next">' + escape(_('next_page', '下一页')) + '</a>';
        html += '</nav>';
        el.innerHTML = html;
        Array.prototype.forEach.call(el.querySelectorAll('a[data-p]'), function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                onGo(parseInt(a.getAttribute('data-p'), 10));
            });
        });
    }

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] === undefined || data[k] === null) return;
            if (String(data[k]).trim() === '') return;
            out[k] = data[k];
        });
        return out;
    }

    function eventEl(e) {
        var t = e && e.target;
        if (t && t.nodeType === 3) t = t.parentNode;
        return t && t.nodeType === 1 ? t : null;
    }

    function rowFromClick(e, api) {
        var t = eventEl(e);
        if (!t || !t.closest) return null;
        if (t.closest('.js-pager, .pagination')) return null;
        var tr = t.closest('tr[data-idx]');
        if (!tr) return null;
        var rows = api && api.rows ? api.rows() : [];
        return rows[tr.getAttribute('data-idx')] || null;
    }

    function nearestFilter(wrap) {
        var root = wrap.closest('.card-body, .card-panel, .card, .content') || wrap.parentNode;
        if (!root || !root.querySelectorAll) return null;
        var forms = root.querySelectorAll('form.filter-bar');
        for (var i = 0; i < forms.length; i++) {
            var id = forms[i].id || '';
            var cls = forms[i].className || '';
            if (/-try/.test(id) || /try-bar/.test(cls)) continue;
            return forms[i];
        }
        return null;
    }

    function bindFilter(wrap, api, opts) {
        var form = opts.filter
            ? (typeof opts.filter === 'string' ? document.querySelector(opts.filter) : opts.filter)
            : nearestFilter(wrap);
        if (!form || form._adminTableBound) return;
        form._adminTableBound = true;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            api.reload(cleanWhere(formData(form)));
        });
        form.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            var tag = e.target && e.target.tagName;
            if (tag !== 'INPUT' && tag !== 'SELECT') return;
            e.preventDefault();
            api.reload(cleanWhere(formData(form)));
        });
        form.addEventListener('reset', function () {
            setTimeout(function () {
                resetQueueFields(form, opts.queueKeys || []);
                markQueueChips(form, opts.queueRoot || null);
                if (typeof opts.afterReset === 'function') {
                    opts.afterReset(form);
                }
                api.reload(cleanWhere(formData(form)));
            }, 0);
        });
    }

    /** Clear hidden queue filter fields on a filter form. */
    function resetQueueFields(form, keys) {
        if (!form) return;
        (keys || []).forEach(function (k) {
            var el = form.elements && form.elements[k]
                ? form.elements[k]
                : form.querySelector('[name="' + k + '"]');
            if (el && el.tagName) {
                el.value = '';
            }
        });
    }

    /** Activate the "all" chip (empty data-queue / first chip) after reset. */
    function markQueueChips(form, root) {
        var chipsRoot = root
            ? (typeof root === 'string' ? document.querySelector(root) : root)
            : (form && form.parentNode ? form.parentNode.querySelector('.queue-chips') : null);
        if (!chipsRoot) return;
        var chips = chipsRoot.querySelectorAll('button.chip, a.chip');
        var all = null;
        Array.prototype.forEach.call(chips, function (c) {
            var q = c.getAttribute('data-queue');
            if (q === '' || q === null) {
                if (!all) all = c;
            }
            c.classList.remove('active');
        });
        if (!all && chips.length) all = chips[0];
        if (all) all.classList.add('active');
    }

    /** Set header <em> count from list API meta.total. */
    function headerCount(el, meta) {
        if (!el) return;
        var total = 0;
        if (meta && meta.total != null) {
            total = parseInt(meta.total, 10) || 0;
        }
        el.textContent = total > 0 ? ('· ' + total) : '';
    }

    function table(opts) {
        var wrap = typeof opts.el === 'string' ? document.querySelector(opts.el) : opts.el;
        if (!wrap) {
            return {
                reload: function () { return Promise.resolve(); },
                refresh: function () { return Promise.resolve(); },
                selectedIds: function () { return []; },
                clearSelection: function () {},
                rows: function () { return []; }
            };
        }
        wrap.classList.add('js-table-mount');
        if (!String(wrap.innerHTML || '').trim()) {
            wrap.innerHTML = '<div class="ui-table-pending" aria-hidden="true"></div>';
        }
        var limit = parseInt(opts.limit, 10) || 15;
        var state = { page: 1, where: Object.assign({}, opts.where || {}) };
        var countEl = opts.countEl
            ? (typeof opts.countEl === 'string' ? document.querySelector(opts.countEl) : opts.countEl)
            : null;

        function rowsFrom(res) {
            var d = res.data || {};
            var list = Array.isArray(d) ? d : (d.data || []);
            var total = d.total != null ? parseInt(d.total, 10) : list.length;
            var per = parseInt(d.per_page, 10) || limit;
            var last = parseInt(d.last_page, 10);
            if (!last) last = Math.max(1, Math.ceil((total || 0) / per));
            var page = parseInt(d.current_page, 10);
            if (!page) page = state.page;
            var dumped = list.length === total && total > per && d.current_page == null && (d.last_page == null || last > 1);
            if (dumped) {
                last = Math.max(1, Math.ceil(total / per));
                if (page > last) page = last;
                list = list.slice((page - 1) * per, page * per);
            }
            return {
                list: list,
                total: total,
                page: page,
                last: last,
                types: d.types || [],
                queues: d.queues || {},
                groups: d.groups || [],
                families: d.families || [],
                away: !!d.away
            };
        }

        function render(res) {
            var parsed = rowsFrom(res);
            state.page = parsed.page;
            var cols = opts.cols || [];
            var html = '<div class="ui-table-wrap"><table class="data"><thead><tr>';
            cols.forEach(function (c) {
                html += '<th' + (c.width ? ' style="width:' + c.width + 'px"' : '') + (c.check ? ' class="col-check"' : '') + '>';
                html += c.check ? '<input type="checkbox" class="js-check-all">' : escape(c.title || '');
                html += '</th>';
            });
            html += '</tr></thead><tbody>';
            if (!parsed.list.length) {
                var empty = typeof opts.emptyHtml === 'function'
                    ? opts.emptyHtml(parsed, state.where)
                    : (opts.emptyHtml || '<div class="list-empty"><p>暂无数据</p></div>');
                html += '<tr><td colspan="' + cols.length + '">' + empty + '</td></tr>';
            } else {
                parsed.list.forEach(function (row, idx) {
                    html += '<tr data-idx="' + idx + '">';
                    cols.forEach(function (c) {
                        if (c.check) {
                            html += '<td class="col-check"><input type="checkbox" class="js-check" value="' + escape(row.id) + '"></td>';
                            return;
                        }
                        var cell = c.html ? c.html(row) : escape(row[c.key] == null ? '' : row[c.key]);
                        html += '<td class="' + (c.cls || '') + '">' + (cell == null ? '' : cell) + '</td>';
                    });
                    html += '</tr>';
                });
            }
            html += '</tbody></table></div><div class="js-pager"></div>';
            wrap.innerHTML = html;
            wrap._rows = parsed.list;
            var pagerEl = wrap.querySelector('.js-pager');
            if (opts.pager !== false && parsed.total > 0) {
                pager(pagerEl, parsed, function (p) {
                    state.page = p;
                    load();
                });
            }
            var all = wrap.querySelector('.js-check-all');
            function fireCheck() {
                if (opts.onCheck) opts.onCheck(wrap._table.selectedIds(), wrap._rows || []);
            }
            if (all) {
                all.addEventListener('change', function () {
                    Array.prototype.forEach.call(wrap.querySelectorAll('.js-check'), function (cb) {
                        cb.checked = all.checked;
                    });
                    fireCheck();
                });
            }
            Array.prototype.forEach.call(wrap.querySelectorAll('.js-check'), function (cb) {
                cb.addEventListener('change', fireCheck);
            });
            fireCheck();
            if (countEl) headerCount(countEl, parsed);
            if (opts.onDraw) opts.onDraw(wrap, parsed.list, parsed);
        }

        function failHtml(msg) {
            return '<div class="list-empty"><p>' + escape(msg || '加载失败') + '</p></div>';
        }

        function load() {
            var params = Object.assign({}, state.where, { page: state.page, limit: limit });
            return request('GET', opts.url, params).then(function (res) {
                if (res && res.code === 0) {
                    render(res);
                    return res;
                }
                var msg = (res && res.msg) || '加载失败';
                toast(msg, 'err');
                wrap.innerHTML = failHtml(msg);
                wrap._rows = [];
                if (countEl) headerCount(countEl, { total: 0 });
                if (opts.onDraw) opts.onDraw(wrap, [], { list: [], total: 0, page: 1, last: 1 });
                return res;
            }).catch(function () {
                wrap.innerHTML = failHtml('加载失败');
                wrap._rows = [];
                if (countEl) headerCount(countEl, { total: 0 });
            });
        }

        wrap._table = {
            reload: function (where) {
                if (where) state.where = Object.assign({}, where);
                state.page = 1;
                return load();
            },
            refresh: function () { return load(); },
            selectedIds: function () {
                return Array.prototype.map.call(wrap.querySelectorAll('.js-check:checked'), function (cb) {
                    return cb.value;
                });
            },
            clearSelection: function () {
                Array.prototype.forEach.call(wrap.querySelectorAll('.js-check, .js-check-all'), function (cb) {
                    cb.checked = false;
                });
                if (opts.onCheck) opts.onCheck([], wrap._rows || []);
            },
            rows: function () { return wrap._rows || []; }
        };
        bindFilter(wrap, wrap._table, opts);
        load();
        return wrap._table;
    }

    function upload(file, url) {
        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', csrf());
        return request('POST', url || '/admin/system/attachments/upload', fd);
    }

    function pickFile(accept) {
        return new Promise(function (resolve) {
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = accept || 'image/*';
            input.onchange = function () { resolve(input.files && input.files[0] ? input.files[0] : null); };
            input.click();
        });
    }

    function resolveEl(root, sel) {
        if (!sel) return null;
        if (typeof sel !== 'string') return sel;
        return (root || document).querySelector(sel);
    }

    /**
     * Bind image URL input + upload button + preview <img>.
     * Creates .img-preview after .field-inline (or the input) when preview is missing.
     */
    function bindImageField(root, opts) {
        opts = opts || {};
        root = root || document;
        var input = resolveEl(root, opts.input);
        if (!input) return null;
        var btn = resolveEl(root, opts.btn);
        var preview = resolveEl(root, opts.preview);
        if (!preview && opts.createPreview !== false) {
            preview = document.createElement('img');
            preview.className = 'img-preview' + (opts.previewClass ? ' ' + opts.previewClass : '');
            preview.alt = '';
            var after = input.closest('.field-inline') || input;
            if (after.parentNode) after.parentNode.insertBefore(preview, after.nextSibling);
        }
        function sync(url) {
            url = String(url == null ? input.value : url).trim();
            if (!preview) return;
            if (url) {
                preview.src = url;
                preview.style.display = 'block';
                preview.hidden = false;
            } else {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                preview.hidden = true;
            }
        }
        sync(input.value);
        if (!input._adminImageBound) {
            input._adminImageBound = true;
            input.addEventListener('input', function () { sync(input.value); });
            input.addEventListener('change', function () { sync(input.value); });
        }
        if (btn && !btn._adminImageBound) {
            btn._adminImageBound = true;
            btn.addEventListener('click', function () {
                pickFile(opts.accept || 'image/*').then(function (file) {
                    if (!file) return;
                    loading(true);
                    return upload(file, opts.uploadUrl).then(function (res) {
                        loading(false);
                        if (res && res.code === 0 && res.data && res.data.url) {
                            input.value = res.data.url;
                            sync(res.data.url);
                            toast(opts.okMsg || '上传成功', 'ok');
                        } else {
                            toast((res && res.msg) || '上传失败', 'err');
                        }
                    }).catch(function () {
                        loading(false);
                        toast('上传失败', 'err');
                    });
                });
            });
        }
        return { sync: sync, input: input, preview: preview, btn: btn };
    }

    /**
     * Searchable multi-select for large catalogs (tags/authors).
     * root[data-search], data-create, data-selected JSON, data-ready, data-placeholder, data-empty
     * data-browse="1" → focus with empty query loads a short list (no full dump).
     */
    function bindPickField(root) {
        if (!root) return { ids: function () { return []; } };
        var ready = root.getAttribute('data-ready') === '1';
        var searchUrl = root.getAttribute('data-search') || '';
        var createUrl = root.getAttribute('data-create') || '';
        var placeholder = root.getAttribute('data-placeholder') || '搜索';
        var emptyHint = root.getAttribute('data-empty') || '';
        var browse = root.getAttribute('data-browse') !== '0';
        var selected = [];
        try { selected = JSON.parse(root.getAttribute('data-selected') || '[]') || []; } catch (e) { selected = []; }
        selected = selected.map(function (r) {
            return { id: parseInt(r.id, 10) || 0, name: String(r.name || '') };
        }).filter(function (r) { return r.id > 0 && r.name; });

        root.innerHTML = '';
        if (!ready) {
            root.innerHTML = '<p class="muted field-hint">相关表还没建，可先保存，迁移后再挂。</p>';
            return { ids: function () { return []; } };
        }

        var chips = document.createElement('div');
        chips.className = 'pick-chips';
        var wrap = document.createElement('div');
        wrap.className = 'pick-search-wrap';
        var input = document.createElement('input');
        input.type = 'search';
        input.setAttribute('autocomplete', 'off');
        input.placeholder = placeholder;
        input.setAttribute('aria-label', placeholder);
        var suggest = document.createElement('ul');
        suggest.className = 'pick-suggest';
        suggest.hidden = true;
        wrap.appendChild(input);
        wrap.appendChild(suggest);
        root.appendChild(chips);
        root.appendChild(wrap);
        if (selected.length === 0 && emptyHint) {
            var hint = document.createElement('p');
            hint.className = 'muted field-hint js-pick-empty';
            hint.innerHTML = emptyHint;
            root.appendChild(hint);
        }

        var timer = null;
        var active = -1;
        var rows = [];

        function hasId(id) {
            return selected.some(function (r) { return r.id === id; });
        }
        function renderChips() {
            chips.innerHTML = '';
            selected.forEach(function (r) {
                var chip = document.createElement('span');
                chip.className = 'pick-chip';
                chip.innerHTML = escape(r.name) + ' <button type="button" aria-label="移除">&times;</button>';
                chip.querySelector('button').addEventListener('click', function () {
                    selected = selected.filter(function (x) { return x.id !== r.id; });
                    renderChips();
                });
                chips.appendChild(chip);
            });
            var empty = root.querySelector('.js-pick-empty');
            if (empty) empty.hidden = selected.length > 0;
        }
        function hideSuggest() {
            suggest.hidden = true;
            suggest.innerHTML = '';
            rows = [];
            active = -1;
        }
        function showSuggest(list, q) {
            rows = list || [];
            suggest.innerHTML = '';
            q = String(q || '').trim();
            if (!rows.length) {
                if (q) {
                    suggest.innerHTML = '<li class="muted">没有匹配，回车可新建「' + escape(q) + '」</li>';
                    suggest.hidden = false;
                } else {
                    hideSuggest();
                }
                active = -1;
                return;
            }
            rows.forEach(function (r) {
                var li = document.createElement('li');
                li.textContent = r.name;
                if (hasId(r.id)) li.className = 'muted';
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    pick(r);
                });
                suggest.appendChild(li);
            });
            suggest.hidden = false;
            active = 0;
            markActive();
        }
        function markActive() {
            Array.prototype.forEach.call(suggest.children, function (li, i) {
                li.classList.toggle('is-on', i === active);
            });
        }
        function pick(row) {
            if (!row || !row.id || hasId(row.id)) { hideSuggest(); input.value = ''; return; }
            selected.push({ id: row.id, name: row.name });
            renderChips();
            input.value = '';
            hideSuggest();
            input.focus();
        }
        function search(q, fromFocus) {
            q = String(q || '').trim();
            if (!q && !fromFocus) { hideSuggest(); return; }
            if (!q && !browse) { hideSuggest(); return; }
            request('GET', searchUrl, { q: q, limit: 12, status: 1 }).then(function (res) {
                if (!res || res.code !== 0) { hideSuggest(); return; }
                var list = (res.data && res.data.data) || res.data || [];
                if (!Array.isArray(list)) list = [];
                showSuggest(list.map(function (r) {
                    return { id: parseInt(r.id, 10) || 0, name: String(r.name || '') };
                }).filter(function (r) { return r.id > 0 && r.name; }), q);
            }).catch(function () { hideSuggest(); });
        }
        function createName(name) {
            name = String(name || '').trim();
            if (!name || !createUrl) return;
            var exist = selected.find(function (r) { return r.name === name; });
            if (exist) { input.value = ''; hideSuggest(); return; }
            loading(true);
            request('POST', createUrl, { name: name, status: 1 }).then(function (res) {
                loading(false);
                if (!res || res.code !== 0) {
                    toast((res && res.msg) || '新建失败', 'err');
                    return;
                }
                var id = parseInt((res.data && res.data.id) || 0, 10) || 0;
                if (id < 1) { toast('新建失败', 'err'); return; }
                pick({ id: id, name: (res.data && res.data.name) || name });
            }).catch(function () { loading(false); toast('新建失败', 'err'); });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value;
            timer = setTimeout(function () { search(q, false); }, 180);
        });
        input.addEventListener('focus', function () {
            if (!String(input.value || '').trim() && browse) {
                search('', true);
            }
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                if (suggest.hidden) return;
                e.preventDefault();
                active = Math.min(active + 1, Math.max(rows.length - 1, 0));
                markActive();
            } else if (e.key === 'ArrowUp') {
                if (suggest.hidden) return;
                e.preventDefault();
                active = Math.max(active - 1, 0);
                markActive();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (!suggest.hidden && active >= 0 && rows[active] && !hasId(rows[active].id)) {
                    pick(rows[active]);
                } else {
                    createName(input.value);
                }
            } else if (e.key === 'Escape') {
                hideSuggest();
            }
        });
        input.addEventListener('blur', function () {
            setTimeout(hideSuggest, 120);
        });

        renderChips();
        return {
            ids: function () { return selected.map(function (r) { return r.id; }); },
            selected: function () { return selected.slice(); }
        };
    }

    global.AdminUi = {
        csrf: csrf,
        escape: escape,
        pickMsg: pickMsg,
        get: function (url, data) { return request('GET', url, data); },
        post: function (url, data) { return request('POST', url, data); },
        toast: toast,
        loading: loading,
        confirm: function (msg) { return global.confirm(msg || _('confirm', '确认？')); },
        prompt: function (title, value) { return global.prompt(title || '', value == null ? '' : String(value)); },
        dialog: dialog,
        setLang: setLang,
        t: _,
        formData: formData,
        fillForm: fillForm,
        table: table,
        pager: pager,
        rowFromClick: rowFromClick,
        cleanWhere: cleanWhere,
        upload: upload,
        pickFile: pickFile,
        bindImageField: bindImageField,
        bindPickField: bindPickField,
        headerCount: headerCount,
        resetQueueFields: resetQueueFields,
        markQueueChips: markQueueChips,
        on: function (sel, ev, fn) {
            var el = typeof sel === 'string' ? document.querySelector(sel) : sel;
            if (el) el.addEventListener(ev, fn);
        },
        q: function (sel, root) { return (root || document).querySelector(sel); },
        qa: function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); },
        status: function (ok, text) {
            return '<span class="status ' + (ok ? 'status-ok' : 'status-off') + '">' + escape(text) + '</span>';
        },
        visit: function (href) { visit(href); }
    };

    var nativeSetInterval = global.setInterval.bind(global);
    var nativeClearInterval = global.clearInterval.bind(global);
    var pageTimers = [];
    global.setInterval = function () {
        var id = nativeSetInterval.apply(null, arguments);
        pageTimers.push(id);
        return id;
    };
    global.clearInterval = function (id) {
        pageTimers = pageTimers.filter(function (x) { return x !== id; });
        nativeClearInterval(id);
    };

    var visitCtl = null;
    var shellBound = false;
    var swapGen = 0;

    function clearPageTimers() {
        pageTimers.forEach(function (id) { nativeClearInterval(id); });
        pageTimers = [];
    }

    var nativeFocus = HTMLElement.prototype.focus;
    var quietFocusDepth = 0;

    function quietFocus(el) {
        if (!el || typeof el.focus !== 'function') return;
        try { el.focus({ preventScroll: true }); } catch (err) { el.focus(); }
    }

    function stripAutofocus(root) {
        if (!root) return;
        if (root.nodeType === 1 && root.hasAttribute && root.hasAttribute('autofocus')) {
            root.removeAttribute('autofocus');
        }
        if (!root.querySelectorAll) return;
        Array.prototype.forEach.call(root.querySelectorAll('[autofocus]'), function (el) {
            el.removeAttribute('autofocus');
        });
    }

    function pinScrollTop() {
        var html = document.documentElement;
        var body = document.body;
        if (html) html.scrollTop = 0;
        if (body) body.scrollTop = 0;
        window.scrollTo(0, 0);
    }

    function quietFocusOn() {
        if (quietFocusDepth === 0) {
            try {
                HTMLElement.prototype.focus = function (opts) {
                    try {
                        nativeFocus.call(this, Object.assign({}, opts || {}, { preventScroll: true }));
                    } catch (err) {
                        nativeFocus.call(this, opts);
                    }
                };
            } catch (err) {
                return;
            }
        }
        quietFocusDepth += 1;
    }

    function quietFocusOff() {
        if (quietFocusDepth === 0) return;
        quietFocusDepth -= 1;
        if (quietFocusDepth === 0) {
            try { HTMLElement.prototype.focus = nativeFocus; } catch (err) {}
        }
    }

    function closeFloaters() {
        Array.prototype.slice.call(document.querySelectorAll('.ui-mask, .ui-toast, #ui-loading')).forEach(function (el) {
            el.remove();
        });
        Array.prototype.slice.call(document.querySelectorAll('details.account-menu, details.lang-pick')).forEach(function (d) {
            d.open = false;
        });
    }

    function syncCsrf(token) {
        if (!token) return;
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', token);
        Array.prototype.forEach.call(document.querySelectorAll('input[name="_token"]'), function (inp) {
            inp.value = token;
        });
    }

    function runScripts(root) {
        if (!root) return;
        Array.prototype.slice.call(root.querySelectorAll('script')).forEach(function (old) {
            var s = document.createElement('script');
            Array.prototype.forEach.call(old.attributes || [], function (a) {
                s.setAttribute(a.name, a.value);
            });
            s.textContent = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
    }

    function headIsSimple(doc) {
        var nodes = doc.head ? doc.head.querySelectorAll('link[rel="stylesheet"], style') : [];
        var extra = 0;
        Array.prototype.forEach.call(nodes, function (n) {
            var href = n.getAttribute('href') || n.href || '';
            if (n.tagName === 'LINK' && /admin\.css|font-awesome/.test(href)) return;
            extra += 1;
        });
        return extra === 0;
    }

    function syncAdminCss(doc) {
        var next = doc.querySelector('link[rel="stylesheet"][href*="admin.css"]');
        var cur = document.querySelector('link[rel="stylesheet"][href*="admin.css"]');
        if (!next || !cur) return;
        var href = next.getAttribute('href') || '';
        if (href && cur.getAttribute('href') !== href) cur.setAttribute('href', href);
    }

    function applyShell(doc, url, push) {
        var newContent = doc.getElementById('admin-content');
        var content = document.getElementById('admin-content');
        var newSide = doc.getElementById('adminSide');
        var side = document.getElementById('adminSide');
        var newScripts = doc.getElementById('admin-page-scripts');
        var scripts = document.getElementById('admin-page-scripts');
        if (!newContent || !content || !newSide || !side || !newScripts || !scripts) {
            location.href = url;
            return;
        }
        if (!headIsSimple(doc)) {
            location.href = url;
            return;
        }
        clearPageTimers();
        closeFloaters();
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }
        var gen = ++swapGen;
        quietFocusOn();
        document.documentElement.classList.add('admin-shell-swap');
        document.title = doc.title || document.title;
        var htmlLang = doc.documentElement.getAttribute('lang');
        if (htmlLang) document.documentElement.setAttribute('lang', htmlLang);
        var newCsrf = doc.querySelector('meta[name="csrf-token"]');
        if (newCsrf) syncCsrf(newCsrf.getAttribute('content'));
        syncAdminCss(doc);
        var oldSideNav = side.querySelector('.side-nav');
        var nextSideNav = newSide.querySelector('.side-nav');
        var oldSideMod = side.querySelector('.mod-nav-side');
        var nextSideMod = newSide.querySelector('.mod-nav-side');
        if (oldSideNav && nextSideNav) {
            oldSideNav.innerHTML = nextSideNav.innerHTML;
            if (oldSideMod && nextSideMod) oldSideMod.innerHTML = nextSideMod.innerHTML;
        } else {
            side.innerHTML = newSide.innerHTML;
        }
        var nav = document.querySelector('.mod-nav-top');
        var newNav = doc.querySelector('.mod-nav-top');
        if (nav && newNav) nav.innerHTML = newNav.innerHTML;
        var title = document.querySelector('.topbar-title');
        var newTitle = doc.querySelector('.topbar-title');
        if (title && newTitle) title.textContent = newTitle.textContent;
        stripAutofocus(newContent);
        content.className = newContent.className;
        content.innerHTML = newContent.innerHTML;
        scripts.innerHTML = newScripts.innerHTML;
        var histUrl = url;
        try {
            var u = new URL(url, location.href);
            u.hash = '';
            histUrl = u.href;
        } catch (err) {}
        if (push) history.pushState({ adminShell: 1 }, '', histUrl);
        else history.replaceState({ adminShell: 1 }, '', histUrl);
        pinScrollTop();
        runScripts(scripts);
        pinScrollTop();
        requestAnimationFrame(function () {
            pinScrollTop();
            if (gen !== swapGen) {
                quietFocusOff();
                return;
            }
            requestAnimationFrame(function () {
                pinScrollTop();
                quietFocusOff();
                if (gen === swapGen) document.documentElement.classList.remove('admin-shell-swap');
            });
        });
    }

    function visit(href, opts) {
        opts = opts || {};
        var url;
        try { url = new URL(href, location.href); url.hash = ''; } catch (err) { location.href = href; return; }
        if (visitCtl) visitCtl.abort();
        visitCtl = new AbortController();
        fetch(url.href, {
            credentials: 'same-origin',
            headers: {
                Accept: 'text/html',
                'X-Admin-Shell': '1'
            },
            signal: visitCtl.signal
        }).then(function (r) {
            if (/\/admin\/login(?:\/|\?|$)/.test(r.url)) {
                location.href = r.url;
                return null;
            }
            var ct = (r.headers.get('content-type') || '').toLowerCase();
            if (!r.ok || ct.indexOf('text/html') === -1) {
                location.href = url.href;
                return null;
            }
            return r.text().then(function (html) {
                return { html: html, url: r.url };
            });
        }).then(function (pack) {
            if (!pack) return;
            var doc = new DOMParser().parseFromString(pack.html, 'text/html');
            if (!doc.getElementById('admin-content') || doc.querySelector('.mac-login-card')) {
                location.href = pack.url;
                return;
            }
            applyShell(doc, pack.url, !opts.replace);
        }).catch(function (err) {
            if (err && err.name === 'AbortError') return;
            location.href = url.href;
        });
    }

    function shouldVisit(a) {
        if (!a || a.hasAttribute('download')) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.getAttribute('data-full-reload') === '1') return false;
        var raw = a.getAttribute('href');
        if (!raw || raw.charAt(0) === '#' || raw.indexOf('javascript:') === 0) return false;
        var url;
        try { url = new URL(a.href, location.href); } catch (err) { return false; }
        if (url.origin !== location.origin) return false;
        if (url.pathname.indexOf('/admin') !== 0) return false;
        if (/^\/admin\/(login|logout|captcha|unlock)(\/|$)/.test(url.pathname)) return false;
        if (/\/(download|export|attachments\/open)(\/|$)/.test(url.pathname)) return false;
        return url;
    }

    function bindShell() {
        if (shellBound || !document.getElementById('admin-content')) return;
        shellBound = true;
        if (history.scrollRestoration) history.scrollRestoration = 'manual';
        history.replaceState({ adminShell: 1 }, '', location.href);
        document.addEventListener('click', function (e) {
            var focusBtn = e.target && e.target.closest ? e.target.closest('[data-focus]') : null;
            if (focusBtn) {
                var sel = focusBtn.getAttribute('data-focus');
                var field = sel ? document.querySelector(sel) : null;
                if (field && typeof field.focus === 'function') {
                    e.preventDefault();
                    quietFocus(field);
                    return;
                }
            }
            if (e.defaultPrevented || e.button !== 0) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
            var url = shouldVisit(a);
            if (!url) return;
            if (a.closest && a.closest('.ui-dialog, .ui-mask')) return;
            if (url.pathname === location.pathname && url.search === location.search && url.hash === location.hash) {
                e.preventDefault();
                return;
            }
            e.preventDefault();
            visit(url.href);
        });
        window.addEventListener('popstate', function () {
            if (!document.getElementById('admin-content')) return;
            visit(location.href, { replace: true });
        });
    }

    bindShell();
})(window);
