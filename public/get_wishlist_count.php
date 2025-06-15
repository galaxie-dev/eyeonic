<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    echo json_encode(['count' => (int)$result['count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>