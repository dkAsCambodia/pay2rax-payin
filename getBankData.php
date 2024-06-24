<?php 
// Include the database config file 
include('connection.php'); 
 
if(!empty($_POST['iso2_val']) &&  !empty($_POST['iso3_val']) ){ 
    // Fetch state data based on the specific country 
    $getiso2 = $_POST['iso2_val'];
    $getiso3 = $_POST['iso3_val'];
     
/*    echo $Singlequery = "SELECT * FROM m_bankcodes WHERE currency_id = '".$getiso2."' AND flag = 1"; 
    $GetIdResult = $link->query($Singlequery); 
    $singlerow = $GetIdResult->fetch_assoc();
	
	$GetCountryID = $singlerow['id'];*/
	
	// Fetch state data based on the specific country
     echo $query = "SELECT * FROM gtech_bankcodes WHERE currency_id = '".$getiso2."' AND source_name= '".$getiso3."'  AND status = 1 ORDER BY bank_name ASC"; 
    $result = $link->query($query); 
     
    // Generate HTML of state options list 
    if($result->num_rows > 0){ 
        echo '<option value="">Select Bank</option>'; 
        while($row = $result->fetch_assoc()){  
            echo '<option value="'.$row['bank_code'].'">'.$row['bank_name'].'</option>'; 
        } 
    }else{ 
        echo '<option value="">Bank not available</option>'; 
    }
} 
?>