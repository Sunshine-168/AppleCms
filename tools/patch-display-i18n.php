<?php

$root = dirname(__DIR__);
$overlay = include $root.'/tools/overlays/rest_keys_display.php';
if (! is_array($overlay)) {
    fwrite(STDERR, "overlay missing\n");
    exit(1);
}
$boards = include $root.'/tools/overlays/rest_keys_boards_ui.php';
if (is_array($boards)) {
    $overlay = array_merge($overlay, $boards);
}
$loginCache = include $root.'/tools/overlays/rest_keys_login_cache.php';
if (is_array($loginCache)) {
    $overlay = array_merge($overlay, $loginCache);
}
$userSafety = include $root.'/tools/overlays/rest_keys_user_safety.php';
if (is_array($userSafety)) {
    $overlay = array_merge($overlay, $userSafety);
}
$tplPush = include $root.'/tools/overlays/rest_keys_tpl_push.php';
if (is_array($tplPush)) {
    $overlay = array_merge($overlay, $tplPush);
}
$makeTypes = include $root.'/tools/overlays/rest_keys_make_types.php';
if (is_array($makeTypes)) {
    $overlay = array_merge($overlay, $makeTypes);
}
$extraCfg = include $root.'/tools/overlays/rest_keys_extra_cfg.php';
if (is_array($extraCfg)) {
    $overlay = array_merge($overlay, $extraCfg);
}
$statsLog = include $root.'/tools/overlays/rest_keys_stats_log.php';
if (is_array($statsLog)) {
    $overlay = array_merge($overlay, $statsLog);
}
$adminList = include $root.'/tools/overlays/rest_keys_admin_list.php';
if (is_array($adminList)) {
    $overlay = array_merge($overlay, $adminList);
}

function convert_tw(mixed $v)
{
    static $tr = false;
    if ($tr === false) {
        $tr = function_exists('transliterator_create') ? transliterator_create('Hans-Hant') : null;
    }
    $swap = [
        '默認' => '預設',
        '信息' => '資訊',
        '數據庫' => '資料庫',
        '數據' => '資料',
        '软件' => '軟體',
        '軟件' => '軟體',
        '視頻' => '影片',
        '視頻' => '影片',
        '缓存' => '快取',
        '緩存' => '快取',
        '登錄' => '登入',
        '鏈接' => '連結',
        '屏幕' => '螢幕',
        '回收站' => '回收桶',
        '布爾' => '布林',
        '文件夾' => '資料夾',
        '內存' => '記憶體',
        '客戶端' => '用戶端',
        '裏' => '裡',
        '小时' => '小時',
        '分钟' => '分鐘',
        '设置' => '設定',
        '自定义' => '自訂',
        '周期' => '週期',
        '即将' => '即將',
        '执行' => '執行',
        '约' => '約',
        '后' => '後',
        '无效' => '無效',
        '跳过' => '跳過',
        '无' => '無',
        '页' => '頁',
        '条' => '條',
        '支持' => '支援',
        '选' => '選',
        '写' => '寫',
        '给' => '給',
        '当' => '當',
        '热门' => '熱門',
        '记录' => '記錄',
        '积分' => '積分',
        '会员' => '會員',
        '邮箱' => '信箱',
        '已经' => '已經',
        '注册' => '註冊',
        '定时' => '定時',
        '任务' => '任務',
        '采集' => '採集',
        '备份' => '備份',
        '统计' => '統計',
        '这' => '這',
        '个' => '個',
        '发' => '發',
        '现' => '現',
        '开' => '開',
        '关' => '關',
        '会' => '會',
        '组' => '組',
        '库' => '庫',
        '点' => '點',
        '还' => '還',
        '没' => '沒',
        '过' => '過',
        '长' => '長',
        '时' => '時',
        '为' => '為',
        '吗' => '嗎',
        '于' => '於',
        '与' => '與',
        '并' => '並',
        '从' => '從',
        '对' => '對',
        '将' => '將',
        '经' => '經',
        '该' => '該',
        '内' => '內',
        '号' => '號',
        '码' => '碼',
        '单' => '單',
        '据' => '據',
        '实' => '實',
        '际' => '際',
        '标' => '標',
        '题' => '題',
        '类' => '類',
        '图' => '圖',
        '画' => '畫',
        '览' => '覽',
        '问' => '問',
        '题' => '題',
        '请' => '請',
        '填写' => '填寫',
        '删除' => '刪除',
        '启用' => '啟用',
        '默认' => '預設',
        '确认' => '確認',
        '说明' => '說明',
        '内容' => '內容',
        '来源' => '來源',
        '联系' => '聯絡',
        '方式' => '方式',
        '领取' => '領取',
        '连续' => '連續',
        '目标' => '目標',
        '动作' => '動作',
        '搜索' => '搜尋',
        '编辑' => '編輯',
        '添加' => '新增',
        '商品' => '商品',
        '兑换' => '兌換',
        '订单' => '訂單',
        '发货' => '發貨',
        '实物' => '實物',
        '卡密' => '卡密',
        '天数' => '天數',
        '时长' => '時長',
        '折扣' => '折扣',
        '门槛' => '門檻',
        '库存' => '庫存',
        '充值' => '儲值',
        '通用' => '通用',
        '满减' => '滿減',
        '结账' => '結帳',
        '前台' => '前台',
        '后台' => '後台',
        '插件' => '外掛',
        '服务器' => '伺服器',
        '网址' => '網址',
        '参数' => '參數',
        '命令' => '命令',
        '监控' => '監控',
        '人气' => '人氣',
        '访问' => '造訪',
        '清理' => '清理',
        '上架' => '上架',
        '推送' => '推送',
        '静态' => '靜態',
        '生成' => '產生',
        '成功' => '成功',
        '失败' => '失敗',
        '毫秒' => '毫秒',
        '内存' => '記憶體',
        '文件' => '檔案',
        '未知' => '未知',
        '勾选' => '勾選',
        '作品' => '作品',
        '章节' => '章節',
        '图片' => '圖片',
        '分类' => '分類',
        '书架' => '書架',
        '不为' => '不為',
        '推荐' => '推薦',
        '列表' => '列表',
        '稿' => '稿',
        '里' => '裡',
        '们' => '們',
        '尔' => '爾',
        '银' => '銀',
        '钱' => '錢',
        '账' => '帳',
        '览' => '覽',
        '验' => '驗',
        '证' => '證',
        '签到' => '簽到',
        '里程碑' => '里程碑',
        '观看' => '觀看',
        '评论' => '評論',
        '链接' => '連結',
        '绑定' => '綁定',
        '手机' => '手機',
        '分享' => '分享',
        '微信' => '微信',
        '支付宝' => '支付寶',
        '套餐' => '套餐',
        '标价' => '標價',
        '商城' => '商城',
        '场景' => '場景',
        '现金' => '現金',
        '购买' => '購買',
        '拒单' => '拒單',
        '对照' => '對照',
        '苹果' => '蘋果',
        '入账' => '入帳',
        '现场' => '現場',
        '当场' => '當場',
        '关闭' => '關閉',
        '注册' => '註冊',
        '默认' => '預設',
    ];
    if (is_array($v)) {
        foreach ($v as $k => $item) {
            $v[$k] = convert_tw($item);
        }

        return $v;
    }
    if (! is_string($v) || $v === '') {
        return $v;
    }
    $out = $v;
    if ($tr) {
        $converted = $tr->transliterate($v);
        if (is_string($converted) && $converted !== '') {
            $out = $converted;
        }
    }

    return strtr($out, $swap);
}

