<?php
session_start();
require 'function.php';
require 'user_authentication.php';

if(!$is_authenticate){
	echo unauthorizedResponse(); exit; 
}

$leads = array();

$data = filter_input_array(INPUT_POST);

if(!empty($data) && isset($data['enquiry_id']) && isset($data['sales_person_id'])){

	$current_date = date('Y-m-d H:i:s');
	$current_month = (int) date('m') - 1;
	$current_year = date('Y');
	
	// update lead for lead accept by sales person 
	
	$update_lead = 'UPDATE lead '
			. ' SET is_lead_accepted = 1, lead_accept_datetime = "'.$current_date.'" '
			. ' WHERE enquiry_id = '.$data['enquiry_id']. ' AND lead_assigned_to_sp = '.$data['sales_person_id'].' LIMIT 1';
	
	if(mysql_query($update_lead)){
		
		$sales_person_name = getEmployeeName($data['sales_person_id']);
		
		// Lead current status 
        $lead_current_status = getCurrentEnquiryStatus($data['enquiry_id']);
        $lead_status         = '';
        if(!empty($lead_current_status)){
            $lead_status = $lead_current_status['primary_status_title']. ' '. $lead_current_status['secondary_status_title'];
        }
		
		// log history 
		$details		= 'Lead has been accepted by sales person '.$sales_person_name.' on '.$current_date.'';
		$emp_id			= $data['sales_person_id'];
		$lead_number	= getLeadNumber($data['enquiry_id']);
		
		$log = array(
            ' enquiry_id' => $data['enquiry_id'],
            ' lead_number' => $lead_number,
            ' details' => $details,
            ' employee_id' => $emp_id,
            ' type' => "new"
        );
        createLog($log);
		
		echo 1; exit;
	}
}else{
	echo 0; exit;
}
