# 插件配置界面（Config UI）

| 字段 | 值 |
|------|-----|
| 状态 | **已落地** |
| 更新 | 2026-07-15 |
| 后台模板 | **仅** `application/admin/view_new/addon/*`（`Addon` 构造函数强制 `view_path`） |
| 控制器 | `application/admin/controller/Addon.php` → `config()` |

---

## 1. 页面与读写路径

1. 列表「配置」→ `Addon::config` GET，渲染 `view_new/addon/config.html`
2. 表单 POST `row[字段名]` + `__token__` + `name`
3. 服务端按 `config.php` schema 校验并回写 **同一文件** 的 `value`
4. 成功后调用 `Service::refresh()`，并 `Cache::rm('hooks')` / `Cache::rm('addons')`

读取运行时配置：

```php
$config = get_addon_config('demo'); // name => value
$full = get_addon_fullconfig('demo'); // 完整 schema 数组
```

---

## 2. 支持的 `type`

与 `view_new/addon/config.html` 的 `{switch}` 一致；未知类型在保存时被拒绝。

| type | UI | 保存形态 | 校验要点 |
|------|-----|----------|----------|
| `string` | 单行 | 字符串 | `rule` 含 `required` 则非空；长度 ≤ 65535 |
| `text` | 多行 | 字符串 | 同上 |
| `number` | number | 字符串数字 | 非空时 `is_numeric` |
| `datetime` | 文本/日期控件 | 字符串 | required |
| `array` | 键值输入 | **数组** | JSON/表单数组均可 |
| `checkbox` | 多选 | `a,b,c` | 选项必须 ∈ `content` 键；未选 → `''` |
| `radio` / `select` | 单选 / 下拉 | 字符串 | 值必须 ∈ `content` 键 |
| `selects` | 多选下拉 | `a,b,c` | 同 checkbox |
| `image` / `images` | 上传+路径 | URL/路径串 | required |
| `file` / `files` | 上传+路径 | URL/路径串 | required |
| `bool` | 是/否 | `1` / `0` | 归一化为 0/1 |

`content`：选项映射，键为提交值、值为展示文案。  
`rule`：含 `required` 时前端 `lay-verify="required"`，后端同样强制。  
字段 `name`：须匹配 `^[a-zA-Z_][a-zA-Z0-9_]*$`。

---

## 3. 钩子热更新说明

| 场景 | 是否即时 | 说明 |
|------|----------|------|
| 仅改配置 `value`（开关、密钥、文案等） | **是** | 保存写回 `config.php`；下次 `get_addon_config` 即新值 |
| 启用/禁用插件 | **是** | `Service::enable/disable` + refresh |
| 在主类**新增/删除**公开钩子方法 | **需重新启用或再保存配置触发 refresh** | `Service::refresh()` 扫描主类方法重写 `application/extra/addons.php` 的 `hooks` |
| 改 `install.sql` / 静态资源 | **否** | 属安装/启用副作用，不是配置页职责 |

要点：配置页会 **始终** refresh，因此多数情况下保存配置即可刷新钩子表；但若插件处于禁用态，钩子仍不会被调度，需先启用。

---

## 4. 安全与运维

- CSRF：`Token` 校验 + 失败/成功回传新 `__token__`
- 启停/卸载：列表页必须 **POST**（`view_new/addon/index.html`）
- 不要再改 `application/admin/view/addon/*`（遗留目录，不维护）

开发者 schema 总览仍见 [`addon-developer.md`](addon-developer.md) 第 5 节。
