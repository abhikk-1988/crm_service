<?php

/**
 * This API is only for sending mails.
 * 
 * Mail data is POST to this APi
 */

require 'email.php';

$_post_data = filter_input_array(INPUT_POST);

if( isset($_post_data) ) {
	
	$default_to_users	= json_decode($_post_data['default_to_users'],true);
	$default_cc_users	= json_decode($_post_data['default_cc_users'],true);
	$default_bcc_users	= json_decode($_post_data['default_bcc_users'],true);
	
	$mail -> Subject	= $_post_data['subject'];
	$mail -> Body		= $_post_data['message'];	
	
	if( !empty ($default_to_users)){
		
		foreach($default_to_users as $user){
			$mail -> addAddress ($user['email'], $user['user_fullname']);
		}
	}
	
	if( !empty ($default_cc_users)){
		
		foreach($default_cc_users as $user){
			$mail -> addCC ($user['email'], $user['user_fullname']);
		}
	}
	
	if( !empty ($default_bcc_users)){
		
		foreach($default_bcc_users as $user){
			$mail -> addBCC ($user['email'], $user['user_fullname']);
		}
	}
	
	// To Address
	$mail -> addAddress ($_post_data['to'], $_post_data['to_name']);
	
	// Cc Address
	if( isset($_post_data['cc']) && $_post_data['cc']){
		$mail -> addCC($_post_data['cc']);
	}
	
	// Bcc Address
	if( isset($_post_data['bcc']) && $_post_data['bcc']){
		$mail -> addCC($_post_data['bcc']);
	}
	
	$mail -> isHTML (true); // send mail content in HTML
	
	if( $mail -> send()){
		return 1;
	}else{
		return 0;
	}
	
}

