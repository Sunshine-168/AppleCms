/**
 * 前台埋点 beacon。由 All::label_fetch() 在 </body> 前注入，不需要模板改动。
 *
 * 分工（见 application/common/util/AnalyticsServerTracker.php）：
 *   MAC_ANA_SS === 1 —— 动态渲染，服务端已经写了 pageview 行。
 *                        这里只在页面离开时上报 stay_ms，由后端 UPDATE 回填。
 *                        绝不再发 pageview，否则同一次浏览会写两条。
 *   MAC_ANA_SS === 0 —— 静态生成的页面，PHP 不执行，服务端没记录。
 *                        这里必须走完整 pageview 上报。
 *
 * 刻意不依赖 jQuery / MAC 对象：注入点在所有前台模板上，不能假设它们加载了什么。
 *
 * window.MacAnalytics 是与 static_new/js/home.js 的互锁：那份 MAC.Analytics.Init()
 * 开头有 `if (window.MacAnalytics) return;`。本脚本先于 DOM-ready 执行并占位，
 * 所以即便将来前端把 home.js 挂上去，也不会双重上报。
 */
(function () {
    'use strict';

    if (window.MacAnalytics) {
        return;
    }

    var SS = (typeof window.MAC_ANA_SS !== 'undefined') ? parseInt(window.MAC_ANA_SS, 10) : 0;
    var VISITOR_COOKIE = 'mac_ana_vid';
    var SESSION_KEY = 'mac_ana_sk';
    var startAt = Date.now();
    var basePath = (window.maccms && window.maccms.path) ? window.maccms.path : '/';

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    }

    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 86400000);
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
    }

    function rnd() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
    }

    function visitorId() {
        var v = getCookie(VISITOR_COOKIE);
        if (!v) {
            v = 'v_' + rnd();
            setCookie(VISITOR_COOKIE, v, 3650);
        }
        return v;
    }

    function sessionKey() {
        // 服务端埋点开着时，session_key 由后端种 cookie；读它即可，保证两边同一把 key。
        var s = getCookie(SESSION_KEY);
        if (!s) {
            s = sessionStorage.getItem(SESSION_KEY);
        }
        if (!s) {
            s = 's_' + rnd();
            sessionStorage.setItem(SESSION_KEY, s);
        }
        return s;
    }

    function currentPath() {
        return location.pathname + location.search;
    }

    function post(url, payload, keepalive) {
        var full = basePath.replace(/\/$/, '') + url;
        var body = JSON.stringify(payload);
        if (keepalive && navigator.sendBeacon) {
            navigator.sendBeacon(full, new Blob([body], { type: 'application/json' }));
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', full, true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.send(body);
    }

    function reportLeave() {
        var stayMs = Math.max(0, Date.now() - startAt);
        post('/api.php/analytics/event', {
            event_code: 'page_leave',
            session_key: sessionKey(),
            visitor_id: visitorId(),
            path: currentPath(),
            props: { stay_ms: stayMs, path: currentPath() },
            ts: Math.floor(Date.now() / 1000)
        }, true);
    }

    function reportPageviewFull() {
        // 仅静态生成的页面走这条路：服务端没有机会记录。
        post('/api.php/analytics/pageview', {
            session_key: sessionKey(),
            visitor_id: visitorId(),
            path: currentPath(),
            prev_path: document.referrer || '',
            stay_ms: 0,
            ts: Math.floor(Date.now() / 1000)
        }, false);
    }

    if (SS !== 1) {
        reportPageviewFull();
    }

    window.addEventListener('beforeunload', reportLeave);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            reportLeave();
        }
    });

    // 占位，让 home.js 的 MAC.Analytics.Init() 自动让位，避免双重上报
    window.MacAnalytics = {
        event: function (code, props) {
            post('/api.php/analytics/event', {
                event_code: code,
                session_key: sessionKey(),
                visitor_id: visitorId(),
                props: props || {},
                ts: Math.floor(Date.now() / 1000)
            }, false);
        }
    };
})();
