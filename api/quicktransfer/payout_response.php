<?php
echo "This is QT Response page created by DK";
$xml_data = file_get_contents("php://input");
if(!empty($xml_data)){

        $xml = json_encode($xml_data, true);
        // $xml = 'MerchantCode=G0313&TransactionID=GZTRN1702114174c6n&CurrencyCode=THB&Amount=180.00&TransactionDatetime=12/9/2023 9:30:56 AM&Key=C897325C5D19784AF366D6F1820A041C&Status=000&MemberCode=GZ-1081702114253fo1&ID=6812511&PayoutFee=20.00&Message=';
        // Parse the XML string
        parse_str($xml, $results);
        // echo "<pre>"; print_r($results['Status']);
        $payout_aar=json_encode($results, true);
        $transactionId=$results['transactionid'];
        $payout_request_id=$results['customername'];
        date_default_timezone_set('Asia/Phnom_Penh');
        $pt_timestamp=date("Y-m-d h:i:sA");
        $orderstatus=$results['status'];
        if ($orderstatus == 'A0') {
            $orderstatus = 'Success';
        } elseif ( $orderstatus == 'A1' || $orderstatus == 'A2' || $orderstatus == 'A3' || $orderstatus == 'A4' || $orderstatus == 'A5' || $orderstatus == 'A6' || $orderstatus == 'TR00' ) {
            $orderstatus = 'Pending';
        } else {
            $orderstatus = 'Failed';
        }
       
    // Code for update Transaction status START
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payouts` SET `orderid`='$transactionId', `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus', `status`='1', `payout_aar`='$payout_aar' WHERE payout_request_id='$payout_request_id' ";
    mysqli_query($link,$query1);
    // Code for update Transaction status END

     // Send To callback URL Code START
    $query2 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,orderremarks,orderstatus,created_at FROM `gtech_payouts` WHERE payout_request_id='$payout_request_id' ";
    $qrv=mysqli_query($link,$query2);
    $row=mysqli_fetch_assoc($qrv);
    if(!empty($row)){
        if($row['orderstatus'] == 'Success' || $row['orderstatus'] == 'Approved' || $row['orderstatus'] == 'success' || $row['orderstatus'] == 'SUCCESS') {
        $paymentStatus = 'success';
        $redirecturl = $row['payout_success_url'];
        }elseif($row['orderstatus'] == 'Failed' || $row['orderstatus'] == 'Rejected' || $row['orderstatus'] == 'Cancelled' || $row['orderstatus'] == 'failed' || $row['orderstatus'] == 'FAILED') {
        $paymentStatus = 'failed';
        $redirecturl = $row['payout_notify_url'];
        }elseif($row['orderstatus'] == 'Pending' || $row['orderstatus'] == 'pending' || $row['orderstatus'] == 'PENDING') {
        $paymentStatus = 'pending';
        $redirecturl = $row['payout_error_url'];
        }else{
        $redirecturl = $row['payout_success_url'];
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
    } ?>
    <script>
    window.location.href = '<?php echo $callbackURL; ?>';
    </script>
    <?php

     // Send To callback URL Code END
    
}else{
    echo "No Data Available or Invalid Request!";
}
?>