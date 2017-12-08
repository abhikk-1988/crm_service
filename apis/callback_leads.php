<?php
require_once 'function.php';

$user_id = '';

$post_data = json_decode(file_get_contents('php://input'),true);

if(isset($post_data) && !empty($post_data)){
	
	$user_id = $post_data['user_id'];
}

$lead_status_type_slug		= 'future_references';
$future_reference_leads		= array();
$parent_status_id			= '';
$child_status_id			= '';

$select_lead_status		= 'SELECT id, status_title FROM `disposition_status_substatus_master` WHERE status_slug = "'.$lead_status_type_slug.'" LIMIT 1';

$result			= mysql_query($select_lead_status);

if($result){
	
	$lead_status_data	= mysql_fetch_object($result);
	
	$parent_status_id = $lead_status_data -> id;
	
	$child_status		= 'SELECT id, sub_status_title, status_slug FROM disposition_status_substatus_master WHERE parent_status = '.$lead_status_data->id.'';
	
	$child_status_result = mysql_query($child_status);
	
	if($child_status_result){
		
		while($row = mysql_fetch_assoc($child_status_result)){
		
			if($row['status_slug'] === 'call_back') {
				
				$child_status_id = $row['id'];
			}
		}
	}
	
}

if(isset($parent_status_id) && $child_status_id){
	
	// Fetch leads 
	
	$select_leads = 'SELECT '
			. '	lead_id, enquiry_id, assignedTo, leadAddDate, customerMobile, customerLandline, customerName, '
			. '	customerEmail, customerCity, future_followup_date, future_followup_time, disposition_status_id, disposition_sub_status_id '
			. ' FROM '
			. '	`lead`'
			. ' WHERE assignedTo = '.$user_id.' AND disposition_status_id = '.$parent_status_id.' AND disposition_sub_status_id = '.$child_status_id.'';
			
	$leads_result = mysql_query($select_leads);
	
	if($leads_result && mysql_num_rows($leads_result) > 0){
		
		while($row = mysql_fetch_assoc($leads_result)){
			
			array_push($future_reference_leads, $row);
		}
		
	}
}

echo json_encode($future_reference_leads, true);