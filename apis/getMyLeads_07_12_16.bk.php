<?php
/* API: To get leads of an individual user */

session_start();

require_once 'function.php';
require_once 'user_authentication.php';
    
if(!$is_authenticate){
	echo unauthorizedResponse(); exit; 
}

$data = array();
$post_data = filter_input_array(INPUT_POST);

$lead_status_type_slug		= array('meeting','site_visit');
$lead_status_ids			= '';
$select_lead_status			= 'SELECT '
		. '				id, status_title '
		. '				FROM '
		. '					`disposition_status_substatus_master` '
		. '				WHERE status_slug IN ("'.implode('","',$lead_status_type_slug).'") ';

$lead_result			= mysql_query($select_lead_status);

if($lead_result && mysql_num_rows($lead_result) > 0){
	
	$temp = []; // Temporary lead_status_ids array
	while($lead = mysql_fetch_assoc($lead_result)){
		array_push($temp,$lead['id']);
	}
	
	$lead_status_ids = implode(',', $temp);
}

if(isset($post_data) && $post_data['user_id']){
	
	$user_id			= $post_data['user_id'];
	$designation_id		= $post_data['designation_id'];
	
	$leads = "SELECT lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate, lead.is_cold_call, lead.assignedTo, lead.lead_reassign_to, emp.firstname, emp.lastname"
			. " FROM lead as lead"
			. " LEFT JOIN employees as emp ON (lead.lead_reassign_to = emp.id) "
			. "WHERE lead.assignedTo = ".$user_id." AND lead.lead_reassign_to IS NULL AND lead.disposition_status_id IN (".$lead_status_ids.")";
	
	echo $leads; exit;
	
	$result = mysql_query($leads);
	
	
	$leads_data = array();
	
	if($result){
		
		if(mysql_num_rows($result) > 0){
			
			while($row = mysql_fetch_assoc($result)){
				array_push($leads_data , $row);
			}
		}
	}
	
	$response = array(
		'success' => 1,
		'http_status_code' => 200,
		'data' => $leads_data,
	);
	
	echo json_encode($response,true); exit; 
}else{
	
	$error_response = array(
		'success' => 0,
		'http_status_code' => 401,
		'message' => 'Unauthorized access'
 	);
	
	echo json_encode($error_response,true); exit; 
}