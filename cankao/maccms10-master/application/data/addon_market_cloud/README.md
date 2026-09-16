# 插件云市场 — 目录签发工具

与 `template_market_cloud` 同形态的 **RS256 目录签名** 工具集。私钥仅留在发布机，勿提交 Git。

## 文件

| 文件 | 说明 |
|------|------|
| `generate_keys.php` | 生成 `keys/catalog_private.pem` + `catalog_public.pem` |
| `build_package.php` | 将 `addons/{name}` 打成 zip |
| `build_catalog.php` | 读 `catalog.source.json`，算 hash，输出签后 `dist/catalog.json` |
| `catalog.source.example.json` | 源数据模板 |
| `catalog_public.pem` | 客户端验签公钥（生成后提交；并同步到 `AddonCloudService::CATALOG_PUBLIC_KEY_PEM`） |

## 流程

```bash
# 1. 生成密钥（首次）
php application/data/addon_market_cloud/generate_keys.php

# 2. 打包插件
php application/data/addon_market_cloud/build_package.php --name=adminloginbg

# 3. 编辑 catalog.source.json（从 example 复制），填 package_file / base_url

# 4. 签目录
php application/data/addon_market_cloud/build_catalog.php

# 5. 将 dist/catalog.json 与 packages/*.zip 发布到 CDN；catalog_url 指向签后 JSON
```

CMS：`addon_cloud.status=1`，`catalog_url` 指向已发布目录。

联调：`addon_cloud.mock=1` 可读 `catalog.mock.json`（仅列表，禁止安装）。  
旧目录：`legacy_catalog=1`（默认）时本机列表只读合并 `api.maccms.com` 展示字段。
