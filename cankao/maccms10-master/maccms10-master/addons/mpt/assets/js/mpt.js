/* AI短视频（MPT）任务台 —— 无依赖，不引 layui/jQuery，插件页独立渲染 */
(function () {
    'use strict';

    var CFG = window.MPT || {};
    // 服务端在极端情况下（语言包非 UTF-8，json_encode 失败）会退成 {}。
    // 那时页面已经没救了，但别让这里先抛一个 TypeError 把整段脚本停掉。
    CFG.lang = CFG.lang || {};
    var pickedVodId = 0;
    var pollTimer = null;
    /** poll 是否还在飞行中，见 tick() */
    var polling = false;
    /** 任务列表当前页；服务端每页 20 条 */
    var page = 1;
    var pageCount = 1;

    function $(id) { return document.getElementById(id); }

    function post(action, params, cb) {
        var body = [];
        params = params || {};
        for (var k in params) {
            if (Object.prototype.hasOwnProperty.call(params, k)) {
                body.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
            }
        }
        request('POST', CFG.base + action, body.join('&'), cb);
    }

    /**
     * 往一个地址上追加 query 时该用 '?' 还是 '&'。
     * 站点关掉 PATH_INFO 时 CFG.base 长这样：/index.php?s=/addons/mpt/api/ ——
     * 再拼一个 '?' 的话参数全部丢失（服务端只会看到 s=...?page=1 这一个值）。
     */
    function sep(url) { return url.indexOf('?') >= 0 ? '&' : '?'; }

    function get(action, params, cb) {
        var qs = [];
        params = params || {};
        for (var k in params) {
            if (Object.prototype.hasOwnProperty.call(params, k)) {
                qs.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
            }
        }
        var url = CFG.base + action;
        request('GET', url + (qs.length ? (sep(url) + qs.join('&')) : ''), null, cb);
    }

    function request(method, url, body, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        if (method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        // GET 也要带 token：poll/preview 都是有副作用的动作，服务端对 GET 与 POST
        // 一视同仁地校验（见 Api::_initialize 的注释）。
        // 走请求头而不是 query string —— 后者会原样写进 web 服务器的访问日志。
        xhr.setRequestHeader('x-mpt-csrf', CFG.token);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) { return; }
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch (e) { res = null; }
            cb(res || { code: 0, msg: 'bad response', data: {} });
        };
        xhr.send(body);
    }

    function esc(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function toast(msg) {
        if (!msg) { return; }
        var el = document.createElement('div');
        el.className = 'mpt-toast';
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(function () { el.parentNode && el.parentNode.removeChild(el); }, 3200);
    }

    // ---------------------------------------------------------------- 选片

    function searchVod() {
        var wd = $('mpt-wd').value.replace(/^\s+|\s+$/g, '');
        if (!wd) { return; }
        get('searchVod', { wd: wd }, function (res) {
            if (res.code !== 1) { toast(res.msg); return; }
            var list = (res.data && res.data.list) || [];
            var box = $('mpt-vod-list');
            if (!list.length) { box.innerHTML = '<div class="mpt-hint">0</div>'; return; }
            var html = '';
            for (var i = 0; i < list.length; i++) {
                var v = list[i];
                html += '<div class="mpt-voditem"><span>#' + esc(v.vod_id) + ' ' + esc(v.vod_name)
                    + (v.vod_year ? (' (' + esc(v.vod_year) + ')') : '') + '</span>'
                    + '<button type="button" class="mpt-btn mpt-btn-xs" data-vod="' + esc(v.vod_id)
                    + '" data-name="' + esc(v.vod_name) + '">' + esc(CFG.lang.pick) + '</button></div>';
            }
            box.innerHTML = html;
        });
    }

    function loadPreview(vodId, vodName) {
        pickedVodId = parseInt(vodId, 10) || 0;
        if (!pickedVodId) { return; }
        $('mpt-picked-name').textContent = '#' + pickedVodId + ' ' + (vodName || '');
        get('preview', { vod_id: pickedVodId }, function (res) {
            if (res.code !== 1) { toast(res.msg); return; }
            var d = res.data || {};
            $('mpt-subject').value = d.subject || '';
            $('mpt-script').value = d.script || '';
            $('mpt-terms').value = (d.terms || []).join(', ');
            if (!vodName && d.vod_name) {
                $('mpt-picked-name').textContent = '#' + pickedVodId + ' ' + d.vod_name;
            }
            $('mpt-preview').style.display = 'block';
            // 模板/LLM 回退等提示走 msg，成功也可能带一句
            if (res.msg) { toast(res.msg); }
        });
    }

    function submitTask() {
        if (!pickedVodId) { return; }
        var btn = $('mpt-submit');
        btn.disabled = true;
        post('submit', {
            vod_id: pickedVodId,
            subject: $('mpt-subject').value,
            script: $('mpt-script').value,
            terms: $('mpt-terms').value
        }, function (res) {
            btn.disabled = false;
            toast(res.msg || (res.code === 1 ? CFG.lang.working : ''));
            if (res.code === 1) {
                $('mpt-preview').style.display = 'none';
                pickedVodId = 0;
                // 新任务排在第一页（列表按 mpt_id desc），停在第 3 页会看不到刚提交的那条
                page = 1;
                refreshList();
            }
        });
    }

    // ---------------------------------------------------------------- 任务列表

    function refreshList() {
        get('status', { page: page }, function (res) {
            if (res.code !== 1) { return; }
            var d = res.data || {};
            // 服务端会把越界的页码收回来（比如本页任务被删光时），以它的为准
            page = d.page || 1;
            pageCount = d.page_count || 1;
            renderList(d.list || []);
            renderPager(d.total || 0);
        });
    }

    function renderPager(total) {
        $('mpt-pageinfo').textContent = (CFG.lang.page || '%1 / %2 (%3)')
            .replace('%1', page).replace('%2', pageCount).replace('%3', total);
        $('mpt-prev').disabled = (page <= 1);
        $('mpt-next').disabled = (page >= pageCount);
    }

    function gotoPage(n) {
        if (n < 1 || n > pageCount || n === page) { return; }
        page = n;
        refreshList();
    }

    function renderList(list) {
        var tb = $('mpt-tbody');
        if (!list.length) {
            tb.innerHTML = '<tr><td colspan="7" class="mpt-hint">' + esc(CFG.lang.empty) + '</td></tr>';
            return;
        }
        var html = '';
        for (var i = 0; i < list.length; i++) {
            var t = list[i];
            var cls = t.status === 3 ? 'c-ok' : (t.status === 4 ? 'c-err' : 'c-run');
            html += '<tr>'
                + '<td>' + esc(t.task_id) + '</td>'
                + '<td>#' + esc(t.vod_id) + ' ' + esc(t.vod_name) + '</td>'
                + '<td>' + esc(t.subject) + '</td>'
                + '<td class="' + cls + '">' + esc(t.status_text)
                + (t.error ? ('<div class="mpt-err">' + esc(t.error) + '</div>') : '') + '</td>'
                + '<td>' + esc(t.progress) + '%</td>'
                + '<td>' + esc(t.time_add) + '</td>'
                + '<td>'
                + (t.url ? ('<a class="mpt-btn mpt-btn-xs" target="_blank" href="' + esc(t.url) + '">' + esc(CFG.lang.view) + '</a> ') : '')
                // 0/1 是「入库了但没提交出去」——服务端 5 分钟后会自己重投，
                // 但站长不该只能干等，重试按钮对这两个状态也放开（服务端 retry
                // 只拒绝 2/5 这类正在跑的状态，不会和推进者打架）。
                + ((t.status === 4 || t.status === 1 || t.status === 0)
                    ? ('<button type="button" class="mpt-btn mpt-btn-xs" data-retry="' + esc(t.task_id) + '">' + esc(CFG.lang.retry) + '</button> ') : '')
                + '<button type="button" class="mpt-btn mpt-btn-xs mpt-btn-danger" data-del="' + esc(t.task_id) + '">' + esc(CFG.lang.del) + '</button>'
                + '</td></tr>';
        }
        tb.innerHTML = html;
    }

    /**
     * L1 主路径：页面开着时每 5s 打一次 poll 推进任务，再拉一次列表。
     * 页面被切到后台标签时暂停，别白烧服务器。
     *
     * ★ 上一次没回来就不发下一次 ★
     * 一次 poll 可能要下载几十 MB 的成片、跑上几分钟，而定时器是 5 秒一次。
     * 不挡的话请求会一层层叠上去，每一个都是 set_time_limit(0) 的长进程，
     * 一个任务就能把 PHP-FPM 的进程池占满。服务端那边有租约保证不会重复
     * 处理同一条任务（TaskRunner::advance()），这里只是别去撞它。
     */
    function tick() {
        if (document.hidden || polling) { return; }
        polling = true;
        post('poll', {}, function () {
            polling = false;
            refreshList();
        });
    }

    // ---------------------------------------------------------------- 绑定

    document.addEventListener('click', function (e) {
        var el = e.target;
        if (!el || el.nodeType !== 1) { return; }
        if (el.id === 'mpt-search') { searchVod(); return; }
        if (el.id === 'mpt-submit') { submitTask(); return; }
        if (el.id === 'mpt-ping') {
            get('ping', {}, function (res) { toast(res.msg || (res.code === 1 ? 'ok' : 'fail')); });
            return;
        }
        if (el.id === 'mpt-prev') { gotoPage(page - 1); return; }
        if (el.id === 'mpt-next') { gotoPage(page + 1); return; }
        if (el.id === 'mpt-cleanup') {
            if (!window.confirm(CFG.lang.confirmCleanup)) { return; }
            post('cleanup', { days: 30 }, function (res) {
                toast(res.msg);
                refreshList();
            });
            return;
        }
        if (el.getAttribute('data-vod')) {
            loadPreview(el.getAttribute('data-vod'), el.getAttribute('data-name'));
            return;
        }
        if (el.getAttribute('data-retry')) {
            post('retry', { task_id: el.getAttribute('data-retry') }, function (res) {
                toast(res.msg); refreshList();
            });
            return;
        }
        if (el.getAttribute('data-del')) {
            if (!window.confirm(CFG.lang.confirmDelete)) { return; }
            post('remove', { task_id: el.getAttribute('data-del') }, function (res) {
                toast(res.msg); refreshList();
            });
        }
    });

    $('mpt-wd').addEventListener('keydown', function (e) {
        if (e.keyCode === 13) { e.preventDefault(); searchVod(); }
    });

    if (CFG.presetVodId) {
        loadPreview(CFG.presetVodId, '');
    }
    refreshList();
    pollTimer = setInterval(tick, 5000);
    window.addEventListener('beforeunload', function () { clearInterval(pollTimer); });
})();
