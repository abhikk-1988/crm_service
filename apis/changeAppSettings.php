<?php
session_start();

require 'function.php';
require 'user_authentication.php';
    
if(!$is_authenticate){
	echo unauthorizedResponse(); exit; 
}

$data = json_decode(file_get_contents('php://input'),true);

if(isset($data['setting']) && !empty($data['setting'])){
	
	// Change round robin process state 
	
	$setting	= $data['setting'];
	$status		= (int) $data['state'];
	
	if($status == 0){
		$status = '1';
	}else{
		$status = '0';
	}
	
	$sql = 'UPDATE crm_settings SET '.$setting.' = "'.$status.'" LIMIT 1';
	 
	if(mysql_query($sql)){
		
		// success response
		
		$success = array('success' => 1);
		echo json_encode($success,true); exit;
	}
	else{
		
		// error response 
		$error = array('success' => 0);
		echo json_encode($error,true); exit;
	}
}else{
	
	// error response 
	$error = array('success' => 0);
	echo json_encode($error,true); exit;
}


