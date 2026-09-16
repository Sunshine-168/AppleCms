# 安全检测、URL推送、应用市场、在线升级

## 安全体检 (`应用 → 安全体检` / `menu/safety_checkup`)

- 检测 **CSRF、操作审计、API/前台防爬、安全响应头** 等开关是否开启。  
- 检测 **cache_flag** 是否为弱默认值、**admin_audit_crypto_secret** 是否配置、**site_url** 是否暴露本地/内网地址。  
- 红/黄/绿灯 + **一键修复**（可自动项）或跳转参数配置。  
- 升级后若表单 **403**：可在 **系统 → 参数配置** 开启/关闭 CSRF，或追加 `security_csrf_admin_exempt` 豁免路由（默认含 `upload/*`）。

### 防爬限流与多机

- 实现：`SlidingWindowIpLimiter`（`AntiScrape` 调用）。  
- **`cache.type=redis` 且为 phpredis `\Redis`**：跨节点固定窗口计数（优先 Lua 原子 `INCR`+`EXPIRE`；禁 SCRIPT 时回退 INCR 并愈合无 TTL 键）。  
- **无 Redis / 非 Redis 驱动**：回退 `runtime/anti_scrape_rl/` 文件 + flock（仅单机）。  
- ThinkPHP 官方 Redis 驱动注明业务侧 Redis Cluster 需另用 Redisd；该客户端类型下本限流会自动回退文件模式。  
- 多机请共用同一 Redis，并保证各节点 `cache_flag` 一致（弱默认值会在安全体检中提示）。

## 文件安全检测 (`安全 → 文件安全检测` / `menu/safety_file`)

- 扫描 `template/`、`runtime/`、`application/` 等处 **新增/可疑 PHP**，对比常见 **WebShell** 特征。  
- **误报**可能：自行上传的合法脚本。结论需 **人工打开文件**确认。  
- 发现木马：**隔离服务器 → 修改所有密码 → 全量更新程序 → 复盘入口**。

## 数据挂马检测 (`安全 → 数据挂马检测` / `menu/safety_data`)

- 在 **标题/内容/备注** 字段中搜 **script / iframe / javascript:** 等关键字。  
- 清理后建议 **关闭留言/评论匿名** 或加强 **审核、验证码**。

---

## URL 推送 (`menu/urlsend`)

- 向搜索引擎 **主动提交 URL 列表**（需各平台 token/API）。  
- 仅辅助收录；**内容与内链**仍是 SEO 核心。

---

## 应用市场 (`应用 → 应用市场` / `menu/addon`)

- **本机**：上传本地 zip、启停、配置、卸载（加固见 `AddonSecureInstaller`）。旧 `api.maccms.com` **只读**丰富列表元数据（不装包）。  
- **云市场**：`addon_cloud.status=1` 时 RS256 签目录一键安装；`mock=1` 可读本地骨架目录。协议：`application/data/addon/docs/addon-marketplace-protocol.md`。  
- **生产环境**：先在 **副本站**测插件；备份后安装。  
- 卸载按 **作者说明**执行，避免残留钩子导致 **白屏**。

---

## 在线升级 (`Update` 控制器 / 欢迎页入口)

- **检查文件完整性**（`Base` 对 `Update.php` 有校验逻辑）。  
- 升级前：**全站备份**。  
- FTP/权限不足会导致 **升级半截**；需在服务器修权限后重试。

---

## English summary

**Safety** scans files/DB for injections—verify hits manually. **URL push** helps indexing. **Addons** change DB & code—test in staging first. **Updater** needs write perms and backups.
