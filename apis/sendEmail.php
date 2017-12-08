<?php

/**
  * @file_overview : API (sending mail)
  */

require 'email.php';

$errors = array(); // global variable 
$errors['invalid_emails'] = array();
$to_emails = array();
$cc_emails = array();
$bcc_emails = array();

// Fucntion to check email address passed in array
function checkEmailAddress($email_address){
    
    global $errors;

    if(!empty($email_address)){
        foreach($email_address as $email){

            if($email != ''){
                if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                    array_push($errors['invalid_emails'], array('email' => $email));
                }
            }    
        }
    }
}

if(isset($_POST)){

    if( isset($_POST['toEmail'])){

        // explode string into array of emails
        $to_emails = explode(',', $_POST['toEmail']);
        checkEmailAddress($to_emails);
    }else{
        array_push($errors,'To address is missing');
    }

    if(isset($_POST['ccEmail']) && $_POST['ccEmail']!= ''){
        // explode string into array of emails
        $cc_emails = explode(',', $_POST['ccEmail']);
        checkEmailAddress($cc_emails);
    }

    if(isset($_POST['bccEmail']) && $_POST['bccEmail']!= ''){	
        // explode string into array of emails
        $bcc_emails = explode(',', $_POST['bccEmail']);
        checkEmailAddress($bcc_emails);
    }

    if(!isset($_POST['subject'])){
        array_push($errors,'Subject is not define');
    }

    if(!isset($_POST['content'])){
        array_push($errors,'Content is empty. Please provide mail content');
    }

    if(!empty($errors['invalid_emails'])){
        echo json_encode( 
            array(
            'success' => (int)-1,
            'message' => 'Please correct following errors',
            'errors' => $errors
            ),true
        ); exit;
    }

    // Form to addresses
    foreach($to_emails as $email){
        $mail -> addAddress ($email);
    }

    // Form CC addresses
    foreach($cc_emails as $email){
        $mail -> addCC ($email);
    }   
    
    // Form BCC addresses
    foreach($bcc_emails as $email){
        $mail -> addBCC ($email);
    }
    

    $mail -> Subject	= $_POST['subject'];
	$mail -> Body		= $_POST['content'];

    $mail -> isHTML (true);

    if(isset($_POST['attachments'])){
        $file_path = '../upload/mailer_attachments/';

        if(!empty($_POST['attachments'])){
            foreach($_POST['attachments'] as $attachment){
                if(file_exists($file_path . $attachment['save_name'])){
                    $mail -> addAttachment($file_path . $attachment['save_name'], $attachment['original_name']);
                }
            }
        }
    }

    if( $mail -> send()){
        echo json_encode( 
            array(
            'success' => (int)1,
            'message' => 'Mail sent successfully'
            ),true
        ); exit;
	}else{
        echo json_encode( 
            array(
            'success' => (int)0,
            'message' => 'Failed to sent email. Please try again later',
            ),true
        ); exit;
	}

}