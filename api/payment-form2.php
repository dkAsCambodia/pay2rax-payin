<?php
session_start();
ob_start();
if(array_key_exists('paynow',$_POST)){
	payout();
}
function payout(){
    $payout_request_id= $_POST['payout_request_id'];
    $customer_name=$_POST['customer_name'];
    $bank_code=$_POST['bank_code'];
    $customer_bank_name=$_POST['customer_bank_name'];
    $customer_account_number=$_POST['customer_account_number'];
    $Amount=$_POST['amount'];

        // Code for VIZPAY START
        Class Vizpay{
            private $api_key;
            private $secret_key;
            private$version;
            public $api_url;

            function __construct($config){
                $this->api_key = $config['api_key'];
                $this->secret_key = $config['secret_key'];
                $this->version = $config['version'];
                $this->api_url = $config['api_url'];
            }
 
            public function gen_signature($array_data){
                $array_data['key'] = $this->secret_key;
                ksort($array_data);
                $json_string = json_encode($array_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                $signature = MD5($json_string);
                return $signature;
            }

            public function call_url($path, $method, $array_data){
                $curl = curl_init();
                $url = $this->api_url.$this->version.$path;
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => $method,
                    CURLOPT_POSTFIELDS =>json_encode($array_data),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic '.base64_encode($this->api_key.":"),
                        'Content-Type: application/json'
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                return json_decode($response, true);
            }
        }
        $config = [
            'api_key' => '07c784da-1ab7825e-d5742394-07a47b63',
            'secret_key' => 'ef2bc47d-31ecf9a6-97bb9144-228b38dc',
            'version' => 'v1',
            'api_url' => 'https://apipoint.first2pay.io/'
        ];
        $vizpay = new Vizpay($config);

        /* Withdraw */
        $post_data = [
            "order_id" => $payout_request_id,
            "amount" => $Amount,
            "to_bank_code" =>  $bank_code,
            "to_account_no" =>  $customer_account_number,
            "to_name" =>  $customer_name,
            "callback_url" =>  "https://payin.implogix.com/api/vizpay/viz_payout_response.php"
        ];
        $post_data['signature'] = $vizpay->gen_signature($post_data);
        $resArray = $vizpay->call_url('/withdraw','POST', $post_data);

        if(!empty($resArray)){
            // Code for update Transaction status START
            if($resArray['code']=='200'){
                $orderstatus='Success';
                @$transactionId=$resArray['result']['order_id'];
            }else{
                $orderstatus='Failed';
                @$transactionId=$payout_request_id;
            }
            @$orderremarks=$resArray['message'];
            if(!empty($orderremarks)){
                $status='1';
            }else{
                $status='0';
            }
           
            $payout_aar=json_encode($resArray, true);
            include("../connection.php");
            $query = "UPDATE `gtech_payouts` SET `orderid`='$transactionId', `orderremarks`='$orderremarks', `orderstatus`='$orderstatus', `status`='$status', `payout_aar`='$payout_aar', `customer_name`='$customer_name', `customer_bank_name`='$customer_bank_name', `customer_account_number`='$customer_account_number' WHERE payout_request_id='$payout_request_id' ";
            $res=mysqli_query($link,$query);
            // Code for update Transaction status END

            // Code for update Transaction 4thparty dashboard START
            $host     = "localhost";
            $user     = "implogix";
            $password = "password";
            $database = "payment";
            $con   = mysqli_connect($host, $user, $password, $database);
            //if (mysqli_connect_errno($con)) {
            if (!$con) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }
            $query3 = "UPDATE `settle_requests` SET  `customer_name`='$customer_name', `customer_bank_name`='$customer_bank_name', `customer_account_number`='$customer_account_number' WHERE fourth_party_transection='$payout_request_id'";
            mysqli_query($con, $query3);
            // Code for update Transaction 4thparty dashboard END

             // Send To callback URL Code START
             include("../connection.php");
              // Send To callback URL Code START
             $query2 = "SELECT price,customer_email,payout_request_id,payout_notify_url,payout_success_url,payout_error_url,orderid,created_at,orderstatus FROM `gtech_payouts` WHERE payout_request_id='$payout_request_id' ";
             $qrv=mysqli_query($link,$query2);
             $row=mysqli_fetch_assoc($qrv);
             if(!empty($row)){
                 if($row['orderstatus'] == 'Success' || $row['orderstatus'] == 'Approved' || $row['orderstatus'] == 'success' || $row['orderstatus'] == 'SUCCESS') {
                     $paymentStatus = 'success';
                     $redirecturl = $row['payout_success_url'];
                 }elseif($row['orderstatus'] == 'Failed' || $row['orderstatus'] == 'Rejected' || $row['orderstatus'] == 'Cancelled' || $row['orderstatus'] == 'failed' || $row['orderstatus'] == 'FAILED') {
                     $paymentStatus = 'failed';
                     $redirecturl = $row['payout_notify_url'];
                 }elseif($row['orderstatus'] == 'Pending' || $row['orderstatus'] == 'pending' || $row['orderstatus'] == 'PENDING') {
                     $paymentStatus = 'pending';
                     $redirecturl = $row['payout_error_url'];
                 }else{
                     $redirecturl = $row['payout_success_url'];
                     $paymentStatus = 'pending';
                 }

                 if (!empty($redirecturl)) {
                     $info = [
                         'settlement_trans_id' => $transactionId,
                         'orderstatus' => $row['orderstatus'],
                         'payment_email' => $row['customer_email'],
                         'transaction_id' => $row['payout_request_id'],
                         'payment_amount' => $row['price'],
                         'payment_timestamp' => $row['created_at'],
                         'payment_status' => $paymentStatus,
                         'orderremarks' => $orderremarks,
                     ];
                     $queryString = http_build_query($info, '', '&');
                     $callbackURL = $redirecturl . '?' . $queryString;
                 }else{
                     echo "Callback URL not Found or Invalid Request!";
                 }
             }
            ?>
            <script>
                window.location.href = '<?php echo $callbackURL; ?>';
            </script>
            <?php
        }
			// Code for VIZPAY END
}

	
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="dns-prefetch" href="//127.0.0.1:8000">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="LtVoJUqPiAmr2GwQeh4q91sK2LmXMyRERPMrYtGy">
    <meta name="keywords" content="admin, dashboard">
    <meta name="author" content="Soeng Souy">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Payment Gateway">
    <meta property="og:title" content="Payment Gateway">
    <meta property="og:description" content="Payment Gateway">
    <meta property="og:image" content="../assets/images/logo.png">
    <meta name="format-detection" content="telephone=no">
    <title>Gtechz PSP – Payment Service Provider</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/favicon.png">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/toastr.min.css">
    <script src="../assets/js/toastr_jquery.min.js"></script>
    <script src="../assets/js/toastr.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    </head>
    <body>
    <style>
        .auth-form {
            padding: 20px 20px !important;
            margin-top:20px;
        }
        .form-control {
            height: 2.5rem !important;
        }
        .height-form{
            margin-top:10%;
        }
    </style>
        <div class="authincation">
            <div class="container height-form">
