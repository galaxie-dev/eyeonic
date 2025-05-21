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

// handle new image uploads and update image path
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $image_path = $product['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $image_name;
        } else {
            $image_path = null; // fallback if upload fails
        }

    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, image_path=? WHERE id=?");
    $stmt->execute([$name, $desc, $price, $category_id, $image_path, $id]);

    header('Location: manage_products.php');
    exit;
}

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
    </select>
    <?php if ($product['image_path']): ?>
    <img src="../<?= $product['image_path'] ?>" alt="Image" width="100"><br>
<?php endif; ?>
<input type="file" name="image" accept="image/*"><br>
<br>
    <button type="submit">Update Product</button>
</form>
<a href="manage_products.php">← Back</a>
