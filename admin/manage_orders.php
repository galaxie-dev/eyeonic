<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    $adminComment = $_POST['admin_comment'] ?? '';
    
    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, admin_comment = ? WHERE id = ?");
    $stmt->execute([$status, $adminComment, $orderId]);
    
    // Log the status change
    $logStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
    $logAction = "Updated order #$orderId status to $status";
    if ($adminComment) {
        $logAction .= " with comment: $adminComment";
    }
    $logStmt->execute([$_SESSION['admin_id'], $logAction]);
    
    // Redirect based on status
    if ($status === 'approved') {
        header("Location: ../public/payment.php?order_id=$orderId");
        exit;
    } else {
        // Set success message and redirect back
        $_SESSION['admin_message'] = "Order #$orderId status updated to $status";
        header("Location: manage_orders.php");
        exit;
    }
}

// Fetch orders with user details and order items
$stmt = $pdo->query("
    SELECT o.id, u.name as user_name, u.email, u.phone, o.total, 
           o.order_status as status, o.shipping_address as address, o.created_at, o.admin_comment 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

// Function to get order items
function getOrderItems($pdo, $orderId) {
    $stmt = $pdo->prepare("
        SELECT p.name, oi.quantity, oi.price, pv.type as variant_type, pv.value as variant_value
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .order-details { background-color: #f9f9f9; padding: 10px; margin-top: 5px; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-approved { color: #4caf50; font-weight: bold; }
        .status-declined { color: #f44336; font-weight: bold; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #dff0d8; color: #3c763d; }
    </style>
</head>
<body>
    <h2>Manage Orders</h2>
    
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="message success"><?= $_SESSION['admin_message'] ?></div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($order['user_name']) ?></strong><br>
                    <?= htmlspecialchars($order['email']) ?><br>
                    <?= htmlspecialchars($order['phone']) ?>
                </td>
                <td>
                    <strong>KES <?= number_format($order['total'], 2) ?></strong>
                </td>
                <td>
                    <span class="status-<?= $order['status'] ?>">
                        <?= ucfirst(htmlspecialchars($order['status'])) ?>
                    </span>
                </td>
                <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" required>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="approved" <?= $order['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="declined" <?= $order['status'] === 'declined' ? 'selected' : '' ?>>Declined</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select><br><br>
                        <textarea name="admin_comment" placeholder="Admin comment (optional)" style="width: 100%;"><?= htmlspecialchars($order['admin_comment']) ?></textarea><br>
                        <button type="submit" name="update_status">Update Status</button>
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <div class="order-details">
                        <h4>Order Details:</h4>
                        <ul>
                            <?php foreach (getOrderItems($pdo, $order['id']) as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item['name']) ?> 
                                    <?php if ($item['variant_type']): ?>
                                        (<?= htmlspecialchars($item['variant_type']) ?>: <?= htmlspecialchars($item['variant_value']) ?>)
                                    <?php endif; ?>
                                    - <?= $item['quantity'] ?> x KES <?= number_format($item['price'], 2) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p><strong>Shipping Address:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                        <?php if ($order['admin_comment']): ?>
                            <p><strong>Admin Note:</strong> <?= htmlspecialchars($order['admin_comment']) ?></p>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="dashboard.php">← Back to Dashboard</a>
</body>
</html>