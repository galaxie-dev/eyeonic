<?php
require_once '../config/database.php';
require_once '../mpesa-php-sdk/src/Mpesa.php';
require_once '../vendor/autoload.php';

session_start();

if (!isset($_POST['order_id']) || !isset($_POST['mpesa_phone_number']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$orderId = (int)$_POST['order_id'];
$phone = $_POST['mpesa_phone_number'];
$userId = $_SESSION['user_id'];

// Verify order belongs to user
$stmt = $pdo->prepare("SELECT total FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    die(json_encode(['success' => false, 'message' => 'Order not found']));
}

// Initialize M-Pesa
$mpesa = new Safaricom\Mpesa\Mpesa();

try {
    // Initiate STK push
    $response = $mpesa->STKPushSimulation(
        $businessShortCode = '174379',
        $amount = $order['total'],
        $phoneNumber = $phone,
        $callbackUrl = 'https://yourdomain.com/mpesa_callback.php',
        $accountReference = 'Order_' . $orderId,
        $transactionDesc = 'Eyeonic Order Payment'
    );

    // Save payment attempt to database
    $stmt = $pdo->prepare("
        INSERT INTO payments (order_id, amount, payment_method, status, transaction_id, created_at)
        VALUES (?, ?, 'mpesa', 'pending', ?, NOW())
    ");
    $stmt->execute([$orderId, $order['total'], $response->CheckoutRequestID]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}