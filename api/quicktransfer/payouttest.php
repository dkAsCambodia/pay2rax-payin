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

$apiUrl = 'https://payment.pay2rax.com/api/gpayout';

$params = [
    'merchant_code' => 'testmerchant005',
    'product_id' => '3',       // for 2/3
    'transaction_id' => "GTRN" . time() . generateRandomString(3),
    'callback_url' => 'https://payin.pay2rax.com/payout/payout_response_url.php',
    'currency' => 'THB',
    'amount' => '10000',
    'customer_name' => 'Sirichai bangpa',
    'customer_email' => 'sirichai.ewallet@gmail.com',   
    'customer_phone' => '+855968509332',                
    'customer_bank_name' => 'BBL',              //bank Code       
    'customer_account_number' => '1234567',                          
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