<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your wishlist']);
    exit;
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = (int)$_POST['product_id'];

try {
    // Check if product exists
    $productCheck = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $productCheck->execute([$productId]);
    if (!$productCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Check if already in wishlist
    $checkStmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$userId, $productId]);
    
    if ($checkStmt->fetch()) {
        // Remove from wishlist
        $deleteStmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $deleteStmt->execute([$userId, $productId]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to wishlist
        $insertStmt = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $insertStmt->execute([$userId, $productId]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (PDOException $e) {
    error_log("Wishlist Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
?>