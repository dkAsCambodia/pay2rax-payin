<?php
// echo "This is M2p deposit Response page created by DK";
$results= json_decode(file_get_contents('php://input'), true);
if(!empty($results)){
    
    // $results = '{
    //     "depositAddress":"C9wic7ex7etARjPGQPKBHGLr2cRcCD17aZ",
    //     "cryptoTransactionInfo":
    //       [
    //         {
    //         "txid":"b20feab400c3cd61a9d0daec8526d739a2335fe1900415f24835001e58a837a7",
    //         "confirmations":2,
    //         "amount":0.10000000,
    //         "confirmedTime":"Mar 20, 2019 7:06:38PM",
    //         "status":"DONE",
    //         "processingFee":0.00500000,
    //         "conversionRate":3198.64800
    //         }
    //       ],
    //     "paymentId":"99ca8c34-5191-41d9-a1a2-666b9badf1ce",
    //     "status":"DONE",
    //     "transactionAmount":0.10000000,
    //     "netAmount":0.09500000,
    //     "transactionCurrency":"BTC",
    //     "processingFee":0.00500000,
    //     "finalAmount":303.87,
    //     "finalCurrency":"USD",
    //     "conversionRate":3198.65
    //     }';
    //     $results=json_decode($results, true);
        // echo "<pre>"; print_r($results); die;

    // Decode JSON data
    $payin_aar=json_encode($results, true);
    $transactionId=$results['paymentId'];
    $payin_request_id=$results['paymentId'];
    date_default_timezone_set('Asia/Phnom_Penh');
    $pt_timestamp=date("Y-m-d h:i:sA");
    $orderstatus=$results['status'];
       
    
    // Code for update Transaction status START
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payins` SET `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='1', `payin_aar`='$payin_aar' WHERE orderid='$payin_request_id' ";
    mysqli_query($link,$query1);
    // Code for update Transaction status END

    // Send To callback URL Code START
    $query2 = "SELECT price,customer_email,payin_request_id,payin_notify_url,payin_success_url,payin_error_url,orderid,orderremarks,orderstatus FROM `gtech_payins` WHERE orderid='$payin_request_id' ";

    $qrv = mysqli_query($link, $query2);
    $row = mysqli_fetch_assoc($qrv);
    if (!empty($row)) {

           
        if ($orderstatus == 'DONE' || $orderstatus == 'done' || $orderstatus == 'Done' || $orderstatus == 'success' || $orderstatus == 'SUCCESS') {
            $paymentStatus = 'success';
            $redirecturl = $row['payin_success_url'];
        } elseif ($orderstatus == 'PENDING' || $orderstatus == 'Pending' || $orderstatus == 'pending' || $orderstatus == 'NEW' ) {
            $paymentStatus = 'pending';
            $redirecturl = $row['payin_error_url'];
        } elseif ($orderstatus == 'DECLINED' || $orderstatus == 'Rejected'  || $orderstatus == 'Cancelled' ) {
            $paymentStatus = 'failed';
            $redirecturl = $row['payin_notify_url'];
        } else {
            $redirecturl = $row['payin_success_url'];
        }

        if (!empty($redirecturl)) {
            $info = [
                'payment_transaction_id' => $row['orderid'],
                'orderstatus' => $orderstatus,
                'payment_email' => $row['customer_email'],
                'transaction_id' => $row['payin_request_id'],
                'payment_amount' => $row['price'],
                'payment_timestamp' => $row['orderremarks'],
                'payment_status' => $paymentStatus,
            ];
            $queryString = http_build_query($info, '', '&');
            $callbackURL = $redirecturl . '?' . $queryString;
            
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
             echo "<pre>"; print_r($res); die;
            
        }else{
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