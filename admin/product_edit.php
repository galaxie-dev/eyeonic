<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $brand = trim($_POST['brand']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";
    if ($discount_price !== null && $discount_price >= $price) $errors[] = "Discount price must be less than original price.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";
    if (!in_array($category_id, array_column($categories, 'id'))) $errors[] = "Invalid category.";

    $image_url = $product['image_url'];
    $image_path = $product['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../images/";
        $fileName = basename($_FILES['image']['name']);
        $targetFile = $targetDir . uniqid() . '_' . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $errors[] = "File is not an image.";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['image']['size'] > 5000000) {
            $errors[] = "Image size must be less than 5MB.";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_url = str_replace('../', '', $targetFile); // Store relative path
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = ?, description = ?, price = ?, discount_price = ?, brand = ?, category_id = ?, stock = ?, image_url = ?, is_featured = ?
            WHERE id = ?
        ");
        $success = $stmt->execute([$name, $description, $price, $discount_price, $brand, $category_id, $stock, $image_url, $is_featured, $productId]);

        if ($success) {
            header('Location: manage_products.php?');
            exit;
        } else {
            $errors[] = "Failed to update product.";
        }
    }
}

?>

<style>
    .edit-section {
        max-width: 800px;
        margin: 2.5rem auto;
        padding: 0 1rem;
    }
    .edit-title {
        font-weight: 600;
        font-size: 1.5rem;
        color: #111827;
        margin-bottom: 1.5rem;
    }
    .edit-form {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .form-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #111827;
    }
    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
        border-color: #2563eb;
        outline: none;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .form-group input[type="checkbox"] {
        width: auto;
    }
    .btn {
        display: inline-block;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        text-decoration: none;
        transition: background-color 0.2s;
        border: none;
        cursor: pointer;
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
    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    .current-image {
        max-width: 100px;
        margin-top: 0.5rem;
        border-radius: 0.25rem;
    }
</style>

<main>
    <section class="edit-section">
        <h2 class="edit-title">Edit Product</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="edit-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (KES)</label>
                <input type="number" id="price" name="price" step="0.01" value="<?= $product['price'] ?>" required>
            </div>
            <div class="form-group">
                <label for="discount_price">Discount Price (KES, optional)</label>
                <input type="number" id="discount_price" name="discount_price" step="0.01" value="<?= $product['discount_price'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="<?= $product['stock'] ?>" required>
            </div>
            <div class="form-group">
                <label for="image">Image (optional)</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($product['image_url'] || $product['image_path']): ?>
                    <img src="<?= !empty($product['image_url']) ? '../' . htmlspecialchars($product['image_url']) : '../' . htmlspecialchars($product['image_path']) ?>" alt="Current Image" class="current-image">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?>>
                    Featured Product
                </label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>

