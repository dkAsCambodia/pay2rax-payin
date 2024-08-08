<?php
session_start();
ob_start();
include("connection.php");
$selrev = "SELECT * FROM `gtech_currencies`";
$qrv=mysqli_query($link,$selrev);
$referenceNo="GZTRN".time().generateRandomString(3);
if(array_key_exists('paynow',$_POST)){
	checkout();
}
function checkout(){
	$baseurl = "https://payin.pay2rax.com";	
	$payin_api_token		="noadf49CKEYSWsBFHZQ0Oe2MPIb1T5"; // For Gtechz Official
	$vstore_id	="GZ-108"; // For Gtechz Official
    $pramPost=array();
    if($_POST['source_type']=='Source1'){
        $payin_url="https://payment.pay2rax.com/api/paypal/checkout";
    }elseif($_POST['source_type']=='Source2'){
        // if(!empty($_POST['card_number']) && !empty($_POST['expiration']) && !empty($_POST['cvv'])){
            $payin_url="https://payment.pay2rax.com/api/stripe/checkout";
            $pramPost['card_number'] =$_POST['card_number'];
            $pramPost['expiration'] =$_POST['expiration'];
            $pramPost['cvv'] =$_POST['cvv'];
        // }else{
        //     return "Card details is required!";
        // }
	}else{
		$payin_url=$baseurl."/api/V5/";
	}
	$protocol	= isset($_SERVER["HTTPS"])?'https://':'http://';
	$referer	= $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
	$pramPost['client_ip'] =(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
	$pramPost["payin_api_token"] = $payin_api_token;
	$pramPost['vstore_id']	=$vstore_id;
	$pramPost['action']		='checkout';
	$pramPost['source']		='checkout-Encode';
	$pramPost['source_url']	=$referer;
	$pramPost['source_type'] =$_POST['source_type'];
	$pramPost['price'] = $_POST['price'];
	if($_POST['currency_namez']=="USD(Cambodia)"){
	    $pramPost['curr'] = "USD";
	}
	else{
	    $pramPost['curr'] = $_POST['currency_namez'];
	}
	$pramPost['product_name']	= 'test product';// Any Thing
	$pramPost['remarks']	= "Checkout PayIn";
	$pramPost['customer_name']	=$_POST['customer_name']; // Customer Name
    $pramPost['customer_email'] =$_POST['customer_email'];
    $pramPost['customer_phone'] =$_POST['customer_phone'];
	$pramPost['merchant_code']	="winpipseightplus1005";
	
	if($_POST['currency_namez']=="USD(Cambodia)"){
	    $pramPost['customer_bank_code'] = "USD";
	}else{
	    $pramPost['customer_bank_code'] = $_POST['currency_namez'];
	}
	$pramPost['payin_request_id']	= $_POST['payin_request_id']; // Should be unique from Merchant
    $pramPost['transaction_id']	    = $_POST['payin_request_id'];
	$pramPost['payin_notify_url']	='https://payin.pay2rax.com/payin_response_url.php'; // Notify URL
	$pramPost['payin_success_url']	='https://payin.pay2rax.com/payin_response_url.php'; // Success CallBack URL
	$pramPost['payin_error_url']	='https://payin.pay2rax.com/payin_response_url.php';

	$curl_cookie="";
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($curl, CURLOPT_URL, $payin_url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($curl, CURLOPT_REFERER, $referer);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $pramPost);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($curl);
	if (curl_errno($curl)) {
		echo 'Error: ' . curl_error($curl); die;
	}
	curl_close($curl);
  	print_r($response); die;
}

function generateRandomString($length = 3) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {$randomString .= $characters[rand(0, $charactersLength - 1)];}
      return $randomString;
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
    <meta property="og:image" content="assets/images/logo.png">
    <meta name="format-detection" content="telephone=no">
    <title>Gtechz PSP – Payment Service Provider</title>
    <link rel="shortcut icon" type="image/png" href="assets/images/favicon.png">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/toastr.min.css">
    <script src="assets/js/toastr_jquery.min.js"></script>
    <script src="assets/js/toastr.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <style>
             .invalid-feedback{
                font-size: 14px;
            }
            .auth-form {
                padding: 20px 20px !important;
            }
            .form-control {
                height: 2.5rem !important;
            }
            .justify-content-center {
                margin-top: 120px;
            }
            .hidden{
                display: none;
            }
        </style>
    </head>
    <body>
        
        <div class="authincation h-100">
            <img src="https://pay2rax.com/wp-content/uploads/2024/06/cropped-Blue_Flat_Illustrated_Finance_Company_Logo_20240612_080918_0000-removebg-preview-100x80.png" height="50px">
            <div class="container h-100">
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-8">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h3 class="text-center mb-4"><b>Pay2rax Transfer or Deposit</b></h3>
                        <form class="form-horizontal" enctype="multipart-formdata" method="post" action="#">
							<div class="row mb-4">
                                <label for="Reference" class="col-md-3 form-label">Reference ID</label>
                                <div class="col-md-9">
								<input class="form-control" name="payin_request_id" id="payin_request_id" placeholder="Enter Reference ID" value="<?php echo $referenceNo; ?>" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Source" class="col-md-3 form-label">Source</label>
                                <div class="col-md-9">
									<input type="hidden" name="source_typez" id="source_typez"/>
										<select class="form-control select2-show-search form-select  text-dark" id="source_type" name="source_type" required data-placeholder="---" tabindex="-1" aria-hidden="true">
											<option value="">---</option>
											<!-- <option value="source1">source1</option> -->
											 <option value="Source1">Source1</option>
											 <option value="Source2">Source2</option>
											<!--<option value="source8">source8</option>
											<option value="source9">source9</option> -->
										</select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Currency" class="col-md-3 form-label">Currency</label>
                                <div class="col-md-9">
										<input type="hidden" name="currency_namez" id="currency_namez"/>
										<select class="form-control select2-show-search form-select  text-dark" id="currency" name="currency" required data-placeholder="---" tabindex="-1" aria-hidden="true">
											<option value="">---</option>
											<?php while($rowrv=mysqli_fetch_array($qrv)){ ?>
											<option value="<?php echo $rowrv['id']?>"><?php echo $rowrv['currency_name']?></option>
											<?php } ?>
										</select>
                                </div>
                            </div>
                            <!-- <div class="row mb-2">
                                <label for="Bank-Code" class="col-md-3 form-label">Bank Code</label>
                                <div class="col-md-9">
										<select class="form-control select2-show-search form-select  text-dark" id="bank_type" name="bank_type" required data-placeholder="---" tabindex="-1" aria-hidden="true">
											<option value="">---</option>
										</select>
                                </div>
                            </div> -->
                            <div class="row mb-4">
                                <label for="price" class="col-md-3 form-label">Amount</label>
                                <div class="col-md-9">
									<input class="form-control" required name="price" id="price" placeholder="Enter your Amount" value="100" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_name" class="col-md-3 form-label">Customer Name</label>
                                <div class="col-md-9">
								<input class="form-control" required name="customer_name" id="customer_name" placeholder="Enter Customer Name" type="text" value="">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_email" class="col-md-3 form-label">Customer Email</label>
                                <div class="col-md-9">
								<input class="form-control" required name="customer_email" id="customer_email" placeholder="Enter Customer email" type="email" value="" >
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_phone" class="col-md-3 form-label">Phone Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_phone" id="customer_phone" placeholder="Enter your phone">
                                </div>
                            </div>
                            <div class="row mb-4 hidden cardFiled">
                                <label for="card_number" class="col-md-3 form-label">Card Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="card_number" id="card_number" placeholder="Card number" maxlength='16'>
                                </div>
                            </div>
                            <div class="row mb-4 hidden cardFiled">
                                <label for="expiration" class="col-md-3 form-label">Expiration</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control expirationInput" name="expiration" id="expiration" maxlength='5' placeholder="MM/YY">
                                    <p class="expirationInput-warning text text-danger" style="display:none">Please fillup
                                    correct!</p>
                                </div>
                            </div>
                            <div class="row mb-4 hidden cardFiled">
                                <label for="cvv" class="col-md-3 form-label">CVC</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="cvv" id="cvv" placeholder="Enter your cvv" maxlength='3'>
                                </div>
                            </div>
                            <!-- <div class="row mb-4">
                                <label for="customer_addressline_1" class="col-md-3 form-label">Address Line 1</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_addressline_1" id="customer_addressline_1" placeholder="Enter your address" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_city" class="col-md-3 form-label">City</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_city" id="customer_city" placeholder="Enter your city" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_state" class="col-md-3 form-label">State</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_state" id="customer_state" placeholder="Enter your state" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_country" class="col-md-3 form-label">Country</label>
                                <div class="col-md-9">
                                    <select class="form-control " name="customer_country" id="customer_country" required>
                                            <option value="">--Select--</option>
                                            <option value="MY">Malaysia</option>
                                            <option value="TH">Thailand</option>
                                            <option value="VN">Vietnam</option>
                                            <option value="ID">Indonesia</option>
                                            <option value="US">United States</option>
                                            <option value="PH">Philippines</option>
                                            <option value="IN">India</option>
                                            <option value="KH">Cambodia</option>
                                            <option value="CN">China</option>
									</select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_zip" class="col-md-3 form-label">ZipCode</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_zip" id="customer_zip" placeholder="Enter your zipcode" required>
                                </div>
                            </div> -->
                            <div class="text-center">
                                <button type="submit" name="paynow" id="paynow" class="btn btn-primary btn-block">Pay Now</button>
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
    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/dlabnav-init.js"></script>
	<script>
	$(document).ready(function(){
    $('#currency').on('change', function(){
        var iso2 = $(this).val();
        var iso3 = $('#source_type').val();
        var currencyname=$("#currency option:selected");
        var sourcetype=$("#source_type option:selected");
        //$('#currency_namez').val(currencyname);
        $('#currency_namez').val(currencyname.text());
        $('#source_typez').val(sourcetype.text());
        // alert(iso2);
        if(iso2 && iso3){
            $.ajax({
                type:'POST',
                url:'getBankData.php',
                /*data:{'iso2_val='+iso2},*/
                data: {iso2_val:iso2, iso3_val:iso3},
                success:function(html){
                    //alert(html);
                    $('#bank_type').html(html);
                }
            }); 
        }else{
            $('#bank_type').html('<option value="">---</option>'); 
        }
    });

    $('#source_type').on('change', function(){
        var sourceval = $(this).val();
        // alert(sourceval);
        if (sourceval == 'Source2') {
            $('.cardFiled').removeClass("hidden");
        } else {
            $('.cardFiled').addClass("hidden");
        }
    });

     // On keyUp validate Expiry Moth and Year START
     $(document).ready(function(){
        $('.expirationInput').on('keyup', function(){
            var val = $(this).val();
            // Remove any non-numeric characters
            val = val.replace(/\D/g,'');
            if(val.length > 2){
                // If more than 2 characters, trim it
                val = val.slice(0,2) + '/' + val.slice(2);
            }
            else if (val.length === 2){
                // If exactly 2 characters, add "/"
                val = val + '/';
            }
            $(this).val(val);

            // Check if the entered date is in the future
            var today = new Date();
            var currentYear = today.getFullYear().toString().substr(-2);
            var currentMonth = today.getMonth() + 1;
            var enteredYear = parseInt(val.substr(3));
            var enteredMonth = parseInt(val.substr(0, 2));

            if (enteredYear < currentYear || (enteredYear == currentYear && enteredMonth < currentMonth)) {
                // Entered date is not in the future, clear the input
                $('.expirationInput-warning').css("display", "block");
                $('.expirationInput').addClass("inputerror");
                $('button.card-btn').prop('disabled', true);
                // alert("Please enter a future expiry date.");
            }else{
                $('.expirationInput-warning').css("display", "none");
                $('.expirationInput').removeClass("inputerror");
                $('button.card-btn').prop('disabled', false);
            }
        });
    });
    // On keyUp validate Expiry Moth and Year END

    // $('#bank_type').on('change', function(){
    //     var bankval = $(this).val();
    //     // alert(bankval);
    //     if(bankval=='QTSE'){
    //         var currency = $('#currency').val();
    //         // alert(currency);
    //         if(currency=='10'){     //for CNY
    //             $('#price').val('1000');
    //         }else if(currency=='5' || currency=='9'){   //for USD
    //             $('#price').val('5');
    //         }else if(currency=='2'){
    //             $('#price').val('100');              //for THB
    //         }else{
    //             $('#price').val('100.00');   
    //         }
    //     }else{
    //         $('#price').val('100.00');   
    //     }
    // });
});
</script>
    </body>
</html>

