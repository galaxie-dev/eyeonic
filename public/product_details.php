<?php
require_once '../config/database.php';
include 'header.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p>Product not found.</p>";
    include 'footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<p>Product not found.</p>";
    include 'footer.php';
    exit;
}
?>

<div class="container">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="product-detail">
        <?php if (!empty($product['image_path'])): ?>
            <img src="../<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <?php else: ?>
            <img src="../assets/no-image.png" alt="No image available">
        <?php endif; ?>
        <div class="details">
            <p><strong>Price:</strong> KES <?= number_format($product['price'], 2) ?></p>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <a href="cart.php?add=<?= $product['id'] ?>"><button>Add to Cart</button></a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
