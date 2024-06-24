<?php
if(!empty($_POST)){
    // echo "dk "; print_r($_POST); 
    $client_ip =$_POST['client_ip'];
    $Merchant="PA020";      //for powerpay88
    $payout_request_id= $_POST['payout_request_id']; // Should be unique from Merchant Reference
    $Customer = $_POST['customer_name'];
    $customer_bank_name=$_POST['customer_bank_name'];
    $Amount=$_POST['price'];
    date_default_timezone_set('Asia/Kuala_Lumpur');
	$dated=date("Y-m-d h:i:sA");
	$Datetime=date("YmdHis"); //yyyyMMddHHmmss // echo $dated;
    //$SecurityCode="r0fxPapA1OFmT1DO4cMu";   // for powerpay88 staging
	$SecurityCode="zSAIDEPVZLyuc4ESXKO2";   // for powerpay88 production
    $payout_api_token	=$_POST['payout_api_token']; // For Gtechz Official
	$vstore_id	=$_POST['vstore_id']; // For Gtechz Official
	$action=$_POST['action'];
	$source=$_POST['source'];
    $source_url=$_POST['source_url'];
	$source_type =$_POST['source_type'];
	$curr = $_POST['curr'];
	$product_name= $_POST['product_name'];// Any Thing
	$remarks= $_POST['remarks'];
    $narration= $_POST['narration'];
	$customer_name=$_POST['customer_name']; // Customer Name
	$customer_email=$_POST['customer_email'];
	$customer_addressline_1=$_POST['customer_addressline_1']; // Customer Address Line 1
	$customer_city=$_POST['customer_city']; // Customer City
	$customer_state=$_POST['customer_state']; // Customer State
	$customer_country=$_POST['customer_country']; // Customer Country
	$customer_zip=$_POST['customer_zip']; // Customer Zipcode
	$customer_phone=$_POST['customer_phone']; // Customer 78760
	$customer_bank_code=$_POST['customer_bank_code'];
    $customer_account_number=$_POST['customer_account_number'];
    $payout_membercode=$_POST['payout_membercode'];
	$payout_response_url=$_POST['payout_response_url']; // Success CallBack URL


    $Keystring= $Merchant.$payout_request_id.$payout_membercode.$Amount.$curr.$Datetime.$customer_account_number.$SecurityCode;
    $Key= MD5($Keystring);
		
		date_default_timezone_set('Asia/Phnom_Penh');
		$created_date=date("Y-m-d H:i:s");
		include("../../../connection.php");
		try {
			$query2 = "INSERT INTO `gtech_payouts`( `client_ip`, `payout_api_token`, `vstore_id`, `action`, `source`, `source_url`, `source_type`, `price`, `curr`, `product_name`, `remarks`, 
            `narration`,`customer_name`, `customer_email`, `customer_addressline_1`,  `customer_city`, `customer_state`, `customer_country`, `customer_zip`,
             `customer_phone`, `customer_bank_name`, `customer_bank_code`, `customer_account_number`, `payout_request_id`, `payout_membercode`, `payout_success_url`, `orderstatus`, `created_at`)
             VALUES ( '$client_ip', '$payout_api_token', '$vstore_id', '$action', '$source', '$source_url', '$source_type', '$Amount', '$curr', '$product_name', '$remarks', '$narration',
              '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_city', '$customer_state', '$customer_country', '$customer_zip',
               '$customer_phone', '$customer_bank_name', '$customer_bank_code', '$customer_account_number', '$payout_request_id', '$payout_membercode', '$payout_response_url', 'Failed', '$created_date')";
			$result = mysqli_query($link, $query2);
			if (!empty($result)) {
				// echo "Data inserted successfully!";
			} else {
				throw new Exception("Query execution failed: " . mysqli_error($link));  die;
			}
		} catch (Exception $e) {
			echo "Error: " . $e->getMessage(); die;
		}

        $curl_cookie="";
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_URL, 'https://service.securepaymentapi.com/MerchantPayout/PA020');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_REFERER, $source_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('Key' => $Key,'ClientIP' => $client_ip,'ReturnURI' => 'https://payin.implogix.com/api/status','MerchantCode' => $Merchant,
        'TransactionID' => $payout_request_id,'CurrencyCode' => $curr,'MemberCode' => $payout_membercode,'Amount' => $Amount,'TransactionDateTime' => $dated,
        'BankCode' => $customer_bank_name,'toBankAccountName' => $customer_name,'toBankAccountNumber' => $customer_account_number,'toProvince' => $customer_state,'toCity' => $customer_city));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        $xml = simplexml_load_string($response);
        $json= json_encode($xml);
        $resArray = json_decode($json,TRUE);
        
        if(!empty($resArray)){
            // Code for update Transaction status START
            if($resArray['statusCode']=='000'){
                $orderstatus='Success';
            }else{
                $orderstatus='Failed';
            }
            @$orderremarks=$dated.' '.$resArray['message'];
            if(!empty($orderremarks)){
                $status='1';
            }else{
                $status='0';
            }
            $query = "UPDATE `gtech_payouts` SET `orderremarks`='$orderremarks', `orderstatus`='$orderstatus', `status`='$status', `payout_aar`='$json' WHERE payout_request_id='$payout_request_id' ";
            $res=mysqli_query($link,$query);
            // Code for update Transaction status END
            $callbackURL=$payout_response_url.'?pt_transactionId='.$payout_request_id.'&dkstatus='.$resArray['statusCode'].'&pt_email='.$customer_email.'&pt_reference='.$payout_request_id.'&pt_amount='.$Amount.'&pt_timestamp='.$orderremarks.'&pt_status='.$orderstatus;
            // header("Location:$callbackURL"); 
            ?>
            <script>
                window.location.href = '<?php echo $callbackURL; ?>';
            </script>
            <?php
        }
}else{
        echo "No Data Available or Invalid Request";
} ?>
   
