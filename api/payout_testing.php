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

if(!empty($_GET['merchant_code'])){
    $params = [
        'merchant_code' => $_GET['merchant_code'],
        'product_id' => '5',       
        'transaction_id' => "GTRN" . time() . generateRandomString(3),
        'callback_url' => 'https://payin.pay2rax.com/payout/payout_response_url.php',
        'currency' => 'THB',
        'amount' => '80',
        'customer_name' => 'Sirichai bangpa',
        'customer_email' => 'sirichai.ewallet@gmail.com',   
        'customer_phone' => '+855968509332',                
        'customer_bank_name' => 'BBL',              //bank Code       
        'customer_account_number' => '6924108520',                          
        'customer_addressline_1' => 'Singapore',            
        'customer_zip' => '670592',                         
        'customer_country' => 'TH',                      
        'customer_city' => 'Singapore',                     
    ];
}else{
    echo "merchant_code not found!";
    return false;
}


$queryString = http_build_query($params, '', '&');
$callPaymentUrl = $apiUrl . '?' . $queryString;
?>
<script>
    window.location.href = '<?php echo $callPaymentUrl; ?>';
</script>