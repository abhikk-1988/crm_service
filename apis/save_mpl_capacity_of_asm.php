<?php
session_start();
require 'function.php';

$post_data = json_decode(file_get_contents('php://input'),true);

if(!empty($post_data)){
	
	if(isset($post_data['asm_id']) && isset($post_data['capacity'])){
		
		$asm_id			= $post_data['asm_id'];
		$capacity		= $post_data['capacity'];
		$date			= date('Y-m-d H:i:s');
		$added_by_user	= $post_data['session_user'];
		
		if(isset($post_data['addon_value'])){
			$capacity = (int) $capacity + (int) $post_data['addon_value'];
		}
		
		$save_capacity = 'INSERT INTO mpl_capacity SET'
				. ' user_id = '.$asm_id.','
				. ' capacity = '.$capacity.','
				. ' remaining_capacity = '.$capacity.','
				. ' creation_date = "'.$date.'",'
				. ' added_by = '.$added_by_user.''
				. ' ON DUPLICATE KEY UPDATE `remaining_capacity` = `remaining_capacity` + ('.$capacity.' - `capacity`), capacity = '.$capacity.', edited_by = '.$added_by_user.'' ;
		
		if(mysql_query($save_capacity)){
			
			// success response
			$success = array('success' => 1, 'message' => 'Capacity updated successfully');
			echo json_encode($success, true); exit;
		}else{
			
			// error response
			$error = array('success' => 0, 'message' => 'Server Error. We could not add capacity at this time.');
			echo json_encode($error, true); exit;
		}
	}	
}else{
	
	// error response
	
	$error = array('success' => 0, 'message' => 'Could not add capacity. No data recieved');
	echo json_encode($error,true); exit;
}