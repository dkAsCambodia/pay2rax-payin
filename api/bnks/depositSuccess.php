<?php
// echo "This is BankSy Response created by DK";
session_start();
if (!empty($_SESSION['payin_request_id'])) {
    $payin_request_id=$_SESSION['payin_request_id'];
}else{
    echo "Session not found!";
}
$Transactionid=$_GET['paymentId'];
$orderstatus='success';
date_default_timezone_set('Asia/Phnom_Penh');
$orderremarks=date("Y-m-d h:i:sA");

 // Code for update Transaction status START
 if(!empty($Transactionid)){
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payins` SET `orderid`='$Transactionid', `orderremarks`='$orderremarks', `orderstatus`='$orderstatus', `status`='1' WHERE payin_request_id='$payin_request_id' ";
    mysqli_query($link,$query1);

        // Send To callback URL Code START
            $query2 = "SELECT price,customer_email,payin_request_id,payin_notify_url,
            payin_success_url,payin_error_url,orderid,orderremarks,orderstatus 
            FROM `gtech_payins` WHERE payin_request_id='$payin_request_id' ";

            $qrv = mysqli_query($link, $query2);
            $row = mysqli_fetch_assoc($qrv);
            if (!empty($row)) {
            if ($orderstatus == 'Successful' || $orderstatus == 'Success' || $orderstatus == 'Approved' || $orderstatus == 'success') {
            $paymentStatus = 'success';
            $redirecturl = $row['payin_success_url'];
            } elseif (
            $orderstatus == 'Failed' ||
            $orderstatus == 'Rejected'  ||
            $orderstatus == 'Cancelled'
            ) {
            $paymentStatus = 'failed';
            $redirecturl = $row['payin_notify_url'];
            } elseif ($orderstatus == 'Pending' || $orderstatus == 'pending') {
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
                'payment_timestamp' => $row['orderremarks'],
                'payment_status' => $paymentStatus,
            ];
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