<?php
// {
//     "paymentId": "673599717cc2c752f95f5e4b",
//     "paymentRaw": {
//       "id": "673599717cc2c752f95f5e4b",
//       "keyUsed": "ck_test_3b680ac1-4d0a-4fc0-a560-37c3ccecd616",
//       "amount": 200,
//       "currency": "USD",
//       "successCallback": "http://localhost/pay2rax-payin/api/bnks/depositSuccess.php",
//       "failureCallback": "http://localhost/pay2rax-payin/api/bnks/depositFail.php",
//       "status": "success",        //awaiting/failed
//       "environment": "test",
//       "createdAt": "2024-11-14T06:32:17.850Z"
//     }
//   }
$rawData = file_get_contents("php://input");
$cleanData = str_replace("\n", "", $rawData);
$results = json_decode($cleanData, true);
if(!empty($results)){
    $transaction_id = $results['paymentId'];
    
    include("../../connection.php");
    echo $Query = "UPDATE `gtech_payins` SET `orderremarks`='2024-11-20 05:24:33PM', `orderstatus`='Success', `status`='webhook2 callback working', `payin_all`='$cleanData' WHERE `orderid`='$transaction_id'";
    mysqli_query($link, $Query);

    echo "cleanData =>"; print_r($cleanData);
}else{
    echo "Data Not Found or Invalid Request!";
}  