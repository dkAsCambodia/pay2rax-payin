<?php
// echo "This is VIZPAY Response page created by DK";
$results= json_decode(file_get_contents('php://input'), true);
if(!empty($results)){
    // echo "<pre>"; print_r($results); die;
    // $jsonString = '{
    //     "type": "DEPOSIT",
    //     "status": "SUCCESS",
    //     "status_code": "DEPOSIT_AUTO",
    //     "agent_confirm": "WAIT",
    //     "stm_ref_id": "d6c623ab-d62e812c-5597a81f-7bca944a",
    //     "stm_date": "YYYY-MM-DD hh:mm:ss",
    //     "stm_amount": "100.50",
    //     "stm_bank_name": "SCB",
    //     "stm_account_no": "1234567890",
    //     "stm_remark": "TR fr 004-1234567890 โอนเงินเข้าพร้อมเพย์",
    //     "txn_ref_id": "ca3c3757-ffa8-49db-89df-e314cc5ecf60",
    //     "txn_order_id": "xxxxxxxx",
    //     "txn_user_id": "xxxxxxxx",  
    //     "deposit_balance": "100.00",
    //     "withdraw_balance": "0.00",
    //     "remark": "",
    //     "signature": "d95ba17d99c862a44ebb6f1c3039e6b4"
    //  }';
        $payin_aar=json_encode($results);
        $transactionId=$results['txn_ref_id'];
        $payin_request_id=$results['txn_order_id'];
        date_default_timezone_set('Asia/Phnom_Penh');
        $pt_timestamp=date("Y-m-d h:i:sA");
        $orderstatus=$results['status'];
       
    
    // Code for update Transaction status START
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payins` SET `orderid`='$transactionId', `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='1', `payin_aar`='$payin_aar' WHERE payin_request_id='$payin_request_id' ";
    mysqli_query($link,$query1);
    // Code for update Transaction status END

     // Send To callback URL Code START
    $query2 = "SELECT price,customer_email,payin_request_id,payin_notify_url,payin_success_url,payin_error_url,orderid,orderremarks,orderstatus FROM `gtech_payins` WHERE payin_request_id='$payin_request_id' ";
    $qrv=mysqli_query($link,$query2);
    $row=mysqli_fetch_assoc($qrv);
    if(!empty($row)){
        if ($orderstatus == 'Success' || $orderstatus == 'Approved' || $orderstatus == 'success' || $orderstatus == 'SUCCESS') {
            $paymentStatus = 'success';
            $redirecturl = $row['payin_success_url'];
        } elseif ($orderstatus == 'Failed' || $orderstatus == 'Rejected' || $orderstatus == 'Cancelled' || $orderstatus == 'FAILED') {
            $paymentStatus = 'failed';
            $redirecturl = $row['payin_notify_url'];
        } elseif ($orderstatus == 'Pending') {
            $paymentStatus = 'pending';
            $redirecturl = $row['payin_error_url'];
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