function export_php(mixed $v, int $level = 0): string
{
    $pad = str_repeat('    ', $level);
    $inner = str_repeat('    ', $level + 1);
    if (is_array($v)) {
        if ($v === []) {
            return '[]';
        }
        $list = array_is_list($v);
        $out = "[\n";
        foreach ($v as $k => $item) {
            $key = $list ? '' : var_export((string) $k, true).' => ';
            $out .= $inner.$key.export_php($item, $level + 1).",\n";
        }

        return $out.$pad.']';
    }

    return var_export($v, true);
}

function key_exists_path(array $data, string $path): bool
{
    $ref = $data;
    foreach (explode('.', $path) as $part) {
        if (! is_array($ref) || ! array_key_exists($part, $ref)) {
            return false;
        }
        $ref = $ref[$part];
    }

    return true;
}
function set_key_paths(array $data, array $keys): array
{
    foreach ($keys as $path => $val) {
        if (! is_string($path) || $path === '' || ! is_string($val) || $val === '') {
            continue;
        }
        $parts = explode('.', $path);
        $ref = &$data;
        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $ref[$part] = $val;
                break;
            }
            if (! isset($ref[$part]) || ! is_array($ref[$part])) {
                $ref[$part] = [];
            }
            $ref = &$ref[$part];
        }
        unset($ref);
    }

    return $data;
}

$locales = ['en', 'zh_cn', 'zh_tw', 'de', 'es', 'fr', 'ja', 'ko', 'pt'];
foreach ($locales as $code) {
    $path = $root.'/resources/lang/'.$code.'/admin.php';
    $cur = is_file($path) ? include $path : [];
    if (! is_array($cur)) {
        $cur = [];
    }
    $map = [];
    foreach ($overlay as $keyPath => $langs) {
        if (! is_array($langs)) {
            continue;
        }
        if ($code === 'zh_tw') {
            $val = $langs['zh_tw'] ?? convert_tw((string) ($langs['zh_cn'] ?? ''));
        } else {
            $val = $langs[$code] ?? '';
        }
        if (is_string($val) && $val !== '') {
            $forceEn = ['ui.restore_word', 'ui.sql_word', 'ui.restore_typed_wrong', 'ui.sql_typed_wrong', 'ui.rewrite_local_mode'];
            $skipExisting = in_array($code, ['en', 'zh_cn', 'zh_tw'], true)
                && key_exists_path($cur, $keyPath)
                && ! ($code === 'en' && in_array($keyPath, $forceEn, true));
            if ($skipExisting) {
                continue;
            }
            $map[$keyPath] = $val;
        }
    }
    $merged = set_key_paths($cur, $map);
    $ok = file_put_contents($path, "<?php\n\nreturn ".export_php($merged).";\n");
    echo $code.' write='.($ok === false ? 'FAIL' : $ok).' keys='.count($map).PHP_EOL;
}
