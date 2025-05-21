<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $category_id]);

    header('Location: manage_products.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<h2>Add New Product</h2>
<form method="post">
    <input type="text" name="name" required placeholder="Product Name"><br>
    <textarea name="description" required placeholder="Description"></textarea><br>
    <input type="number" step="0.01" name="price" required placeholder="Price"><br>
    <select name="category_id" required>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">Add Product</button>
</form>
<a href="manage_products.php">← Back</a>
