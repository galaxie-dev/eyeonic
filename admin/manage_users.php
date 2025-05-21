<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$stmt = $pdo->query("SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<h2>Users</h2>
<table border="1">
    <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Registered</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['full_name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= $user['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="dashboard.php">← Back to Dashboard</a>
