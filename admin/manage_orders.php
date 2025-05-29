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
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #e0f2fe;
            --dark: #1e293b;
            --light: #f8fafc;
            --accent: #f43f5e;
            --success: #10b981;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--light);
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h2 {
            color: var(--primary-dark);
            margin-top: 0;
        }
        
        .message {
            padding: 12px 15px;
            margin: 0 0 20px 0;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .orders-table th, 
        .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        
        .orders-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .order-details {
            background-color: white;
            padding: 15px;
            margin-top: 5px;
            border-radius: 0 0 6px 6px;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        
        .status-pending { color: #f59e0b; }
        .status-processing { color: #3b82f6; }
        .status-approved { color: var(--success); }
        .status-declined { color: var(--accent); }
        .status-shipped { color: #8b5cf6; }
        .status-delivered { color: #10b981; }
        .status-cancelled { color: #64748b; }
        
        .status-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .admin-comment {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            min-height: 60px;
            margin-bottom: 8px;
            font-family: inherit;
        }
        
        .update-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .update-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .items-list {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }
        
        .items-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .items-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Orders</h2>
        
        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="message success"><?= $_SESSION['admin_message'] ?></div>
            <?php unset($_SESSION['admin_message']); ?>
        <?php endif; ?>
        
        <table class="orders-table">
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
                            <select name="status" class="status-select" required>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="approved" <?= $order['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="declined" <?= $order['status'] === 'declined' ? 'selected' : '' ?>>Declined</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <textarea name="admin_comment" class="admin-comment" placeholder="Admin comment (optional)"><?= htmlspecialchars($order['admin_comment']) ?></textarea>
                            <button type="submit" name="update_status" class="update-btn">Update Status</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <div class="order-details">
                            <h4>Order Details:</h4>
                            <ul class="items-list">
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
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>