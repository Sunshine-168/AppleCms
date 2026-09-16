# Backlog：插件市场（Addon Marketplace）

| 字段 | 值 |
|------|-----|
| 状态 | **设计 + CMS 客户端骨架已落地**；云签发/审核后台仍站外 |
| 优先级 | 云端产品能力按供给增长再开 |
| 更新 | 2026-07-15（选项 2：签目录骨架 + 旧 API 只读并存） |
| 结论 | 新 RS256 云目录负责一键安装；旧 `api.maccms.com` **只读**丰富本机列表，**不切断**；未签名远程装包仍禁用 |

## 现状

| 能力 | 现状 | 代码锚点 |
|------|------|----------|
| 签目录云市场（客户端） | 已有 | `AddonCloudService` + `cloudCatalog` / `cloudInstall` |
| 旧 `api.maccms.com` | **只读并存** | `Addon::fetchLegacyOnlineCatalog` → `downloaded` 元数据合并 |
| 未签名远程 install | **关闭** | `Addon::install` 拒绝 |
| mock 空/骨架目录 | 已有 | `addon_cloud.mock=1` + `catalog.mock.json` |
| 云端审核后台 | 无 | `status=approved\|delisted` 约定 |

## 拆分完成度

1–3 文档/安装加固/配置 UI — **已完成**  
4 市场客户端骨架（选项 2）— **已完成**  
5 云端运营产品 — **长期 backlog**

## 相关文件

- `application/common/util/AddonCloudService.php`
- `application/admin/controller/Addon.php`
- `application/admin/view_new/addon/index.html`
- `application/data/addon/docs/addon-marketplace-protocol.md`
- `application/data/addon_market_cloud/`
