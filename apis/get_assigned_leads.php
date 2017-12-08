<?php

require_once 'function.php';

$post_data = filter_input_array(INPUT_POST);
$user_id = '';
$assigned_leads = array();

if(isset($post_data['user_id'])){
	
	$user_id = $post_data['user_id'];
	
	$select_leads = 'SELECT lead.lead_id, lead.enquiry_id, lead.lead_reassign_to, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate,'
		. ' CONCAT(emp.firstname," ",emp.lastname) as assigned_lead_employee_name'
		. ' FROM lead as lead'
		. ' LEFT JOIN employees as emp ON (lead.lead_reassign_to = emp.id)'
		. ' WHERE assignedTo = '.$user_id.' AND lead_reassign_to IS NOT NULL';

	$result = mysql_query($select_leads);
	
	if($result && mysql_num_rows($result) > 0){

		while($row = mysql_fetch_assoc($result)){
			array_push($assigned_leads, $row);
		}
		
		$success_response = array('success' => 1 , 'data' => $assigned_leads);
		echo json_encode($success_response,true); exit;
	}else{
		$success_response = array('success' => 0 , 'data' => array(), 'message' => 'No Leads Found');
		echo json_encode($success_response,true); exit;
	}

}else{
	
	$error_response = array('success' => 0 , 'data' => array(),'message' => 'No Leads Found');
	echo json_encode($error_response,true);
}