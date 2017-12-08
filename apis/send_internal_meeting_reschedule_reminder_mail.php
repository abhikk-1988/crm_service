<?php

session_start();

require 'function.php';

$data = filter_input_array(INPUT_POST);

function sendMailData( $email_data = '', $enquiry_id = ''){
	
	$curl_url	= BASE_URL . 'apis/sendEmailReminder.php';
	$curl		= curl_init($curl_url);
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $email_data
	));
	
	$result = curl_exec($curl);
	curl_close($curl);
}

if(isset($data['enquiry_id']) && $data['enquiry_id'] != ''){
 
    $enquiry_id = $data['enquiry_id'];
    
    $client_info_query = 'SELECT '
			. ' customerMobile, customer_alternate_mobile, '
			. ' customerLandline, customerEmail, customerName, '
			. ' customerProfession, customerCity, customerAddress, '
			. ' email_template_id, lead_id, disposition_status_id, '
			. ' disposition_sub_status_id, lead_assigned_to_asm, lead_assigned_to_sp, meeting_id '
			. ' FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
	
	$client_info_result = mysql_query($client_info_query);

	if($client_info_result && mysql_num_rows($client_info_result) > 0) {
		
		$client_data  = mysql_fetch_object($client_info_result);
        
        $sales_person_name      = getEmployeeName($client_data -> lead_assigned_to_sp);
        $sales_person_email_id  = getEmployeeEmailAddress($client_data -> lead_assigned_to_sp);
        $asm_name               = getEmployeeName($client_data -> lead_assigned_to_asm);
        $asm_email_id           =  getEmployeeEmailAddress($client_data -> lead_assigned_to_asm);
        
        // meeting data 
        $meeting_data = getLeadMeetingData($enquiry_id, $client_data -> meeting_id);
        
        $meeting_schedule_date = date('d-M-Y H:i:s',$meeting_data['meeting_timestamp']/1000);
        
        $meeting_address       = $meeting_data['meeting_address'];
        
        // meeting project 
        $project_name  = '';
        $project_city  = '';
        $project_url   = '';
        
        if(isset($meeting_data['project']) && $meeting_data['project'] != ''){
            $meeting_project = json_decode($meeting_data['project'],true);
            $project_name = $meeting_project[0]['project_name'];
            $project_city = $meeting_project[0]['project_city'];
            $project_url  = $meeting_project[0]['proejct_url'];
            
        }
        
        // Fetch mail data for meeting schedule
        
        $email_data     = 'SELECT * FROM email_templates WHERE email_category = "internal" AND event = "meeting_reschedule"';
        
        $email_result   =  mysql_query($email_data);
        
        if($email_result && mysql_num_rows($email_result) > 0){
            $email_template_object = mysql_fetch_object($email_result);    
        }
        
        
        $keywords_to_replace =  array(
            '{{sales_person_name}}',
            '{{enquiry_no}}',
            '{{lead_no}}',
            '{{meeting_schedule_date}}',
            '{{meeting_address}}',
            '{{project_name}}',
            '{{project_city}}',
            '{{client_name}}'
        );
        
                            
        $replacement_values = array(
            $sales_person_name,
            $enquiry_id,
            $client_data -> lead_id,
            $meeting_schedule_date,
            $meeting_address,
            $project_name, 
            $project_city,
            $client_data -> customerName
        );
        
        
        $mail_body = str_replace($keywords_to_replace, $replacement_values, $email_template_object -> message_body);
        
        $default_to_users   = '';
        $default_cc_users   = '';
        $default_bcc_users  = '';
        
        if($email_template_object -> to_users != ''){
            $default_to_users = $email_template_object -> to_users;
        }
        if($email_template_object -> cc_users != ''){
            $default_cc_users = $email_template_object -> cc_users;
        }
        if($email_template_object -> bcc_users != ''){  
            $default_bcc_users = $email_template_object -> bcc_users;
        }
        
        $mail_data = array(
            MESSAGE => $mail_body,
            DEFAULT_TO_USERS	=> $default_to_users,
            DEFAULT_CC_USERS	=> $default_cc_users,
            DEFAULT_BCC_USERS	=> $default_bcc_users,
            TO	=> $sales_person_email_id,
            CC	=> $asm_email_id,
            BCC => '', // add if any 
            SUBJECT => $email_template_object -> subject,
            TO_NAME => $client_data -> customerName
        );
        
        sendMailData($mail_data, $enquiry_id);
        
	}
}
                    