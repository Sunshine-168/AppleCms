# 插件开发文档（Addon Developer Guide）

适用：maccms10 本地插件（基于 `karsonzhang/fastadmin-addons`）。  
样例：`addons/adminloginbg`（最小钩子）、`addons/aicontent`（完整：配置 / SQL / 控制器 / 静态资源）、`addons/socialws`（自定义钩子 + controller）。

> **PHP 基线：7.0+**。新插件禁止使用 7.1+ 语法（返回类型 `: bool`、标量类型提示、`?string`、`void` 等）。`aicontent` 中部分写法仅作功能参考，不要照抄类型声明。

---

## 1. 目录与命名

插件根目录：`addons/{name}/`，`{name}` 必须为**小写字母/数字**，与 `info.ini` 的 `name` 一致。

```
addons/demo/
├── Demo.php                 # 主类（必填）。文件名 = ucfirst(name)，如 Demo.php
├── info.ini                 # 元信息（必填）
├── config.php               # 配置项 schema（可选，有则后台可「配置」）
├── install.sql              # 安装 SQL（可选；框架会自动执行）
├── bootstrap.js             # 可选，启用后合并进 static/js/addons.js
├── controller/              # 可选，插件 HTTP 控制器
│   └── Index.php
├── model/                   # 可选
├── view/                    # 可选，插件自有模板
├── assets/                  # 可选，安装/启用时复制到 static/addons/{name}/
│   ├── js/
│   ├── css/
│   └── images/
└── lang/                    # 可选，插件多语言
```

主类约定：

```php
<?php
namespace addons\demo;

use think\Addons;

class Demo extends Addons
{
    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
```

- 命名空间：`addons\{name}`
- 类名：`ucfirst({name})`（与目录名对应）
- **必须**实现：`install()`、`uninstall()`（返回 `true`/`false`）
- 可选：`enable()`、`disable()`（启停时由 Service 调用）

主类路径属性请使用基类提供的 **`$this->addons_path`**（注意复数），指向 `addons/{name}/`。

---

## 2. `info.ini`

由 `Config::parse` 读取。`think\Addons::checkInfo()` 要求至少包含：

| 字段 | 必填 | 说明 |
|------|------|------|
| `name` | 是 | 插件标识，与目录名一致 |
| `title` | 是 | 后台列表显示名 |
| `intro` | 是 | 简介 |
| `author` | 是 | 作者 |
| `version` | 是 | 语义化版本，如 `1.0.0` |
| `state` | 是 | `0` 禁用 / `1` 启用（启停操作会回写本文件） |
| `website` | 否 | 主页 |
| `image` | 否 | Logo，常用 `/static/addons/{name}/images/logo.png` |
| `url` | 否 | 插件入口 URL（列表「管理」链接），如 `/addons/demo/admin/index` |

示例（参考 `addons/adminloginbg/info.ini`）：

```ini
name = adminloginbg
title = 后台登录背景图
intro = 可自定义后台登录背景图
author = MagicBlack
website = http://www.maccms.la
version = 1.0.0
state = 0
image = /static/addons/adminloginbg/images/logo.png
url = /addons/adminloginbg.html
```

---

## 3. 钩子约定

### 3.1 注册规则

启用插件并执行 `Service::refresh()` 后，会扫描主类**公开方法**，去掉基类方法及 `install` / `uninstall` / `enable` / `disable`，其余方法名经 `Loader::parseName(..., 0, false)` 转为**下划线钩子名**，写入 `application/extra/addons.php` 的 `hooks`：

| 主类方法 | 钩子名 |
|----------|--------|
| `appInit` | `app_init` |
| `adminLoginInit` | `admin_login_init` |
| `socialBroadcast` | `social_broadcast` |

当前仓库 `application/extra/addons.php` 中 `autoload = false`，因此以 **refresh 写出的 hooks 表**为准，而不是每次请求现场扫描。

### 3.2 触发

内核已有行为钩子用：

```php
\think\Hook::listen('admin_login_init', $request);
```

业务侧也可：

```php
hook('social_broadcast', ['kind' => 'chat', 'data' => $row, 'vod_id' => $id]);
```

对应插件方法签名接收同一参数（引用/数组均可，跟调用方一致）。方法内应判断 `state`、配置是否启用，**不要抛致命异常**拖垮宿主请求（参考 `socialws`）。

### 3.3 新增钩子点（进主库时）

