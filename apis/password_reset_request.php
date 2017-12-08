<?php
require 'function.php';

$post_data = filter_input_array(INPUT_POST);

if(isset($post_data['request_data'])){

	$user_request_data = mysql_real_escape_string($post_data['request_data']);
		
	// Identify if user has supplied their username or email addresss
	
	if(!filter_var($user_request_data, FILTER_VALIDATE_EMAIL)){
		
		// check against username
		$is_user_exists = 'SELECT id, contactNumber as otp_number FROM employees WHERE username = "'.$user_request_data.'" LIMIT 1';
		
	}else{
		// Check against email 
		$is_user_exists = 'SELECT id, contactNumber as otp_number FROM employees WHERE email = "'.$user_request_data.'" LIMIT 1';
	}
	
	$result = mysql_query($is_user_exists);
	
	if($result){
		
		$user = mysql_fetch_object($result);
		
		if($user -> otp_number){
			
			// Sent OTP to user's number 
			
			// generating a random number of 4 digit as OTP number 
			
			$otp = rand(1000, 9999);
			
			$otp_number = $user -> otp_number;
			$message_text = urlencode( 'You have Initiated a password reset request for your BMH CRM Account. '.$otp.' is your reset password OTP. Please treat this as confidential' );
//			$url = 'http://5.189.187.160/api/mt/SendSMS?user=bookmyhouse01&password=123456&senderid=BKMYHS&channel=2&DCS=0&flashsms=0&number='.$otp_number.'&text='.$message_text.'&route=1';
    		$url = 'http://promotionsms.in/api/swsendSingle.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$otp_number.'&message='.$message_text;	
			
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
			
			// save request data otp and mobile number 
			
			$request_data = array(
				'request_email' => $user_request_data,
				'request_username' => $user_request_data,
				'request_userid' => $user -> id,
				'request_datetime' => date('Y-m-d H:i:s'),
				'otp' => $otp,
				'is_otp_verified' => 0,
				'is_user_verified' => 1,
				'otp_sent_on_number' => $otp_number,
				'attempt_in_a_day' => 'attempt_in_a_day + 1'
			);
			
			$create_request = 'INSERT INTO reset_password '
					. ' SET ';
			
			foreach ($request_data as $col => $val){
					if($col === 'attempt_in_a_day'){
						$create_request .= ' '.$col.' = '.$val.' ,';
					}else{
						$create_request .= ' '.$col.' = "'.$val.'" ,';
					}
			}
			
			$create_request = rtrim($create_request,',');
			
			$request_id = '';
			
			if(mysql_query($create_request)){
			
					$request_id = mysql_insert_id();
			}
			
			// Send response of request ID
			
			$response = array(
				'data' => array('success' => 1, 'request_id' => $request_id,'message' => 'OTP has been sent to your registered mobile number')
			);
			
			echo json_encode($response, true); exit;
			
		}else{
			
			$response = array(
				'data' => array(
					'success' => 0,
					'message' => 'Mobile number not registered. Please contact your account administrator to reset your password.'
				)
			);
			
			echo json_encode($response, true); exit;
		}	
	}
}else{
	$error_response = array('data' => array('success' => 0, 'message' => 'No data received'));
	echo json_encode($error_response, true); exit;
}