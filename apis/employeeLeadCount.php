<?php
session_start();
require 'function.php';
require 'user_authentication.php';

$json = file_get_contents('php://input');

$data = json_decode($json, true);

if( isset($data['employee_id']) ){
		
	// count no of lead of empl_id
	
	$emp_id = $data['employee_id']; 
	
	$query = "SELECT count(enquiry_id) AS total FROM lead WHERE (lead_added_by_user='$emp_id' OR lead_assigned_to_asm='$emp_id' OR lead_assigned_to_sp='$emp_id' OR reassign_user_id = '$emp_id')";
	
	$resource = mysql_query($query);
	
	if(mysql_num_rows($resource) > 0){
		
		$result = mysql_fetch_assoc($resource);
		
		$response_payload = array('success' => 1, 'count' => $result['total']); 
		
		echo json_encode($response_payload,true); exit;
	}else{
	
		$response_payload = array('success' => 1, 'count' => 0); 
		
		echo json_encode($response_payload,true); exit;
	}
}else{

	$response_payload = array('success' => 0, 'count' => 0); 
		
	echo json_encode($response_payload,true); exit;
}

?>