<?php
session_start();
require 'function.php';

$data = json_decode(file_get_contents('php://input'),true);

if(isset($data['template_data']) && isset($data['user_id'])){
	
	$mode  = $data['mode'];
	
	$template_data = array();
	
	$template_data['subject']				= $data['template_data']['subject'];
	$template_data['email_category']		= $data['template_data']['category'];
	$template_data['event']					= $data['template_data']['event'];
	$template_data['to_users']				= mysql_real_escape_string(json_encode($data['template_data']['toUsers'],true));
	$template_data['cc_users']				= mysql_real_escape_string(json_encode($data['template_data']['ccUsers'],true));
	$template_data['bcc_users']				= mysql_real_escape_string(json_encode($data['template_data']['bccUsers'],true));
	$template_data['message_body']			= mysql_real_escape_string($data['template_data']['message']);
	
	
	if($mode === 'add'){
		
		$template_data['template_added_by']		= $data['user_id'];
		$template_data['template_add_datetime'] = date('Y-m-d H:i:s');
		
		$insert_template_sql = "INSERT INTO `email_templates` SET ";
		
		foreach($template_data as $column => $value){
			
			$insert_template_sql .= $column ." = '".$value."' ,";
		}
	
		if(mysql_query(rtrim($insert_template_sql,' ,'))){
			
			$response = array('success' => 1, 'message' => 'Email template has been added successfully');
			echo json_encode($response,true); exit;
		}else{
			
			$error = mysql_error();
			$response = array('success' => 0, 'message' => $error);
			echo json_encode($response, true); exit;
		}
		
	}else{
		
		$template_data['template_edited_by']		= $data['user_id'];
		
		// code for edit template 
		$template_id			= $data['template_data']['template_id'];
		
		$insert_template_sql	= "UPDATE `email_templates` SET ";
		
		foreach($template_data as $column => $value){
			$insert_template_sql .= $column ." = '".$value."' ,";
		}
		
		$insert_template_sql = rtrim($insert_template_sql,', '). ' WHERE template_id = '. $template_id;		
		
		if(mysql_query(rtrim($insert_template_sql,' ,'))){
			
			$response = array('success' => 1, 'message' => 'Email template has been updated successfully');
			echo json_encode($response,true); exit;
		}else{
			
			$error = mysql_error();
			$response = array('success' => 0, 'message' => $error);
			echo json_encode($response, true); exit;
		}
	}	
}
else{
	$response = array('success' => 0, 'message' => 'Email Template could not be added at this time. Data not available');
	echo json_encode($response,true); exit;
}