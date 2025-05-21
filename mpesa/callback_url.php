<?php
$data = file_get_contents("php://input");
$log = fopen("mpesa_response.json", "a");
fwrite($log, $data);
fclose($log);

$mpesaResponse = json_decode($data, true);

$resultCode = $mpesaResponse['Body']['stkCallback']['ResultCode'];
$resultDesc = $mpesaResponse['Body']['stkCallback']['ResultDesc'];
$metadata = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];

if ($resultCode == 0) {
    $amount = 0;
    $mpesa_code = "";
    $phone = "";

    foreach ($metadata as $item) {
        if ($item['Name'] == "Amount") $amount = $item['Value'];
        if ($item['Name'] == "MpesaReceiptNumber") $mpesa_code = $item['Value'];
        if ($item['Name'] == "PhoneNumber") $phone = $item['Value'];
    }

    // You can now insert into payments table
    // Link to order using session or reference
}
