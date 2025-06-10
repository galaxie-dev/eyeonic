<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to manage wishlist']);
        exit;
    }

    $productId = (int)$_POST['product_id'];
    $userId = $_SESSION['user_id'];

    // Check if item is already in wishlist
    $checkStmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$userId, $productId]);
    $exists = $checkStmt->fetch();

    if ($exists) {
        // Remove from wishlist
        $deleteStmt = $pdo->prepare("DELETE FROM wishlists WHERE id = ?");
        $deleteStmt->execute([$exists['id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to wishlist
        $insertStmt = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $insertStmt->execute([$userId, $productId]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
}