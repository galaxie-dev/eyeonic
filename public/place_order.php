<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header at the top (as requested)
include 'header.php';

// Database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Use JavaScript redirect to avoid headers issue
    echo '<script>window.location.href = "login.php?redirect=checkout.php";</script>';
    exit;
}

// Fetch user details
$userId = $_SESSION['user_id'];
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

if (!$user) {
    session_destroy();
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

// Initialize variables
$errors = [];
$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$subtotal = 0;
$orderSuccess = false; // Flag to track successful order

// Process cart items
if (!empty($cart)) {
    foreach ($cart as $productId => $quantity) {
        $stmt = $pdo->prepare("SELECT id, name, price, discount_price, image_path FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Use discount price if available and lower
            $price = ($product['discount_price'] && $product['discount_price'] < $product['price']) 
                   ? $product['discount_price'] 
                   : $product['price'];
            
            $imagePath = !empty($product['image_path']) 
                        ? '../' . $product['image_path'] 
                        : '../assets/no-image.png';
            
            $cartItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $price,
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
                'image' => $imagePath
            ];
            
            $subtotal += $price * $quantity;
        }
    }
}

// Calculate delivery fee
$deliveryFee = max(200, $subtotal * 0.1);
$total = $subtotal + $deliveryFee;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $requiredFields = ['address', 'city', 'zip_code', 'country', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Please fill in all required fields.";
            break;
        }
    }
    
    // Sanitize inputs
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $zipCode = htmlspecialchars(trim($_POST['zip_code']));
    $country = htmlspecialchars(trim($_POST['country']));
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $paymentMethod = isset($_POST['payment_method']) ? htmlspecialchars(trim($_POST['payment_method'])) : 'M-Pesa';
    
    // Validate phone number
    if (!preg_match('/^\+?\d{8,15}$/', $phone)) {
        $errors[] = "Please enter a valid phone number.";
    }
    
    // Validate payment method
    $allowedMethods = ['M-Pesa', 'Cash on Delivery'];
    if (!in_array($paymentMethod, $allowedMethods)) {
        $errors[] = "Invalid payment method selected.";
    }
    
    // Process order if no errors
    if (empty($errors) && !empty($cartItems)) {
        try {
            $pdo->beginTransaction();
            
            // Update user details if changed
            $updateFields = [];
            $params = [];
            
            if ($user['address'] !== $address) {
                $updateFields[] = "address = ?";
                $params[] = $address;
            }
            
            if ($user['city'] !== $city) {
                $updateFields[] = "city = ?";
                $params[] = $city;
            }
            
            if ($user['zip_code'] !== $zipCode) {
                $updateFields[] = "zip_code = ?";
                $params[] = $zipCode;
            }
            
            if ($user['country'] !== $country) {
                $updateFields[] = "country = ?";
                $params[] = $country;
            }
            
            if ($user['phone'] !== $phone) {
                $updateFields[] = "phone = ?";
                $params[] = $phone;
            }
            
            if (!empty($updateFields)) {
                $params[] = $userId;
                $updateStmt = $pdo->prepare("UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?");
                $updateStmt->execute($params);
            }
            
            // Create order
            $orderStmt = $pdo->prepare("INSERT INTO orders 
                (user_id, total, delivery_fee, payment_method, order_status, shipping_address, created_at)
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
            
            $shippingAddress = "$address, $city, $zipCode, $country";
            $orderStmt->execute([$userId, $total, $deliveryFee, $paymentMethod, $shippingAddress]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $itemStmt = $pdo->prepare("INSERT INTO order_items 
                    (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)");
                $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            }
            
            $pdo->commit();
            
            // Clear cart and set success flag
            unset($_SESSION['cart']);
            $orderSuccess = true;
            $redirectOrderId = $orderId; // Store order ID for redirect
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Order processing error: " . $e->getMessage());
            $errors[] = "An error occurred while processing your order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Eyeonic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .order-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .order-grid {
            display: grid;
            gap: 2rem;
        }
        @media (min-width: 1024px) {
            .order-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        .order-section {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .order-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .cart-item {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 0.25rem;
            margin-right: 1rem;
            border: 1px solid #e5e7eb;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .cart-item-price {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .summary-total {
            font-weight: 600;
            font-size: 1.125rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .error-message {
            color: #dc2626;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background-color: #fef2f2;
            border-radius: 0.375rem;
            border-left: 4px solid #dc2626;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Place Your Order</h1>
        
        <?php if ($orderSuccess): ?>
            <div class="success-message">
                <p>Order placed successfully! Redirecting to confirmation...</p>
            </div>
            <script>
                // JavaScript redirect to checkout.php
                setTimeout(() => {
                    window.location.href = 'checkout.php?order_id=<?= $redirectOrderId ?>';
                }, 1000); // Short delay to show success message
            </script>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart)): ?>
            <div class="order-section">
                <p>Your cart is empty. <a href="products.php" class="text-blue-600 hover:underline">Continue shopping</a>.</p>
            </div>
        <?php else: ?>
            <div class="order-grid">
                <!-- Shipping Information Form -->
                <div class="order-section">
                    <h2 class="order-title">Shipping Information</h2>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" class="form-input" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-input" 
                                   value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Street Address *</label>
                            <textarea id="address" name="address" class="form-input" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" id="city" name="city" class="form-input" 
                                   value="<?= htmlspecialchars($user['city']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="zip_code" class="form-label">ZIP/Postal Code *</label>
                            <input type="text" id="zip_code" name="zip_code" class="form-input" 
                                   value="<?= htmlspecialchars($user['zip_code']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="country" class="form-label">Country *</label>
                            <input type="text" id="country" name="country" class="form-input" 
                                   value="<?= htmlspecialchars($user['country']) ?>" required>
                        </div>
                        
                        <!-- Add payment method field -->
                        <!-- <div class="form-group">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select id="payment_method" name="payment_method" class="form-input" required>
                                <option value="M-Pesa" <?= $paymentMethod === 'M-Pesa' ? 'selected' : '' ?>>M-Pesa</option>
                                <option value="Cash on Delivery" <?= $paymentMethod === 'Cash on Delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
                            </select>
                        </div> -->
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-shopping-bag mr-2"></i> Place Order
                        </button> 
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-section">
                    <h2 class="order-title">Order Summary</h2>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                            <div class="cart-item-details">
                                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="cart-item-price">KES <?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></div>
                            </div>
                            <div>KES <?= number_format($item['subtotal'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>KES <?= number_format($subtotal, 2) ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>KES <?= number_format($deliveryFee, 2) ?></span>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>KES <?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>