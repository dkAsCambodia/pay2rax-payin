<?php
if(!empty($_POST)){
        $client_ip =(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $payout_request_id= $_POST['transaction_id']; // Should be unique from Merchant Reference
        $Currency=$_POST['currency'];
        $customer_bank_name=$_POST['customer_bank_name'];
        $price = $_POST['amount'];
        $Amount = (int)$price;
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
        $payout_membercode=$_POST['transaction_id'];
        $payout_notify_url=$_POST['callback_url'];
        $payout_success_url=$_POST['callback_url']; // Success CallBack URL
        $payout_error_url=$_POST['callback_url'];

		date_default_timezone_set('Asia/Phnom_Penh');
		$created_date=date("Y-m-d H:i:s");
		include("../../connection.php");
		try {
			$query2 = "INSERT INTO `gtech_payouts`( `client_ip`, `payout_api_token`, `vstore_id`, `action`, `source`, `source_url`, `price`, `curr`, `product_name`, 
            `customer_name`, `customer_email`, `customer_addressline_1`, `customer_city`, `customer_country`, `customer_zip`,
             `customer_phone`, `customer_bank_name`, `customer_bank_code`, `customer_account_number`, `payout_request_id`, `payout_membercode`, `payout_notify_url`, `payout_success_url`, `payout_error_url`, `orderstatus`, `created_at`)
             VALUES ( '$client_ip', '$payout_api_token', '$vstore_id', 'payout', 'payout-Encode', 'v_payout', '$Amount', '$Currency', '$product_name', '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_city', '$customer_country', '$customer_zip',
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

        // $redirecturl='https://payin.implogix.com/api/payment-form2.php';
        // $callbackURL=$redirecturl.'?amount='.base64_encode($Amount).'&ref_bank_code='.base64_encode($customer_bank_name).'&ref_account_no='.base64_encode($customer_account_number).'&order_id='.base64_encode($payout_request_id).'&ref_name='.base64_encode($customer_name);
       

    // Code for VIZPAY START
    Class Vizpay{
        private $api_key;
        private $secret_key;
        private$version;
        public $api_url;

        function __construct($config){
            $this->api_key = $config['api_key'];
            $this->secret_key = $config['secret_key'];
            $this->version = $config['version'];
            $this->api_url = $config['api_url'];
        }

        public function gen_signature($array_data){
            $array_data['key'] = $this->secret_key;
            ksort($array_data);
            $json_string = json_encode($array_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            $signature = MD5($json_string);
            return $signature;
        }

        public function call_url($path, $method, $array_data){
            $curl = curl_init();
            $url = $this->api_url.$this->version.$path;
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS =>json_encode($array_data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic '.base64_encode($this->api_key.":"),
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return json_decode($response, true);
        }
    }
    $config = [
        'api_key' => '07c784da-1ab7825e-d5742394-07a47b63',
        'secret_key' => 'ef2bc47d-31ecf9a6-97bb9144-228b38dc',
        'version' => 'v1',
        'api_url' => 'https://apipoint.first2pay.io/'
    ];
    $vizpay = new Vizpay($config);

    /* Withdraw */
    $post_data = [
        "order_id" => $payout_request_id,
        "amount" => $Amount,
        "to_bank_code" =>  $customer_bank_name,
        "to_account_no" =>  $customer_account_number,
        "to_name" =>  $customer_name,
        "callback_url" =>  "https://payin.implogix.com/api/vizpay/viz_payout_response.php"
    ];
    $post_data['signature'] = $vizpay->gen_signature($post_data);
    $resArray = $vizpay->call_url('/withdraw','POST', $post_data);

    if(!empty($resArray)){
        // Code for update Transaction status START
        if($resArray['code']=='200'){
            $orderstatus='Success';
            @$transactionId=$resArray['result']['order_id'];
        }else{
            $orderstatus='Failed';
            @$transactionId=$payout_request_id;
        }
        @$orderremarks=$resArray['message'];
        if(!empty($orderremarks)){
            $status='1';
        }else{
            $status='0';
        }
       
        $payout_aar=json_encode($resArray, true);
        include("../../connection.php");
        $query = "UPDATE `gtech_payouts` SET `orderid`='$transactionId', `orderremarks`='$orderremarks', `orderstatus`='$orderstatus', `status`='$status', `payout_aar`='$payout_aar'  WHERE payout_request_id='$payout_request_id' ";
        $res=mysqli_query($link,$query);
        // Code for update Transaction status END


         // Send To callback URL Code START
         include("../../connection.php");
          // Send To callback URL Code START
         $query2 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,created_at,orderstatus FROM `gtech_payouts` WHERE payout_request_id='$payout_request_id' ";
         $qrv=mysqli_query($link,$query2);
         $row=mysqli_fetch_assoc($qrv);
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
                     'settlement_trans_id' => $transactionId,
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
         }
        ?>
        <script>
            window.location.href = '<?php echo $callbackURL; ?>';
        </script>
        <?php
    }
        // Code for VIZPAY END
}else{
        echo "No Data Available or Invalid Request";
} ?>
   