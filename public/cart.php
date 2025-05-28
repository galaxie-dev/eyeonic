<?php
include 'header.php';
session_start();
require_once '../config/database.php';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            $quantity = max(1, (int)$quantity); // Ensure quantity is at least 1
            $_SESSION['cart'][$productId] = $quantity;
        }
    } elseif (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
        $productId = $_POST['product_id'];
        unset($_SESSION['cart'][$productId]);
    }
}

$cart = $_SESSION['cart'] ?? [];

// Fetch categories for navigation
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Eyeonic - Your Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .cart-section {
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 0 1rem;
        }
        .cart-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #111827;
        }
        .cart-empty {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            overflow: hidden;
        }
        .cart-table th {
            text-align: left;
            padding: 1rem;
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }
        .cart-table td {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
        .cart-item-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: #111827;
        }
        .cart-item-price {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .quantity-input {
            width: 60px;
            padding: 0.375rem;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
            text-align: center;
        }
        .remove-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .remove-btn:hover {
            text-decoration: underline;
        }
        .cart-total {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        .total-box {
            background: white;
            padding: 1.5rem;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .total-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .total-amount {
            font-weight: 600;
            color: #111827;
        }
        .grand-total {
            font-size: 1.125rem;
            border-top: 1px solid #e5e7eb;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }
        .btn-checkout {
            display: block;
            width: 100%;
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 1rem;
            text-align: center;
        }
        .btn-checkout:hover {
            background-color: #1d4ed8;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }
        .btn-update {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-update:hover {
            background-color: #e5e7eb;
        }
        .btn-continue {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-continue:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main>
        <section class="cart-section">
            <h2 class="cart-title">Your Shopping Cart</h2>
            
            <?php if (empty($cart)): ?>
                <div class="cart-empty">
                    <p>Your cart is currently empty.</p>
                    <a href="products.php" class="btn-continue" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($cart as $id => $qty):
                                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                                $stmt->execute([$id]);
                                $product = $stmt->fetch();
                                $subtotal = $product['price'] * $qty;
                                $total += $subtotal;
                                $imagePath = !empty($product['image']) ? '../' . $product['image'] : '../assets/no-image.png';
                            ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="cart-item-img">
                                            <div>
                                                <div class="cart-item-name"><?= htmlspecialchars($product['name']) ?></div>
                                                <div class="cart-item-price">KES <?= number_format($product['price'], 2) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>KES <?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <input type="number" name="quantity[<?= $id ?>]" value="<?= $qty ?>" min="1" class="quantity-input">
                                    </td>
                                    <td>KES <?= number_format($subtotal, 2) ?></td>
                                    <td>
                                        <button type="submit" name="remove_item" value="1" class="remove-btn">
                                            <input type="hidden" name="product_id" value="<?= $id ?>">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn-continue">
                            <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                        <button type="submit" name="update_cart" class="btn-update">
                            <i class="fas fa-sync-alt mr-2"></i> Update Cart
                        </button>
                    </div>
                </form>
                
                <div class="cart-total">
                    <div class="total-box">
                        <div class="total-row">
                            <span class="total-label">Subtotal</span>
                            <span class="total-amount">KES <?= number_format($total, 2) ?></span>
                        </div>
                        <div class="total-row">
                            <span class="total-label">Shipping</span>
                            <span class="total-amount">Calculated at checkout</span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="total-label">Total</span>
                            <span class="total-amount">KES <?= number_format($total, 2) ?></span>
                        </div>
                        <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>