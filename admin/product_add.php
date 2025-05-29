<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/image_helper.php';
requireAdminLogin();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image_path = null;

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_tmp = $_FILES["image"]["tmp_name"];
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($target_tmp, $target_file)) {
            $resized_path = $target_dir . "resized_" . $image_name;

            if (resizeAndCropImage($target_file, $resized_path)) {
                unlink($target_file);
                $image_path = "uploads/resized_" . $image_name; // Relative path for DB
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $category_id, $image_path]);

    header('Location: manage_products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
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
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: var(--primary-dark);
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-upload {
            margin-bottom: 20px;
        }
        
        .file-upload-label {
            display: block;
            padding: 12px;
            background-color: var(--secondary);
            color: var(--primary-dark);
            border: 1px dashed var(--primary);
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        input[type="file"] {
            display: none;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
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
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Product</h2>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required placeholder="Enter product name">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required placeholder="Enter product description"></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price (KES)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required placeholder="Enter price">
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group file-upload">
                <label for="image" class="file-upload-label">Choose Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <img id="image-preview" class="preview-image" src="#" alt="Preview">
            </div>
            
            <button type="submit" class="btn">Add Product</button>
        </form>
        
        <a href="manage_products.php" class="back-link">← Back to Products</a>
    </div>

    <script>
        // Show image preview when file is selected
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('image-preview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>