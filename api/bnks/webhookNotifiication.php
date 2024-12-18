<?php
// {
//     "paymentId": "673599717cc2c752f95f5e4b",
//     "paymentRaw": {
//       "id": "673599717cc2c752f95f5e4b",
//       "keyUsed": "ck_test_3b680ac1-4d0a-4fc0-a560-37c3ccecd616",
//       "amount": 200,
//       "currency": "USD",
//       "successCallback": "http://localhost/pay2rax-payin/api/bnks/depositSuccess.php",
//       "failureCallback": "http://localhost/pay2rax-payin/api/bnks/depositFail.php",
//       "status": "success",        //awaiting/failed
//       "environment": "test",
//       "createdAt": "2024-11-14T06:32:17.850Z"
//     }
//   }
$rawData = file_get_contents("php://input");
$cleanData = str_replace("\n", "", $rawData);
$results = json_decode($cleanData, true);
if(!empty($results)){
    // Re-encode JSON data to a clean JSON string
    
    $transaction_id = $results['paymentId'];
    date_default_timezone_set('Asia/Phnom_Penh');
    $pt_timestamp = date("Y-m-d h:i:sA");

    if ($results['paymentRaw']['status'] === 'success') {
        $orderstatus = 'Success';
    } elseif ($results['paymentRaw']['status'] === 'awaiting' || $results['paymentRaw']['status'] === 'pending') {
        $orderstatus = 'processing';
    } else {
        $orderstatus = 'failed';
    }
    sleep(20);
    include("../../connection.php");
    $Query = "UPDATE `gtech_payins` SET `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='webhook1 Notification', `payin_all`='$cleanData' WHERE `orderid`='$transaction_id'";
    mysqli_query($link, $Query);
    
    if (mysqli_query($link, $Query)) {
        echo "Transaction updated successfully!";
    } else {
        echo "Error updating record: " . mysqli_error($link);
    }

        // Send To callback URL Code START
        include("../../connection.php");
        $query2 = "SELECT price,customer_email,payin_request_id,payin_notify_url,payin_success_url,payin_error_url,orderid,orderremarks,orderstatus FROM `gtech_payins` WHERE orderid='$transaction_id'";
        $qrv = mysqli_query($link, $query2);
        $row = mysqli_fetch_assoc($qrv);
        // echo "qrvData =>"; print_r($qrv);
        if (!empty($qrv)) {
                
                $paymentStatus = $row['orderstatus'];
                $redirecturl = $row['payin_success_url'];
                if(!empty($row['orderid'])){
                    $payment_transaction_id = $row['orderid'];
                }else{
                    $payment_transaction_id = $row['payin_request_id'];
                }

            if (!empty($redirecturl)) {
                $info = [
                    'payment_transaction_id' => $payment_transaction_id,
                    'orderstatus' => 'webhook1',
                    'payment_email' => $row['customer_email'],
                    'transaction_id' => $row['payin_request_id'],
                    'payment_amount' => $row['price'],
                    'payment_timestamp' => $row['orderremarks'],
                    'payment_status' => $paymentStatus,
                ];
                $queryString = http_build_query($info, '', '&');
                $callbackURL = $redirecturl . '?' . $queryString;
                // for Webhook Callback code START
                $ch = curl_init($callbackURL);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_POSTFIELDS, '');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    // 'Content-Type: application/json',
                    // 'Content-Length: ' . strlen($jsonData)
                ));
                $res = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                }
                curl_close($ch);
                echo "<pre>"; print_r($res);
                // for Webhook Callback code END
                
            } else {
                echo $transaction_id." Callback URL not Found or Invalid Request!";
            }
        } else {
            echo $transaction_id." Select query not working or Invalid Request!";
        }
        // Send To callback URL Code END
       

}else{
    echo "Data Not Found or Invalid Request!";
}
?>