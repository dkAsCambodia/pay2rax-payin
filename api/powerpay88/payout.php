<?php
if(!empty($_POST)){
        $client_ip =(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $payout_request_id= $_POST['transaction_id']; // Should be unique from Merchant Reference
        $Currency=$_POST['currency'];
        $customer_bank_name=$_POST['customer_bank_name'];
        $Amount = $_POST['amount'];
        $payout_api_token	=$_POST['merchant_code'] . '-' . $payout_request_id;
        $vstore_id	=$_POST['merchant_code']; // For Gtechz Official
        $product_name= 'testing dk';// Any Thing
        $customer_name=$_POST['customer_name']; // Customer Name
        $customer_email=$_POST['customer_email'];
        $customer_addressline_1=$_POST['customer_addressline_1']; // Customer Address Line 1
        $customer_city=$_POST['customer_city']; // Customer City
        $customer_country=$_POST['customer_country']; // Customer Country
        $customer_zip=$_POST['customer_zip']; // Customer Zipcode
        $customer_phone=$_POST['customer_phone']; // Customer 78760
        $customer_bank_code=$_POST['currency'];
        $customer_account_number=$_POST['customer_account_number'];
        $payout_membercode=$_POST['merchant_code'];
        $payout_notify_url=$_POST['callback_url'];
        $payout_success_url=$_POST['callback_url']; // Success CallBack URL
        $payout_error_url=$_POST['callback_url'];

        date_default_timezone_set('Asia/Kuala_Lumpur');
        $dated=date("Y-m-d h:i:sA");
        $Datetime=date("YmdHis");
        $Merchant = $_POST['Merchant'];          // for powerpay88 production
        $SecurityCode = $_POST['SecurityCode']; // for powerpay88 production
        // {MerchantCode }{TransactionId }{MemberCode }{Amount}{CurrencyCode}{TransactionDatetime}{ToBankAccountNumber }{SecurityCode}
        $Keystring= $Merchant.$payout_request_id.$payout_membercode.$Amount.$Currency.$Datetime.$customer_account_number.$SecurityCode;
        // echo "<br/>".$Keystring;
        $Key= MD5($Keystring);

		date_default_timezone_set('Asia/Phnom_Penh');
		$created_date=date("Y-m-d H:i:s");
		include("../../connection.php");
		try {
			$query2 = "INSERT INTO `gtech_payouts`( `client_ip`, `payout_api_token`, `vstore_id`, `action`, `source`, `source_url`, `price`, `curr`, `product_name`, 
            `customer_name`, `customer_email`, `customer_addressline_1`, `customer_city`, `customer_country`, `customer_zip`,
             `customer_phone`, `customer_bank_name`, `customer_bank_code`, `customer_account_number`, `payout_request_id`, `payout_membercode`, `payout_notify_url`, `payout_success_url`, `payout_error_url`, `orderstatus`, `created_at`)
             VALUES ( '$client_ip', '$payout_api_token', '$vstore_id', 'payout', 'payout-Encode', 'powerpay88_payout', '$Amount', '$Currency', '$product_name', '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_city', '$customer_country', '$customer_zip',
               '$customer_phone', '$customer_bank_name', '$customer_bank_code', '$customer_account_number', '$payout_request_id', '$payout_membercode', '$payout_notify_url', '$payout_success_url', '$payout_error_url', 'Pending', '$created_date')";
			$result = mysqli_query($link, $query2);
			if (!empty($result)) {
				// echo "Data inserted successfully!";
			} else {
				throw new Exception("Query execution failed: " . mysqli_error($link));  die;
			}
		} catch (Exception $e) {
			echo "Error: " . $e->getMessage(); die;
		}

        $protocol	= isset($_SERVER["HTTPS"])?'https://':'http://';
        $referer	= $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
        $curl_cookie="";
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        // curl_setopt($curl, CURLOPT_URL, 'https://service.powerpay88test.com/MerchantPayout/PA020'); // For  powerpay88 Testing 
        curl_setopt($curl, CURLOPT_URL, 'https://service.securepaymentapi.com/MerchantPayout/PA020'); // For powerpay88 Production
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_REFERER, $referer);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('ClientIP' => $client_ip,'ReturnURI' => 'https://payin.pay2rax.com/api/powerpay88/payoutCallback.php','MerchantCode' => $Merchant,
        'TransactionID' => $payout_request_id,'CurrencyCode' => $Currency,'MemberCode' => $payout_membercode,'Amount' => $Amount,'TransactionDateTime' => $dated,
        'BankCode' => $customer_bank_name,'toBankAccountName' => $customer_name,'toBankAccountNumber' => $customer_account_number,'Key' => $Key));
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

                // Send To callback URL Code START
                $query3 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,created_at,orderstatus FROM `gtech_payouts` WHERE payout_request_id='$payout_request_id' ";
                $qrv3=mysqli_query($link,$query3);
                $row=mysqli_fetch_assoc($qrv3);
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
                                        'settlement_trans_id' => $payout_request_id,
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
                } ?>
                <script>
                window.location.href = '<?php echo $callbackURL; ?>';
                </script>
        <?php
        }

}else{
        echo "No Data Available or Invalid Request";
} ?>
   