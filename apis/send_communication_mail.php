<?php
require 'function.php';
require 'email.php';

// Get data from POST HTTP Method 
$post_data = filter_input_array(INPUT_POST);

if(!empty($post_data)){
	
	
	// Subject 
	$mail->Subject	= $post_data['subject'];
	
	// Body 
	$mail->Body		= $post_data['message_body'];
	
	// TO recepients
	if(isset($post_data['to_users'])){
		
		foreach($post_data['to_users'] as $email){
			$mail->addAddress($email, getEmployeeByEmail($email));
		}
	}
	
	// CC recepients 
	if(isset($post_data['cc_users'])){
		
		foreach($post_data['cc_users'] as $email){
			$mail->addCC($email);
		}
	}
	
	// BCC recepients 
	if(isset($post_data['bcc_users'])){
		
		foreach($post_data['bcc_users'] as $email){
			$mail->addBCC($email);
		}
	}
	
	$mail->isHTML(true);
	
	// For attachment 
//	$mail->addAttachment();
	
	if(!$mail->send()) 
	{
		echo "Mailer Error: " . $mail->ErrorInfo;
		
		echo json_encode(array('success' => 0, 'message' => $mail->ErrorInfo),true); exit;
	} 
	else 
	{
		$msg =  "Message has been sent successfully";
		echo json_encode(array('success' => 1, 'message' => $msg),true); exit;
	}
}else{
	
	echo json_encode(array('success' => 0, 'message' => 'Message could not be sent'),true); exit;
}


