<?php
session_start();
require_once '../config/database.php';

$response = ['count' => 0];

if(isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Count pending orders (adjust the status condition as needed)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders 
                          WHERE user_id = ? AND order_status = 'pending'");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    $response['count'] = $result['count'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>