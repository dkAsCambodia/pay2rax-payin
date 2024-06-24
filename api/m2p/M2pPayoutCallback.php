<?php
echo "This is M2p withdrawal Response page created by DK";
$results= json_decode(file_get_contents('php://input'), true);
if(!empty($results)){
    
    // $results = '{
    //     "address":"0640043054",
    //     "tempTransactionId":"",
    //     "cryptoTransactionInfo":[],
    //     "paymentId":"c6242ee0-9eea-11ee-a5be-bbd3984d8e9d",
    //     "status":"DECLINED",
    //     "transactionAmount":0.685753,
    //     "netAmount":0.678895,
    //     "transactionCurrency":"USX",
    //     "processingFee":0.006858,
    //     "finalAmount":5,
    //     "finalCurrency":"CNY",
    //     "conversionRate":7.29125
    // }';
    // $results=json_decode($results, true);
    // echo "<pre>"; print_r($results['status']); die;

    // Decode JSON data
    $payout_aar=json_encode($results, true);
    $transactionId=$results['paymentId'];
    $payout_request_id=$results['paymentId'];
    date_default_timezone_set('Asia/Phnom_Penh');
    $pt_timestamp=date("Y-m-d h:i:sA");
    $orderstatus=$results['status'];
       
    // Code for update Transaction status START
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payouts` SET `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='1', `payout_aar`='$payout_aar' WHERE orderid='$payout_request_id' ";
    mysqli_query($link,$query1);
    // Code for update Transaction status END

     // Send To callback URL Code START
    $query2 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,orderremarks,orderstatus,created_at FROM `gtech_payouts` WHERE orderid='$payout_request_id' ";
    $qrv=mysqli_query($link,$query2);
    $row=mysqli_fetch_assoc($qrv);
    if(!empty($row)){
        // echo "<pre>"; print_r($row); die;
        if($row['orderstatus']=='DONE' ){
            $redirecturl=$row['payout_success_url'];
            $paymentStatus = 'success';
        }elseif($row['orderstatus']=='DECLINED'){
            $redirecturl=$row['payout_notify_url'];
            $paymentStatus = 'failed';
        }elseif($row['orderstatus']=='PENDING' || $row['orderstatus']=='ADMIN CONFIRMATION' || $row['orderstatus']=='NEW'){
            $redirecturl=$row['payout_error_url'];
            $paymentStatus = 'pending';
        }else{
            $redirecturl=$row['payout_success_url'];
            $paymentStatus = 'pending';
        }

        if (!empty($redirecturl)) {
            $info = [
                'settlement_trans_id' => $transactionId,
                'orderstatus' => $row['orderstatus'],
                'payment_email' => $row['customer_email'],
                'transaction_id' => $row['payout_request_id'],
                'payment_amount' => $row['price'],
                'payment_timestamp' => $row['created_at'],
                'payment_status' => $paymentStatus,
                'orderremarks' => $row['orderremarks'],
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
        }else{
            echo "Callback URL not Found or Invalid Request!";
        }
        
    }else{
        echo "No Data Available or Invalid Request!";
    }
     // Send To callback URL Code END
}else{
    echo "No Data Available or Invalid Request!";
}
?>