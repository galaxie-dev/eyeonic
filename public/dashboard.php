<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(); 
?>

<h1>Welcome to your dashboard, <?= htmlspecialchars($user['name']) ?>!</h1>
<p><strong>User ID:</strong> <?= $_SESSION['user_id'] ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
<p><a href="logout.php">Logout</a></p>
