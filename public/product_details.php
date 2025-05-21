<?php
include 'header.php';
require_once '../config/database.php';

$productId = $_GET['id'] ?? null;

if ($productId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}
?>
<div class="container">
    <?php if ($product): ?>
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p><?= htmlspecialchars($product['description']) ?></p>
        <p><strong>KES <?= number_format($product['price'], 2) ?></strong></p>
        <form method="post" action="cart.php">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
            <button type="submit">Add to Cart</button>
        </form>
    <?php else: ?>
        <p>Product not found.</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
