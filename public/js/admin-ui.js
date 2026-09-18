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

    function loading(on) {
        var el = document.getElementById('ui-loading');
        if (on) {
            if (!el) {
                el = document.createElement('div');
                el.id = 'ui-loading';
                el.className = 'ui-loading';
                el.textContent = '处理中…';
                document.body.appendChild(el);
            }
            return;
        }
        if (el) el.remove();
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
        closeBtn.textContent = '关闭';
        head.appendChild(title);
        head.appendChild(closeBtn);
        var body = document.createElement('div');
        body.className = 'ui-dialog-body';
        if (typeof opts.content === 'string') body.innerHTML = opts.content;
        else if (opts.content) body.appendChild(opts.content);
        var foot = document.createElement('div');
        foot.className = 'ui-dialog-foot';
        var cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn btn-muted';
        cancel.textContent = opts.cancelText || '取消';
        var save = document.createElement('button');
        save.type = 'button';
        save.className = 'btn';
        save.textContent = opts.okText || '保存';
        if (opts.hideOk) save.style.display = 'none';
        foot.appendChild(cancel);
        if (!opts.hideOk) foot.appendChild(save);
        box.appendChild(head);
        box.appendChild(body);
        if (!opts.hideFoot) box.appendChild(foot);
        mask.appendChild(box);
        document.body.appendChild(mask);
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
        var html = '<nav class="pagination" aria-label="分页">';
        html += '<span>共' + total + '条</span>';
        if (page > 1) html += '<a href="#" data-p="' + (page - 1) + '" rel="prev">上一页</a>';
        html += '<span>' + page + '/' + last + '</span>';
        if (page < last) html += '<a href="#" data-p="' + (page + 1) + '" rel="next">下一页</a>';
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
                if (opts.onDraw) opts.onDraw(wrap, [], { list: [], total: 0, page: 1, last: 1 });
                return res;
            }).catch(function () {
                wrap.innerHTML = failHtml('加载失败');
                wrap._rows = [];
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

    global.AdminUi = {
        csrf: csrf,
        escape: escape,
        pickMsg: pickMsg,
        get: function (url, data) { return request('GET', url, data); },
        post: function (url, data) { return request('POST', url, data); },
        toast: toast,
        loading: loading,
        confirm: function (msg) { return global.confirm(msg || '确认？'); },
        prompt: function (title, value) { return global.prompt(title || '', value == null ? '' : String(value)); },
        dialog: dialog,
        formData: formData,
        fillForm: fillForm,
        table: table,
        pager: pager,
        rowFromClick: rowFromClick,
        cleanWhere: cleanWhere,
        upload: upload,
        pickFile: pickFile,
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

    function clearPageTimers() {
        pageTimers.forEach(function (id) { nativeClearInterval(id); });
        pageTimers = [];
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
        document.title = doc.title || document.title;
        var htmlLang = doc.documentElement.getAttribute('lang');
        if (htmlLang) document.documentElement.setAttribute('lang', htmlLang);
        var newCsrf = doc.querySelector('meta[name="csrf-token"]');
        if (newCsrf) syncCsrf(newCsrf.getAttribute('content'));
        side.innerHTML = newSide.innerHTML;
        var nav = document.querySelector('.mod-nav-top');
        var newNav = doc.querySelector('.mod-nav-top');
        if (nav && newNav) nav.innerHTML = newNav.innerHTML;
        var title = document.querySelector('.topbar-title');
        var newTitle = doc.querySelector('.topbar-title');
        if (title && newTitle) title.textContent = newTitle.textContent;
        content.className = newContent.className;
        content.innerHTML = newContent.innerHTML;
        scripts.innerHTML = newScripts.innerHTML;
        if (push) history.pushState({ adminShell: 1 }, '', url);
        else history.replaceState({ adminShell: 1 }, '', url);
        window.scrollTo(0, 0);
        runScripts(scripts);
    }

    function visit(href, opts) {
        opts = opts || {};
        var url;
        try { url = new URL(href, location.href); } catch (err) { location.href = href; return; }
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
        history.replaceState({ adminShell: 1 }, '', location.href);
        document.addEventListener('click', function (e) {
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
