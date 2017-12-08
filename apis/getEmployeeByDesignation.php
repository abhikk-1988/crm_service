<?php

require_once 'db_connection.php';
require_once 'function.php';

$post_data	= filter_input_array(INPUT_POST);

$designation_id		= '';
$slug				= ''; // if true then designation text will be passed else designation id will be passed
$employees			= array();

if(isset($post_data)){
	
	if($post_data['slug'] != ''){
		
		if(isset($post_data['designation_id']) && $post_data['designation_id'] != ''){
			// fetch designation id by slug
			$designation_id = $post_data('designation_id');
		}else{
			$designation_detail = json_decode(getDesignationBySlug($post_data['slug']),true);
			$designation_id = $designation_detail['id'];
		}
		
		
		$select_employees = 'SELECT id , firstname, lastname, email, contactNumber '
			. ' FROM `employees` '
			. ' WHERE designation = '.$designation_id.'';
		
		$result = mysql_query($select_employees);
		
		if($result){

			while($row = mysql_fetch_assoc($result)){
				$row['full_name'] = $row['firstname'].' '.$row['lastname'];
				array_push($employees, $row);
			}			
		}		
	}	
}

// Sending response 
echo json_encode(array(
	'success' => 1,
	'data' => $employees
));
