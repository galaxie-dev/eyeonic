<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<h2>Categories</h2>
<ul>
    <?php foreach ($categories as $cat): ?>
    <li><?= htmlspecialchars($cat['name']) ?></li>
    <?php endforeach; ?>
</ul>
<a href="dashboard.php">← Back to Dashboard</a>
