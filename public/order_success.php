<?php 
require_once '../config/database.php';
session_start();

// Verify order exists and belongs to user
if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: cart.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user_id'];

// Get order details including payment status
$stmt = $pdo->prepare("
    SELECT o.total, p.status as payment_status 
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Eyeonic</title>
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
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .order-details {
            background-color: var(--secondary);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .detail-label {
            color: var(--dark);
            font-weight: 600;
        }
        
        .detail-value {
            color: var(--dark);
            font-weight: 500;
        }
        
        .total-row {
            border-top: 1px solid #e2e8f0;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            font-weight: 700;
        }
        
        .payment-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .btn-continue {
            display: inline-block;
            width: 100%;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            text-align: center;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: background-color 0.2s;
            margin-top: 1rem;
        }
        
        .btn-continue:hover {
            background-color: var(--primary-dark);
        }
        
        .success-icon {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .success-icon svg {
            width: 4rem;
            height: 4rem;
            color: var(--success);
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <h1>Order Successful</h1>
        
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Order ID:</span>
                <span class="detail-value">#<?= htmlspecialchars($orderId) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">Cash on Delivery</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value payment-status">Pending</span>
            </div>
            <div class="detail-row total-row">
                <span class="detail-label">Amount to Pay:</span>
                <span class="detail-value">KES <?= number_format($order['total'], 2) ?></span>
            </div>
        </div>
        
        <p class="text-center text-gray-700 mb-6">Thank you for your order! Please have the exact amount ready when our delivery agent arrives.</p>
        
        <a href="products.php" class="btn-continue">
            Continue Shopping
        </a>
    </main>
</body>
</html>

<?php include 'mobile-menu.php'; ?>
<?php include 'footer.php'; ?>