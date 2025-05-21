<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$error = $success = "";

// ADD NEW CATEGORY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);

    if (!$name) {
        $error = "Category name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            $success = "Category added successfully.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// DELETE CATEGORY
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Category deleted.";
}

// EDIT CATEGORY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $desc, $id]);
    $success = "Category updated.";
}

// Fetch updated categories list
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        form { margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        .success { color: green; }
        .error { color: red; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>

<h2>Manage Categories</h2>

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<h3>Add New Category</h3>
<form method="post">
    <input type="text" name="name" placeholder="Category Name" required>
    <textarea name="description" placeholder="Category Description (optional)"></textarea>
    <button type="submit" name="add">Add Category</button>
</form>

<h3>All Categories</h3>
<table>
    <thead>
        <tr><th>Name</th><th>Description</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td><?= htmlspecialchars($cat['description']) ?></td>
            <td class="actions">
                <!-- Inline edit form -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required>
                    <input type="text" name="description" value="<?= htmlspecialchars($cat['description']) ?>">
                    <button type="submit" name="update">Update</button>
                </form>
                <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Delete this category?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><a href="dashboard.php">← Back to Dashboard</a></p>

</body>
</html>
