<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?");
$stmt->execute([$userId]);
$result = $stmt->fetch();

echo json_encode(['count' => $result['count']]);