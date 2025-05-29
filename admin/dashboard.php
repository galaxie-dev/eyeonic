<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE order_status = 'delivered'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            padding: 0;
            background-color: var(--light);
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            margin: 0;
            font-size: 28px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: var(--primary);
        }
        
        .stat-card p {
            font-size: 18px;
        }
        
        .stat-card strong {
            font-size: 24px;
            color: var(--primary-dark);
        }
        
        .admin-menu {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .admin-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .admin-menu li {
            margin: 0;
        }
        
        .admin-menu a {
            display: block;
            padding: 15px;
            background-color: var(--secondary);
            color: var(--primary-dark);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
        }
        
        .admin-menu a:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .logout-link {
            margin-top: 30px;
            text-align: center;
        }
        
        .logout-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        
        .logout-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h1>
        </header>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><strong><?= $totalUsers ?></strong> registered users</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><strong><?= $totalOrders ?></strong> orders placed</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p><strong>KES <?= number_format($totalRevenue ?? 0, 2) ?></strong> generated</p>
            </div>
        </div>
        
        <div class="admin-menu">
            <ul>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="manage_categories.php">Manage Categories</a></li>
                <li><a href="product_add.php">Add a Product</a></li>
            </ul>
            
            <div class="logout-link">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>