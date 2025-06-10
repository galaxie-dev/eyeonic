<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
// $host = 'sql307.infinityfree.com';
// $db   = 'if0_39115861_eyeonic';
// $user = 'if0_39115861';
// $pass = 'QPDY35CzNmhsUMy';
// $charset = 'utf8mb4'; 

$host = 'localhost';
$db   = 'eyeonic';
$user = 'root';
$pass = '';
$charset = 'utf8mb4'; 

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}



// Fetch all dashboard statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE order_status = 'delivered'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalFeaturedProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Get recent orders
$recentOrders = $pdo->query("
    SELECT o.id, o.created_at, o.total, o.order_status, u.name as customer_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();

// Get low stock products
$lowStockProducts = $pdo->query("
    SELECT id, name, stock 
    FROM products 
    WHERE stock < 10
    ORDER BY stock ASC
    LIMIT 5
")->fetchAll();








function requireAdminLogin() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
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
            --warning: #f59e0b;
            --danger: #ef4444;
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
        
        h2 {
            color: var(--primary-dark);
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            margin-bottom: 0;
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
            margin-bottom: 30px;
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
        
        .dashboard-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background-color: var(--secondary);
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        .status-pending {
            color: var(--warning);
            font-weight: 500;
        }
        
        .status-delivered {
            color: var(--success);
            font-weight: 500;
        }
        
        .status-cancelled {
            color: var(--danger);
            font-weight: 500;
        }
        
        .low-stock {
            color: var(--danger);
            font-weight: 500;
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
                <h3>Total Admins</h3>
                <p><strong><?= $totalAdmins ?></strong> admin accounts</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><strong><?= $totalOrders ?></strong> orders placed</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p><strong>KES <?= number_format($totalRevenue ?? 0, 2) ?></strong> generated</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><strong><?= $totalProducts ?></strong> products</p>
            </div>
            
            <div class="stat-card">
                <h3>Featured Products</h3>
                <p><strong><?= $totalFeaturedProducts ?></strong> featured items</p>
            </div>
            
            <div class="stat-card">
                <h3>Categories</h3>
                <p><strong><?= $totalCategories ?></strong> categories</p>
            </div>
        </div>
        
        <div class="admin-menu">
            <ul>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="manage_categories.php">Manage Categories</a></li>
                <li><a href="product_add.php">Add a Product</a></li>
                <li><a href="manage_admins.php">Manage Admins</a></li>
            </ul>
        </div>
        
        <div class="dashboard-section">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><a href="order_details.php?id=<?= $order['id'] ?>">#<?= $order['id'] ?></a></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td>KES <?= number_format($order['total'], 2) ?></td>
                        <td class="status-<?= strtolower($order['order_status']) ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="dashboard-section">
            <h2>Low Stock Products</h2>
            <?php if (count($lowStockProducts) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockProducts as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><a href="product_edit.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></td>
                            <td class="<?= $product['stock'] < 5 ? 'low-stock' : '' ?>">
                                <?= $product['stock'] ?> left
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products with low stock.</p>
            <?php endif; ?>
        </div>
        
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>