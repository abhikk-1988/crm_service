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
	
	// Check if any proejcts to remove 
	if(isset($data['removed_projects']) && !empty($data['removed_projects'])){
		
		$projects_to_remove = implode(',', $data['removed_projects']);
		
		// DELETE PROJECTS FROM user capacity
		
		mysql_query('DELETE FROM capacity_master WHERE pId IN ('.$projects_to_remove.') AND userId = '.$user_capacity -> userId.' AND capacity_month = "'.$user_capacity -> capacity_month.'" AND capacity_year = "'.$user_capacity -> capacity_year.'"');
	}
	
	$update_sql = 'UPDATE capacity_master SET ';
	
	if(!empty($data['project_wise_capacity'])){

		foreach ($data['project_wise_capacity'] as $key => $val){
			
			$user_capacity -> pId		= $val['project_id'];
			$user_capacity -> pName		= $val['project_name'];
			$user_capacity -> capacity	= $val['capacity'];
			$user_capacity -> remaining_capacity = $val['capacity'];
			$user_capacity -> addDate	= date('Y-m-d');
			
			$sql  = 'INSERT INTO `capacity_master` (userId, pId, pName, capacity, remaining_capacity, capacity_month,capacity_year,addDate, capacity_added_by, capacity_edited_by)';
			$sql .= ' VALUES ';
			$sql .= ' ( ';
			$sql .=  $user_capacity -> userId .','.$user_capacity -> pId .',"'.$user_capacity -> pName .'",'. $user_capacity -> capacity .','.$user_capacity -> capacity.','. $user_capacity ->capacity_month.',"'.$user_capacity ->capacity_year.'","'.$user_capacity -> addDate.'",'. $user_capacity -> capacity_added_by.','. $user_capacity -> capacity_added_by ;
			$sql .= ' )';		
			$sql .= ' ON DUPLICATE KEY UPDATE remaining_capacity = remaining_capacity + ('.$user_capacity -> capacity.' - capacity), capacity = '.$user_capacity -> capacity.', capacity_edited_by = '. $user_capacity -> capacity_added_by;
			
			//echo $sql; exit;
			
			mysql_query($sql);
		}
		
		
//		exit;
		
		$update_total_capacity = 'UPDATE employees SET `total_capacity` = '.$user_capacity -> total_capacity.' WHERE id = '.$user_capacity -> userId.' LIMIT 1';
			
		mysql_query($update_total_capacity);
		
		echo json_encode(array('success' => 1, 'message' => 'User capacity has been updated successfully'), true); exit;
		
	}else{
		echo json_encode(array('success' => 0, 'error' => 'No Data recieved'), true); exit;
	}
}else{
		echo json_encode(array('success' => 0, 'error' => 'No Data recieved'), true); exit;
}

