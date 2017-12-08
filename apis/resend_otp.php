<?php

require 'function.php';

$post_data = filter_input_array(INPUT_POST);

if(!empty($post_data)){
	
		if(isset($post_data['request_id']) && $post_data['request_id'] !== ''){
			
			$request_id  = $post_data['request_id'];
			
			// confirm request ID from server 
			// IF invalid request id found then we will not generate new OTP 
			if(!confirmRequestID($request_id)){
				
				echo json_encode(array('data' => array('success' => 0, 'message' => 'Invalid Request ID.')), true);
				exit;
			}
			
			
			$otp = rand(1000, 9999);
			
			$number = getNumberToResendOTP($request_id);
			
			$otp_number = $number;
			$message_text = urlencode( 'You have Initiated a password reset request for your BMH CRM Account. '.$otp.' is your reset password OTP. Please treat this as confidential' );
			$url = 'http://5.189.187.160/api/mt/SendSMS?user=bookmyhouse01&password=123456&senderid=BKMYHS&channel=2&DCS=0&flashsms=0&number='.$otp_number.'&text='.$message_text.'&route=1';
    			
			// Get cURL resource
			$curl = curl_init();
			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url,
				CURLOPT_TIMEOUT => 120
			));
			// Send the request & save response to $resp
			$resp = curl_exec($curl);
			// Close request to clear up some resources
			curl_close($curl);
			
			// updating new otp number against request ID 
			
			$update_otp			= 'UPDATE reset_password SET otp = '.$otp.' WHERE id = '.$request_id.' LIMIT 1';
			
			$update_otp_result	= mysql_query($update_otp);
			
			if($update_otp_result ) {
				
				echo json_encode(array('data' => array('success' => 1, 'message' => 'An OTP has been sent again on registered number')), true);
				exit;
			}else{
				echo json_encode(array('data' => array('success' => 0, 'message' => 'Internal server error. Please try again')), true);
				exit;
			}
			
		}else{
			
			echo json_encode(array('data' => array('success' => 0, 'message' => 'Request ID not received')), true);
			exit;
		}
}else{
	echo json_encode(array('data' => array('success' => 0, 'message' => 'Bad request')), true);
	exit;
}