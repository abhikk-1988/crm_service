<?php
session_start();
require 'function.php';

$_post_data = filter_input_array(INPUT_POST);

$user_id = ''; 
if( isset($_post_data['user_id']) && $_post_data['user_id'] != ''){
	$user_id= $_post_data['user_id'];
	
	
	if( isset($_FILES['photo']) && $_FILES['photo']['name'] != ''){

		if( $_FILES['photo']['size'] > 0){

			// upload file to server 

			$uploaded_file = mysql_real_escape_string(file_get_contents( $_FILES['photo']['tmp_name']));

			$upload_profile_image = 'UPDATE employees SET profile_image = "'.$uploaded_file.'" WHERE id = '.$user_id.' LIMIT 1';

			if(mysql_query($upload_profile_image) ){

				echo json_encode(array('success' => 1), true); exit;

			}else{
				echo json_encode(array('success' => 0), true); exit;
			}

		}
		else{
			echo json_encode(array('success' => 1), true); exit;
		}
	}
	else{
		echo json_encode(array('success' => 1), true); exit;		
	}
	
}else{
	
	echo json_encode(array('success' => 1), true); exit;		
}

