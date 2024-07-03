<?php
  if(!empty($_POST)){ 
    // echo "dk "; print_r($_POST); die;
    $client_ip =(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $payin_request_id= $_POST['transaction_id']; // Should be unique from Merchant Reference
    $Currency=$_POST['currency'];
    $customer_bank_name=$_POST['customer_bank_name'];
    $Amount=$_POST['amount'];
	$customer_account_number =$_POST['customer_account_number']; 
    $payin_api_token	=$_POST['payin_api_token']; // For Gtechz Official
	$vstore_id	=$_POST['merchant_code']; // For Gtechz Official
	
	$customer_name=$_POST['customer_name']; // Customer Name
	$customer_email=$_POST['customer_email'];
	$customer_addressline_1=$_POST['customer_addressline_1']; // Customer Address Line 1
	$customer_city=$_POST['customer_city']; // Customer City
	$customer_state=$_POST['customer_city']; // Customer State
	$customer_country=$_POST['customer_country']; // Customer Country
	$customer_zip=$_POST['customer_zip']; // Customer Zipcode
	$customer_phone=$_POST['customer_phone']; // Customer 78760
	$customer_bank_code=$_POST['currency'];
	$payin_notify_url=$_POST['callback_url'];
	$payin_success_url=$_POST['callback_url']; // Success CallBack URL
	$payin_error_url=$_POST['callback_url'];

        if(!empty($_POST)){
            //echo "<pre>"; print_r($_POST); die;
            date_default_timezone_set('Asia/Phnom_Penh');
            $created_date=date("Y-m-d H:i:s");
            include("../../connection.php");
            try {
                $query2 = "INSERT INTO `gtech_payins`( `client_ip`, `payin_api_token`, `vstore_id`, `action`, `source`, `source_url`, `source_type`, `price`, `curr`, `product_name`,  
            `customer_name`, `customer_email`, `customer_addressline_1`, `customer_city`, `customer_state`, `customer_country`, `customer_zip`,
            `customer_phone`, `customer_bank_name`, `customer_bank_code`, `payin_request_id`, `payin_notify_url`, `payin_success_url`, `payin_error_url`, `orderstatus`, `created_at`)
            VALUES ( '$client_ip', '$payin_api_token', '$vstore_id', 'checkout', 'checkout-Encode', 'vizpay', 'vizpay', '$Amount', '$Currency', 'test product',
            '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_city', '$customer_state', '$customer_country', '$customer_zip',
            '$customer_phone', '$customer_bank_name', '$customer_bank_code', '$payin_request_id', '$payin_notify_url', '$payin_success_url', '$payin_error_url', 'pending', '$created_date')";
                $result = mysqli_query($link, $query2);
                if (!empty($result)) {
                    // echo "Data inserted successfully!";
                } else {
                    throw new Exception("Query execution failed: " . mysqli_error($link));  die;
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage(); die;
            }

			$redirecturl='https://payin.pay2rax.com/api/payment-form.php';
			$callbackURL=$redirecturl.'?amount='.base64_encode($Amount).'&ref_bank_code='.base64_encode($customer_bank_name).'&ref_account_no='.base64_encode($customer_account_number).'&order_id='.base64_encode($payin_request_id).'&ref_name='.base64_encode($customer_name);
			?>
			<script>
				window.location.href = '<?php echo $callbackURL; ?>';
			</script>
			<?php

            // Code for VIZPAY START
			// Class Vizpay{
			// 	private $api_key;
			// 	private $secret_key;
			// 	private$version;
			// 	public $api_url;
			// 	function __construct($config){
			// 		$this->api_key = $config['api_key'];
			// 		$this->secret_key = $config['secret_key'];
			// 		$this->version = $config['version'];
			// 		$this->api_url = $config['api_url'];
			// 	}

			// 	public function gen_signature($array_data){
			// 		// Add Secret Key to object or array parameters 
			// 		$array_data['key'] = $this->secret_key;
			// 		// Sort the key parameters alphabetically in ascending order and convert them into JSON string.
			// 		ksort($array_data);
			// 		$json_string = json_encode($array_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			// 		// Use JSON string encrypted to MD5 and you get signature
			// 		$signature = MD5($json_string);
			// 		return $signature;
			// 	}

			// 	/*
			// 	*	$method :, POST API CALL:
			// 	*/
			// 	public function call_url($path, $method, $array_data){
			// 		$curl = curl_init();
			// 		$url = $this->api_url.$this->version.$path;
			// 		curl_setopt_array($curl, array(
			// 			CURLOPT_URL => $url,
			// 			CURLOPT_RETURNTRANSFER => true,
			// 			CURLOPT_ENCODING => '',
			// 			CURLOPT_MAXREDIRS => 10,
			// 			CURLOPT_TIMEOUT => 0,
			// 			CURLOPT_FOLLOWLOCATION => true,
			// 			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			// 			CURLOPT_CUSTOMREQUEST => $method,
			// 			CURLOPT_POSTFIELDS =>json_encode($array_data),
			// 			CURLOPT_HTTPHEADER => array(
			// 				'Authorization: Basic '.base64_encode($this->api_key.":"),
			// 				'Content-Type: application/json'
			// 			),
			// 		));
			// 		$response = curl_exec($curl);
			// 		curl_close($curl);
			// 		return json_decode($response, true);
			// 	}
			// }

			// $config = [
				// 'api_key' => '86591ce6-685603e2-a5bd552d-dcb29058',
				// 'secret_key' => 'b3e9c45a-f64342da-20837e7b-8b54479b',
			// 	'version' => 'v1',
			// 	'api_url' => 'https://prod.vizpay.io/'
			// ];
			// $vizpay = new Vizpay($config);

			// /* Deposit by QR Code */
			// $post_data = [
			// 	"order_id" => $payin_request_id,
			// 	"amount" => $Amount,
			// 	"ref_bank_code" =>  $customer_bank_name, 
			// 	"ref_account_no" =>  $customer_account_number, 
			// 	"ref_name" =>  $customer_name, 
			// 	"user_id" =>  "1234", 
			// 	"callback_url" =>  "https://payin.pay2rax.com/api/vizpay/viz_payin_response.php" 
			// ];
			// // echo "<pre>"; print_r($post_data); die;
			// $post_data['signature'] = $vizpay->gen_signature($post_data);
			// $result = $vizpay->call_url('/deposit/qrcode','POST', $post_data);
			// // echo "<pre>"; print_r($result); 
			// if(!empty($result)){
			// 	$redirecturl='https://payin.pay2rax.com/api/payment.php';
			// 	$callbackURL=$redirecturl.'?token='.base64_encode($result['result']['image']).'&amount='.base64_encode($Amount).'&ref_bank_code='.base64_encode($customer_bank_name).'&ref_account_no='.base64_encode($customer_account_number).'&order_id='.base64_encode($payin_request_id).'&ref_name='.base64_encode($customer_name);
			// 	?>
			// 	<script>
			// 		window.location.href = '<?php echo $callbackURL; ?>';
			// 	</script>
			// 	<?php
			// }
			// Code for VIZPAY END

        } 
}else{
    echo "No Data Available or Invalid Request";
} ?>
     