<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireAdminLogin();

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header('Location: manage_products.php');
exit;
