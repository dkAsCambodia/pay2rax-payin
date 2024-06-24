<?php
include("../../connection.php");
$results = $_POST;
if (!empty($results)) {
    // echo "<pre>"; print_r($results); die;
    $payin_aar = json_encode($results);

    // echo "QuickTransfer";
    $transactionId = $results['transaction_id'];
    $payin_request_id = $results['last_name'];
    date_default_timezone_set('Asia/Phnom_Penh');
    $pt_timestamp = date("Y-m-d h:i:sA");
    $orderstatus = $results['status'];
    if ($orderstatus == 'A0') {
        $orderstatus = 'Success';
        $paymentStatus = 'success';
    } elseif (
        $orderstatus == 'A1' ||
        $orderstatus == 'A2' ||
        $orderstatus == 'A3' ||
        $orderstatus == 'A4' ||
        $orderstatus == 'A5' ||
        $orderstatus == 'A6' ||
        $orderstatus == 'TR00'
    ) {
        $orderstatus = 'Pending';
        $paymentStatus = 'pending';
    } else {
        $orderstatus = 'Failed';
        $paymentStatus = 'failed';
    }

    // Code for update Transaction status START
    $query1 = "UPDATE `gtech_payins` SET `orderid`='$transactionId',
                 `orderremarks`='$pt_timestamp', `orderstatus`='$orderstatus',
                 `status`='1', `payin_aar`='$payin_aar' WHERE payin_request_id='$payin_request_id' ";
    mysqli_query($link, $query1);
    // Code for update Transaction status END

    // Send To callback URL Code START
    $query2 = "SELECT price,customer_email,payin_request_id,payin_notify_url,
                payin_success_url,payin_error_url,orderid,orderremarks,orderstatus 
                FROM `gtech_payins` WHERE payin_request_id='$payin_request_id' ";

    $qrv = mysqli_query($link, $query2);
    $row = mysqli_fetch_assoc($qrv);
    if (!empty($row)) {
        if ($orderstatus == 'Success' || $orderstatus == 'Approved') {
            $paymentStatus = 'success';
            $redirecturl = $row['payin_success_url'];
        } elseif (
            $orderstatus == 'Failed' ||
            $orderstatus == 'Rejected' ||
            $orderstatus == 'Cancelled'
        ) {
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

} else {
    echo "No Data Available or Invalid Request!";
}
?>