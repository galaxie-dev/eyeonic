<?php
require_once '../config/database.php';
session_start();
require_once 'autoload.php';



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




\Stripe\Stripe::setApiKey(''); // Replace with your Stripe Secret Key

if (!isset($_POST['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: cart.php');
    exit;
}

$orderId = (int)$_POST['order_id'];
$paymentMethod = $_POST['payment_method'];

// Verify order belongs to the user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    if ($paymentMethod === 'card') {
        // Handle Stripe payment
        if (!isset($_POST['payment_method_id'])) {
            throw new Exception('Payment method ID missing');
        }

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $order['total'] * 100, // Amount in cents
            'currency' => 'kes', // Kenyan Shilling
            'payment_method' => $_POST['payment_method_id'],
            'confirmation_method' => 'manual',
            'confirm' => true,
            'return_url' => 'http://your-domain.com/payment_success.php', // Replace with your success URL
        ]);

        if ($paymentIntent->status === 'succeeded') {
            // Update order status in the database
            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', payment_method = ? WHERE id = ?");
            $stmt->execute(['card', $orderId]);
            $response = ['success' => true];
        } else {
            $response['message'] = 'Payment failed. Please try again.';
        }
    } elseif ($paymentMethod === 'mpesa') {
        // Handle M-Pesa payment (implement your M-Pesa logic here)
        $stmt = $pdo->prepare("UPDATE orders SET status = 'pending', payment_method = ? WHERE id = ?");
        $stmt->execute(['mpesa', $orderId]);
        $response = ['success' => true];
    } elseif ($paymentMethod === 'cash_on_delivery') {
        // Handle Payment on Delivery
        $stmt = $pdo->prepare("UPDATE orders SET status = 'pending', payment_method = ? WHERE id = ?");
        $stmt->execute(['cash_on_delivery', $orderId]);
        $response = ['success' => true];
    } else {
        $response['message'] = 'Invalid payment method';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;