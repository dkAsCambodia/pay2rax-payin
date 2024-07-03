<?php
function generateRandomString($length = 3)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$apiUrl = 'https://payment.pay2rax.com/api/payment';
// $apiUrl = 'http://127.0.0.1:8000/api/payment';

$params = [
    'merchant_code' => 'MGMD',
    'product_id' => '9',      //for m2p
    'transaction_id' => "GTRN" . time() . generateRandomString(3),
    'callback_url' => 'https://payin.pay2rax.com/payin_response_url.php',
    // 'callback_url' => 'http://localhost/payin/payin_response_url.php',
    'currency' => 'USD',
    'amount' => '100',
    'customer_name' => 'Sirichai bangpa',
    'customer_email' => 'sirichai.ewallet@gmail.com',   
    'customer_phone' => '+855968509332',                
    'customer_bank_name' => 'PPTP',              
    'customer_addressline_1' => 'Singapore',            
    'customer_zip' => '670592',                         
    'customer_country' => 'TH',                      
    'customer_city' => 'Singapore',                     
];

$queryString = http_build_query($params, '', '&');
$callPaymentUrl = $apiUrl . '?' . $queryString;
?>
<script>
    window.location.href = '<?php echo $callPaymentUrl; ?>';
</script>