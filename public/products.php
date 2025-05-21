<?php
include 'header.php';
require_once '../config/database.php';

$categoryId = $_GET['category'] ?? null;

if ($categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$categoryId]);
} else {
    $stmt = $pdo->query("SELECT * FROM products");
}
$products = $stmt->fetchAll();
?>
<div class="container">
    <h1>Products</h1>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <a href="product_details.php?id=<?= $product['id'] ?>">
                    <?php if (!empty($product['image_path'])): ?>
                        <img src="../<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <img src="../assets/no-image.png" alt="No image available">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                </a>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p><strong>KES <?= number_format($product['price'], 2) ?></strong></p>
                <a href="product_details.php?id=<?= $product['id'] ?>"><button>View Details</button></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
