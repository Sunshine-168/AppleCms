# 插件云市场目录协议（CMS 客户端）

| 字段 | 值 |
|------|-----|
| 状态 | **设计 + CMS 客户端骨架（选项 2）** |
| 更新 | 2026-07-15 |
| 参考 | 模板市场 `TemplateCloudService` RS256 流水线 |
| 默认目录 URL | `https://api.maccms.ai/addons/catalog.json` |
| 旧目录 | `api.maccms.com` **只读并存**（默认开） |

云端审核后台、CDN 运维、计费不在本仓库；下架通过目录 `status=delisted` 或从 `items` 移除体现。

---

## 1. 与旧 `api.maccms.com` 关系（并存，不切断）

| 路径 | 行为 |
|------|------|
| `http(s)://api.maccms.com/addon/index` | **只读并存**：专用安全 GET（主机白名单、禁跳转、体积上限、字段净化）；`legacy_catalog=0` 可关 |
| FastAdmin 式远程 `Service::install`（uid/token） | **仍关闭**：未签名远程装包禁止；请用签目录或本地 zip |
| 本地 zip（`Addon::local`） | 保留 |
| 新签目录（`AddonCloudService`） | **在线一键安装**唯一通道（`addon_cloud.status=1`） |
| `addon_cloud.mock=1` | 读本地 `catalog.mock.json` 做列表骨架；**禁止安装** |

---

## 2. 目录 JSON（签包根对象）

```json
{
  "items": [ { "...": "见下" } ],
  "sig_alg": "RS256",
  "signature": "<base64 RSA-SHA256>"
}
```

**签名载荷**：`json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)`  
（与 [`application/data/template_market_cloud/build_catalog.php`](../../template_market_cloud/build_catalog.php) / 模板市场一致）

客户端：`openssl_verify($payload, base64_decode(signature), pubkey, OPENSSL_ALGO_SHA256) === 1`

公钥：

1. 文件 `application/data/addon_market_cloud/catalog_public.pem`（若可读）
2. 否则 `AddonCloudService::CATALOG_PUBLIC_KEY_PEM`

---

## 3. `items[]` 字段

### 必填

| 字段 | 约束 |
|------|------|
| `id` | `^[a-z0-9][a-z0-9._-]{0,63}$`，目录内唯一 |
| `name` | 插件标识 `^[a-z][a-z0-9_]{0,31}$`，与包内 `info.ini` 一致 |
| `title` | 展示名 |
| `version` | 语义化版本字符串 |
| `package_url` | `https://` 公网 URL（客户端 SSRF 校验） |
| `package_hash` | `sha256:` + 64 位小写 hex |

### 扩展

| 字段 | 说明 |
|------|------|
| `status` | `approved`（可装）\| `delisted`（客户端忽略） |
| `cms_compat` | `{ "min": "2026.1000.0", "max": "" }`，对 `version.php` 的 `code` 做 `version_compare` |
| `intro` / `author` / `image` / `price` | 展示用 |

---

## 4. 安装流水线（客户端）

1. `fetchCatalog`：缓存 → 安全 GET → RS256 → 格式校验 → 只保留 `status=approved`
2. `installById`：只信目录项；**尝试即计限流**；下载 zip（≤10MB，SSRF + PRIMARY_IP 复核）→ `hash_equals(sha256)` → `AddonSecureInstaller::extractZipSafe` → 主类/`info.ini` 校验 → 落盘 `addons/{name}/` → `install()` + `importsql` + `Service::refresh`
3. **升级必须先备份成功再 purge**；安装互斥锁防并发；失败回滚
4. 兼容矩阵或限流失败则拒绝；写入本机审计

---

## 5. CMS 配置（`Init` 默认合并，不强制改写站点文件）

```php
'addon_cloud' => [
  'status' => '0',
  'catalog_url' => 'https://api.maccms.ai/addons/catalog.json',
  'cache_ttl' => '10800',
  'rate_limit' => '10',
  'audit_max' => '200',
  'legacy_catalog' => '1', // 旧 api.maccms.com 只读元数据
  'mock' => '0',           // 1=本地 catalog.mock.json 列表骨架（禁安装）
]
```

本机产物：

- `application/extra/addon_market_installed.php`
- `application/extra/addon_market_audit.php`

---

## 6. 签发侧工具

见 [`application/data/addon_market_cloud/`](../../addon_market_cloud/README.md)：`generate_keys.php`、`build_package.php`、`build_catalog.php`。  
**私钥不得入库。**
