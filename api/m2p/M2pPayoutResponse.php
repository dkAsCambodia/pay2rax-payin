<?php
// echo "This is M2p Response created by DK";
$results=$_POST;
$payout_request_id=$results['payout_request_id'];
unset($results['payout_request_id']);
// echo "<pre>"; print_r($results);

$apiUrl = 'https://m2p.match-trade.com/api/v2/withdraw/crypto_agent';
$jsonData = json_encode($results);
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
));
$res = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}
curl_close($ch);
$response=json_decode($res);
// echo "<pre>"; print_r($res); 
if(!empty($response->paymentId)){
    if(!empty($response->errorList[0])){
        @$orderremarks=$response->errorList[0];
        $orderstatus=$response->status;
    }else{
        $orderremarks='DONE';
        $orderstatus='Success';
    }
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payouts` SET `orderid`='$response->paymentId', `orderremarks`='$orderremarks', `orderstatus`='$orderstatus', `status`='1', `payout_aar`='$res' WHERE payout_request_id='$payout_request_id' ";
    mysqli_query($link,$query1);

     // Send To callback URL Code START
     include("../../connection.php");
    $query2 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,created_at,orderstatus FROM `gtech_payouts` WHERE payout_request_id='$payout_request_id' ";
    $qrv=mysqli_query($link,$query2);
    $row=mysqli_fetch_assoc($qrv);
    if(!empty($row)){
        if($row['orderstatus'] == 'Success' || $row['orderstatus']=='DONE' ) {
            $paymentStatus = 'success';
            $redirecturl = $row['payout_success_url'];
        }elseif($row['orderstatus'] == 'failed' || $row['orderstatus'] == 'DECLINED') {
            $paymentStatus = 'failed';
            $redirecturl = $row['payout_notify_url'];
        }elseif($row['orderstatus']=='PENDING' || $row['orderstatus']=='ADMIN CONFIRMATION' || $row['orderstatus']=='NEW') {
            $paymentStatus = 'pending';
            $redirecturl = $row['payout_error_url'];
        }else{
            $redirecturl = $row['payout_success_url'];
            $paymentStatus = 'pending';
        }

        if (!empty($redirecturl)) {
            $info = [
                'settlement_trans_id' => $response->paymentId,
                'orderstatus' => $row['orderstatus'],
                'payment_email' => $row['customer_email'],
                'transaction_id' => $row['payout_request_id'],
                'payment_amount' => $row['price'],
                'payment_timestamp' => $row['created_at'],
                'payment_status' => $paymentStatus,
                'orderremarks' => $orderremarks,
            ];
            $queryString = http_build_query($info, '', '&');
            $callbackURL = $redirecturl . '?' . $queryString;
        }else{
            echo "Callback URL not Found or Invalid Request!";
        }
      
    }else{
        echo "Row not Found!"; die;
    }
   ?>
   <script>
       window.location.href = '<?php echo $callbackURL; ?>';
   </script>
   <?php
}else{
    echo "API not working!"; die;
}
?>