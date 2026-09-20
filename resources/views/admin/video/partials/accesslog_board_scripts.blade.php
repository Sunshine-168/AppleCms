<script>
(function () {
    var U = AdminUi;
    if (!U) return;
    var L = @json($accessJsLang ?? [
        'spider' => admin_t('ui.spider'),
        'visitor' => admin_t('ui.visitor'),
        'no_match' => admin_t('ui.no_match_rows'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_front_visits' => admin_t('ui.empty_front_visits'),
        'empty_front_visits_hint' => admin_t('ui.empty_front_visits_hint'),
        'open_front' => admin_t('ui.open_front'),
        'please_select_rows' => admin_t('ui.please_select_rows'),
        'confirm_batch_del_access' => admin_t('ui.confirm_batch_del_access'),
        'confirm_del_access' => admin_t('ui.confirm_del_access'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
    ], JSON_UNESCAPED_UNICODE);
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
        var html = '<span class="badge' + (bot ? ' badge-warn' : '') + '">' + U.escape(d.visitor_label || (bot ? (L.spider || '') : (L.visitor || ''))) + '</span>';
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
                return '<div class="list-empty"><p>' + U.escape(L.no_match || '') + '</p><p><button type="button" class="btn btn-muted btn-sm" id="accesslog-empty-reset">' + U.escape(L.clear_filter || '') + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_front_visits || '') + '</p><p class="muted">' + U.escape(L.empty_front_visits_hint || '') + '</p><p><a class="btn btn-muted btn-sm" href="/" target="_blank" rel="noopener">' + U.escape(L.open_front || '') + '</a></p></div>';
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
            batchCount.textContent = String(AdminUi.t('selected_n') || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: AdminUi.t('time'), width: 150, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: AdminUi.t('who'), width: 120, html: visitorHtml},
            {title: AdminUi.t('opened'), html: urlHtml},
            {title: 'IP', width: 140, html: ipHtml},
            {title: AdminUi.t('identifier'), html: function (d) { return '<span class="muted" title="' + U.escape(d.ua || '') + '">' + U.escape(d.ua_short || d.ua || '') + '</span>'; }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_rows || '', 'err'); return; }
        if (!U.confirm(String(L.confirm_batch_del_access || '').replace(':n', String(ids.length)))) return;
        U.post('/admin/video/accesslogs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail || '', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted || '', 'ok');
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
            if (!U.confirm(L.confirm_del_access || '')) return;
            U.post('/admin/video/accesslogs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail || '', 'err'); return; }
                table.refresh();
                U.toast(L.deleted || '', 'ok');
            });
        }
    });
})();
</script>
