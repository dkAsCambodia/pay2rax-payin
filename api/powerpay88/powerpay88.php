<?php
// print_r($_POST); die;
if (!empty($_POST)) {
    $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $Merchant = $_POST['Merchant'];
    $payin_request_id = $_POST['transaction_id']; // Should be unique from Merchant Reference
    $Customer = $_POST['customer_name'];
    $Currency = $_POST['currency'];
    $customer_bank_name = $_POST['customer_bank_name'];
    $Amount = $_POST['amount'].'.00';
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $dated = date("Y-m-d h:i:sA");
    $Datetime = date("YmdHis");
    //yyyyMMddHHmmss // echo $dated;
    $FrontURI = "https://payin.implogix.com/api/powerpay88/response.php";
    $BackURI = "https://payin.implogix.com/api/powerpay88/response.php";
    //$SecurityCode="r0fxPapA1OFmT1DO4cMu";   // for powerpay88 staging
    $SecurityCode = $_POST['SecurityCode']; // for powerpay88 production
    $Keystring = $Merchant . $payin_request_id . $Customer . $Amount . $Currency . $Datetime . $SecurityCode . $client_ip;
    $Key = MD5($Keystring);

    $payin_notify_url = $_POST['callback_url'];
    $payin_success_url = $_POST['callback_url']; // Success CallBack URL
    $payin_error_url = $_POST['callback_url'];
    if (!empty($_POST)) {
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
                        'checkout', 'checkout-Encode', 'powerpay88',
                        '$source_type', '$Amount', '$Currency', 'product_name', 'remarks',
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
    }

    // $gateway_urlpayment = "https://service.powerpay88test.com/MerchantTransfer"; // for staging
    $gateway_urlpayment = "https://api.securepaymentapi.com/MerchantTransfer"; // for production
?>
    <form name="cartCheckout" id="cartCheckout" method="post" action="<?php echo $gateway_urlpayment; ?>">
        <!-- Merchant	 -->
        <input type="hidden" name="Merchant" value="<?php echo $Merchant ?>" /><br /><br />
        <!-- Currency -->
        <input type="hidden" name="Currency" value="<?php echo $Currency ?>" /><br /><br />
        <!-- Customer -->
        <input type="hidden" name="Customer" value="<?php echo $Customer ?>" /><br /><br />
        <!-- Reference -->
        <input type="hidden" name="Reference" value="<?php echo $payin_request_id ?>" /><br /><br />
        <!-- Key -->
        <input type="hidden" name="Key" value="<?php echo $Key ?>" /><br /><br />
        <!-- Amount -->
        <input type="hidden" name="Amount" value="<?php echo $Amount ?>" /><br /><br />
        <!-- Note -->
        <input type="hidden" name="Note" value="Note" /><br /><br />
        <!-- Datetime -->
        <input type="hidden" name="Datetime" value="<?php echo $dated ?>" /><br /><br />
        <!-- FrontURI -->
        <input type="hidden" name="FrontURI" value="<?php echo $FrontURI ?>" style="width:350px" /><br /><br />
        <!-- BackURI -->
        <input type="hidden" name="BackURI" value="<?php echo $BackURI ?>" style="width:350px" /><br /><br />
        <!-- Language -->
        <input type="hidden" name="Language" value="en-us" /><br /><br />
        <!-- Bank -->
        <input type="hidden" name="Bank" value="<?php echo $customer_bank_name; ?>" /><br /><br />
        <!-- ClientIP -->
        <input type="hidden" name="ClientIP" value="<?php echo $client_ip ?>" /><br /><br />
        <br /><br />
        <input type="submit" name="Submit1" value="Deposit" name="deposit" style="display:none;">
    </form>
    <script type="text/javascript">
        window.onload = function() {
            document.cartCheckout.submit();
        };
    </script>
<?php } else {
    echo "No Data Available or Invalid Request";
} ?>