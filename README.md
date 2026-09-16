# LaraVideo

Laravel 影视站：分类、影片、播放线路、采集入库、会员积分、专题/演员/资讯、静态化与推送。前台用 Blade **`@vod*`** 标签，后台是 Layui。

不做漫画、直播、商城、秒杀、AI、主题设计器和插件市场。本地插件在 `plugins/`（已带弹幕：播放页发送/滚动，后台「弹幕管理」）。

MIT。二次开发按 Laravel 常规（Service / 主题目录）。

## 安装

需要 PHP **8.4+** 和 Composer（本机宝塔目前是 8.2，跑不了 Laravel 13）。可用 Docker：

```bash
docker compose up --build
# 另开终端
docker compose run --rm --entrypoint php php artisan migrate
```

前台/后台走 http://127.0.0.1:8010 。SQLite 文件是 `database/database.sqlite`，结构以 `database/migrations/2026_*` 为准。

本机 PHP：

```bash
cd D:\Project\LaraVideo
composer install
copy .env.example .env
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8010
```

安装页：http://127.0.0.1:8010/install（不要用 8000，那个端口当前是收款码 ThinkPHP 项目）。

打开 http://127.0.0.1:8000/install 检查环境、填数据库和管理员。也可命令行：

```bash
php artisan video:install --password=YourPass#1
php artisan serve
```

装好后：

- 前台 http://127.0.0.1:8000/
- 后台 http://127.0.0.1:8000/admin/login

安装锁：`storage/app/install.lock`（删掉会再次进入 `/install`）。

## 前台页面

| 路径 | 说明 |
|------|------|
| `/` | 首页（推荐 / 最新 / 热门 / 分类） |
| `/type/{id}` `/show` `/search` | 分类、筛选、搜索 |
| `/vod/{id}` `/play/{id}` | 详情、播放 |
| `/latest` `/topics` `/actors` `/arts` | 更新、专题、演员、资讯列表 |
| `/member` | 会员中心（收藏、历史、站内信、卡密、邀请码） |

模板在 `resources/views/themes/default/`。标签示例：`@vod` `@vodType` `@vodFilter` `@vodArt` `@vodTopic`。

## 后台常用

影片、分类、采集源与定时采集、播放器、评论/报错/留言、会员与卡密、站点设置、伪静态说明、安全扫描、静态生成、百度推送。

定时任务：`video:collect-due`（每分钟）、`video:hits-reset`（每天）。
