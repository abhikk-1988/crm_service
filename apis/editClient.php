<?php 
session_start();
require 'function.php';

$session_user = '';

if(isset($_SESSION['currentUser'])){
    $session_user = $_SESSION['currentUser'];
}else{

    $json_response = json_encode(array(

        'success' => 0,
        'message' => 'Invalid request. User is not authenticated',
        'message_title' => 'Authentication Error',
        'http_status_code' => 401 // Access fobidden
    ),true);

    echo $json_response; exit;
}

$post_data = filter_input_array(INPUT_POST);

$errors = array();

// VALIDATION ON POSTED DATA    

if($post_data['customerMobile'] == ''){
    $errors['customerMobile'] = 'Please enter client mobile number';
}else
{
    if(strlen($post_data['customerMobile']) < 10)
    {
        $errors['customerMobile'] = 'Please enter a 10 digit mobile number';
    }
    if(preg_match('/\s/',$post_data['customerMobile'])){
        $errors['customerMobile'] = 'Space is not allowed in mobile number';
    }
    if (filter_var((int) $post_data['customerMobile'], FILTER_VALIDATE_INT) === false) {
        $errors['customerMobile'] = 'Please enter a valid mobile number';
    }
}

if($post_data['customerName'] == ''){
    $errors['customerName'] = 'Please enter client name';
}

if($post_data['customerEmail'] != ''){
    if (filter_var($post_data['customerEmail'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['customerEmail'] = 'Please enter a valid email id';
    }
}

// Alternate Number validation if provided

if($post_data['customer_alternate_mobile'] != ''){

    if(strlen($post_data['customer_alternate_mobile']) < 10 || strlen($post_data['customer_alternate_mobile']) > 10)
    {
        $errors['customer_alternate_mobile'] = 'Please enter a 10 digit alternate mobile number';
    }
    if(preg_match('/\s/',$post_data['customer_alternate_mobile'])){
        $errors['customer_alternate_mobile'] = 'Space is not allowed in alternate mobile number';
    }
    if (filter_var((int) $post_data['customer_alternate_mobile'], FILTER_VALIDATE_INT) === false) {
        $errors['customer_alternate_mobile'] = 'Please enter a valid alternate mobile number';
    }
}

if(count($errors) > 0){

    $json_response = json_encode(array(
        'success' => -1,
        'errors' => $errors,
        'message_title' => 'Errors',
        'http_status_code' => 200
    ),true);

    echo $json_response; exit;
}

// Log purpose
$changed_client_attr = array();

// save client information

$update_client = 'UPDATE lead SET ';
foreach($post_data as $key => $val){

    // collecting client information columns for log purpose
    array_push($changed_client_attr,$key);

    if($key == 'altered_fields') continue;

    if($key != 'enquiry_id'){ 

        if($key == 'customerDOB'){
            if($val != ''){
                $val = date('Y-m-d', strtotime($val));
            }else{
                $val = '';
            }
        }

        $update_client .= $key .'= "'. $val .'" ,';
    }
}

// trim comma from end of the sql string 
$sql = trim(rtrim($update_client,' ,'));

$sql .= ' WHERE enquiry_id = '.$post_data['enquiry_id'].'';

if(mysql_query($sql)){

    $altered_fields = '';
    if(isset($post_data['altered_fields'])){
        $altered_fields = implode(',', $post_data['altered_fields']);
    }

    // update change log 
    $log_text = 'Client information has been updated on '.date('d/m/Y H:i A').' by '.$session_user['firstname'].' '.$session_user['lastname'].' on following attributes ('.$altered_fields.')';
    $log = array(
        'enquiry_id' => $post_data['enquiry_id'],
        'lead_number' => getLeadNumber($post_data['enquiry_id']),
        'details' => $log_text,
        'type' => 'edit',
        'employee_id' => $session_user['id']
    );
    createLog($log);

    $json_response = json_encode(array(
        'success' => 1,
        'message' => 'Client information updated successfully',
        'message_title' => 'Edit Lead'
    ),true);
    echo $json_response; exit;

}else{
    $json_response = json_encode(array(
        'success' => 0,
        'message' => 'We could not update information at this time. Some server error occured.',
        'message_title' => 'Server Error',
        'http_status_code' => 500
    ),true);
    echo $json_response; exit;
}