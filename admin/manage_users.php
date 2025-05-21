<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$stmt = $pdo->query("SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();


$search = $_GET['q'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();

?>

<h2>Users</h2>
<form method="get">
    <input type="text" name="q" placeholder="Search users..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Search</button>
</form>

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
