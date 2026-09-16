/**
 * 监控页的 uPlot 封装层。
 *
 * 为什么监控页不沿用后台其余地方的 ApexCharts：
 * 本页约 19 条 series，要保留 24h 的分钟级分辨率就是 1440 点/series，
 * 合计约 27000 个点。ApexCharts 是 SVG，这个量级会产生数万个 DOM 节点，
 * 初次渲染要好几秒、tooltip 和缩放都会卡。
 * 唯一能让它撑住的办法是降采样（把 24h 压成 24 个小时点），
 * 但那会把 3 分钟的 CPU 尖峰、2 分钟的 5xx 突增直接抹平 ——
 * 而排查事故要看的恰恰就是这种东西。
 *
 * uPlot 是 canvas，为密集时序而生，且体积反而更小
 * （50KB min / 21.7KB gzip，对比 ApexCharts 533KB / 138KB）。
 *
 * 其余后台页面一律维持 ApexCharts，不做迁移。
 */
var MonitorCharts = (function () {
    'use strict';

    // 页面应当在引入本文件后定义 MONITOR_I18N。给一份兜底，
    // 免得漏定义时整个图表模块直接 ReferenceError 白屏 ——
    // 监控页自己挂掉是最讽刺的失败。
    var I18N = (typeof MONITOR_I18N !== 'undefined') ? MONITOR_I18N : {};
    var TEXT = {
        no_data: I18N.no_data || 'No data',
        load_failed: I18N.load_failed || 'Failed to load'
    };

    // Layui 主题色系
    var PALETTE = [
        '#1E9FFF', '#FF5722', '#5FB878', '#FFB800',
        '#A233C6', '#01AAED', '#009688', '#FF69B4'
    ];

    /**
     * 线型。★ 颜色不能是区分序列的唯一手段 ★
     *
     * CPU 图里同时有 CPU%、Load1、Load5、Load15 四条线。
     * 全世界约 8% 的男性有某种形式的色觉障碍 —— 对他们来说，
     * 蓝色和绿色的两条线可能长得一模一样，图表直接失去意义。
     * 所以每条序列除了颜色，还要有自己的虚线样式（实线/短虚/点线/长虚）。
     *
     * null = 实线；数组 = canvas 的 setLineDash 参数。
     */
    var DASHES = [
        null,        // 实线
        [8, 4],      // 长虚线
        [2, 3],      // 点线
        [12, 3, 2, 3], // 点划线
        [6, 2],
        [1, 3],
        [10, 5],
        [4, 2, 1, 2]
    ];

    /** 所有图共享一个游标同步组：鼠标移到任一图，所有图同时显示同一时刻的值。
     *  这是排查「CPU 尖峰时 5xx 有没有同时涨」的关键。 */
    var syncKey = uPlot.sync('monitor');

    var charts = [];

    function fmtNum(v, unit) {
        if (v === null || v === undefined) {
            return '--';
        }
        var s = (Math.round(v * 100) / 100).toString();
        return unit ? s + unit : s;
    }

    function tsFormat(ts) {
        var d = new Date(ts * 1000);
        var p = function (n) { return (n < 10 ? '0' : '') + n; };
        return p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
    }

    /**
     * @param {string} el       容器选择器
     * @param {Object} opt      {labels: [], unit: '', stacked: bool, height: int}
     * @returns {Object} chart 句柄
     */
    function create(el, opt) {
        var node = document.querySelector(el);
        if (!node) {
            return null;
        }
        opt = opt || {};
        var labels = opt.labels || [];
        var unit = opt.unit || '';
        var height = opt.height || 220;

        var series = [{
            label: 'time',
            value: function (u, v) { return v == null ? '--' : tsFormat(v); }
        }];

        for (var i = 0; i < labels.length; i++) {
            series.push({
                label: labels[i],
                stroke: PALETTE[i % PALETTE.length],
                dash: DASHES[i % DASHES.length],   // 颜色之外的第二个区分维度
                fill: opt.stacked ? PALETTE[i % PALETTE.length] + '55' : null,
                width: 1.5,
                points: { show: false },
                // 缺失点画成断线，而不是插值连成一条平滑的假线 ——
                // cron 漏跑的那几分钟应该看得出来是断的
                spanGaps: false,
                value: function (u, v) { return fmtNum(v, unit); }
            });
        }

        var u = new uPlot({
            width: node.clientWidth || 600,
            height: height,
            series: series,
            cursor: {
                sync: { key: syncKey.key },
                drag: { x: true, y: false }
            },
            scales: { x: { time: true } },
            axes: [
                // 网格线要低对比（不能和数据抢视线），但坐标轴文字必须可读：
                // #999 在白底上只有 2.8:1，达不到 WCAG AA 的 4.5:1
                { grid: { stroke: '#eee' }, stroke: '#666' },
                { grid: { stroke: '#eee' }, stroke: '#666', size: 55 }
            ],
            legend: { live: true }
        }, [[]], node);

        // 空数据提示层：没有数据时不能只画一个空坐标框，
        // 站长会以为是页面坏了，而不是「这段时间没采到数据」
        var empty = document.createElement('div');
        empty.className = 'mon-chart-empty';
        empty.textContent = TEXT.no_data;
        empty.setAttribute('role', 'status');
        empty.style.cssText = 'display:none;position:absolute;top:0;left:0;right:0;bottom:0;'
            + 'align-items:center;justify-content:center;color:#666;font-size:13px;'
            + 'background:rgba(255,255,255,.85);pointer-events:none;';
        node.style.position = 'relative';
        node.appendChild(empty);

        var chart = {
            u: u,
            node: node,
            setData: function (data) {
                u.setData(data);
                empty.style.display = hasAnyPoint(data) ? 'none' : 'flex';
            },
            showError: function (msg) {
                empty.textContent = msg;
                empty.style.display = 'flex';
            },
            resize: function () {
                u.setSize({ width: node.clientWidth || 600, height: height });
            }
        };
        charts.push(chart);
        return chart;
    }

    /**
     * 列式数据里是否有任何一个非 null 的点。
     * data[0] 是时间轴，其余是各序列。
     */
    function hasAnyPoint(data) {
        if (!data || data.length < 2) {
            return false;
        }
        for (var s = 1; s < data.length; s++) {
            var col = data[s];
            for (var i = 0; i < col.length; i++) {
                if (col[i] !== null && col[i] !== undefined) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 极简 sparkline：无坐标轴、无图例，只画一条线。
     * canvas 重绘 < 1ms，5 秒轮询滚动更新不会卡。
     */
    function sparkline(el, color) {
        var node = document.querySelector(el);
        if (!node) {
            return null;
        }
        var u = new uPlot({
            width: node.clientWidth || 160,
            height: 40,
            legend: { show: false },
            cursor: { show: false },
            scales: { x: { time: true } },
            axes: [{ show: false }, { show: false }],
            series: [
                {},
                {
                    stroke: color || PALETTE[0],
                    fill: (color || PALETTE[0]) + '22',
                    width: 1.5,
                    points: { show: false },
                    spanGaps: false
                }
            ]
        }, [[], []], node);

        return {
            u: u,
            setData: function (data) { u.setData(data); }
        };
    }

    /**
     * 拉曲线数据。后端直接返回 uPlot 要的列式格式 [[ts...],[v1...],[v2...]]，
     * 前端不用再转置一次。
     */
    function load(keys, range, cb) {
        var url = MONITOR_SERIES_URL
            + (MONITOR_SERIES_URL.indexOf('?') >= 0 ? '&' : '?')
            + 'keys=' + encodeURIComponent(keys.join(','))
            + '&range=' + encodeURIComponent(range);

        fetch(url, { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function (res) {
                if (res.code !== 1) {
                    cb(null, res.msg || TEXT.load_failed);
                    return;
                }
                cb(res.data, null);
            })
            .catch(function (e) { cb(null, TEXT.load_failed + ': ' + String(e.message || e)); });
    }

    window.addEventListener('resize', function () {
        for (var i = 0; i < charts.length; i++) {
            charts[i].resize();
        }
    });

    return {
        create: create,
        sparkline: sparkline,
        load: load,
        palette: PALETTE
    };
})();
