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
    try {
        // First remove any parent relationships to this category
        $stmt = $pdo->prepare("UPDATE categories SET parent_id = NULL WHERE parent_id = ?");
        $stmt->execute([$id]);
        
        // Then delete the category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Category deleted.";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// UPDATE CATEGORY DETAILS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $desc, $id]);
    $success = "Category details updated.";
}

// UPDATE PARENT RELATIONSHIP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_parent'])) {
    $id = (int)$_POST['id'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    // Prevent a category from being its own parent
    if ($id === $parent_id) {
        $error = "A category cannot be its own parent.";
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET parent_id = ? WHERE id = ?");
        $stmt->execute([$parent_id, $id]);
        $success = "Parent relationship updated.";
    }
}

// Fetch all categories with their parent names
$stmt = $pdo->query("
    SELECT c.*, p.name as parent_name 
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build hierarchical category tree
function buildCategoryTree($categories, $parent_id = null) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $children = buildCategoryTree($categories, $category['id']);
            if ($children) {
                $category['children'] = $children;
            }
            $tree[] = $category;
        }
    }
    return $tree;
}

$categoryTree = buildCategoryTree($categories);

// Function to display categories recursively
function displayCategories($categories, $level = 0) {
    $html = '';
    foreach ($categories as $category) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $html .= '<tr>';
        $html .= '<td>' . $indent . htmlspecialchars($category['name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($category['description']) . '</td>';
        $html .= '<td>' . ($category['parent_name'] ?? '-') . '</td>';
        $html .= '<td class="actions">';
        $html .= '<a href="#edit-' . $category['id'] . '">Edit</a> | ';
        $html .= '<a href="?delete=' . $category['id'] . '" onclick="return confirm(\'Delete this category?\')">Delete</a> | ';
        $html .= '<a href="#parent-' . $category['id'] . '">Set Parent</a>';
        $html .= '</td>';
        $html .= '</tr>';
        
        if (!empty($category['children'])) {
            $html .= displayCategories($category['children'], $level + 1);
        }
    }
    return $html;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        form { margin-bottom: 20px; }
        input, textarea, select { padding: 8px; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; vertical-align: top; }
        .success { color: green; }
        .error { color: red; }
        .actions a { margin-right: 5px; }
        .edit-form { background: #f5f5f5; padding: 15px; margin: 10px 0; }
        .edit-form h4 { margin-top: 0; }
    </style>
</head>
<body>

<h2>Manage Categories</h2>

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<h3>Add New Category</h3>
<form method="post">
    <input type="text" name="name" placeholder="Category Name" required style="width: 300px;">
    <textarea name="description" placeholder="Category Description (optional)" style="width: 300px; height: 80px;"></textarea>
    <button type="submit" name="add">Add Category</button>
</form>

<h3>All Categories</h3>
<table>
    <thead>
        <tr><th>Name</th><th>Description</th><th>Parent</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?= displayCategories($categoryTree) ?>
    </tbody>
</table>

<!-- Edit forms for each category (hidden until clicked) -->
<?php foreach ($categories as $cat): ?>
<div id="edit-<?= $cat['id'] ?>" class="edit-form" style="display: none;">
    <h4>Edit Category: <?= htmlspecialchars($cat['name']) ?></h4>
    <form method="post">
        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
        <label>Name: <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required></label><br>
        <label>Description: <textarea name="description"><?= htmlspecialchars($cat['description']) ?></textarea></label><br>
        <button type="submit" name="update_details">Update Details</button>
        <button type="button" onclick="this.parentNode.parentNode.style.display='none'">Cancel</button>
    </form>
</div>

<div id="parent-<?= $cat['id'] ?>" class="edit-form" style="display: none;">
    <h4>Set Parent for: <?= htmlspecialchars($cat['name']) ?></h4>
    <form method="post">
        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
        <select name="parent_id">
            <option value="">-- No Parent (Top-Level) --</option>
            <?php foreach ($categories as $opt): ?>
                <?php if ($opt['id'] !== $cat['id']): ?>
                <option value="<?= $opt['id'] ?>" <?= $opt['id'] == $cat['parent_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['name']) ?>
                </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="update_parent">Update Parent</button>
        <button type="button" onclick="this.parentNode.parentNode.style.display='none'">Cancel</button>
    </form>
</div>
<?php endforeach; ?>

<p><a href="dashboard.php">← Back to Dashboard</a></p>

<script>
// Show edit form when edit link is clicked
document.querySelectorAll('a[href^="#edit-"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.getAttribute('href').substring(1);
        document.getElementById(id).style.display = 'block';
    });
});

// Show parent form when set parent link is clicked
document.querySelectorAll('a[href^="#parent-"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.getAttribute('href').substring(1);
        document.getElementById(id).style.display = 'block';
    });
});
</script>

</body>
</html>