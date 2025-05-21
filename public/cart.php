<?php
include 'header.php';
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $_SESSION['cart'][$productId] = $quantity;
}

$cart = $_SESSION['cart'] ?? [];
?>
<div class="container">
    <h1>Your Cart</h1>
    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <?php
        $total = 0;
        foreach ($cart as $id => $qty):
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            $subtotal = $product['price'] * $qty;
            $total += $subtotal;
        ?>
        <div class="cart-item">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p>Qty: <?= $qty ?> | Price: KES <?= number_format($product['price'], 2) ?></p>
            <p><strong>Subtotal: KES <?= number_format($subtotal, 2) ?></strong></p>
        </div>
        <?php endforeach; ?>
        <h2>Total: KES <?= number_format($total, 2) ?></h2>
        <a href="checkout.php"><button>Proceed to Checkout</button></a>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
