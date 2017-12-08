<?php
session_start();
require_once 'function.php';
require_once 'user_authentication.php';

$enquiry_id = $_GET['enquiry_id'];

$lead_id = $_GET['lead_id'];

$where_lead_id = '';

$edit_data = array();

$lead_details = array();

$client_projects = array();

$client_preference = array();

$lead_source = array();

$disposition_source = array();

if($lead_id !== null && $lead_id != 'null'){
	$where_lead_id = ' AND lead_id = "'.$lead_id.'"' ;
}


// Client basic details 

$client_info = 'SELECT * FROM lead WHERE enquiry_id = '.$enquiry_id.' '.$where_lead_id;

$result = mysql_query($client_info);

if($result && mysql_num_rows($result) > 0){   
		$lead_details = mysql_fetch_assoc($result);	
		$edit_data['lead'] = array();

		foreach($lead_details as $column => $value){

			if($column == 'lead_added_by_user'){
				$crm_agent_name = getEmployeeName($value);
				$edit_data['lead'][$column] = $value;
				$edit_data['lead']['crm_agent_name'] = $crm_agent_name;
			}else{
				$edit_data['lead'][$column] = $value;
			}
		}
		// array_push($edit_data['lead'],$client_details);
}

// Client suggested projects 

$select_client_projects = 'SELECT * FROM lead_enquiry_projects WHERE enquiry_id = '.$enquiry_id;

$project_result = mysql_query($select_client_projects);

$edit_data['enquiry_projects'] = array();

if($project_result){
   
	if(mysql_num_rows($project_result)){
		   
		while($row = mysql_fetch_assoc($project_result)){

			$temp = array();
			
			$temp['id'] 			= $row['id'];
			$temp['project_id'] 	= $row['project_id'];
			$temp['project_url'] 	= $row['project_url'];
			$temp['project_name'] 	= $row['project_name'];
			$temp['project_city'] 	= $row['project_city'];
			
			array_push($edit_data['enquiry_projects'], $temp);
		}
	}
}

echo json_encode(array('success' => 1, 'http_status_code' => 200, 'data' => $edit_data),true); exit;