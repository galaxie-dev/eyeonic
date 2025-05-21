<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=? WHERE id=?");
    $stmt->execute([$name, $desc, $price, $category_id, $id]);

    header('Location: manage_products.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<h2>Edit Product</h2>
<form method="post">
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br>
    <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea><br>
    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>
    <select name="category_id">
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
        </option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">Update Product</button>
</form>
<a href="manage_products.php">← Back</a>
