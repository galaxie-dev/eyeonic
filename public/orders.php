<?php
include 'header.php';


// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user's orders
$userId = $_SESSION['user_id'];
$ordersQuery = "
    SELECT o.id, o.created_at, o.total, o.payment_status, o.order_status, 
           o.payment_method, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($ordersQuery);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>My Orders | Eyeonic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --accent: #facc15;
            --text-dark: #0f172a;
            --text-light: #f8fafc;
            --bg-light: #f8fafc;
            --border-light: #e2e8f0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }
        
        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
            overflow: hidden;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }
        
        .order-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-processing {
            background-color: #fef9c3;
            color: #d97706;
        }
        
        .status-shipped {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        
        .status-delivered {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .payment-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .payment-pending {
            background-color: #fef9c3;
            color: #d97706;
        }
        
        .payment-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .payment-failed {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .order-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .order-details.active {
            max-height: 1000px;
            transition: max-height 0.5s ease-in;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-light);
        }
        
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>

    
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-box-open text-3xl text-gray-400"></i>
                </div>
                <h2 class="text-xl font-medium text-gray-700 mb-2">No orders yet</h2>
                <p class="text-gray-500 mb-6">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                <a href="products.php" class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Shop Now
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <h3 class="font-medium text-gray-900">Order #<?= $order['id'] ?></h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        <?= date('F j, Y g:i a', strtotime($order['created_at'])) ?>
                                    </p>
                                </div>
                                
                                <div class="flex flex-wrap gap-3">
                                    <div class="order-status status-<?= strtolower($order['order_status']) ?>">
                                        <i class="fas fa-truck mr-1.5"></i>
                                        <?= ucfirst($order['order_status']) ?>
                                    </div>
                                    <div class="payment-status payment-<?= strtolower($order['payment_status']) ?>">
                                        <i class="fas fa-credit-card mr-1.5"></i>
                                        <?= ucfirst($order['payment_status']) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="flex items-center">
                                    <span class="text-gray-600 mr-2"><?= $order['item_count'] ?> item<?= $order['item_count'] > 1 ? 's' : '' ?></span>
                                </div>
                                
                                <div class="text-right">
                                    <span class="text-lg font-semibold">KSh <?= number_format($order['total'], 2) ?></span>
                                    <button class="ml-4 text-primary hover:text-primary-dark font-medium toggle-details" data-order="<?= $order['id'] ?>">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-details border-t border-gray-100" id="details-<?= $order['id'] ?>">
                            <?php
                            // Fetch order items for this order
                       $itemsQuery = "
                                    SELECT oi.*, p.name, p.image_path, p.price, v.type as variant_type, v.value as variant_value
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    LEFT JOIN product_variants v ON oi.variant_id = v.id
                                    WHERE oi.order_id = ?
                                ";
                            $stmt = $pdo->prepare($itemsQuery);
                            $stmt->execute([$order['id']]);
                            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            
                            <div class="p-6">
                                <h4 class="font-medium text-gray-900 mb-4">Order Items</h4>
                                
                                <div class="space-y-4">
                                    <?php foreach ($items as $item): ?>

                                        <div class="flex items-start gap-4">
                                            <?php
                                            $imagePath = !empty($item['image_path']) ? '../' . $item['image_path'] : '../assets/no-image.png';
                                            ?>
                                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                                alt="<?= htmlspecialchars($item['name']) ?>" 
                                                class="product-image">
                                            
                                            <div class="flex-1">
                                                <h5 class="font-medium"><?= htmlspecialchars($item['name']) ?></h5>
                                                <?php if ($item['variant_type']): ?>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        <?= htmlspecialchars($item['variant_type']) ?>: <?= htmlspecialchars($item['variant_value']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <p class="text-sm text-gray-500 mt-1">Quantity: <?= $item['quantity'] ?></p>
                                            </div>
                                            
                                            <div class="text-right">
                                                <p class="font-medium">KSh <?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                                <p class="text-sm text-gray-500">KSh <?= number_format($item['price'], 2) ?> each</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="mt-6 pt-6 border-t border-gray-100">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium">KSh <?= number_format($order['total'], 2) ?></span>
                                    </div>
                                    
                                    <div class="flex justify-between mt-2">
                                        <span class="text-gray-600">Payment Method</span>
                                        <span class="font-medium">
                                            <?php 
                                            switch($order['payment_method']) {
                                                case 'mpesa': echo 'M-Pesa'; break;
                                                case 'card': echo 'Credit Card'; break;
                                                default: echo ucfirst($order['payment_method']); 
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="flex justify-between mt-4 pt-4 border-t border-gray-100">
                                        <span class="text-lg font-semibold">Total</span>
                                        <span class="text-lg font-semibold">KSh <?= number_format($order['total'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'mobile-menu.php'?>
    <?php include 'footer.php'?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle order details
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order');
                const details = document.getElementById(`details-${orderId}`);
                details.classList.toggle('active');
                
                // Change button text
                this.textContent = details.classList.contains('active') ? 'Hide Details' : 'View Details';
            });
        });
    });
    </script>
</body>
</html>