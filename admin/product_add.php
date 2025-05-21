<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/image_helper.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image_path = null;

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

    if (!empty($_FILES['image']['name'])) {
    $target_dir = "../uploads/";
    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_tmp = $_FILES["image"]["tmp_name"];
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($target_tmp, $target_file)) {
        $resized_path = $target_dir . "resized_" . $image_name;
        resizeAndCropImage($target_file, $resized_path);
        unlink($target_file); // remove original
        $image_path = "uploads/resized_" . $image_name;
    }
}


    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $category_id, $image_path]);

    header('Location: manage_products.php');
    exit;
}

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
    </select>
    <input type="file" name="image" accept="image/*"><br>

    <form method="post" enctype="multipart/form-data">
<br>
    <button type="submit">Add Product</button>
</form>
<a href="manage_products.php">← Back</a>
