<?php

session_start();

require 'function.php';

// API to edit sales person capacity

$_post = file_get_contents('php://input');

$encoded_data = json_decode($_post, true);

if(!empty($_post)){
    
    // decode JSON string 
    
    $encoded_data = json_decode($_post, true);
    
    $user_obj = new stdClass();
	$user_obj -> capacity		= $encoded_data['sales_person_capacity'];
	$user_obj -> id				= $encoded_data['id'];
	$user_obj -> capacity_month = $encoded_data['capacity_month'];
	$user_obj -> capacity_year	= $encoded_data['capacity_year'];
  
    
    // CALCULATION OF ADDON IN REMAINING CAPACITY
    
    $insert_sql = 'INSERT INTO sales_person_capacities SET'
			. ' sales_person_id = '.$user_obj -> id.' ,'
			. ' capacity = "'.$user_obj -> capacity.'" ,'
			. ' remaining_capacity = "'.$user_obj -> capacity.'" ,'
			. ' month = "'.$user_obj -> capacity_month.'" ,'
			. ' year = "'.$user_obj -> capacity_year .'" ,'
			. ' add_date = "'.date('Y-m-d').'"'
			. ' ON DUPLICATE KEY UPDATE remaining_capacity = ('.$user_obj -> capacity.' - capacity) + remaining_capacity,  capacity = "'.$user_obj -> capacity.'"' ;
	
	if(mysql_query($insert_sql)){
		echo json_encode(array('success' => 1,'message' => 'Sales person capacity has been updated successfully'), true); exit;
	}
    else{
		echo json_encode(array('success' => 0,'error' => mysql_error()), true); exit;
	}	
}
else{
	echo json_encode(array('success' => 0, 'error' => 'No Data recieved'), TRUE); exit;
}