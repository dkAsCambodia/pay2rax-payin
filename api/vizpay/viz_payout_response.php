<?php
echo "This is Payout Response page created by DK";
$results= json_decode(file_get_contents('php://input'), true);
if(!empty($results)){

    // $results = '{
    //     "type": "WITHDRAW",
    //     "status": "SUCCESS",
    //     "status_code": "OK",
    //     "stm_ref_id": "",
    //     "stm_date": "2023-10-27 14:00:00",
    //     "stm_amount": "1000.00",
    //     "stm_bank_name": "",
    //     "stm_bank_code": "",
    //     "stm_last_4account": "",
    //     "stm_remark": "",
    //     "txn_ref_id": "xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx",
    //     "txn_order_id": "xxxxxxxx",
    //     "txn_user_id": "",
    //     "timestamp": "2023-10-27 14:02:46",
    //     "account_no": "3482511463",
    //     "account_bank_name": "SCB",
    //     "agent_confirm": "",
    //     "stm_account_no": "",
    //     "deposit_balance": "1.00",
    //     "withdraw_balance": "1.00",
    //     "remark": "",
    //     "signature": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    // }';

    // Decode JSON data
        $payout_aar=json_encode($results, true);
        $transactionId=$results['txn_ref_id'];
        $payout_request_id=$results['txn_order_id'];
        date_default_timezone_set('Asia/Phnom_Penh');
        $pt_timestamp=date("Y-m-d h:i:sA");
        $orderstatus=$results['status'];
       
    
    // Code for update Transaction status START
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payouts` SET `orderid`='$transactionId', `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='1', `payout_aar`='$payout_aar' WHERE payout_request_id='$payout_request_id' ";
    mysqli_query($link,$query1);
    // Code for update Transaction status END

     // Send To callback URL Code START
    $query2 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,orderremarks,orderstatus FROM `gtech_payouts` WHERE payout_request_id='$payout_request_id' ";
    $qrv=mysqli_query($link,$query2);
    $row=mysqli_fetch_assoc($qrv);
    if(!empty($row)){
        // echo "<pre>"; print_r($row); die;
        if($row['orderstatus']=='Success' || $row['orderstatus'] == 'Approved' || $row['orderstatus'] == 'success' || $row['orderstatus'] == 'SUCCESS' ){
            $redirecturl=$row['payout_success_url'];
            $paymentStatus = 'success';
        }elseif($row['orderstatus']=='Failed' || $row['orderstatus']=='failed' || $row['orderstatus']=='FAILED'  || $row['orderstatus']=='Rejected'  || $row['orderstatus']=='Cancelled'){
            $redirecturl=$row['payout_notify_url'];
            $paymentStatus = 'failed';
        }elseif($row['orderstatus']=='Pending' || $row['orderstatus']=='pending' || $row['orderstatus']=='PENDING'){
            $redirecturl=$row['payout_error_url'];
            $paymentStatus = 'pending';
        }else{
            $redirecturl=$row['payout_success_url'];
        }
       
        if (!empty($redirecturl)) {
            $info = [
                'settlement_trans_id' => $transactionId,
                'orderstatus' => $row['orderstatus'],
                'payment_email' => $row['customer_email'],
                'transaction_id' => $row['payout_request_id'],
                'payment_amount' => $row['price'],
                'payment_timestamp' => $row['orderremarks'],
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