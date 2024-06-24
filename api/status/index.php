<?php
// echo "This is powerpay88 Response created by DK";
$results=$_POST;
if(!empty($results)){
    // echo "<pre>"; print_r($results); die;
    $payin_aar=json_encode($results);
   
    //  Code for QuickTransfer START
    if(isset($results['bankcode']) && !empty($results['bankcode'])){
        // echo "QuickTransfer";
        $transactionId=$results['transaction_id'];
        $payin_request_id=$results['last_name'];
        date_default_timezone_set('Asia/Phnom_Penh');
        $pt_timestamp=date("Y-m-d h:i:sA");
        $orderstatus=$results['status'];
        if($orderstatus=='A0'){
            $orderstatus='Success';
        }elseif($orderstatus=='A1' || $orderstatus=='A2' || $orderstatus=='A3' || $orderstatus=='A4' || $orderstatus=='A5' || $orderstatus=='A6' || $orderstatus=='TR00'){
            $orderstatus='Pending';
        }else{
            $orderstatus='Failed';
        }
         //  Code for QuickTransfer END
    }else{
        // echo "powerpay88";
        $transactionId=$results['ID'];
        $payin_request_id=$results['Reference'];
        $pt_timestamp=$results['Datetime'];
        $orderstatus=$results['Status'];
        if($orderstatus=='000'){
            $orderstatus='Success';
        }elseif($orderstatus=='001'){
            $orderstatus='Failed';
        }elseif($orderstatus=='006'){
            $orderstatus='Approved';
        }elseif($orderstatus=='007'){
            $orderstatus='Rejected';
        }elseif($orderstatus=='008'){
            $orderstatus='Cancelled';
        }elseif($orderstatus=='009'){
            $orderstatus='Pending';
        }
    }
 
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
        // echo "<pre>"; print_r($row); die;
        if($row['orderstatus']=='Success' || $row['orderstatus']=='Approved' ){
            $redirecturl=$row['payin_success_url'];
        }elseif($row['orderstatus']=='Failed' || $row['orderstatus']=='Rejected'  || $row['orderstatus']=='Cancelled'){
            $redirecturl=$row['payin_notify_url'];
        }elseif($row['orderstatus']=='Pending'){
            $redirecturl=$row['payin_error_url'];
        }else{
            $redirecturl=$row['payin_success_url'];
        }
       
        if(!empty($redirecturl)){
            $callbackURL=$redirecturl.'?pt_transactionId='.$row['orderid'].'&dkstatus='.$results['Status'].'&pt_email='.$row['customer_email'].'&pt_reference='.$row['payin_request_id'].'&pt_amount='.$row['price'].'&pt_timestamp='.$row['orderremarks'].'&pt_status='.$row['orderstatus'];
            // header("Location:$callbackURL"); 
            ?>
            <script>
                window.location.href = '<?php echo $callbackURL; ?>';
            </script>
            <?php
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

