<?php
// echo "This is M2p Response created by DK";
$results=$_POST;
$payin_request_id=$results['payin_request_id'];
unset($results['payin_request_id']);

// $fields=json_encode($results);
// $protocol	= isset($_SERVER["HTTPS"])?'https://':'http://';
// $referer	= $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 

//   $curl_cookie="";
// 	$curl = curl_init(); 
// 	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
// 	curl_setopt($curl, CURLOPT_URL, 'https://m2p.match-trade.com/api/v2/deposit/crypto_agent');
// 	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
// 	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
// 	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
// 	curl_setopt($curl, CURLOPT_REFERER, $referer);
// 	curl_setopt($curl, CURLOPT_POST, 1);
// 	curl_setopt($curl, CURLOPT_POSTFIELDS, $results);
// 	curl_setopt($curl, CURLOPT_HEADER, 0);
// 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
// 	$res = curl_exec($curl);

// $curl = curl_init();
// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://m2p.match-trade.com/api/v2/deposit/crypto_agent',
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
//   CURLOPT_POSTFIELDS =>$fields,
//   CURLOPT_HTTPHEADER => array(
//     'Content-Type: application/json'
//   ),
// ));
// $res = curl_exec($curl);



// API endpoint URL
$apiUrl = 'https://m2p.match-trade.com/api/v2/deposit/crypto_agent';
$jsonData = json_encode($results);
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
));
$res = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}
curl_close($ch);
// echo "<pre>"; print_r($res); die;
$response=json_decode($res);

 // Code for update Transaction status START
 if(!empty($response->paymentId)){
    include("../../connection.php");
    $query1 = "UPDATE `gtech_payins` SET `orderid`='$response->paymentId',  `status`='1', `payin_aar`='$res' WHERE payin_request_id='$payin_request_id' ";
    mysqli_query($link,$query1);
 }
 // Code for update Transaction status END
?>
<script>
    window.location.href = '<?php echo $response->checkoutUrl; ?>';
</script>