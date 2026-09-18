<?php
require_once __DIR__ . '/../includes/config.php';
if (!isAdmin()) redirect(BASE_URL . '/admin/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    verify_admin_csrf();

    $action = $_POST['action'] ?? '';
    $ruleKey = $_POST['rule_key'] ?? '';

    if ($action === 'check' && isset($_FILES['rule_file'])) {
        $file = $_FILES['rule_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => '文件上传失败']);
            exit;
        }
        $content = file_get_contents($file['tmp_name']);
        $ruleData = json_decode($content, true);
        if (!$ruleData || empty($ruleData['rule_key']) || empty($ruleData['name']) || empty($ruleData['site_url']) || empty($ruleData['charset']) || !isset($ruleData['config'])) {
            echo json_encode(['error' => '无效的规则文件：缺少必要字段（rule_key, name, site_url, charset, config）']);
            exit;
        }

        $stmt = $db->query("SELECT 1 FROM crawl_rules WHERE rule_key = ?", [$ruleData['rule_key']]);
        $exists = $stmt->fetch() ? true : false;
        $_SESSION['pending_rule_data'] = $ruleData;

        echo json_encode([
            'exists' => $exists,
            'rule_key' => $ruleData['rule_key'],
            'name' => $ruleData['name']
        ]);
        exit;
    }

    elseif ($action === 'update' && $ruleKey) {
        if (!isset($_SESSION['pending_rule_data']) || $_SESSION['pending_rule_data']['rule_key'] !== $ruleKey) {
            echo json_encode(['error' => '会话数据丢失，请重新上传文件']);
            exit;
        }
        $ruleData = $_SESSION['pending_rule_data'];
        unset($_SESSION['pending_rule_data']);

        $configJson = json_encode($ruleData['config'], JSON_UNESCAPED_UNICODE);
        $db->query(
            "UPDATE crawl_rules SET name = ?, site_url = ?, charset = ?, config = ?, status = 1 WHERE rule_key = ?",
            [$ruleData['name'], $ruleData['site_url'], $ruleData['charset'], $configJson, $ruleKey]
        );
        echo json_encode(['success' => true, 'message' => "规则「{$ruleData['name']}」已覆盖更新"]);
        exit;
    }

    elseif ($action === 'insert' && $ruleKey) {
        // 第二步（可选）：插入新规则
        if (!isset($_SESSION['pending_rule_data']) || $_SESSION['pending_rule_data']['rule_key'] !== $ruleKey) {
            echo json_encode(['error' => '会话数据丢失，请重新上传文件']);
            exit;
        }
        $ruleData = $_SESSION['pending_rule_data'];
        unset($_SESSION['pending_rule_data']);

        $configJson = json_encode($ruleData['config'], JSON_UNESCAPED_UNICODE);
        $db->query(
            "INSERT INTO crawl_rules (rule_key, name, site_url, charset, config, status) VALUES (?, ?, ?, ?, ?, ?)",
            [$ruleKey, $ruleData['name'], $ruleData['site_url'], $ruleData['charset'], $configJson, 1]
        );
        echo json_encode(['success' => true, 'message' => "新规则「{$ruleData['name']}」导入成功"]);
        exit;
    }

    echo json_encode(['error' => '非法请求']);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>导入采集规则</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>导入采集规则</h1>
        <div id="message-area"></div>
        <form id="upload-form" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div><label>规则文件 (JSON)</label><input type="file" name="rule_file" id="rule_file" accept=".json" required></div>
            <button type="submit" class="btn btn-primary" id="submit-btn">上传并导入</button>
        </form>
    </div>
</div>

<script>
    const form = document.getElementById('upload-form');
    const msgArea = document.getElementById('message-area');
    const submitBtn = document.getElementById('submit-btn');

    function showMessage(text, type = 'info') {
        msgArea.innerHTML = `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
    }

    function escapeHtml(str) {
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('rule_file');
        if (!fileInput.files.length) {
            showMessage('请选择规则文件', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('rule_file', fileInput.files[0]);
        formData.append('action', 'check');
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) formData.append('csrf_token', csrfToken);

        submitBtn.disabled = true;
        submitBtn.textContent = '检测中...';

        try {
            const checkResp = await fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const checkData = await checkResp.json();

            if (checkData.error) {
                showMessage(checkData.error, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = '上传并导入';
                return;
            }

            if (checkData.exists) {
                const confirmMsg = `规则 "${checkData.name}" (标识: ${checkData.rule_key}) 已存在。\n是否覆盖原有规则？`;
                if (confirm(confirmMsg)) {
                    await performAction('update', checkData.rule_key);
                } else {
                    if (confirm('是否将它作为新规则导入？\n注意：rule_key 必须唯一，可能会因重复键冲突而失败。')) {
                        await performAction('insert', checkData.rule_key);
                    } else {
                        showMessage('已取消导入', 'info');
                    }
                }
            } else {
                await performAction('insert', checkData.rule_key);
            }
        } catch (err) {
            showMessage('网络错误：' + err.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = '上传并导入';
            fileInput.value = '';
        }
    });

    async function performAction(action, ruleKey) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('rule_key', ruleKey);
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) formData.append('csrf_token', csrfToken);

        const resp = await fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await resp.json();
        if (data.error) {
            showMessage(data.error, 'error');
        } else if (data.success) {
            showMessage(data.message, 'success');
        }
    }
</script>
</body>
</html>