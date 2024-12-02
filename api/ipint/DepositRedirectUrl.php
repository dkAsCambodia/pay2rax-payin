<?php
// echo "This is Ipint Response created by DK";
session_start();
if (!empty($_SESSION['payin_request_id'])) {
    $payin_request_id=$_SESSION['payin_request_id'];
}else{
    echo "Session not found!";
}
$invoice_id = $_GET['invoice_id'];
//Generate Signature START
$nonce = time() * 1000;
$apiPath = '/invoice?id='.$invoice_id;
$apiSecret='2TLcHzh13meEXwX1eruGVCiKoNVF4bRT72QhXc5d1hyq5EdcwPzsbNCgPquyZ6JZo';
$sig = '/api/'.$nonce.$apiPath;
$signature = hash_hmac('sha384', $sig, $apiSecret, false);
//Generate Signature END

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.ipint.io:8003/invoice?id='.$invoice_id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'content-type: application/json',
    'apikey: 2F4yX41QTva26mi5p5SsaqeLo4idFrye4HpqDcFNtuL4irD29uxiA39M1gsC3wFwU',
    'signature: '.$signature,
    'nonce: '.$nonce
  ),
));
$response = curl_exec($curl);
curl_close($curl);
$result= json_decode($response, true);
//  echo "<pre>"; print_r($result); die;
$Transactionid=$result['data']['invoice_id'];
$orderstatus=$result['data']['transaction_status'];
$cryptoCurrency=$result['data']['transaction_crypto'];
if(!empty($result['data']['received_crypto_amount'])){
    $receivedCryptoAmount=$result['data']['received_crypto_amount'];
}else{
    $receivedCryptoAmount=$result['data']['invoice_crypto_amount'];
}
date_default_timezone_set('Asia/Phnom_Penh');
$orderremarks=date("Y-m-d h:i:sA");

 // Code for update Transaction status START
 if(!empty($Transactionid)){
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payins` SET `orderid`='$Transactionid', `price`='$receivedCryptoAmount', `curr`='$cryptoCurrency', `orderremarks`='$orderremarks', `payin_aar`='$response', `orderstatus`='$orderstatus', `status`='1' WHERE payin_request_id='$payin_request_id' ";
    mysqli_query($link,$query1);

        // Send To callback URL Code START
            $query2 = "SELECT price,curr,customer_email,payin_request_id,payin_notify_url,
            payin_success_url,payin_error_url,orderid,orderremarks,orderstatus 
            FROM `gtech_payins` WHERE payin_request_id='$payin_request_id' ";

            $qrv = mysqli_query($link, $query2);
            $row = mysqli_fetch_assoc($qrv);
            if (!empty($row)) {
            if ($orderstatus == 'Successful' || $orderstatus == 'Success' || $orderstatus == 'COMPLETED' || $orderstatus == 'success') {
            $paymentStatus = 'success';
            $redirecturl = $row['payin_success_url'];
            } elseif (
            $orderstatus == 'FAILED' ||
            $orderstatus == 'Rejected'  ||
            $orderstatus == 'CANCELLED'
            ) {
            $paymentStatus = 'failed';
            $redirecturl = $row['payin_notify_url'];
            } elseif ($orderstatus == 'Pending' || $orderstatus == 'PENDING') {
            $paymentStatus = 'pending';
            $redirecturl = $row['payin_error_url'];
            } else {
            $paymentStatus = 'processing';
            $redirecturl = $row['payin_success_url'];
            }

            if (!empty($redirecturl)) {
            $info = [
                'payment_transaction_id' => $row['orderid'],
                'orderstatus' => $orderstatus,
                'payment_email' => $row['customer_email'],
                'transaction_id' => $row['payin_request_id'],
                'payment_amount' => $row['price'],
                'currency' => $row['curr'],
                'payment_timestamp' => $row['orderremarks'],
                'payment_status' => $paymentStatus,
            ];
            // echo "<pre>"; print_r($info); die;
            $queryString = http_build_query($info, '', '&');
            $callbackURL = $redirecturl . '?' . $queryString;
            ?>
            <script>
                window.location.href = '<?php echo $callbackURL; ?>';
            </script>
            <?php
            } else {
            echo "Callback URL not Found or Invalid Request!";
            }
            } else {
            echo "No Data Available or Invalid Request!";
            }
            // Send To callback URL Code END

 }
 // Code for update Transaction status END
?>