<?php
require_once '../config/database.php';

session_start();

if (!isset($_POST['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: cart.php');
    exit;
}

$orderId = (int)$_POST['order_id'];
$userId = $_SESSION['user_id'];

// Verify order belongs to user
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

// Update order status
$stmt = $pdo->prepare("UPDATE orders SET order_status = 'pending_payment' WHERE id = ?");
$stmt->execute([$orderId]);

// Record payment method
$stmt = $pdo->prepare("
    INSERT INTO payments (order_id, amount, payment_method, status, created_at)
    VALUES (?, (SELECT total FROM orders WHERE id = ?), 'cash_on_delivery', 'pending', NOW())
");
$stmt->execute([$orderId, $orderId]);

// Redirect to success page
header('Location: order_success.php?order_id=' . $orderId);
exit;