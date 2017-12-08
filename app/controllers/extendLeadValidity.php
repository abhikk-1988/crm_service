<?php
session_start();
require_once 'function.php';
require_once 'user_authentication.php';

if (!$is_authenticate) {
	echo unauthorizedResponse();
	exit;
}
$data		= file_get_contents("php://input");
$data 		= json_decode($data);

$user_id			= $_SESSION['currentUser']['id']; 

die($user_id);

if($data->enquiry_id && $data->sp_id){
	
	$leads_query 	= 'SELECT * FROM lead_extend_date WHERE enquiry_id ="'.$data->enquiry_id.'" AND sp_id="'.$data->sp_id.'" LIMIT 1';

	$result = mysql_query($leads_query);

	if(mysql_num_rows($result) > 0){

		$row = mysql_fetch_assoc($result);

		$no_of_extension = $row['no_of_extension']+1;
		
		$updated = mysql_query("UPDATE lead_extend_date SET extend_by_user_id='$useri_id', extend_to_user_id='".$data->sp_id."' , no_of_extension= '$no_of_extension', status= 'extended' WHERE enquiry_id ='".$data->enquiry_id."AND sp_id='".$data->sp_id);

		if($updated){

			$success = 1;
			
			$http_status_code = 401;

		}else{

			$success = 0;
			
			$http_status_code = 401;
		}
		
	}else{

		$success = 0;
			
		$http_status_code = 401;
	}
	
	
}else{

	$success = 0;
			
	$http_status_code = 401;
}

$resultArr = array('success' => $success,'http_status_code' =>$http_status_code);

$utf_result  	= array_utf8_encode($resultArr);

$json_result 	= json_encode($utf_result,true);

echo $json_result; 

exit;