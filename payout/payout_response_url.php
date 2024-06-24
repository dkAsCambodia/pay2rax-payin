<?php
//  echo "<pre>"; print_r($_POST); die;
$transaction_id=$_POST['transaction_id'];
$customer_name=$_POST['customer_name'];
$orderremarks=$_POST['orderremarks'];
$amount=$_POST['amount'];
$pt_timestamp=$_POST['created_at'];
$status=$_POST['status'];
$Currency=$_POST['Currency'];

if(!empty($_POST)){
    echo "Transaction Information as follows for Payout".'<br/>'.
    "Temperory TransactionId : ".$transaction_id.'<br/>'.
    "Customer_name : ".$customer_name.'<br/>'.
    "Amount : ".$amount.'<br/>'.
    "Currency : ".$Currency.'<br/>'.
    "Datetime : ".$pt_timestamp.'<br/>'.
    "Status : ".$status.'<br/>';
    "Message : ".$orderremarks;
}else{
    echo "No Data Available or Invalid Request";
}
?>