<div class="row justify-content-center align-items-center">
    <div class="col-md-8">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h3 class="text-center mb-4"><b> GTech Merchant Payout</b></h3>
                        <form class="form-horizontal" enctype="multipart-formdata" method="post" action="#">
							<div class="row mb-3">
                                <label for="Reference" class="col-md-4 form-label">Reference ID <span style="color:#28a745 !important;">*</span></label>
                                <div class="col-md-8">
								<input class="form-control" name="payout_request_id" id="payout_request_id" placeholder="Enter Reference ID" value="<?php echo base64_decode($_GET['order_id']); ?>" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="amount" class="col-md-4 form-label">Amount (THB) <span style="color:#28a745 !important;">*</span></label>
                                <div class="col-md-8">
									<input class="form-control" required name="amount" id="amount" placeholder="Enter your Amount" value="<?php echo base64_decode($_GET['amount']); ?>" type="text" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="customer_name" class="col-md-4 form-label">Account Holder Name <span style="color:#dc3545 !important;">*</span></label>
                                <div class="col-md-8">
								<input class="form-control" required name="customer_name" id="customer_name" placeholder="Enter Customer Name" type="text" value="">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="Bank-Code" class="col-md-4 form-label">Select Bank Code <span style="color:#dc3545 !important;">*</span></label>
                                <div class="col-md-8">
                                        <input name="customer_bank_name" id="customer_bank_name" type="hidden" value="" required>
										<select class="form-control select2-show-search form-select  text-dark" id="bank_code" name="bank_code" required>
                                            <option value="">------------------------------</option>
                                            <option value="002">Bangkok Bank</option>
                                            <option value="004">Karsikorn Bank (K-Bank)</option>
                                            <option value="006">Krung Thai Bank</option>
                                            <option value="011">TMB Bank Public Company Limited</option>
                                            <option value="014">Siam Commercial Bank</option>
                                            <option value="017">Citibank</option>
                                            <option value="018">SUMITOMO MITSUI BANKING CORPORATION (SMBC)</option>
                                            <option value="020">Standard Chartered Bank (Thai) Public Company Limited (SCBT)</option>
                                            <option value="022">CIMB Niaga Bank</option>
                                            <option value="024">United Overseas Bank (Thai) PCL (UOB)</option>
                                            <option value="025">Bank of Ayudhya (Krungsri) (BAY)</option>
                                            <option value="030">Government Savings Bank (GSB)</option>
                                            <option value="031">Hong Kong & Shanghai Corporation Limited (HSBC)</option>
                                            <option value="032">Deutsche Bank (DB)</option>
                                            <option value="033">Government Housing Bank</option>
                                            <option value="034">Bank for Agriculture and Agricultural Cooperatives (BAAC)</option>
                                            <option value="039">Mizuho Corporate Bank Limited (MHCB)</option>
                                            <option value="066">Islamic Bank of Thailand (ISBT)</option>
                                            <option value="067">Tisco Bank Public Company Limited (TISCO)</option>
                                            <option value="069">Kiatnakin Bank (KKP)</option>
                                            <option value="070">ICBCTHBK (ICBC)</option>
                                            <option value="071">The Thai Credit Retail Bank Public Company Limited (TCRB)</option>
                                            <option value="073">Land and Houses Bank Public Company Limited (LHBANK)</option>
                                            <option value="801">ANZ BANK (TRUE)</option>
										</select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="customer_account_number" class="col-md-4 form-label">Bank Account Number <span style="color:#dc3545 !important;">*</span></label>
                                <div class="col-md-8">
									<input class="form-control" required name="customer_account_number" id="customer_account_number" placeholder="Enter Account Number" value="" type="text">
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" name="paynow" id="paynow" class="btn btn-primary btn-block">Payout Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
      
    </div>
</div>
<script src="../assets/vendor/global/global.min.js"></script>
<script src="../assets/js/custom.min.js"></script>
<script src="../assets/js/dlabnav-init.js"></script>
<script>
    $(document).ready(function(){
        $('#bank_code').on('change', function(){
            var sourcetype=$("#bank_code option:selected");
            $('#customer_bank_name').val(sourcetype.text());
        });
    });
</script>
</body>
</html>

