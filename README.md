# 苹果v12

Laravel 影视站：分类、影片、播放、采集、会员、专题。前台模板用 `@vod*` 标签，扩展放 `plugins/`。MIT。

需要 **PHP 8.4+**（扩展：`pdo`、`mbstring`、`openssl`、`curl`、`fileinfo`、`tokenizer`、`xml`、`ctype`、`json`）。试用可以只用 SQLite，不必先装 MySQL。

---

## 先选你的场景

| 你现在要做什么 | 往下看 |
|---|---|
| 自己电脑先跑起来看看 | [本机试用](#本机试用) |
| 宝塔已经建好网站 / 有域名 | [宝塔上线](#宝塔上线) |
| 装好了，不知道进哪 | [装好后怎么进](#装好后怎么进) |
| `/install` 打不开、500、定时不跑 | [打不开时](#打不开时) |

代码必须放在**项目根**（能看到 `artisan`、`install.sh`、`public/` 的那一层）。宝塔「网站根目录」填的是里面的 `public`，终端命令要在项目根执行，不要进 `public`。

```bash
git clone <你的仓库地址> LaraVideo
cd LaraVideo
```

没有 Git 就把压缩包解到这一层，再 `cd` 进去。

---

## 本机试用

三选一。装完都用 http://127.0.0.1:8010/ 。

### 方式 A：Docker（本机已装 Docker）

```bash
docker compose up --build
```

浏览器打开 http://127.0.0.1:8010/install ，选「文件数据库」，设管理员。

不想点网页、一条命令装完：

```bash
AUTO_INSTALL=1 INSTALL_PASSWORD=YourPass#1 docker compose up --build
```

后台账号 `admin`，密码是 `INSTALL_PASSWORD`。

### 方式 B：一条脚本（本机或宝塔终端已有 PHP 8.4）

Linux / macOS / 宝塔终端，在项目根：

```bash
bash install.sh YourPass#1
```

不写密码时默认 `admin123`。脚本会装 Composer 依赖、生成密钥、建 SQLite、写入示例分类。

Windows（先把 `php`、`composer` 加进 PATH）：

```powershell
.\install.ps1 YourPass#1
```

本机没有 Nginx 时再开内置服务：

```bash
php artisan serve --host=0.0.0.0 --port=8010
```

宝塔已经用域名反代了，**不要**再跑 `artisan serve`，直接打开你的域名。

### 方式 C：网页点完

项目根执行：

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Windows 第二行改成 `copy .env.example .env`。然后：

- 本机：`php artisan serve --host=0.0.0.0 --port=8010`，打开 http://127.0.0.1:8010/install
- 宝塔：打开 `http://你的域名/install`

三步：环境检查 → 数据库 → 管理员。试用选文件数据库；正式站选 MySQL（先建好空库再填）。

和脚本等价的一条命令：

```bash
php artisan video:install --password=YourPass#1 --demo
```

---

## 宝塔上线

按顺序做，不要跳。下面把 `/www/wwwroot/video.test-sun.com` 换成你的**项目根**。

1. **建站**  
   PHP 选 **8.4**。运行目录 / 网站根目录填：

   ```
   /www/wwwroot/video.test-sun.com/public
   ```

   不要填到项目根，也不要填到 `public` 再往里一层。

2. **放代码**  
   项目根里要有 `artisan`。终端先进入项目根：

   ```bash
   cd /www/wwwroot/video.test-sun.com
   ```

3. **权限**（运行用户一般是 `www`）  
   文件管理里把 `storage`、`bootstrap/cache` 所有者改为 `www`，并允许写入。或：

   ```bash
   chown -R www:www storage bootstrap/cache database
   chmod -R ug+rwx storage bootstrap/cache database
   ```

4. **安装**  
   同一目录执行 `bash install.sh YourPass#1`，或打开 `http://你的域名/install` 用网页装。  
   正式站建议 MySQL，库字符集 `utf8mb4`。

5. **改站点地址**  
   编辑项目根 `.env`：

   ```
   APP_URL=https://你的域名
   APP_ENV=production
   APP_DEBUG=false
   ```

   改完执行：

   ```bash
   /www/server/php/84/bin/php artisan config:clear
   ```

6. **计划任务（每分钟）**  
   必须用 **`/bin/php`**，不要用 `php-fpm`。后台「系统 → 监控」也会给出当前机器的一行。宝塔计划任务示例：

   ```bash
   /www/server/php/84/bin/php /www/wwwroot/video.test-sun.com/artisan schedule:run >> /dev/null 2>&1
   ```

   采集、点击重置、监控都靠这一行。没配就会停。

7. **进后台**  
   `http://你的域名/admin/login`。脚本安装账号是 `admin`，密码是你在第 4 步写的那个。

nginx 完整示例、php.ini 建议见安装页「上线部署」或后台说明「环境」。

---

## 装好后怎么进

| | 本机 Docker / `artisan serve` | 宝塔 / 已有域名 |
|---|---|---|
| 前台 | http://127.0.0.1:8010/ | `http://你的域名/` |
| 后台 | http://127.0.0.1:8010/admin/login | `http://你的域名/admin/login` |
| 账号 | `admin` | 网页安装时你填的账号 |

脚本没写密码时，默认密码是 `admin123`。Docker 自动安装默认也是 `admin123`，上线务必改掉。

---

## 打不开时

| 现象 | 先查 |
|---|---|
| `/install` 或全站 500 | `storage`、`bootstrap/cache` 不能写；或还没有 `APP_KEY`（先 `php artisan key:generate`） |
| 打开首页是目录列表 / 只有文件 | 网站根目录没指到 `public` |
| 提示已经装过 | 删掉 `storage/app/install.lock`，再打开 `/install` |
| 计划任务没反应、监控没数据 | 任务用了 `php-fpm` 而不是 `/bin/php`；到「系统 → 监控」复制正确命令 |
| 日志里有 `pcntl_signal` | 把本仓库更新后再跑 `schedule:run`。宝塔 PHP 常禁用这个函数，程序已兼容 |
| 采集网页点「今天」中途断 | 把 PHP `max_execution_time`、nginx `fastcgi_read_timeout` 调到 300 以上 |

重装：删 `storage/app/install.lock`，必要时清空库或删 `database/database.sqlite`，再走安装。

---

## 前台页面

| 路径 | 说明 |
|------|------|
| `/` | 首页 |
| `/type/{id}` `/show` `/search` | 分类、筛选、搜索 |
| `/vod/{id}` `/play/{id}` | 详情、播放 |
| `/latest` `/topics` `/actors` `/arts` | 更新、专题、演员、资讯 |
| `/member` | 会员中心 |

模板在 `resources/views/themes/default/`。
