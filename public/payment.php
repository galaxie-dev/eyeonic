<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        header('Location: cart.php');
        exit;
    }

    // Calculate total
    $total = 0;
    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        $total += $product['price'] * $qty;
    }

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, created_at, updated_at) VALUES (?, ?, 'pending', NOW(), NOW())");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $orderId = $pdo->lastInsertId();

    // Insert order items
    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $id, $qty, $product['price']]);
    }

    // Simulate payment processing
    // In production, integrate M-Pesa Daraja API here

    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
    $stmt->execute([$orderId]);

    // Clear cart
    unset($_SESSION['cart']);

    header('Location: order_success.php');
    exit;
}
?>
