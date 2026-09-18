<?php
/**
 * 签到日志管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}

// 分页参数
$page = max(1, (int)input('page', 1, 'GET'));
$limit = 20;
$offset = ($page - 1) * $limit;

// 筛选条件
$searchUser = trim(input('search_user', '', 'GET'));
$startDate = input('start_date', '', 'GET');
$endDate = input('end_date', '', 'GET');

$where = "l.type = 'sign'";
$params = [];

if ($searchUser !== '') {
    $where .= " AND u.username LIKE ?";
    $params[] = "%$searchUser%";
}
if ($startDate) {
    $where .= " AND DATE(l.created_at) >= ?";
    $params[] = $startDate;
}
if ($endDate) {
    $where .= " AND DATE(l.created_at) <= ?";
    $params[] = $endDate;
}

// 总记录数
$countSql = "SELECT COUNT(*) as cnt FROM gold_logs l 
             JOIN users u ON l.user_id = u.id 
             WHERE $where";
$totalRows = $db->fetch($db->query($countSql, $params))['cnt'] ?? 0;
$totalPages = ceil($totalRows / $limit);

// 查询记录
$sql = "SELECT l.id, l.user_id, u.username, l.gold_change, l.gold_after, l.remark, l.created_at
        FROM gold_logs l
        JOIN users u ON l.user_id = u.id
        WHERE $where
        ORDER BY l.created_at DESC
        LIMIT $offset, $limit";
$logs = $db->fetchAll($db->query($sql, $params));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>签到日志</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .filter-bar {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .filter-group label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gray-600);
        }
        .filter-group input, .filter-group select {
            padding: 0.4rem 0.6rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
        }
        .pagination {
            margin-top: 1.5rem;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            margin: 0 2px;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            text-decoration: none;
            color: var(--gray-700);
        }
        .pagination a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-calendar-check"></i> 签到日志</h1>

        <!-- 筛选表单 -->
        <form method="get" class="filter-bar">
            <div class="filter-group">
                <label>用户名</label>
                <input type="text" name="search_user" value="<?= h($searchUser) ?>" placeholder="用户名">
            </div>
            <div class="filter-group">
                <label>开始日期</label>
                <input type="date" name="start_date" value="<?= h($startDate) ?>">
            </div>
            <div class="filter-group">
                <label>结束日期</label>
                <input type="date" name="end_date" value="<?= h($endDate) ?>">
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn-primary btn-sm"><i class="fas fa-search"></i> 筛选</button>
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <a href="sign_logs.php" class="btn-outline btn-sm"><i class="fas fa-undo-alt"></i> 重置</a>
            </div>
        </form>

        <!-- 数据表格 -->
        <div style="background:white; border-radius:20px; border:1px solid var(--gray-200); overflow:auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户ID</th>
                        <th>用户名</th>
                        <th>获得金币</th>
                        <th>变动后金币</th>
                        <th>备注</th>
                        <th>签到时间</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" style="text-align:center;">暂无签到记录</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td><?= $log['user_id'] ?></td>
                            <td><?= h($log['username']) ?></td>
                            <td class="stat-number" style="color:var(--success);">+<?= number_format($log['gold_change']) ?></td>
                            <td><?= number_format($log['gold_after']) ?></td>
                            <td><?= h($log['remark']) ?></td>
                            <td><?= $log['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>