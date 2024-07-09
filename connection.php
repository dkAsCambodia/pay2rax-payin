<?php

// $host     = "localhost";
// $user     = "root";
// $password = "";
// $database = "4th_party_db";

$host     = "localhost";
$user     = "implogix";
$password = "password";
$database = "payin_pay2rax_db";

$link   = mysqli_connect($host, $user, $password, $database);
//if (mysqli_connect_errno($link)) {
if (!$link) {
   echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>