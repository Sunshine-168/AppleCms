# 苹果v12

Laravel 影视 CMS：分类、影片、播放、采集、会员、专题、静态化。前台用 Blade `@vod*` 标签。低频能力做成本地插件（`plugins/`）。

MIT。

## 三种安装，选一个

需要 **PHP 8.4+**。试用默认 SQLite，不用先装 MySQL。

| 你有什么 | 怎么装 |
|---|---|
| Docker | 一条命令启动，浏览器点完 |
| 服务器 / 本机已有 PHP | Shell 一条命令 |
| 只想点鼠标 | 网页安装 |

装好后：

- 前台 http://127.0.0.1:8010/
- 后台 http://127.0.0.1:8010/admin/login
- 默认账号 `admin`（Shell / 自动安装时密码见下方）

---

### 1. Docker

```bash
docker compose up --build
```

打开 http://127.0.0.1:8010/install 选 SQLite、设管理员即可。

不想用网页、直接装好：

```bash
AUTO_INSTALL=1 INSTALL_PASSWORD=YourPass#1 docker compose up --build
```

后台账号 `admin`，密码就是 `INSTALL_PASSWORD`。

---

### 2. Shell

Linux / macOS / 宝塔终端：

```bash
bash install.sh
```

自定义密码：

```bash
bash install.sh YourPass#1
```

会执行 Composer、生成密钥、SQLite、导入示例分类，并提示定时任务命令。

Windows：

```powershell
.\install.ps1 YourPass#1
```

然后：

```bash
php artisan serve --host=0.0.0.0 --port=8010
```

---

### 3. 网页

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --host=0.0.0.0 --port=8010
```

Windows 第二行改成 `copy .env.example .env`。打开 http://127.0.0.1:8010/install ，三步点完。

手动命令行等价于：

```bash
php artisan video:install --password=YourPass#1 --demo
```

---

## 宝塔上线

1. 网站根目录指到项目里的 **`public`**，PHP 选 **8.4**。
2. `storage`、`bootstrap/cache` 交给运行用户（一般是 `www`）并允许写入。
3. 用上面的 Shell 或网页装完。
4. 计划任务每分钟：

```bash
/www/server/php/84/bin/php /www/wwwroot/你的站点/artisan schedule:run >> /dev/null 2>&1
```

后台「系统 → 监控 / 定时」里会按当前服务器自动给出这一行。

正式站建议 MySQL（utf8mb4），并把 `.env` 里 `APP_DEBUG=false`、`APP_URL` 改成你的域名。nginx 示例见安装页「上线部署」或后台说明「环境」。

重装：删掉 `storage/app/install.lock` 再打开 `/install`。

## 前台页面

| 路径 | 说明 |
|------|------|
| `/` | 首页 |
| `/type/{id}` `/show` `/search` | 分类、筛选、搜索 |
| `/vod/{id}` `/play/{id}` | 详情、播放 |
| `/latest` `/topics` `/actors` `/arts` | 更新、专题、演员、资讯 |
| `/member` | 会员中心 |

模板在 `resources/views/themes/default/`。
