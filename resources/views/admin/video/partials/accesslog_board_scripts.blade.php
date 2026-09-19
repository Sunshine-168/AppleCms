<script>
(function () {
    var U = AdminUi;
    if (!U) return;
    var form = document.getElementById('accesslog-search');
    if (!form) return;
    var batchBar = document.getElementById('accesslog-batch');
    var batchCount = document.getElementById('accesslog-batch-count');
    var countEl = document.getElementById('accesslog-count');
    var ipChip = document.getElementById('accesslog-ip-chip');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var today = form.today.value;
        var visitor = form.visitor.value;
        var ip = form.ip.value;
        U.qa('#accesslog-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-chip') || '';
            var on = false;
            if (key === 'all') on = today === '' && visitor === '' && ip === '';
            else if (key === 'today') on = today === '1';
            else if (key === 'people') on = visitor === 'people';
            else if (key === 'bot') on = visitor === 'bot';
            else if (key === 'ip') on = ip !== '';
            chip.classList.toggle('active', on);
        });
        if (ipChip) {
            ipChip.hidden = ip === '';
            ipChip.textContent = ip;
        }
    }
    function applyChip(key) {
        if (key === 'ip') {
            form.ip.value = '';
            runSearch();
            return;
        }
        if (key === 'all') {
            form.today.value = '';
            form.visitor.value = '';
            form.ip.value = '';
        } else if (key === 'today') {
            form.today.value = form.today.value === '1' ? '' : '1';
        } else if (key === 'people' || key === 'bot') {
            form.visitor.value = form.visitor.value === key ? '' : key;
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function filterIp(ip) {
        form.ip.value = ip || '';
        runSearch();
    }
    function visitorHtml(d) {
        var bot = d.visitor_kind === 'bot';
        var html = '<span class="badge' + (bot ? ' badge-warn' : '') + '">' + U.escape(d.visitor_label || (bot ? '爬虫' : '访客')) + '</span>';
        if (bot && d.group_label) html += '<div class="muted">' + U.escape(d.group_label) + '</div>';
        return html;
    }
    function urlHtml(d) {
        var full = d.url || '';
        var short = d.url_short || full;
        if (!full) return '<span class="muted">—</span>';
        if (!/^https?:\/\//i.test(full)) return U.escape(short);
        return '<a class="botlog-url" href="' + U.escape(full) + '" target="_blank" rel="noopener">' + U.escape(short) + '</a>';
    }
    function ipHtml(d) {
        var ip = d.ip || '';
        if (!ip) return '<span class="muted">—</span>';
        return '<a href="#" class="log-ip js-ip" data-ip="' + U.escape(ip) + '">' + U.escape(ip) + '</a>';
    }

    var table = U.table({
        el: '#accesslog-table',
        countEl: countEl,
        url: '/admin/video/accesslogs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="accesslog-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有前台访问</p><p class="muted">本机只开后台不会记。打开前台任意页才会出现。</p><p><a class="btn btn-muted btn-sm" href="/" target="_blank" rel="noopener">打开前台</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('accesslog-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.today.value = '';
                form.visitor.value = '';
                form.ip.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '时间', width: 150, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: '谁来的', width: 120, html: visitorHtml},
            {title: '打开了', html: urlHtml},
            {title: 'IP', width: 140, html: ipHtml},
            {title: '标识', html: function (d) { return '<span class="muted" title="' + U.escape(d.ua || '') + '">' + U.escape(d.ua_short || d.ua || '') + '</span>'; }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选记录', 'err'); return; }
        if (!U.confirm('删除这 ' + ids.length + ' 条？只清流水，不会封 IP。')) return;
        U.post('/admin/video/accesslogs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    }

    U.on('#accesslog-search-btn', 'click', runSearch);
    U.on('#accesslog-search', 'keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            runSearch();
        }
    });
    U.on('#accesslog-reset-btn', 'click', function () { setTimeout(function () {
        form.today.value = '';
        form.visitor.value = '';
        form.ip.value = '';
        runSearch();
    }, 0); });
    U.on('#accesslog-queues', 'click', function (e) {
        var chip = e.target.closest('[data-chip]');
        if (!chip) return;
        applyChip(chip.getAttribute('data-chip') || '');
    });
    U.on('#accesslog-batch-del', 'click', batchDel);
    U.on('#accesslog-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#accesslog-table', 'click', function (e) {
        var ipLink = e.target.closest('a.js-ip');
        if (ipLink) {
            e.preventDefault();
            filterIp(ipLink.getAttribute('data-ip') || '');
            return;
        }
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href && href !== '#' && href.indexOf('javascript:') !== 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条？只清流水，不会封 IP。')) return;
            U.post('/admin/video/accesslogs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
