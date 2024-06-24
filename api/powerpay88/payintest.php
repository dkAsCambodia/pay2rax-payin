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

$apiUrl = 'https://payment.implogix.com/api/payment';

$params = [
    'merchant_code' => 'testmerchant005',
    'product_id' => '1',
    'transaction_id' => "GTRN" . time() . generateRandomString(3),
    'callback_url' => 'https://payin.implogix.com/payin_response_url.php',
    'currency' => 'THB',
    'amount' => '100',
    'customer_name' => 'Sirichai bangpa',
    'customer_email' => 'sirichai.ewallet@gmail.com',   
    'customer_phone' => '+855968509332',                
    'customer_bank_name' => 'BBL',                     
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