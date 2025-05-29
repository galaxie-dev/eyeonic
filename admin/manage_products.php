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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #e0f2fe;
            --dark: #1e293b;
            --light: #f8fafc;
            --accent: #f43f5e;
            --success: #10b981;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--light);
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        h2 {
            color: var(--primary-dark);
            margin: 0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: #6b7280;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .products-table th,
        .products-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .products-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .products-table tr:hover {
            background-color: var(--secondary);
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .action-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-right: 10px;
            transition: color 0.3s;
        }
        
        .action-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .action-link.delete {
            color: var(--accent);
        }
        
        .action-link.delete:hover {
            color: #dc2626;
        }
        
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-size: 16px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .featured-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: var(--success);
            color: white;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manage Products</h2>
            <div>
                <a href="product_add.php" class="btn">+ Add New Product</a>
                <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-message">
                <p>No products found. Add your first product to get started.</p>
                <a href="product_add.php" class="btn">Add Product</a>
            </div>
        <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Featured</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($product['name']) ?>
                                <div style="font-size: 12px; color: #6b7280;">
                                    <?= htmlspecialchars(substr($product['description'] ?? '', 0, 50)) . (strlen($product['description'] ?? '') > 50 ? '...' : '') ?>
                                </div>
                            </td>
                            <td>
                                <strong>KES <?= number_format($product['price'], 2) ?></strong>
                                <?php if ($product['discount_price']): ?>
                                    <div style="font-size: 12px; color: var(--accent);">
                                        <s>KES <?= number_format($product['discount_price'], 2) ?></s>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                            <td>
                                <?php
                                $imagePath = !empty($product['image_url']) ? '../' . htmlspecialchars($product['image_url']) : 
                                            (!empty($product['image_path']) ? '../' . htmlspecialchars($product['image_path']) : 
                                            '../assets/no-image.png');
                                ?>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                            </td>
                            <td>
                                <?php if ($product['is_featured']): ?>
                                    <span class="featured-badge">Featured</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                            <td>
                                <a href="product_edit.php?id=<?= $product['id'] ?>" class="action-link">Edit</a>
                                <a href="product_delete.php?id=<?= $product['id'] ?>" class="action-link delete" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>