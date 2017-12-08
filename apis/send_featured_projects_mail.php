<?php

require 'function.php';
require 'email.php';

$data = filter_input_array(INPUT_POST);

if(isset($data) && $data['featured_projects'] != ''){
    
    $message = 'Dear '. $data['to_name'];
    $message .= '<br/><br/>';
    $message .= 'Please find below our featured projects.';
    $message .= '<br/>';

    $message .= $data['featured_projects'];

    $mail->isHTML(true);
    $mail->Subject	= 'Featured Projects';
    $mail->Body		= $message;
    $mail->addAddress( $data['to_email'], $data['to_name']);
    $mail->addCC($data['cc_email']);
    $mail->addBCC($data['bcc_email']);
    if(!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        echo json_encode(array('success' => 0, 'message' => $mail->ErrorInfo),true); exit;
    }else{
        echo json_encode(
            array('success' => 1),true
        ); exit;
    } 
}

// INPUT data 
// project list 
// To email address
// To name
// Cc email address
// Bcc email address
// Mail subject

