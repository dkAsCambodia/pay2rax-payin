<?php
// print_r($_POST); die;
if (!empty($_POST)) {
    $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $apiToken = $_POST['apiToken'];
    $secretKey = $_POST['secretKey']; // for M2p production
    $payin_request_id = $_POST['transaction_id']; // Should be unique from Merchant Reference
    $Customer = $_POST['customer_name'];
    $Currency = $_POST['currency'];
    $customer_bank_name = $_POST['customer_bank_name'];
    $price = $_POST['amount'];
    $Amount = (int)$price;
    $payin_notify_url = $_POST['callback_url'];
    $payin_success_url = $_POST['callback_url']; // Success CallBack URL
    $payin_error_url = $_POST['callback_url'];
   
        //echo "<pre>"; print_r($_POST); die;
        date_default_timezone_set('Asia/Phnom_Penh');
        $created_date = date("Y-m-d H:i:s");
        include("../../connection.php");
        try {
            $payin_api_token = $_POST['merchant_code'] . '-' . $payin_request_id;
            $vstore_id = $_POST['merchant_code'];
            $customer_bank_code = $_POST['customer_bank_code'] ?? 'THB';
            $customer_bank_name = $_POST['customer_bank_name'];
            $customer_phone = $_POST['customer_phone'];
            $customer_zip = $_POST['customer_zip'];
            $customer_country = $_POST['customer_country'];
            $customer_state = $_POST['customer_city'];
            $customer_city = $_POST['customer_city'];
            $customer_addressline_1 = $_POST['customer_addressline_1'];
            $customer_email = $_POST['customer_email'];
            $customer_name = $_POST['customer_name'];

            $query2 = "INSERT INTO `gtech_payins`( `client_ip`, `payin_api_token`, `vstore_id`, `action`, `source`,
                        `source_url`, `source_type`, `price`, `curr`, `product_name`, `remarks`,
                        `customer_name`, `customer_email`, `customer_addressline_1`, `customer_city`,
                        `customer_state`, `customer_country`, `customer_zip`,
                        `customer_phone`, `customer_bank_name`, `customer_bank_code`, `payin_request_id`,
                        `payin_notify_url`, `payin_success_url`, `payin_error_url`, `orderstatus`, `created_at`)
                        VALUES ( '$client_ip', '$payin_api_token', '$vstore_id',
                        'checkout', 'checkout-Encode', 'Match2Pay',
                        'payin', '$Amount', '$Currency', 'Match2Pay', 'remarks',
                        '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_city',
                        '$customer_state', '$customer_country', '$customer_zip',
                        '$customer_phone', '$customer_bank_name', '$customer_bank_code', '$payin_request_id',
                        '$payin_notify_url', '$payin_success_url', '$payin_error_url', 'pending', '$created_date')";

            $result = mysqli_query($link, $query2);
            if (!$result && empty($result)) {
                throw new Exception("Query execution failed: " . mysqli_error($link));
                die();
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            die;
        }
    
?>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
     <script>
        function generatecontrol(pform)
            {
                // {amount}{apiToken}{callbackUrl}{currency}{paymentCurrency}{paymentGatewayName}{timestamp}{tradingAccountLogin}{apiSecret}
                var s  =pform.amount.value+
                        pform.apiToken.value+
                        pform.callbackUrl.value+
                        pform.currency.value+
                        pform.paymentCurrency.value+
                        pform.paymentGatewayName.value+
                        pform.timestamp.value;   
                var secretKey='<?php echo $secretKey; ?>';
                var finalString=s+secretKey;
                // alert(finalString);
                // Calculate the SHA-384 hash
                var hash = CryptoJS.SHA384(finalString);
                // Get the hexadecimal representation of the hash
                var hexHash = hash.toString(CryptoJS.enc.Hex);
                pform.signature.value = hexHash;
                pform.submit();
            }
        </script>
        <form  method="post" action="https://payin.pay2rax.com/api/m2p/M2pPayinResponse.php">
            <!-- payin_request_id -->
                 <input  name="payin_request_id" type="hidden" placeholder="1" value="<?php echo $payin_request_id; ?>">
            <!-- Amount -->
                  <input id="amount" name="amount" type="hidden" placeholder="1" value="<?php echo $Amount; ?>">
              <!-- ApiToken -->
                  <input name="apiToken" id="apiToken" type="hidden" value="<?php echo $apiToken; ?>">
			        <!-- CallbackUrl -->
                  <input  name="callbackUrl" id="callbackUrl" type="hidden" value="https://payin.pay2rax.com/api/m2p/M2pPayinCallback.php">
              <!-- Currency -->
                  <input id="currency" name="currency" type="hidden" placeholder="USD|CNY" value="<?php echo $Currency; ?>">
              <!-- PaymentCurrency -->
                  <input name="paymentCurrency" id="paymentCurrency" type="hidden" value="USX">              
              <!-- PaymentGatewayName -->
                  <input name="paymentGatewayName" id="paymentGatewayName" type="hidden" value="USDT TRC20">
              <!-- Signature -->
                  <input name="signature" id="signature" type="hidden" value="" readonly>
              <!-- Timestamp -->
                  <input name="timestamp" id="timestamp" type="hidden" value="<?php echo time(); ?>">
              <!-- TradingAccountLogin <input name="tradingAccountLogin" id="tradingAccountLogin" type="hidden" value="tradingAccountLogin"> -->
                <button id="cartCheckout" class="btn btn-primary" OnClick="generatecontrol(this.form);" style="display:none;">Submit</button>
        </form>
        <script type="text/javascript">
           jQuery(function(){
                jQuery('#cartCheckout').click();
            });  
        </script>
<?php } else {
    echo "No Data Available or Invalid Request";
} ?>