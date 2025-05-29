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
        $html .= '<a href="#edit-' . $category['id'] . '" class="action-link">Edit</a> | ';
        $html .= '<a href="?delete=' . $category['id'] . '" class="action-link delete" onclick="return confirm(\'Delete this category?\')">Delete</a> | ';
        $html .= '<a href="#parent-' . $category['id'] . '" class="action-link">Set Parent</a>';
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
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
        
        h2, h3 {
            color: var(--primary-dark);
        }
        
        .message {
            padding: 12px 15px;
            margin: 0 0 20px 0;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .error {
            background-color: rgba(244, 63, 94, 0.1);
            color: var(--accent);
            border-left: 4px solid var(--accent);
        }
        
        .add-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .add-form input,
        .add-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .add-form textarea {
            min-height: 80px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
        }
        
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .categories-table th,
        .categories-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .categories-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .action-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
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
        
        .edit-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 15px 0;
            display: none;
        }
        
        .edit-form h4 {
            margin-top: 0;
            color: var(--primary-dark);
        }
        
        .edit-form input,
        .edit-form textarea,
        .edit-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .edit-form textarea {
            min-height: 80px;
        }
        
        .cancel-btn {
            background-color: #6b7280;
            margin-left: 10px;
        }
        
        .cancel-btn:hover {
            background-color: #4b5563;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Categories</h2>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="add-form">
            <h3>Add New Category</h3>
            <form method="post">
                <input type="text" name="name" placeholder="Category Name" required>
                <textarea name="description" placeholder="Category Description (optional)"></textarea>
                <button type="submit" name="add" class="btn">Add Category</button>
            </form>
        </div>
        
        <h3>All Categories</h3>
        <table class="categories-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Parent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?= displayCategories($categoryTree) ?>
            </tbody>
        </table>
        
        <!-- Edit forms for each category (hidden until clicked) -->
        <?php foreach ($categories as $cat): ?>
        <div id="edit-<?= $cat['id'] ?>" class="edit-form">
            <h4>Edit Category: <?= htmlspecialchars($cat['name']) ?></h4>
            <form method="post">
                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                <label>Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required>
                
                <label>Description:</label>
                <textarea name="description"><?= htmlspecialchars($cat['description']) ?></textarea>
                
                <button type="submit" name="update_details" class="btn">Update Details</button>
                <button type="button" class="btn cancel-btn" onclick="this.parentNode.parentNode.style.display='none'">Cancel</button>
            </form>
        </div>
        
        <div id="parent-<?= $cat['id'] ?>" class="edit-form">
            <h4>Set Parent for: <?= htmlspecialchars($cat['name']) ?></h4>
            <form method="post">
                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                
                <label>Parent Category:</label>
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
                
                <button type="submit" name="update_parent" class="btn">Update Parent</button>
                <button type="button" class="btn cancel-btn" onclick="this.parentNode.parentNode.style.display='none'">Cancel</button>
            </form>
        </div>
        <?php endforeach; ?>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <script>
        // Show edit form when edit link is clicked
        document.querySelectorAll('a[href^="#edit-"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('href').substring(1);
                document.getElementById(id).style.display = 'block';
                document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Show parent form when set parent link is clicked
        document.querySelectorAll('a[href^="#parent-"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('href').substring(1);
                document.getElementById(id).style.display = 'block';
                document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>