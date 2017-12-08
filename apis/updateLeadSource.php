<?php
session_start();
require 'function.php';
$sesion_user = array();

if(!isset($_SESSION['currentUser'])){

    $json_response = array(
        'succcess' => 0,
        'message' => 'Invalid request. User not authorized',
        'message_title' => 'Auhentication Failure'
    );
    echo json_encode($json_response,true); exit;
}

$sesion_user = $_SESSION['currentUser'];

$post_data = json_decode(file_get_contents('php://input'),true);

$primary_source_id  = '';
$secondary_source   = '';
$enquiry_id         = '';

if(isset($post_data['enquiry_id'])){
    $enquiry_id = $post_data['enquiry_id'];
}

if(isset($post_data['primary_source_id'])){
    $primary_source_id = $post_data['primary_source_id'];
}

if(isset($post_data['secondary_source'])){
    $secondary_source = $post_data['secondary_source'];
}

$error = '';

if($enquiry_id == ''){
    $error = 'We could not identify the enquiry number to update lead source.';
}

if($error != ''){
    $json_response = array(
        'success' => 0,
        'message' => $error,
        'message_title' => 'Missing Enquiry Number'
    );
    
    echo json_encode($json_response,true); exit;
}

// Update lead source 
$sql = mysql_query('UPDATE lead SET leadPrimarySource = "'.$primary_source_id.'" , leadSecondarySource = "'.$secondary_source.'" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');

// Update change log

$lead_number = getLeadNumber($enquiry_id);    

$log_text = 'Lead source has been update on '.date('d/m/Y H:i A').' by '.$sesion_user['firstname'].' '.$sesion_user['lastname'].' ';

$log = array(
    'details' => $log_text,
    'employee_id' => $sesion_user['id'],
    'type' => 'edit',
    'enquiry_id' => $enquiry_id,
    'lead_number' => $lead_number
);
createLog($log);

if($sql){
    $json_response = array(
        'success' => 1,
        'message' => 'Lead Source updated successfully',
        'message_title' => 'Lead Update'
    );
    
    echo json_encode($json_response,true); exit;
}
else{
    $json_response = array(
        'success' => 0,
        'message' => 'We could not update lead source.',
        'message_title' => 'Server Error'
    );
    
    echo json_encode($json_response,true); exit;
}