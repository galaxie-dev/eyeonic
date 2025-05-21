<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$stmt = $pdo->query("SELECT p.id, p.name, p.price, c.name AS category 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>

<h2>Products</h2>
<a href="product_add.php">+ Add New Product</a>
<table border="1">
    <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr>
    <?php foreach ($products as $product): ?>
    <tr>
        <td><?= $product['id'] ?></td>
        <td><?= htmlspecialchars($product['name']) ?></td>
        <td><?= htmlspecialchars($product['category']) ?></td>
        <td>KES <?= number_format($product['price'], 2) ?></td>
        <td>
            <a href="product_edit.php?id=<?= $product['id'] ?>">Edit</a> |
            <a href="product_delete.php?id=<?= $product['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="dashboard.php">← Back to Dashboard</a>
