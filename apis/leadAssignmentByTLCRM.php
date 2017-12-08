<?php

require_once 'function.php';

$post_data = filter_input_array(INPUT_POST);

if(isset($post_data)){
	

$assign_lead = 'UPDATE `lead` '
		. ' SET lead_reassign_to = '.$post_data['reassign_to'].' '
		. ' WHERE enquiry_id = '.$post_data['enquiry_id'].'';

if(mysql_query($assign_lead)){
	
	// log lead assignment in lead history table 
	
	// Fields 
	/* 
		lead_number 
		enquiry_id
		details 
		employee_id
		type
	*/
	
	$employee						= $post_data['lead_assignee'];
	$lead_assignee_name				= getEmployeeName($employee);
	$lead_assigned_to					= $post_data['reassign_to'];
	$lead_assigned_to_employee_name		= getEmployeeName($lead_assigned_to);
	$type							= 'edit';
	$enquiry_id = $post_data['enquiry_id'];
	$lead_number = NULL;
	$details = $post_data['log_message'] . ' ' . $lead_assigned_to_employee_name .' by '. $lead_assignee_name.' on '. date('Y-m-d');
	
	if($lead_number !== NULL){
		$save_lead_history = 'INSERT INTO `lead_history` (lead_number, enquiry_id, details, employee_id, type)'
			. ' VALUES('.$lead_number.', '.$enquiry_id.', "'.$details.'",'.$employee.',"'.$type.'")';
	}else{
		$save_lead_history = 'INSERT INTO `lead_history` (enquiry_id, details, employee_id, type)'
			. ' VALUES('.$enquiry_id.', "'.$details.'",'.$employee.',"'.$type.'")';
	}
	
	mysql_query($save_lead_history);
	
	
	// Response
	$response = array(
		'success' => 1,
		'message' => 'Lead assigned successfully'
	);
	echo json_encode($response,true); exit;
}else{
	
	// Error response
	$error_response = array(
		'success' => 0,
		'message' => 'Lead Couldn\'t be assigned.'
	);
	
	echo json_encode($error_response,true); exit;
}

}else{
	$error_response = array('success' => 0, 'message' => 'Unauthorized Access');
	echo json_encode($error_response,true); exit; 
}