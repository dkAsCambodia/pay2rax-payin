<?php
session_start();
ob_start();
include("../connection.php");
$selrev = "SELECT * FROM `gtech_currencies`";
$qrv=mysqli_query($link,$selrev);
$referenceNo="GZTRN".time().generateRandomString(3);
if(array_key_exists('paynow',$_POST)){
	payout();
}
function payout(){
	$baseurl = "https://payin.implogix.com";	 	
	$vstore_id	="GZ-108"; // For Gtechz Official
	if($_POST['source_type']=='source1'){
		$payout_url=$baseurl."/api/payout/V1/";
	}else{
		$payout_url=$baseurl."/api/payout/V5/";
	}
	$protocol	= isset($_SERVER["HTTPS"])?'https://':'http://';
	$referer	= $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
	$pramPost=array();
	$pramPost['client_ip'] =(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
	$pramPost["payout_api_token"] = "noadf49CKEYSWsBFHZQ0Oe2MPIb1T5"; // For Gtechz Official
	$pramPost['vstore_id']	=$vstore_id;
	$pramPost['action']		='payout';
	$pramPost['source']		='payout-Encode';
	$pramPost['source_url']	=$referer;
	$pramPost['source_type'] =$_POST['source_type'];
	$pramPost['price'] = $_POST['price'];
	if($_POST['currency_namez']=="USD(Cambodia)"){
	    $pramPost['curr'] = "USD";
	}else{
	    $pramPost['curr'] = $_POST['currency_namez'];
	}
	$pramPost['product_name']	= 'test product';// Any Thing
	$pramPost['remarks']	= "Payout for gtechz";
	$pramPost['narration']	= "Payout narration";
	$pramPost['customer_name']	=$_POST['customer_name']; // Customer Name
	$pramPost['customer_email']	=$_POST['customer_email'];
	$pramPost['customer_addressline_1']	=$_POST['customer_addressline_1']; // Customer Address Line 1
	$pramPost['customer_city']		=$_POST['customer_city']; // Customer City
	$pramPost['customer_state']		=$_POST['customer_state']; // Customer State
	$pramPost['customer_country']	=$_POST['customer_country']; // Customer Country
	$pramPost['customer_zip']		=$_POST['customer_zip']; // Customer Zipcode
	$pramPost['customer_phone']		=$_POST['customer_phone']; // Customer 787602
	$pramPost['customer_bank_name']	=$_POST['bank_type']; // Customer 787602
	if($_POST['currency_namez']=="USD(Cambodia)"){
	    $pramPost['customer_bank_code'] = "USD";
	}else{
	    $pramPost['customer_bank_code'] = $_POST['currency_namez'];
	}
	$pramPost['customer_account_number']	= $_POST['customer_account_number'];  // customer BankAccount Number 
	$pramPost['payout_request_id']	= $_POST['payout_request_id']; // Should be unique from Merchant
	$pramPost['payout_membercode']	= $vstore_id.time().generateRandomString(3); // Should be unique from Merchant
	$pramPost['payout_response_url']	='https://payin.implogix.com/payout/payout_response_url.php'; // Success CallBack URL
	// echo "<pre>"; print_r($pramPost); die;

	$curl_cookie="";
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($curl, CURLOPT_URL, $payout_url);
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
        <meta property="og:image" content="https://payin.implogix.com/assets/images/logo.png">
        <meta name="format-detection" content="telephone=no">
        <title>Gtechz PSP – Payment Service Provider</title>
        <link rel="shortcut icon" type="image/png" href="https://payin.implogix.com/assets/images/favicon.png">
        <link href="https://payin.implogix.com/assets/css/style.css" rel="stylesheet">
        <link rel="stylesheet" href="https://payin.implogix.com/assets/css/toastr.min.css">
        <script src="https://payin.implogix.com/assets/js/toastr_jquery.min.js"></script>
        <script src="https://payin.implogix.com/assets/js/toastr.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    </head>
    <body>
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
        </style>
        <div class="authincation h-100">
            <div class="container h-100">
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-8"><br/><br/>
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h3 class="text-center mb-4"><b>Merchant - Payout</b></h3>
                        <form class="form-horizontal" enctype="multipart-formdata" method="post" action="#">
							<div class="row mb-2">
                                <label for="Reference" class="col-md-3 form-label">Reference ID</label>
                                <div class="col-md-9">
								<input class="form-control" name="payout_request_id" id="payout_request_id" placeholder="Enter Reference ID" value="<?php echo $referenceNo; ?>" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label for="Source" class="col-md-3 form-label">Source</label>
                                <div class="col-md-9">
									<input type="hidden" name="source_typez" id="source_typez"/>
										<select class="form-control select2-show-search form-select  text-dark" id="source_type" name="source_type" required data-placeholder="---" tabindex="-1" aria-hidden="true">
											<option value="">---</option>
											<option value="source1">source1</option>
											<option value="source2">source2</option>
											<option value="source7">source7</option>
											<option value="source8">source8</option>
											<option value="source9">source9</option>
										</select>
                                </div>
                            </div>
                            <div class="row mb-2">
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
                            <div class="row mb-2">
                                <label for="Bank-Code" class="col-md-3 form-label">Bank Code</label>
                                <div class="col-md-9">
										<select class="form-control select2-show-search form-select  text-dark" id="bank_type" name="bank_type" required data-placeholder="---" tabindex="-1" aria-hidden="true">
											<option value="">---</option>
										</select>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label for="price" class="col-md-3 form-label">Amount</label>
                                <div class="col-md-9">
									<input class="form-control" required name="price" id="price" placeholder="Enter your Amount" value="100.00" type="text">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label for="customer_name" class="col-md-3 form-label">Customer Name</label>
                                <div class="col-md-9">
								<input class="form-control" required name="customer_name" id="customer_name" placeholder="Enter Customer Name" type="text" value="">
                                </div>
                            </div>
							<div class="row mb-2">
								<label for="customer_account_number" class="col-md-3 form-label">Bank Account Number</label>
								<div class="col-md-9">
									<input class="form-control" required name="customer_account_number" id="customer_account_number" placeholder="Enter Bank Account Number" type="text" value="">
								</div>
							</div>
                            <div class="row mb-2">
                                <label for="customer_email" class="col-md-3 form-label">Customer Email</label>
                                <div class="col-md-9">
								<input class="form-control" required name="customer_email" id="customer_email" placeholder="Enter Customer email" type="email" value="" >
                                </div>
                            </div>
							<div class="row mb-2">
                                <label for="customer_addressline_1" class="col-md-3 form-label">Address Line 1</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_addressline_1" id="customer_addressline_1" placeholder="Enter your address" required>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label for="customer_city" class="col-md-3 form-label">City</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_city" id="customer_city" placeholder="Enter your city" required>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label for="customer_state" class="col-md-3 form-label">State</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_state" id="customer_state" placeholder="Enter your state" required>
                                </div>
                            </div>
                            <div class="row mb-2">
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
                            <div class="row mb-2">
                                <label for="customer_zip" class="col-md-3 form-label">ZipCode</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_zip" id="customer_zip" placeholder="Enter your zipcode" required>
                                </div>
                            </div>
							<div class="row mb-2">
                                <label for="customer_phone" class="col-md-3 form-label">Phone Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="customer_phone" id="customer_phone" placeholder="Enter your phone">
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
    <script src="https://payin.implogix.com/assets/vendor/global/global.min.js"></script>
    <script src="https://payin.implogix.com/assets/js/custom.min.js"></script>
    <script src="https://payin.implogix.com/assets/js/dlabnav-init.js"></script>
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
                url:'../getBankData.php',
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

    $('#bank_type').on('change', function(){
        var bankval = $(this).val();
        // alert(bankval);
        if(bankval=='QTSE'){
            var currency = $('#currency').val();
            // alert(currency);
            if(currency=='10'){     //for CNY
                $('#price').val('1000');
            }else if(currency=='5' || currency=='9'){   //for USD
                $('#price').val('5');
            }else if(currency=='2'){
                $('#price').val('100');              //for THB
            }else{
                $('#price').val('100.00');   
            }
        }else{
            $('#price').val('100.00');   
        }
    });
});
</script>
    </body>
</html>

