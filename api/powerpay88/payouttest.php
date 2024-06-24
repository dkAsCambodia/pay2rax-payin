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

// $apiUrl = 'http://127.0.0.1:8000/api/gpayout';
$apiUrl = 'https://payment.implogix.com/api/gpayout';

$params = [
    'merchant_code' => 'testmerchant005',
    'product_id' => '11',     //1/11
    'transaction_id' => "GTRN" . time() . generateRandomString(3),
    'callback_url' => 'https://payin.implogix.com/payout/payout_response_url.php',
    // 'callback_url' => 'http://localhost/payin/payout/payout_response_url.php',
    'currency' => 'THB',
    'amount' => '100.00',     //need amount in float two digit
    'customer_name' => 'Dk testing',
    'customer_email' => 'dk@gmail.com',   
    'customer_phone' => '+855968509332',                
    'customer_bank_name' => 'BBL',              //bank Code       
    'customer_account_number' => '1234567',                          
    'customer_addressline_1' => 'cambodia',            
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