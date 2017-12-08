<?php
/*
 * API to create a new meeting 
 */
session_start();
require 'function.php';

$meeting = array(
	'meetingId' => '',
	'lead_number' => '',
	'enquiry_id' => '',
	'meeting_status' => 0, // initially it will be in pending status 
	'meeting_timestamp' => '',
	'project' => '',
	'client' => '',
	'executiveId' => '',
	'executiveName' => '',
	'feedback' => '',
	'meeting_created_at' => '',
	'remark' => '',
	'meeting_address' => '',
	'meeting_location_type' => '',
	'attendees' => 1, // by default attendees set to 1 
	'is_reminder_mail_sent' => '',
	'is_reminder_sms_sent' => '',
	'meeting_update_on' => ''
);

$_post = filter_input_array(INPUT_POST);

if( isset($_post) && !empty($_post)){
	
	if( isset($_post['enquiry_id'])){
		$meeting['enquiry_id'] = $_post['enquiry_id'];
	}
	
	if( isset($_post['lead_number'])){
		$meeting['lead_number'] = $_post['lead_number'];
	}
	
	if( isset($_post['project'])){
		$meeting['project'] = mysql_real_escape_string($_post['project']);
	}
	
	if( isset($_post['client'])){
		$meeting['client'] = mysql_real_escape_string( $_post['client'] );
	}
	
	if( isset( $_post['meeting_address']) ){
		$meeting['meeting_address'] = mysql_real_escape_string($_post['meeting_address']);
	}
	
	if( isset($_post['meeting_time'])){
		$meeting['meeting_timestamp'] = $_post['meeting_time'];
	}
	
	if( isset($_post['employee_id'])){	
		$meeting['executiveId'] = $_post['employee_id'];
	}
	
	if( isset($_post['employee_name'])){
		$meeting['executiveName'] = $_post['employee_name'];
	}

	if( isset($_post['remark'])){
		$meeting['remark'] = mysql_real_escape_string($_post['remark']);
	}
	
	if( isset($_post['meeting_location_type']) ){
		$meeting['meeting_location_type'] = $_post['meeting_location_type'];
	}
	
	if( isset($_post['attendees'])){
		$meeting['attendees'] = $_post['attendees'];
	}
	
	$timestamp_in_ms = time() * 1000;
	$meeting['meeting_update_on'] =		$timestamp_in_ms;
	$meeting['meeting_created_at'] =	$timestamp_in_ms;
	
	// generating new meeting ID
	$meeting['meetingId'] = $meeting_id = createMeetingID();
	
	// check for enquiry id 
	if($meeting['enquiry_id'] != ''){
		
		// create new meeting 
		$query = 'INSERT INTO `lead_meeting` ';
		$query .= 'SET ';
		
		foreach($meeting as $col => $val){
			$query .= ''.$col.' = "'.$val.'" ,';
		}
	  
		// If query executes successfully then send reminder mail and sms to client and internal users 
		if(mysql_query(trim(rtrim($query," ,")))) {
			
			// update meeting Id in lead table 
			mysql_query('UPDATE `lead` SET meeting_id = "'.$meeting_id.'" WHERE enquiry_id = '.$meeting['enquiry_id'].' LIMIT 1');
			
			// send reminder mail and sms
			$is_sent_reminder_email = sendMeetingReminder($meeting['enquiry_id']);
		
            // to be done 
			$is_sent_reminder_sms = '';
		
			// save status of reminder mail and sms in lead_meeting table 
            
//             $history = array(
//                'enquiry_id' => $enquiry_id,
//                'lead_number' => $_post['lead_number'],
//                'type' => 'new',
//                'details' => 'Lead status has been changed to Meeting by '.$employee_name,
//                'employee_id' => $_post['employee_id']
//            );
//            
//            createLog($history);
            
			echo $meeting_id; exit; // return meeting id 
		}
	}else {
		echo ''; exit;
	}
}else{
	
	echo ''; exit;
}
