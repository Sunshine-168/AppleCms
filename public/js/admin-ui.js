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
            return r.json().catch(function () { return { code: 1, msg: '请求失败' }; });
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
        box.appendChild(foot);
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
        var html = '<div class="pagination">';
        html += '<span>共 ' + total + ' 条</span>';
        if (page > 1) html += '<a href="#" data-p="' + (page - 1) + '">上一页</a>';
        html += '<span>' + page + ' / ' + last + '</span>';
        if (page < last) html += '<a href="#" data-p="' + (page + 1) + '">下一页</a>';
        html += '</div>';
        el.innerHTML = html;
        Array.prototype.forEach.call(el.querySelectorAll('a[data-p]'), function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                onGo(parseInt(a.getAttribute('data-p'), 10));
            });
        });
    }

    function table(opts) {
        var wrap = typeof opts.el === 'string' ? document.querySelector(opts.el) : opts.el;
        var state = { page: 1, where: Object.assign({}, opts.where || {}) };

        function rowsFrom(res) {
            var d = res.data || {};
            var list = Array.isArray(d) ? d : (d.data || []);
            var total = d.total != null ? d.total : list.length;
            var per = d.per_page || 15;
            return {
                list: list,
                total: total,
                page: d.current_page || state.page,
                last: d.last_page || Math.max(1, Math.ceil(total / per))
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
                html += '<tr><td colspan="' + cols.length + '"><div class="list-empty"><p>暂无数据</p></div></td></tr>';
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
            pager(wrap.querySelector('.js-pager'), parsed, function (p) {
                state.page = p;
                load();
            });
            var all = wrap.querySelector('.js-check-all');
            if (all) {
                all.addEventListener('change', function () {
                    Array.prototype.forEach.call(wrap.querySelectorAll('.js-check'), function (cb) {
                        cb.checked = all.checked;
                    });
                });
            }
            if (opts.onDraw) opts.onDraw(wrap, parsed.list);
        }

        function load() {
            var params = Object.assign({}, state.where, { page: state.page });
            return request('GET', opts.url, params).then(function (res) {
                if (res && res.code === 0) render(res);
                else toast((res && res.msg) || '加载失败', 'err');
                return res;
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
            rows: function () { return wrap._rows || []; }
        };
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
        }
    };
})(window);
