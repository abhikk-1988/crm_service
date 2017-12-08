<?php

// API to create new site visit

session_start();

require 'function.php';

// model
$site_visit_data = array(
    
    'site_visit_id' => '',
    'enquiry_id' => '',
    'lead_number' => '',
    'site_visit_timestamp' => '',
    'executiveId' => '',
    'executiveName' => '',
    'site_location' => '',
    'project' => '',
    'client' => '',   
    'site_visit_created_at' => '',
    'site_visit_updated_at' => '',
    'vehicle_accomodated' => '',
    'number_of_person_visited' => '',
    'site_visit_status' => 0, // To be update later
    'remark' => '',
    'is_reminder_mail_sent' => '', // to be update later
    'is_reminder_sms_sent' => '', // to be update later
    'client_feedback' => '' // to be update later
);
                
// POST DATA
$_post = filter_input_array(INPUT_POST);
$lead_number = '';

if(isset($_post) && !empty($_post)){
    
    $timestamp_in_ms = time() * 1000;
	$site_visit_data['site_visit_updated_at'] =		$timestamp_in_ms;
	$site_visit_data['site_visit_created_at'] =	    $timestamp_in_ms;
	
	// generating new site Visit ID
	$site_visit_data['site_visit_id'] = $site_visit_id = createSiteVisitID();
    
    
    if(isset($site_visit_data['enquiry_id'])){
        $site_visit_data['enquiry_id'] = $enquiry_id =  $_post['enquiry_id'];
    }
    
    if( isset($_post['lead_number'])){
		$site_visit_data['lead_number'] = $lead_number = $_post['lead_number'];
	}
    else{
        
        // check if Lead Number is generated for this enquiry ID or not
        // If not then generate a new Lead Number for this status 
        
        $is_lead_number = getLeadNumber($_post['enquiry_id']);
        
        if($is_lead_number == 'NULL' || $is_lead_number == ''){
            
            // create new Lead Number
            
            $lead_number = generateLeadNumber($_post['enquiry_id']);
            
            // update lead number in lead table 
            
            mysql_query('UPDATE `lead` SET `lead_number` = "'.$lead_number.'" WHERE enquiry_id = '.$_post['enquiry_id'].' LIMIT 1');
        }
    }
	
	if( isset($_post['project'])){
		$site_visit_data['project'] = mysql_real_escape_string($_post['project']);
	}
	
	if( isset($_post['client'])){
		$site_visit_data['client'] = mysql_real_escape_string( $_post['client'] );
	}
    
    if( isset( $_post['site_location']) ){
		$site_visit_data['site_location'] = mysql_real_escape_string($_post['site_location']);
	}
	
	if( isset($_post['site_visit_timestamp'])){
		$site_visit_data['site_visit_timestamp'] = $_post['site_visit_timestamp'];
	}
	
	if( isset($_post['executiveId'])){	
		$site_visit_data['executiveId'] = $_post['executiveId'];
	}
	
	if( isset($_post['executiveName'])){
		$site_visit_data['executiveName'] = $_post['executiveName'];
	}
    
    if( isset($_post['remark'])){
		$site_visit_data['remark'] = mysql_real_escape_string($_post['remark']);
	}
    
    if(isset($_post['vehicle_accomodated'])){
        $site_visit_data['vehicle_accomodated'] = $_post['vehicle_accomodated'];
    }
    
    if(isset($_post['number_of_person_visited'])){
        $site_visit_data['number_of_person_visited'] = $_post['number_of_person_visited'];
    }
    
    
    if($site_visit_data['enquiry_id'] != ''){
        
        // create new site visit 
		$query = 'INSERT INTO `site_visit` ';
		$query .= 'SET ';
		
		foreach($site_visit_data as $col => $val){
			$query .= ''.$col.' = "'.$val.'" ,';
		}
        
        // If query executes successfully then send reminder mail and sms to client and internal users 
		if(mysql_query(trim(rtrim($query," ,")))) {
			
			// update site visit Id in lead table 
			mysql_query('UPDATE `lead` SET site_visit_id = "'.$site_visit_id.'" WHERE enquiry_id = '.$site_visit_data['enquiry_id'].' LIMIT 1');
			
			// send reminder mail and sms
			$is_sent_reminder_email = sendSiteVisitReminder($site_visit_data['enquiry_id']);
		
			$is_sent_reminder_sms = '';
		
			// save status of reminder mail and sms in site_visit table 
			$employee_name = getEmployeeName($_post['executiveId']);
            
            // Log history 
//            $history = array(
//                'enquiry_id' => $enquiry_id,
//                'lead_number' => $lead_number,
//                'type' => 'new',
//                'details' => 'Lead status has been changed to Site visit by '.$employee_name,
//                'employee_id' => $_post['executiveId']
//            );
//            createLog($history);
//            
			echo $site_visit_id; exit; // return site visit id 
		}
    }
    else{
        echo ''; exit;
    }
}