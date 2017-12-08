<?php
session_start();
require 'function.php';

$data = json_decode(file_get_contents('php://input'),true);

if(!empty($data) && $data['user_id'] != ''){
	
	$message_data = array();
	
	$message_data['message_category']	= $data['message_data']['category'];
	$message_data['event']				= $data['message_data']['event'];
	$message_data['message']			= $data['message_data']['message_text'];
	
	if(!empty($data['message_data']['numbers'])){	
		$message_data['default_numbers'] = implode(',', $data['message_data']['numbers']);
	}else{
        $message_data['default_numbers'] = '';
    }
	

	if($data['mode'] == 'add'){
		
		$message_data['template_added_by'] = $data['user_id'];
		$message_data['template_add_datetime'] = date('Y-m-d H:i:s');
		
		$sql = 'INSERT INTO `message_templates` SET ';
		
		foreach($message_data as $column => $value){
			
			$sql .= $column ." = '".$value."' ,";
		}
		
		if(mysql_query(rtrim($sql,' ,'))){
			
			$response = array('success' => 1, 'message' => 'Message template has been added successfully');
			echo json_encode($response,true); exit;
		}else{
			
			$error = mysql_error();
			$response = array('success' => 0, 'message' => $error);
			echo json_encode($response, true); exit;
		}
		
	}else{
		
		$message_data['template_edited_by'] = $data['user_id'];
		$template_id						= $data['message_data']['template_id'];
	
		$sql	= "UPDATE `message_templates` SET ";
		
		foreach($message_data as $column => $value){
			$sql .= $column ." = '".$value."' ,";
		}
		
		$sql = rtrim($sql,', '). ' WHERE template_id = '. $template_id;
        
		if(mysql_query(rtrim($sql,' ,'))){
			
			$response = array('success' => 1, 'message' => 'Message template has been updated successfully');
			echo json_encode($response,true); exit;
		}else{
			
			$error = mysql_error();
			$response = array('success' => 0, 'message' => $error);
			echo json_encode($response, true); exit;
		}
	}
}
else{
	$response = array('success' => 0, 'message' => 'Message Template could not be added at this time. Data not available');
	echo json_encode($response,true); exit;
}
