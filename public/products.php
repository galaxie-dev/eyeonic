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
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p><strong>KES <?= number_format($product['price'], 2) ?></strong></p>
            <a href="product_details.php?id=<?= $product['id'] ?>"><button>View Details</button></a>
        </div>
    <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
