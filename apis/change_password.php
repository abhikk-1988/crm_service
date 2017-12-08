<?php
session_start();

require 'function.php';

$data = json_decode(file_get_contents('php://input'), true);

//$data = filter_input_array(INPUT_POST);

if(!empty($data)){
	
	$current_password		= '';
	$new_password			= '';
	$confirm_new_password	= '';
	$user_id				= '';
	$user_email				= '';
	
	// Validations on data received
	
	$errors = array();
	
	if(isset($data['current_password']) && $data['current_password'] != ''){
		$current_password = $data['current_password'];
	}else{
		$errors['current_password'] = 'Current Password value is required';
	}
	
	if(isset($data['new_password']) && $data['new_password'] != ''){
		$new_password = $data['new_password'];
		
		// Check password length 
		if(strlen($new_password) < 4){
			$errors['new_password'] = 'Passwod too short. New password should be 4 characters long.';
		}
	}
	else{
		$errors['new_password'] = 'New Password value is required';
	}
	
	if( isset($data['confirm_password']) && $data['confirm_password'] != ''){
		
		$confirm_new_password = $data['confirm_password'];
		
		if($confirm_new_password !== $data['new_password']){
			$errors['confirm_password'] = 'Confirm password should be same as new password';
		}
	}
	else{
		$errors['confirm_password'] = 'Confirm New Password value is required';
	}

	if(isset($data['user_id']) && $data['user_id'] != ''){
		$user_id = $data['user_id'];
	}else{
		$errors['user_id'] = 'User id is blank';
	}
	
	if(isset($data['user_email']) && $data['user_email'] != ''){
		$user_email = $data['user_email'];
	}else{
		$errors['user_email'] = 'User Email address is blank';
	}
	
	// Check in DB if user's current password is same as provided password 
	
	$get_current_password = 'SELECT password FROM employees WHERE id = '.$user_id.' AND email = "'.$user_email.'" LIMIT 1';
	
	$result = mysql_query($get_current_password);	

	if($result && mysql_num_rows($result) > 0){
		
		$user_current_password = mysql_fetch_object($result); 
		
		
		// Enqcrypt user's provided password by SHA1 encryption algo 
		$encrypted_user_password = hash('sha1', $current_password);
		
		if($user_current_password -> password !== $encrypted_user_password){
			$errors['current_password'] = 'Your current password is not matched. Please enter existing password correctly.';
		}
	}else{
		$errors['account_error'] = 'Unauthorized access.';
	}
	
	if(!empty($errors)){
		
		// Form error. Send response back 	
		echo json_encode(array('success' => 0, 'errors' => $errors ,'message' => 'Please corrent the following errors'), true); exit;
		
	}else{
		
		// No form error. Process password change logic
		$update_password_sql = 'UPDATE employees '
				. ' SET password = "'.hash('sha1',mysql_real_escape_string($new_password)).'", password_last_changed = "'.date('Y-m-d H:i:s').'" '
				. ' WHERE id = '.$user_id.' AND email = "'.mysql_real_escape_string($user_email).'"';
		
		if(mysql_query($update_password_sql)){
			
			// send email notification to user 
//			require 'email.php';
			
			$response = array(
				'success' => 1, 
				'propmpt_logout' => 1,
				'message' => 'Your password has been changes successfully. You will be automatically logout in 5 seconds to login again.'
			);
			
			echo json_encode($response, true); exit;
		}
	}
}


echo json_encode(array(
	'success' => 0,
	'message' => 'No Data Received'
),true); exit;