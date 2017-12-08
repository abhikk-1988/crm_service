<?php
require 'function.php';

$post_data = filter_input_array(INPUT_POST);

if( !empty($post_data) ){
	
	if(isset($post_data['otp']) && isset($post_data['request_id'])){
		
		$otp		= $post_data['otp'];
		$request_id = $post_data['request_id'];
		
		// check otp against request id or user_data 
		
		$is_otp_mathched = 'SELECT id '
				. ' FROM reset_password '
				. ' WHERE otp = '.$otp.' AND id = '.$request_id.'  AND request_datetime LIKE "%'.date('Y-m-d').'%"';
		
		$result = mysql_query($is_otp_mathched);
		
		if( $result && mysql_num_rows($result) > 0 ){
			
			// OTP is verified 
			
			$otp_verified = 'UPDATE reset_password SET'
					. ' is_otp_verified = 1 '
					. ' WHERE id = '.$request_id.' AND request_datetime LIKE "%'.date('Y-m-d').'%"';
			
			mysql_query($otp_verified);
				
			// send mail to user for successfully reset password request 
				
			// Send mail to account admin of new password reset request 
			
			$response  = array('data' => array('success' => 1, 'message' => 'Your password reset request is successfully sent to account administrator. You will be notified by email for your new password.'));
			
			echo json_encode($response, true); exit;
		}
		else{
			
			// Increment in wrong input of otp
			$wrong_attempt = 'UPDATE reset_password SET'
					. ' wrong_attempt = wrong_attempt + 1 '
					. ' WHERE id = '.$request_id.' AND request_datetime LIKE "%'.date('Y-m-d').'%"';
			
			mysql_query($wrong_attempt);
			
			// No request find for reset password of this user
			$response = array('data' => array('success' => 0 ,'message' => 'OTP is not verified. Please try again'));
			echo json_encode($response, true); exit;
		}
	}else{
		
		$response = array('data' => array('success' => 0, 'message' => 'Please enter OTP'));
		echo json_encode($response, true); exit;
	}
}else{
	
	$response = array('data' => array('success' => 0, 'message' => 'Please enter OTP'));
	echo json_encode($response, true); exit;
}
