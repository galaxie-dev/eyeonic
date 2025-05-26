<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll();


?>

<style>
    .products-section {
        max-width: 1200px;
        margin: 2.5rem auto;
        padding: 0 1rem;
    }
    .products-title {
        font-weight: 600;
        font-size: 1.5rem;
        color: #111827;
        margin-bottom: 1rem;
    }
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .btn {
        display: inline-block;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        text-decoration: none;
        transition: background-color 0.2s;
    }
    .btn-primary {
        background-color: #2563eb;
        color: white;
    }
    .btn-primary:hover {
        background-color: #1d4ed8;
    }
    .btn-secondary {
        background-color: #6b7280;
        color: white;
    }
    .btn-secondary:hover {
        background-color: #4b5563;
    }
    .products-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        overflow-x: auto;
    }
    .products-table th, .products-table td {
        padding: 0.75rem;
        text-align: left;
        font-size: 0.875rem;
        color: #111827;
    }
    .products-table th {
        background-color: #f9fafb;
        font-weight: 600;
    }
    .products-table tr {
        border-bottom: 1px solid #e5e7eb;
    }
    .products-table tr:last-child {
        border-bottom: none;
    }
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: contain;
        border-radius: 0.25rem;
    }
    .action-links a {
        color: #2563eb;
        text-decoration: none;
        margin-right: 0.5rem;
        transition: color 0.2s;
    }
    .action-links a:hover {
        color: #1d4ed8;
    }
    .action-links .delete {
        color: #dc2626;
    }
    .action-links .delete:hover {
        color: #b91c1c;
    }
</style>

<main>
    <section class="products-section">
        <h2 class="products-title">Manage Products</h2>
        <div class="action-buttons">
            <a href="product_add.php" class="btn btn-primary">+ Add New Product</a>
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        <?php if (empty($products)): ?>
            <p style="text-align: center; color: #6b7280; font-size: 1rem;">No products found.</p>
        <?php else: ?>
            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price (KES)</th>
                            <th>Discount Price</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Image</th>
                            <th>Featured</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars(substr($product['description'] ?? '', 0, 50)) . (strlen($product['description'] ?? '') > 50 ? '...' : '') ?></td>
                                <td><?= number_format($product['price'], 2) ?></td>
                                <td><?= $product['discount_price'] ? number_format($product['discount_price'], 2) : '-' ?></td>
                                <td><?= htmlspecialchars($product['brand']) ?></td>
                                <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                                <td><?= $product['stock'] ?></td>
                                <td>
                                    <?php
                                    $imagePath = !empty($product['image_url']) ? '../' . htmlspecialchars($product['image_url']) : (!empty($product['image_path']) ? '../' . htmlspecialchars($product['image_path']) : '../assets/no-image.png');
                                    ?>
                                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                </td>
                                <td><?= $product['is_featured'] ? 'Yes' : 'No' ?></td>
                                <td><?= date('F j, Y', strtotime($product['created_at'])) ?></td>
                                <td class="action-links">
                                    <a href="product_edit.php?id=<?= $product['id'] ?>">Edit</a> |
                                    <a href="product_delete.php?id=<?= $product['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

