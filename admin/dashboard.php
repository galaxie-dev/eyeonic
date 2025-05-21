<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn();
?>

<h1>Welcome, <?= $_SESSION['admin_name'] ?>!</h1>
<div>
    <h3>Analytics Overview</h3>
    <p>Total Users: <strong><?= $totalUsers ?></strong></p>
    <p>Total Orders: <strong><?= $totalOrders ?></strong></p>
    <p>Total Revenue: <strong>KES <?= number_format($totalRevenue ?? 0, 2) ?></strong></p>
</div>
<ul>
    <li><a href="manage_users.php">Manage Users</a></li>
    <li><a href="manage_products.php">Manage Products</a></li>
    <li><a href="manage_orders.php">Manage Orders</a></li>
    <li><a href="manage_categories.php">Manage Categories</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
