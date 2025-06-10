<?php
require_once '../config/database.php';

session_start();

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: cart.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user_id'];

// Check if payment was completed
$stmt = $pdo->prepare("
    SELECT o.*, p.status as payment_status 
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

if ($order['payment_status'] === 'paid') {
    // Payment successful - redirect to success page
    header('Location: payment_success.php?order_id='.$orderId);
    exit;
} else {
    // Payment not yet complete - check again in 5 seconds
    header('Refresh: 5; url=check_payment.php?order_id='.$orderId);
    
    // Show waiting message
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Processing Payment</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100 flex items-center justify-center h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <h2 class="text-xl font-semibold mb-2">Processing Your Payment</h2>
            <p class="text-gray-600">Please wait while we confirm your payment...</p>
        </div>
    </body>
    </html>';
    exit;
}