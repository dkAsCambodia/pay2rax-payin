<?php
// print_r($_POST); die;

echo "Transaction Information as follows" . '<br/>' .
    "TransactionId : " . $_POST['transaction_id'] . '<br/>' .
    "Currency : " . $_POST['Currency'] . '<br/>' .
    "Amount : " . $_POST['amount'] . '<br/>' .
    "customer_name : " . $_POST['customer_name'] . '<br/>' .
    "Datetime : " . $_POST['created_at'] . '<br/>' .
    "Status : " . $_POST['payment_status'];
die;
?>