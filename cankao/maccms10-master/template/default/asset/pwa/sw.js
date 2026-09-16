'use strict';

/** 构建/部署时可 bump；PHP 输出前会替换 __MAC_PATH__ */
var CACHE_VERSION = 'mac-pwa-v202607211';
var MAC_PATH = '__MAC_PATH__';

function withMacPath(relPath) {
  var path = relPath.charAt(0) === '/' ? relPath : '/' + relPath;
  var base = MAC_PATH || '/';
  if (base === '/') {
    return path;
  }
  return base.replace(/\/+$/, '') + path;
}

var PRECACHE_URLS = [
  withMacPath('index.php/pwa/offline'),
  withMacPath('template/default/asset/css/public-offline.css'),
  withMacPath('template/default/asset/img/pwa/icon-192.png'),
];

var VIDEO_EXT = /\.(m3u8|mp4|ts|webm|flv|mkv)(\?|$)/i;

/**
 * 后台请求：完全不介入（避免离线壳干扰超级控制台）
 * 覆盖常见入口 admin.php 与 /admin/；若入口改名需同步补充。
 */
function isAdminRequest(url) {
  var p = (url.pathname || '').toLowerCase();
  if (p.indexOf('admin.php') !== -1) {
    return true;
  }
  if (/\/admin(\/|$)/.test(p)) {
    return true;
  }
  return false;
}

function isApiRequest(url) {
  var p = url.pathname || '';
  return (
    p.indexOf('/api/') !== -1 ||
    p.indexOf('/index.php/ajax') !== -1 ||
    /\/ajax\//i.test(p)
  );
}

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches
      .open(CACHE_VERSION)
      .then(function (cache) {
        // 逐一预缓存并容错：任一资源失败不阻断 SW 安装/激活
        return Promise.all(
          PRECACHE_URLS.map(function (url) {
            return cache.add(url).catch(function () {
              return null;
            });
          })
        );
      })
      .then(function () {
        return self.skipWaiting();
      })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches
      .keys()
      .then(function (keys) {
        return Promise.all(
          keys
            .filter(function (key) {
              return key !== CACHE_VERSION;
            })
            .map(function (key) {
              return caches.delete(key);
            })
        );
      })
      .then(function () {
        return self.clients.claim();
      })
  );
});

self.addEventListener('fetch', function (event) {
  var req = event.request;
  if (req.method !== 'GET') {
    return;
  }

  var url = new URL(req.url);

  if (isAdminRequest(url)) {
    return;
  }

  if (VIDEO_EXT.test(url.pathname)) {
    return;
  }

  if (isApiRequest(url)) {
    return;
  }

  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(function () {
        return caches.match(withMacPath('index.php/pwa/offline'));
      })
    );
  }
});

/** 主库 PushDispatcher 载荷：{ title, body, url, icon }（扁平）；兼容嵌套 data.url */
function resolvePushUrl(data) {
  var url = '';
  if (data && data.data && data.data.url) {
    url = String(data.data.url);
  } else if (data && data.url) {
    url = String(data.url);
  }
  url = String(url || '').replace(/^\s+|\s+$/g, '');
  if (!url) {
    return withMacPath('/');
  }
  // 仅 http(s) 或站内相对路径（拒绝 //host、javascript: 等）
  if (/^https?:\/\//i.test(url)) {
    return url;
  }
  if (url.charAt(0) === '/' && url.charAt(1) !== '/') {
    var base = MAC_PATH || '/';
    if (base === '/') {
      return url;
    }
    return base.replace(/\/+$/, '') + url;
  }
  return withMacPath('/');
}

self.addEventListener('push', function (event) {
  var data = {};
  try {
    data = event.data ? event.data.json() : {};
  } catch (e0) {
    data = { title: '', body: event.data ? event.data.text() : '' };
  }
  var title = data.title || '';
  var icon = data.icon || withMacPath('template/default/asset/img/pwa/icon-192.png');
  var options = {
    body: data.body || '',
    icon: icon,
    badge: icon,
    data: {
      url: resolvePushUrl(data),
      notify_id: data.notify_id || (data.data && data.data.notify_id) || 0,
    },
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var target =
    (event.notification.data && event.notification.data.url) || withMacPath('/');
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
      for (var i = 0; i < list.length; i++) {
        var c = list[i];
        if (c.url && c.url.indexOf(target) !== -1 && 'focus' in c) {
          return c.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(target);
      }
    })
  );
});