若插件需要新的「插槽」，必须在核心业务代码中增加 `Hook::listen` / `hook()`，并在本文件或 PR 说明中登记钩子名、参数结构、调用时机。插件仓库**不能假设**未合入主库的钩子存在。

已知样例：

| 钩子 | 触发位置 | 插件方法样例 |
|------|----------|--------------|
| `admin_login_init` | `application/admin/controller/Index::login` | `Adminloginbg::adminLoginInit` |
| `app_init` | addons 引导 / 框架 | `Aicontent::appInit`、`Socialws::appInit` |
| `social_broadcast` | `Chatroom` / `Danmaku` 模型写库后 | `Socialws::socialBroadcast` |

---

## 4. 安装 / 卸载与 SQL

### 4.1 生命周期（框架）

| 动作 | Service | 插件回调 | SQL |
|------|---------|----------|-----|
| 安装 | `Service::install`（远程）或本地流程 | `install()` | `Service::importsql($name)` 读 `install.sql` |
| 卸载 | `Service::uninstall` | `uninstall()` | **框架不会自动 DROP**，须在 `uninstall()` 自行清理 |
| 启用 / 禁用 | `Service::enable` / `disable` | `enable()` / `disable()`（若实现） | — |
| 配置保存后 | — | — | `Service::refresh()` 刷新 hooks |

### 4.2 `install.sql`

- 路径：`addons/{name}/install.sql`
- `Service::importsql` 会把 **`__PREFIX__`** 替换为 `config('database.prefix')`（默认常为 `mac_`）
- `INSERT` 会改成 `INSERT IGNORE`
- 注释行：`--` / `/*` 开头跳过；语句以 `;` 结束

推荐写法（注意 `__PREFIX__`，不要写死 `mac_`）：

```sql
CREATE TABLE IF NOT EXISTS `__PREFIX__demo_item` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL DEFAULT '',
  `item_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

卸载示例：

```php
public function uninstall()
{
    $pre = config('database.prefix');
    try {
        \think\Db::execute('DROP TABLE IF EXISTS `' . $pre . 'demo_item`');
    } catch (\Exception $e) {
        // 记录日志；避免阻断卸载流程时可按产品策略处理
    }
    return true;
}
```

> 反例：`aicontent` 安装 SQL 写死 `mac_ai_task`、卸载也写死表名——自定义表前缀站点会出问题。新插件请统一 `__PREFIX__` + `config('database.prefix')`。

### 4.3 静态资源

- 推荐放在插件内 `assets/`；安装时 Service 会复制到 `static/addons/{name}/`
- 也可在 `install()` / `enable()` 中自行部署（参考 aicontent）
- Web 访问路径：`/static/addons/{name}/...`

### 4.4 覆盖核心目录

插件目录下若存在 `application/`、`public/` 等「全局目录」（见 `Service::getCheckDirs()`），安装时会**复制进站点根**。务必做冲突检测（`noconflict`），避免覆盖核心文件。无必要不要向核心树写文件。

---

## 5. 配置 schema（`config.php`）

返回**索引数组**，每一项描述一个表单项。后台 `Addon::config` + `view_new/addon/config.html` 按 `type` 渲染；保存写入同一 `config.php` 的 `value` 字段。

单项字段：

| 键 | 说明 |
|----|------|
| `name` | 配置键，保存后 `get_addon_config($name)[$key]` |
| `title` | 标签文案 |
| `type` | 见下表 |
| `content` | `select`/`radio`/`checkbox` 的选项映射 `值 => 文案` |
| `value` | 当前值 |
| `rule` | 前端 data-rule，如 `required` |
| `tip` | 辅助说明 |
| `msg` / `ok` / `extend` | 提示与额外 HTML 属性 |

`type` 与后台模板一致（`view_new/addon/config.html`）：

| type | 控件 |
|------|------|
| `string` | 单行文本 |
| `text` | 多行 |
| `number` | 数字 |
| `datetime` | 日期时间 |
| `array` | 键值对编辑 |
| `checkbox` | 多选 |
| `radio` | 单选 |
| `select` / `selects` | 下拉 / 多选下拉 |
| `image` / `images` | 图片上传 |
| `file` / `files` | 文件上传 |
| `bool` | 是/否 |

校验与保存行为（2026-07 起）：

- 服务端按白名单类型校验；`select`/`radio`/`checkbox`/`selects` 的值必须落在 `content` 键内。
- `rule` 含 `required` 时，前端 `lay-verify` + 后端必填。
- 保存成功后：`set_addon_fullconfig` → **`Service::refresh()`**（重写 `application/extra/addons.php`）→ 清理 `hooks`/`addons` 缓存。
- 配置值变更即时生效；主类新增/删除钩子方法后依赖 refresh（保存配置或重新启用）。

