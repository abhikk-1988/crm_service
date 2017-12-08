<?php
session_start();

require 'function.php';

$post_data = json_decode(file_get_contents('php://input'), true);

if(!empty($post_data)){
	
	if(isset($post_data['request_userid']) && $post_data['request_userid'] != ''){
	
		// generate random password for user 
		$random_password = generateRandomString(6);
		
		// create hash of password 
		
		$password_hash = hash('sha1', $random_password);
		
		// change user password 
		
		$user_id			= $post_data['request_userid'];
		$user_name_or_email = $post_data['request_email'];

		$password_reset_date = date('Y-m-d H:i:s');
		
		$update_user_password = 'UPDATE employees SET password = "'.$password_hash.'", password_last_reset = "'.$password_reset_date.'" WHERE id = '.$user_id.' AND (email = "'.$user_name_or_email.'" OR username = "'.$user_name_or_email.'")  LIMIT 1';
		
		if( mysql_query($update_user_password) ){
			
			// Delete password reset request 
			
			$delete_password_reset_request = 'DELETE FROM reset_password WHERE id = '.$post_data['id'].' LIMIT 1';
			
			mysql_query($delete_password_reset_request);
			
			
			require_once 'email.php';
			
			 
			/** ----------------------------------------------------- **/
			//	Send mail to user	
			/* ------------------------------------------------------  */
				
				
				$recepient_name		= $post_data['employee_name'];
				$recepient_address	= $post_data['employee_email'];
			
				// Add a recepient
				$mail->addAddress($recepient_address, $recepient_name);

				// Set email format to HTML
				$mail->isHTML(true);                                  

				$mail_body  = 'Dear, '. $recepient_name .' <br/><br/>';
				$mail_body .= 'As per your request your CRM account password has been reset. Please find below your account login credentials - ';
				$mail_body .= '<br/><br/>';
				$mail_body .= '<b>Email:</b> '. $recepient_address .'<br/>';
				$mail_body .= '<b>Password:</b> '. $random_password;
				
				$mail->Subject = 'New Login Password';
				$mail->Body    = $mail_body;
				$mail->AltBody = $mail_body;
			
				if(IS_EMAIL_ON == 1){
					
					if(!$mail->send()) {                                             
						$email_sent = 0;                                             
					} else {                                                           
						$email_sent = 1 ;                                            
					} 
				}
				
			/* ------------------------------------------------------- */
			
			
			
			// send mail to admin itself
		
			
			
			/** ----------------------------------------------------- **/
			//	Send sms to user on their registered mobile number
			/*---------------------------------------------------------*/
				$otp_number = $post_data['otp_sent_on_number'];
				$message	= urlencode( 'Your CRM acount password has been reset and email to you. Please get access to your email for login credentials' );
			
                $url = 'http://promotionsms.in/api/swsendSingle.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$otp_number.'&message='.$message;
            
    			$curl		= curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => 120
				));
				$resp		= curl_exec($curl);
				curl_close($curl);
				
				
			/** ------------------------------------------------------**/
			
			$response = array('success' => 1, 'message' => 'Password has been reset successfully.');
			
			echo json_encode($response, true); exit;
			
		}else{
			
			// send error response
			
			$error = array('success' => 0, 'message' => 'Password could not be reset at the moment. Please try again later.');
			echo json_encode($error,true); exit;
		}
	}else{
		
		echo json_encode(array('success' => 0), true); exit;
	}
	
}else{
	
	$error = array('success' => 0, 'message' => 'No Data Received');
	echo json_encode($error, true); exit;
	
}