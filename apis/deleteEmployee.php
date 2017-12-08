<?php
session_start();
require 'function.php';
require 'user_authentication.php';

$json = file_get_contents('php://input');

$data = json_decode($json, true);

if( isset($data['employee_id'])){
		
	// mark employee as delete 
	
	$query = 'UPDATE employees SET isDelete = 1 WHERE id = '.$data['employee_id']. ' LIMIT 1';
	
	if(mysql_query($query)){
		
		// success response 
		
		$response_payload = array('success' => 1, 'error' => '','message' => 'Employee deleted successfully'); 
		echo json_encode($response_payload,true); exit;
	}else{
		$response_payload = array('success' => 0, 'error' => '','message' => 'Employee not deleted successfully'); 
		echo json_encode($response_payload, true);
	}
}
else{
	$response_payload = array('success' => 0, 'error' => '', 'message' => 'Employee id is missing'); 
	echo json_encode($response_payload, true); exit;
}

