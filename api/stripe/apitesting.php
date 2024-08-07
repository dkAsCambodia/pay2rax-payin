<?php
	function generateRandomString($length = 3) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {$randomString .= $characters[rand(0, $charactersLength - 1)];}
      return $randomString;
   }
	$pramPost=array();
    $payin_url="https://payment.pay2rax.com/api/stripe/checkout";
	$protocol	= isset($_SERVER["HTTPS"])?'https://':'http://';
	$referer	= $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
	$pramPost["transaction_id"] = "GTRN" . time() . generateRandomString(3);
	$pramPost['price'] = $_GET['price'] ?? '100';
	$pramPost['curr'] = $_GET['curr'] ?? 'USD';
	$pramPost['customer_name']	= 'dkstripe api testing'; // Customer Name
	$pramPost['customer_email'] ='customer@gmail.com';
    $pramPost['customer_phone'] ='7777777777';
	$pramPost['merchant_code']	= $_GET['merchant_code'];
	$pramPost['card_number'] ='4111111111111111';
    $pramPost['expiration'] ='12/25';
    $pramPost['cvv'] ='111';
	$curl_cookie="";
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($curl, CURLOPT_URL, $payin_url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($curl, CURLOPT_REFERER, $referer);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $pramPost);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($curl);
	if (curl_errno($curl)) {
		echo 'Error: ' . curl_error($curl); die;
	}
	curl_close($curl);
  	print_r($response); die;
	
?>