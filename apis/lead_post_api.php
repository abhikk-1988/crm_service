<?php
$ch = curl_init();                   
$url  = "http://52.77.73.171/CRM/apis/save_enquiry.php"; 
//$url = "http://localhost/test.crm/apis/save_enquiry.php"; 
$veryString			=   "bmh_enquery";
$postDataval		=	$_REQUEST; //send a copy of enquery data.
$postData   		= 	json_encode(array('key'=>$veryString,'postEnqueryData'=>$postDataval));
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, true);  // Tell cURL you want to post something
curl_setopt($ch, CURLOPT_POSTFIELDS, 'enquery='.$postData); // Define what you want to post
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the output in string format
$output = curl_exec ($ch); // Execute
curl_close ($ch); // Close cURL handle
var_dump($output);

?>
