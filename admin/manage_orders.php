<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$stmt = $pdo->query("SELECT o.id, u.name, o.total_amount, o.status, o.created_at 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>

<h2>Orders</h2>
<table border="1">
    <tr>
        <th>Order ID</th><th>User</th><th>Amount</th><th>Status</th><th>Date</th>
    </tr>
    <?php foreach ($orders as $order): ?>
    <tr>
        <td><?= $order['id'] ?></td>
        <td><?= htmlspecialchars($order['name']) ?></td>
        <td>KES <?= number_format($order['total_amount'], 2) ?></td>
        <td><?= htmlspecialchars($order['status']) ?></td>
        <td><?= $order['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="dashboard.php">← Back to Dashboard</a>
