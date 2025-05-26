<?php
require_once '../config/database.php';
session_start();
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        header('Location: cart.php');
        exit;
    }

    // Calculate total
    $total = 0;
    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        $total += $product['price'] * $qty;
    }

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, created_at, updated_at) VALUES (?, ?, 'pending', NOW(), NOW())");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $orderId = $pdo->lastInsertId();

    // Insert order items
    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $id, $qty, $product['price']]);
    }

    // Simulate payment processing
    // In production, integrate M-Pesa Daraja API here

    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
    $stmt->execute([$orderId]);

    // Clear cart
    unset($_SESSION['cart']);

    header('Location: order_success.php');
    exit;
}

// Fetch cart items for display
$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$total = 0;
if (!empty($cart)) {
    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $cartItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $qty,
                'subtotal' => $product['price'] * $qty
            ];
            $total += $product['price'] * $qty;
        }
    }
}
?>

<style>
    .checkout-section {
        max-width: 1200px;
        margin: 2.5rem auto 0 auto;
        padding: 0 1rem;
    }
    .checkout-title {
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: #111827;
    }
    .checkout-container {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        padding: 1.5rem;
    }
    .checkout-empty {
        font-size: 0.875rem;
        color: #6b7280;
        text-align: center;
        padding: 2rem;
    }
    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 768px) {
        .checkout-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    .checkout-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .checkout-form label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #111827;
    }
    .checkout-form input,
    .checkout-form textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }
    .checkout-form input:focus,
    .checkout-form textarea:focus {
        border-color: #2563eb;
        outline: none;
    }
    .checkout-form textarea {
        resize: vertical;
        min-height: 100px;
    }
    .btn-submit {
        background-color: #2563eb;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.5rem 1.25rem;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        width: max-content;
        align-self: flex-end;
    }
    .btn-submit:hover {
        background-color: #1d4ed8;
    }
    .cart-summary {
        background: #f9fafb;
        border-radius: 0.375rem;
        padding: 1rem;
    }
    .cart-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #111827;
        margin-bottom: 0.5rem;
    }
    .cart-item-name {
        font-weight: 500;
    }
    .cart-item-details {
        color: #6b7280;
    }
    .cart-total {
        font-weight: 600;
        font-size: 1rem;
        color: #111827;
        text-align: right;
        margin-top: 1rem;
    }
</style>

<main>
    <section class="checkout-section">
        <h2 class="checkout-title">Checkout</h2>
        <?php if (empty($cart)): ?>
            <div class="checkout-container">
                <p class="checkout-empty">Your cart is empty. <a href="products.php" class="text-blue-600 hover:text-blue-800">Continue shopping</a>.</p>
            </div>
        <?php else: ?>
            <div class="checkout-container">
                <div class="checkout-grid">
                    <form action="checkout.php" method="post" class="checkout-form">
                        <div>
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div>
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div>
                            <label for="address">Delivery Address</label>
                            <textarea id="address" name="address" required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Place Order</button>
                    </form>
                    <div class="cart-summary">
                        <h3 class="cart-item-name">Order Summary</h3>
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <span class="cart-item-name"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                                <span class="cart-item-details">KES <?= number_format($item['subtotal'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="cart-total">Total: KES <?= number_format($total, 2) ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>