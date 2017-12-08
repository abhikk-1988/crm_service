<?php

session_start();

/**
 * includes function.php file which iteself includes db_connection and constant file  
 */
require_once 'function.php';

$data = file_get_contents('php://input'); // recieves a json file and we need to convert back to php arra

$data_in_arr = json_decode($data,true);

$assigned_status = array();

if(!empty($data_in_arr)){
	
	foreach($data_in_arr['status_data'] as $key => $val){
		
		$temp = array();
		
		if($val['assigned']){
			
			$temp[$val['id']] = array();
			
			foreach($val['childs'] as $k => $child){
				
				if($child['assigned']){
					array_push($temp[$val['id']], $child['id']);
				}
			}
		}
		
		if(!empty($temp)){
			array_push($assigned_status, $temp);
		}
	}
}

// update in DB 

$update_sql = 'UPDATE employees '
		. ' SET `assigned_disposition_status_json` = "'.  mysql_real_escape_string(json_encode($assigned_status,true)).'" '
		. ' WHERE id = '.$data_in_arr['employee_id'].''; 

if(mysql_query($update_sql)){
	
	$success_response = array('success' => 1,  'message' => 'Status(s) has been updated successfully');
	echo json_encode($success_response,true); exit; 
}
else{
	$error_response = array('success' => 0,  'message' => 'Status(s) couldn\'t  be updated at this time');
	echo json_encode($error_response,true);
}