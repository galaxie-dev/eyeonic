<?php
include 'header.php';
require_once '../config/database.php';

// Fetch featured products
$featuredStmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 LIMIT 4");
$featuredProducts = $featuredStmt->fetchAll();

// Fetch categories
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();
?>
<div class="container">
    <h1>Welcome to Eyeonic</h1>
    <h2>Featured Products</h2>
    <?php foreach ($featuredProducts as $product): ?>
        <div class="product-card">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p><strong>KES <?= number_format($product['price'], 2) ?></strong></p>
            <a href="product_details.php?id=<?= $product['id'] ?>"><button>View Details</button></a>
        </div>
    <?php endforeach; ?>

    <h2>Categories</h2>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li><a href="products.php?category=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php include 'footer.php'; ?>
