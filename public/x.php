<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
require_once '../config/database.php';

// Database connection with error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT name, email, phone, address, city, zip_code, country FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Initialize variables
$errors = [];
$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$productTotal = 0;

// Calculate cart totals
if (!empty($cart)) {
    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT id, name, price, discount_price, image_path FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $price = $product['discount_price'] && $product['discount_price'] < $product['price'] 
                   ? $product['discount_price'] 
                   : $product['price'];
            
            $imagePath = !empty($product['image_path']) 
                        ? '../' . $product['image_path'] 
                        : '../assets/no-image.png';
            
            $cartItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $price,
                'quantity' => $qty,
                'subtotal' => $price * $qty,
                'image' => $imagePath
            ];
            
            $productTotal += $price * $qty;
        }
    }
}

$deliveryFee = calculateDeliveryFee($productTotal);
$total = $productTotal + $deliveryFee;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid form submission. Please try again.";
    }
    
    // Validate required fields
    $requiredFields = ['address', 'city', 'zip_code', 'country', 'phone', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Please fill in all required fields.";
            break;
        }
    }
    
    // Sanitize inputs
    $address = trim(htmlspecialchars($_POST['address']));
    $city = trim(htmlspecialchars($_POST['city']));
    $zipCode = trim(htmlspecialchars($_POST['zip_code']));
    $country = trim(htmlspecialchars($_POST['country']));
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $paymentMethod = trim(htmlspecialchars($_POST['payment_method']));
    
    // Validate phone number format
    if (!preg_match('/^\+?\d{8,15}$/', $phone)) {
        $errors[] = "Please enter a valid phone number.";
    }
    
    // Process order if no errors
    if (empty($errors) && !empty($cart)) {
        $fullAddress = "{$address}, {$city}, {$zipCode}, {$country}";
        
        try {
            $pdo->beginTransaction();
            
            // Insert order
            $stmt = $pdo->prepare("INSERT INTO orders 
                                  (user_id, total, delivery_fee, payment_method, 
                                   order_status, shipping_address, created_at) 
                                  VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
            $stmt->execute([
                $_SESSION['user_id'], 
                $total, 
                $deliveryFee, 
                $paymentMethod,
                $fullAddress
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            foreach ($cart as $id => $qty) {
                $stmt = $pdo->prepare("SELECT price, discount_price FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                
                $price = $product['discount_price'] && $product['discount_price'] < $product['price'] 
                       ? $product['discount_price'] 
                       : $product['price'];
                
                $stmt = $pdo->prepare("INSERT INTO order_items 
                                      (order_id, product_id, quantity, price) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $id, $qty, $price]);
            }
            
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
                $params[] = $_SESSION['user_id'];
                $updateStmt = $pdo->prepare("UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?");
                $updateStmt->execute($params);
            }
            
            $pdo->commit();
            
            // Clear cart and redirect
            unset($_SESSION['cart']);
            header("Location: order_confirmation.php?order_id=$orderId");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Order processing error: " . $e->getMessage());
            $errors[] = "An error occurred while processing your order. Please try again.";
        }
    }
}

// Function to calculate delivery fee
function calculateDeliveryFee($total) {
    if ($total < 1000) return 200;
    if ($total < 5000) return 500;
    return 1000;
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Eyeonic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        @media (min-width: 1024px) {
            .checkout-grid {
                grid-template-columns: 2fr 1fr;
            }
        }
        .checkout-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .checkout-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .checkout-form input,
        .checkout-form textarea,
        .checkout-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
        }
        .checkout-form textarea {
            height: 100px;
            resize: vertical;
        }
        .btn-submit {
            background-color: #2563eb;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #1d4ed8;
        }
        .order-summary {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .order-summary h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f3f4f6;
        }
        .cart-item-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 4px;
            margin-right: 15px;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .cart-item-price {
            color: #6b7280;
            font-size: 14px;
        }
        .order-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 18px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <main>
        <div class="checkout-container">
            <h1 class="text-2xl font-bold mb-6">Checkout</h1>
            
            <?php if (empty($cart)): ?>
                <div class="bg-white p-6 rounded-lg shadow">
                    <p>Your cart is empty. <a href="products.php" class="text-blue-600">Continue shopping</a>.</p>
                </div>
            <?php else: ?>
                <div class="checkout-grid">
                    <form action="place_order.php" method="post" class="checkout-form">
                        <h2 class="text-xl font-bold mb-6">Shipping Information</h2>
                        
                        <div>
                            <label for="name">Full Name</label>
                            <input type="text" id="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                        </div>
                        
                        <div>
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        
                        <div>
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        
                        <div>
                            <label for="address">Street Address</label>
                            <textarea id="address" name="address" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <div>
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?= htmlspecialchars($user['city']) ?>" required>
                        </div>
                        
                        <div>
                            <label for="zip_code">ZIP/Postal Code</label>
                            <input type="text" id="zip_code" name="zip_code" value="<?= htmlspecialchars($user['zip_code']) ?>" required>
                        </div>
                        
                        <div>
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>" required>
                        </div>
                        
                        <button type="submit" class="btn-submit">Place Order</button>
                    </form>
                    
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                                <div class="cart-item-details">
                                    <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="cart-item-price">KES <?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?></div>
                                </div>
                                <div>KES <?= number_format($item['subtotal'], 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-item">
                            <div>Subtotal</div>
                            <div>KES <?= number_format($productTotal, 2) ?></div>
                        </div>
                        
                        <div class="cart-item">
                            <div>Delivery Fee</div>
                            <div>KES <?= number_format($deliveryFee, 2) ?></div>
                        </div>
                        
                        <div class="cart-item order-total">
                            <div>Total</div>
                            <div>KES <?= number_format($total, 2) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>