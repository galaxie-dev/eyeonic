<?php
require_once '../config/database.php';

$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Log the raw callback
file_put_contents('mpesa_callback.log', $response . PHP_EOL, FILE_APPEND);

if (isset($data['Body']['stkCallback'])) {
    $callback = $data['Body']['stkCallback'];
    $checkoutRequestID = $callback['CheckoutRequestID'];
    $resultCode = $callback['ResultCode'];
    
    // Get the transaction from database
    $stmt = $pdo->prepare("SELECT * FROM mpesa_transactions WHERE checkout_request_id = ?");
    $stmt->execute([$checkoutRequestID]);
    $transaction = $stmt->fetch();
    
    if ($transaction) {
        // Update transaction status
        $status = ($resultCode == 0) ? 'completed' : 'failed';
        
        $updateStmt = $pdo->prepare("
            UPDATE mpesa_transactions 
            SET callback_data = ?, result_code = ?, result_desc = ?, status = ?
            WHERE checkout_request_id = ?
        ");
        $updateStmt->execute([
            $response,
            $resultCode,
            $callback['ResultDesc'] ?? '',
            $status,
            $checkoutRequestID
        ]);
        
        // If payment was successful, update order status
        if ($resultCode == 0) {
            // Get M-Pesa transaction details
            $item = $callback['CallbackMetadata']['Item'] ?? [];
            $mpesaReceiptNumber = '';
            $amountPaid = 0;
            
            foreach ($item as $detail) {
                if ($detail['Name'] == 'MpesaReceiptNumber') {
                    $mpesaReceiptNumber = $detail['Value'];
                }
                if ($detail['Name'] == 'Amount') {
                    $amountPaid = $detail['Value'];
                }
            }
            
            // Update order and payment status
            $pdo->beginTransaction();
            
            try {
                // Update order
                $pdo->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid', order_status = 'approved'
                    WHERE id = ?
                ")->execute([$transaction['order_id']]);
                
                // Record payment
                $pdo->prepare("
                    INSERT INTO payments (
                        order_id, payment_method, payment_reference, 
                        amount, status
                    ) VALUES (?, 'mpesa', ?, ?, 'success')
                ")->execute([
                    $transaction['order_id'],
                    $mpesaReceiptNumber,
                    $amountPaid
                ]);
                
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                file_put_contents('mpesa_error.log', "DB Update Failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        }
    }
}

// Always return success response to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);