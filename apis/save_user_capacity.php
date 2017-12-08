<?php

session_start();
require 'function.php';

$data			= filter_input_array(INPUT_POST);
$user_capacity	= new stdClass();

$error = array();

if(isset($data) && !empty($data['user'])){
	
	$user_capacity -> userId			= $data['user'];
	$user_capacity -> capacity_month	= $data['capacity_month'];
	$user_capacity -> capacity_year		= $data['capacity_year'];
	$user_capacity -> total_capacity	= $data['total_capacity'];
	$user_capacity -> capacity_added_by = $data['login_user_id'];
	
	// check if this user has already han an entry for set month and year 
	
	$has_record = 'SELECT id FROM `capacity_master` WHERE userId = '.$user_capacity -> userId.' AND capacity_month = '.$user_capacity-> capacity_month.' AND capacity_year = "'.$user_capacity -> capacity_year.'"';
	
	$has_record_result = mysql_query($has_record);
	
	if($has_record_result && mysql_num_rows($has_record_result) > 0){
		
		 // Prevent user to add new records for current month and year 
		
		echo json_encode(array('success' => 0, 'error' => 'User have already '));
	}
	
	
	$insert_sql = 'INSERT INTO capacity_master (userId, pId, pName, capacity, remaining_capacity, capacity_month, capacity_year, addDate, capacity_added_by, capacity_edited_by) VALUES ';
	
	if(!empty($data['project_wise_capacity'])){

		$values = '';
		
		foreach ($data['project_wise_capacity'] as $key => $val){
			
			$user_capacity -> pId					= $val['project_id'];
			$user_capacity -> pName					= $val['project_name'];
			$user_capacity -> capacity				= $val['capacity'];
			$user_capacity -> remaining_capacity	= $val['capacity'];
			$user_capacity -> addDate				= date('Y-m-d');
			
			$insert_sql .= '(';
			$insert_sql .= $user_capacity -> userId .','.$user_capacity -> pId .',"'.$user_capacity -> pName .'",'. $user_capacity -> capacity .','.$user_capacity -> remaining_capacity.','. $user_capacity ->capacity_month.',"'.$user_capacity ->capacity_year.'","'.$user_capacity -> addDate.'",'.$user_capacity -> capacity_added_by.','. $user_capacity -> capacity_added_by;
			$insert_sql .= '),';
		}
		
		if(mysql_query(rtrim($insert_sql,','))){
			
			// save total capacity of user in employee table 
			
			$update_total_capacity = 'UPDATE employees SET `total_capacity` = '.$user_capacity -> total_capacity.' WHERE id = '.$user_capacity -> userId.' LIMIT 1';
			
			mysql_query($update_total_capacity);
			
			echo json_encode(array('success' => 1, 'message' => 'User capacity has been saved successfully'), true); exit;
			
		}else{
			echo json_encode(array('success'=> 0, 'error' => mysql_error()), true); exit;
		}
		
	}
}

