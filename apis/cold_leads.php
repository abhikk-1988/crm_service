<?php
/**
 * API to get not intrested or cold call leads 
 */

require_once 'function.php';

$json_decoded_array = json_decode(file_get_contents('php://input'),true);

$user_id = '';

if(!empty($json_decoded_array)){
	
	$user_id = $json_decoded_array['user_id'];
}

$cold_leads				= array();
$parent_status_id			= '';
$child_status_id			= array();

$select_lead_status		= ' SELECT id, status_title '
		. ' FROM `disposition_status_substatus_master` '
		. ' WHERE status_slug  IN ("technical_issue","not_interested") ';

$result				= mysql_query($select_lead_status);

if($result){
	
	$lead_status_data	=	mysql_fetch_object($result);
	
	$parent_status_id	=	$lead_status_data -> id;
	
	$child_status		=	'SELECT id, sub_status_title, status_slug FROM disposition_status_substatus_master WHERE parent_status = '.$lead_status_data->id.'';
	
	$child_status_result	=	mysql_query($child_status);
	
	if($child_status_result){
		
		while($row = mysql_fetch_assoc($child_status_result)){
			
			array_push($child_status_id, $row['id']);
		}
	}
}
