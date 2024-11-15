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
$input = file_get_contents('php://input');
$results = json_decode($input, true);
if(!empty($results)){
    // Decode JSON data
    $payin_all=json_encode($results, true);
    $transaction_id=$results['paymentId'];
    date_default_timezone_set('Asia/Phnom_Penh');
    $pt_timestamp=date("Y-m-d h:i:sA");
   
        if($results['paymentRaw']['status']=='success'){
            $orderstatus = 'Success';
        }elseif($results['paymentRaw']['status']=='awaiting' || $results['paymentRaw']['status']=='pending'){
            $orderstatus = 'processing';
        }else{
            $orderstatus = 'failed';
        }
  
        // Code for update Deposit Transaction status START
        include("../../connection.php");
        $query1 = "UPDATE `gtech_payins` SET `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='1', `payin_all`='$payin_all' WHERE orderid='$transaction_id' ";
        mysqli_query($link,$query1);
        // Code for update Deposit Transaction status END
        echo "Transaction updated Successfully!";

        // Send To callback URL Code START
        $query2 = "SELECT price,customer_email,payin_request_id,payin_notify_url,
        payin_success_url,payin_error_url,orderid,orderremarks,orderstatus 
        FROM `gtech_payins` WHERE orderid='$transaction_id' ";

        $qrv = mysqli_query($link, $query2);
        $row = mysqli_fetch_assoc($qrv);
        if (!empty($row)) {
                
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
                    'orderstatus' => $orderstatus,
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
                echo "Callback URL not Found or Invalid Request!";
            }
        } else {
            echo "No Data Available or Invalid Request!";
        }
        // Send To callback URL Code END
       

}else{
    echo "No Data Available or Invalid Request!";
}
?>