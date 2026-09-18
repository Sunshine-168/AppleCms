<?php
/**
 * 订单管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
$page = max(1, (int)input('page', 1, 'GET'));
$limit = 20;
$offset = ($page - 1) * $limit;
$searchOrderNo = trim(input('order_no', '', 'GET'));
$searchUser = trim(input('username', '', 'GET'));
$searchStatus = input('status', '', 'GET');
$searchType = input('type', 'vip', 'GET');

$where = "1=1";
$params = [];

if ($searchType) {
    $where .= " AND o.type = ?";
    $params[] = $searchType;
}
if ($searchOrderNo !== '') {
    $where .= " AND o.out_trade_no LIKE ?";
    $params[] = "%$searchOrderNo%";
}
if ($searchUser !== '') {
    $where .= " AND u.username LIKE ?";
    $params[] = "%$searchUser%";
}
if ($searchStatus !== '') {
    $where .= " AND o.status = ?";
    $params[] = (int)$searchStatus;
}

// 总记录数
$countSql = "SELECT COUNT(*) as cnt FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where";
$totalRows = $db->fetch($db->query($countSql, $params))['cnt'] ?? 0;
$totalPages = ceil($totalRows / $limit);

// 查询订单列表
$sql = "SELECT o.*, u.username 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE $where 
        ORDER BY o.id DESC 
        LIMIT $offset, $limit";
$orders = $db->fetchAll($db->query($sql, $params));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>充值订单管理</title>
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
        .status-paid {
            color: green;
            font-weight: bold;
        }
        .status-unpaid {
            color: red;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-shopping-cart"></i> 充值订单管理</h1>

        <!-- 筛选表单 -->
        <form method="get" class="filter-bar">
            <div class="filter-group">
                <label>订单号</label>
                <input type="text" name="order_no" value="<?= h($searchOrderNo) ?>" placeholder="订单号">
            </div>
            <div class="filter-group">
                <label>用户名</label>
                <input type="text" name="username" value="<?= h($searchUser) ?>" placeholder="用户名">
            </div>
            <div class="filter-group">
                <label>支付状态</label>
                <select name="status">
                    <option value="">全部</option>
                    <option value="0" <?= $searchStatus === '0' ? 'selected' : '' ?>>未支付</option>
                    <option value="1" <?= $searchStatus === '1' ? 'selected' : '' ?>>已支付</option>
                </select>
            </div>
            <input type="hidden" name="type" value="vip">
            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn-primary btn-sm"><i class="fas fa-search"></i> 筛选</button>
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <a href="orders.php?type=vip" class="btn-outline btn-sm"><i class="fas fa-undo-alt"></i> 重置</a>
            </div>
        </form>

        <!-- 订单列表 -->
        <div style="background:white; border-radius:20px; border:1px solid var(--gray-200); overflow:auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>订单号</th>
                        <th>用户</th>
                        <th>金额(元)</th>
                        <th>月数</th>
                        <th>状态</th>
                        <th>支付时间</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;">暂无订单记录</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= h($order['out_trade_no']) ?></td>
                        <td><?= h($order['username']) ?> (ID:<?= $order['user_id'] ?>)</td>
                        <td><?= number_format($order['money'], 2) ?></td>
                        <td><?= $order['months'] ?></td>
                        <td class="<?= $order['status'] ? 'status-paid' : 'status-unpaid' ?>">
                            <?= $order['status'] ? '已支付' : '未支付' ?>
                        </td>
                        <td><?= $order['pay_time'] ?: '-' ?></td>
                        <td><?= $order['created_at'] ?></td>
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