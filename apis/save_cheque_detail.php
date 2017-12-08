<?php
session_start();
require 'function.php';

function uploadFile (){
	
	if(isset($_FILES['file'])){
		
		$errors= array();
		$file_name	=	$_FILES['file']['name'];
		$file_size	=	$_FILES['file']['size'];
		$file_tmp	=	$_FILES['file']['tmp_name'];
//		$file_type	=	$_FILES['file']['type'];
		$file_ext	=	strtolower(end(explode('.',$_FILES['file']['name'])));
		$expensions	=	array("jpeg","jpg","png");
      
		$file_name = 'cheque_scan_file_'.time().'.'. $file_ext;
		
		/* Validation Code not applicable now */
		//		if(in_array($file_ext,$expensions)=== false){
		//		   $errors[]="extension not allowed, please choose a JPEG or PNG file.";
		//		}

		//		if($file_size > 2097152){
		//		   $errors[]='File size must be excately 2 MB';
		//		}
		/* End: validation code */	
		
		// system path where file will be uploaded
		$upload_path = dirname(__DIR__). '/'. CHEQUE_UPLOAD_PATH;
		
		if(empty($file_errors)==true){
		   move_uploaded_file($file_tmp, $upload_path.$file_name);
		   
		   return BASE_URL . CHEQUE_UPLOAD_PATH . $file_name;
		   
		}else{
			return '';
		}
	}
	
}

// check if data is posted 

$_post = filter_input_array(INPUT_POST);


if( isset($_post) && !empty($_post) ){
	
	
	$chequeData		= array();	
	$errors			= array();

	// mandatory form fields 
	//	cheque_number; cheque_date; amount; ac_number; bank_name; 
	
	if( isset($_post['enquiry_number']) && !empty($_post['enquiry_number'])) {
		$chequeData['enquiry_id'] = $_post['enquiry_number'];
	}else{
		$errors[] = 'Enquiry id not provided';
	}
	
	if( isset($_post['number']) && !empty($_post['number'])){
		$chequeData['cheque_number'] = $_post['number'];
	}else{
		$errors[] = 'Cheque number is not filled';
	}

    
	if( isset($_post['date']) && !empty($_post['date'])){
		$chequeData['cheque_date'] = date('Y-m-d', strtotime($_post['date']));
	}else{
		$errors[] = 'Cheque date is not filled';
	}
	
	if( isset($_post['amount']) && !empty($_post['amount'])){
		
		$chequeData['amount'] = $_post['amount'];
		
		// check for only number in amount 
		if(! filter_var($chequeData['amount'], FILTER_VALIDATE_FLOAT)){
			$errors[] = 'Please ';
		}
		
	}else{
		$errors[] = 'Amount is not filled';
	}
	
	if( isset($_post['ac_number']) && !empty($_post['ac_number'])){
		$chequeData['account_number'] = $_post['ac_number'];
	}else{
		$errors[] = 'A/c number is not filled';
	}
	
	if( isset($_post['bank_name']) && !empty($_post['bank_name'])){
		$chequeData['bank_name'] = $_post['bank_name'];
	}else{
		$errors[] = 'Bank name is not filled';
	}

	if( isset($_post['payment_type']) ){
		$chequeData['payment_type'] = $_post['payment_type'];
	}
	
	if(isset($_post['ifsc_code'])){
		$chequeData['ifsc_code'] = $_post['ifsc_code'];
	}
	
	if( isset($_post['user_id']) && !empty($_post['user_id'])){
		$chequeData['employee_id'] = $_post['user_id'];
	}else{
		$errors[] = 'User is not identified for this action. Please check your session and login again';
	}
	
	if( !empty($errors)){
		
		// send errors back to client 
		echo json_encode(array('success' => 0, 'errors' => $errors), true); exit;
	}
	
	// check if ant file is uploaded 
	
	if( isset($_FILES) && !empty($_FILES['file'])){
	
		// upload file 
		$chequeData['cheque_scan_filepath'] = uploadFile();
	}
	
        
        // Employee Designation/Type
        $employee_type = '';
        $employee_designation = getEmployeeDesignation($_post['user_id']);
        
        if (count($employee_designation) > 0) {
            $employee_type = $employee_designation[1];
        }
        
	// save cheque data to DB table 
	
	$query = 'INSERT INTO `payment_collection` '
			. 'SET ';
	
	foreach ($chequeData as $col => $val){
		$query .= $col .' = "'. $val .'",';
	}
	
	// trim sql query 
	$trimmed_query = rtrim($query,",");
    
	if(mysql_query($trimmed_query)){
		
                // Update lead disposition status in lead table
            $lead_update = mysql_query('UPDATE `lead` '
                        . ' SET disposition_status_id = '.$_post['status_id'].' , disposition_sub_status_id = '.$_post['sub_status_id'].' '
                        . ' WHERE enquiry_id = '.$_post['enquiry_number'].'');    
            
            
            // Get Lead Activity status
            $lead_activity_status = mysql_query('SELECT leadStatus FROM lead WHERE enquiry_id = '.$post['enquiry_number'].' LIMIT 1');
            $lead_current_activity_status = '';
            if($lead_activity_status){
                
                $row = mysql_fetch_object($lead_activity_status);
                $lead_current_activity_status = $row -> leadStatus;
            }
            
            // Update Status in [lead_status] table 
            
            $update_lead_status = mysql_query('INSERT INTO `lead_status` 
             SET enquiry_id = '.$_post['enquiry_number'].' , disposition_status_id='.$_post['status_id'].' , disposition_sub_status_id= '.$_post['sub_status_id'].',
             user_type="'.$employee_type.'" , user_id = '.$_post['user_id'].', hot_warm_cold_status = "'.$lead_current_activity_status.'"');
            
            
		// log in history 
		$log_date = date('Y-m-d');
		$employee_name = getEmployeeName($_post['user_id']);
		$text = 'Cheque details has been updated against enquiry id '.$_post['enquiry_number'].' by '.$employee_name.' on '. $log_date;
		
		$log_query = 'INSERT INTO lead_history SET '
				. ' enquiry_id = '.$_post['enquiry_number'].','
				. ' details = "'.$text.'",'
				. ' employee_id = '.$_post['user_id'].','
				. ' type = "new"';
		
		mysql_query($log_query);
		
		echo json_encode(array('success' => 1, 'message' => 'Cheque details has been saved successfully'), true);
		exit;
	}
	else{
		$errors[] = 'Cheque details not saved';
		echo json_encode(array('success' => 0, 'errors' => $errors), true);
	}
}
else{
	$errors[] = 'No Data Provided';
	echo json_encode(array('success' => 0, 'errors' => $errors), true);
}