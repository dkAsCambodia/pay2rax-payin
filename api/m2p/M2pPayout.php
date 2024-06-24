<?php
if(!empty($_POST)){
        $client_ip =(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $payout_request_id= $_POST['transaction_id']; // Should be unique from Merchant Reference
        $Currency=$_POST['currency'];
        // $customer_bank_name=$_POST['customer_bank_name'];
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
        // $customer_bank_code=$_POST['currency'];
        $customer_account_number=$_POST['customer_account_number'];
        $payout_membercode=$_POST['transaction_id'];
        $payout_notify_url=$_POST['callback_url'];
        $payout_success_url=$_POST['callback_url']; // Success CallBack URL
        $payout_error_url=$_POST['callback_url'];
        $secretKey = $_POST['secretKey']; // for M2p production
        $apiToken = $_POST['apiToken'];

		date_default_timezone_set('Asia/Phnom_Penh');
		$created_date=date("Y-m-d H:i:s");
		include("../../connection.php");
		try {
			$query2 = "INSERT INTO `gtech_payouts`( `client_ip`, `payout_api_token`, `vstore_id`, `action`, `source`, `source_url`, `price`, `curr`, `product_name`, 
            `customer_name`, `customer_email`, `customer_addressline_1`, `customer_city`, `customer_country`, `customer_zip`,
             `customer_phone`, `customer_account_number`, `payout_request_id`, `payout_membercode`, `payout_notify_url`, `payout_success_url`, `payout_error_url`, `orderstatus`, `created_at`)
             VALUES ( '$client_ip', '$payout_api_token', '$vstore_id', 'M2pPayout', 'payout-Encode', 'M2pPayout', '$Amount', '$Currency', '$product_name', '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_city', '$customer_country', '$customer_zip',
               '$customer_phone', '$customer_account_number', '$payout_request_id', '$payout_membercode', '$payout_notify_url', '$payout_success_url', '$payout_error_url', 'Pending', '$created_date')";
			$result = mysqli_query($link, $query2);
			if (!empty($result)) {
				// echo "Data inserted successfully!";
			} else {
				throw new Exception("Query execution failed: " . mysqli_error($link));  die;
			}
		} catch (Exception $e) {
			echo "Error: " . $e->getMessage(); die;
		}

    ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script>
            function generatecontrol(pform)
            {
                // {"address": "amount": "apiToken" : "callbackUrl" : "currency" :"paymentGatewayName" :"timestamp":"withdrawCurrency" }
                var s  =pform.address.value+
                        pform.amount.value+
                        pform.apiToken.value+
                        pform.callbackUrl.value+
                        pform.currency.value+
                        pform.paymentGatewayName.value+
                        pform.timestamp.value+
                        pform.withdrawCurrency.value;   
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
            <form action="https://payin.implogix.com/api/m2p/M2pPayoutResponse.php" method="POST">
                <!-- payout_request_id -->
                <input  name="payout_request_id" type="hidden" value="<?php echo $payout_request_id; ?>">
                <!-- Currency -->
                  <input id="currency" name="currency" type="hidden" placeholder="USD/CNY" value="<?php echo $Currency; ?>">
                <!-- PaymentGatewayName -->
                  <input name="paymentGatewayName" id="paymentGatewayName" type="hidden" value="USDT TRC20">
                <!-- Amount -->
                  <input id="amount" name="amount" type="hidden" placeholder="1" value="<?php echo $Amount; ?>">
                <!-- WithdrawCurrency -->
                  <input name="withdrawCurrency" id="withdrawCurrency" type="hidden" value="USX">   
                <!-- Wallet Address -->
                  <input name="address" id="address" type="hidden" value="<?php echo $customer_account_number; ?>"> 
                <!-- CallbackUrl -->
                  <input  name="callbackUrl" id="callbackUrl" type="hidden" value="https://payin.implogix.com/api/m2p/M2pPayoutCallback.php">
                <!-- Signature -->
                  <input name="signature" id="signature" type="hidden" value="" readonly>
                <!-- ApiToken -->
                  <input name="apiToken" id="apiToken" type="hidden" value="<?php echo $apiToken; ?>">
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
  <?php
}else{
        echo "No Data Available or Invalid Request";
} ?>
   