详细表与空配置说明见 [`addon-config-ui.md`](addon-config-ui.md)。

读取配置：

```php
$config = $this->getConfig();          // 主类内
$config = get_addon_config('demo');    // 全局
```

最小示例见 `addons/adminloginbg/config.php`；复杂表单见 `addons/aicontent/config.php`。

---

## 6. 路由、控制器与权限

### 6.1 URL

框架注册：`addons/:addon/[:controller]/[:action]` → `\think\addons\Route@execute`。

注意：maccms 默认可能关闭 ThinkPHP 路由检测。若插件 URL 404，可在 `app_init` 钩子中**仅对本插件路径**临时 `App::route(true)`（参考 `Socialws::appInit`），避免影响全站伪静态。

生成链接：`addon_url('demo')` 或 `addon_url('demo/admin/index')`。

### 6.2 控制器

```php
namespace addons\demo\controller;

use think\addons\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch(); // 模板位于插件 view/
    }
}
```

后台相关控制器必须校验 **CMS 管理员会话**（参考 `aicontent\controller\Admin`：`session('admin_auth')` / `admin_info`），并建议对 POST 做 CSRF（`hash_equals`）。

### 6.3 菜单与 `auth.php`

- **插件管理**菜单在核心 `application/admin/common/auth.php` 的 `addon` 控制器下，与具体业务插件无关。
- 业务插件**不要**直接改核心 `auth.php`（升级会被覆盖；也不便分发）。
- 推荐入口：
  1. `info.ini` 的 `url` 指向插件后台页；用户从 **应用 / 插件列表** 进入；或
  2. 仅提供钩子增强现有页面（如登录背景），无需新菜单。
- 若确需出现在左侧菜单：作为产品需求单独合入核心 `auth.php` + 语言包，走正式 PR，不要由 zip 安装静默改权限文件。

子管理员仍只能操作其角色里勾选的 `addon/*` 能力；插件自有后台页须自行做登录校验，不能依赖「藏 URL」。

---

## 7. 开发与调试清单

1. 建目录与主类、`info.ini`，`state=0`。
2. 有配置则写 `config.php`；有表则写 `__PREFIX__` 版 `install.sql`。
3. 实现钩子方法或 controller；需要插槽时先确认主库已有 `Hook::listen`。
4. 将插件放到 `addons/{name}/`，后台启用 → 触发 `refresh`，检查 `application/extra/addons.php` 的 `hooks`。
5. 验证安装 / 启停 / 卸载 / 配置保存；**自定义前缀**库跑一遍 SQL。
6. 静态资源是否落在 `static/addons/{name}/`。
7. PHP 7.0 语法自检；不要在 `template/` 下新增文件；后台 UI 改动只碰 `view_new/`（插件自有 view 除外）。

---

## 8. 发布注意（本地加固 / 市场 backlog）

- **本地 zip 上传已重新开放并加固**（`Addon::local` + `AddonSecureInstaller`）：
  - 名称白名单、Zip Slip、系统路径屏蔽、危险扩展名
  - 可选 RS256：`package.manifest.json` + `package.sig`（见 `local-package-signing.md`）
  - 安装失败回滚目录；卸载前备份，失败尝试恢复
- 默认安装后 **禁用**，需手动启用。
- 完整在线市场（审核 / 目录协议）仍属长期项：`../backlog/addon-marketplace.md`。
- 请勿在插件中关闭 SSL 校验、写死密钥、或向核心目录覆盖 PHP。

---

## 9. 相关路径速查

| 用途 | 路径 |
|------|------|
| 插件目录 | `addons/` |
| 钩子缓存配置 | `application/extra/addons.php` |
| 后台插件管理 | `application/admin/controller/Addon.php` |
| 安全安装工具 | `application/common/util/AddonSecureInstaller.php` |
| 配置页模板（只维护新版） | `application/admin/view_new/addon/` |
| 基类 / Service | `vendor/karsonzhang/fastadmin-addons/src/` |
| 本地包签名说明 | `application/data/addon/docs/local-package-signing.md` |
| 运维说明（站长向） | `application/data/admin_assistant/09-plugins-comments-user.md` |
| 市场 backlog | `application/data/addon/backlog/addon-marketplace.md` |
