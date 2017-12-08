<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
// Make a request to remote server for cities list 
$cities = file_get_contents('https://bookmyhouse.com/staging_api/getcities.php'); 
echo $cities; exit;
