<?php
session_start();
require 'function.php';

require_once 'user_authentication.php';

if(!$is_authenticate){
	echo unauthorizedResponse(); exit; 
}

$leads = array();

function sendMailData( $email_data = '', $enquiry_id = '', $update_lead = true){

    $curl_url	= BASE_URL . 'apis/sendEmailReminder.php';
    $curl		= curl_init($curl_url);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $email_data
    ));

    $result = curl_exec($curl);
    // if($result){

    //     // if($update_lead){
    //     //     // set flag of email sent or not
    //     //      mysql_query('UPDATE lead SET `is_email_template_sent` = "'.date('Y-m-d H:i:s').'" WHERE enquiry_id = '.$enquiry_id.'');
    //     // }    
    // }

    curl_close($curl);
        return $result;
    }

$data = filter_input_array(INPUT_POST);

if(!empty($data) && isset($data['enquiry_id']) && isset($data['sales_person_id'])){

	$current_date = date('Y-m-d H:i:s');
	// Change lead reject status 
	
    $reject_lead_sql = 'UPDATE lead '
			. ' SET is_lead_rejected = 1 , lead_reject_datetime = "'.$current_date.'", '
			. ' lead_rejection_reason = "'.$data['reject_reason'].'", lead_assigned_to_sp = 0 '
			. ' WHERE enquiry_id = '.$data['enquiry_id'].' AND lead_assigned_to_sp = '.$data['sales_person_id'].' LIMIT 1';


	if( mysql_query($reject_lead_sql) ){
		
		$sales_person_name              = getEmployeeName($data['sales_person_id']);
        $sales_person_manager_detail    = getEmployeeManager($data['sales_person_id']);
        
		// increase sales person capacity 
		$current_month    = (int) date('m') - 1;
		$current_year     = date('Y');
		
		/** Important: To be apply in logic
		  
			// Get the lead assignment datetime of sales person by area sales manager 
			// and compare the current month and year from the assignment datetime value 
			// if assignment month and year is same to current month and year then we increase in sales person capacity otherwise we will not.
		*/
        
        $get_lead_assigned_date_to_sales_person = mysql_query('SELECT YEAR(lead_assigned_to_sp_on) as assigned_year, MONTH(lead_assigned_to_sp_on) as assigned_month FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1 ');

        if($get_lead_assigned_date_to_sales_person && mysql_num_rows($get_lead_assigned_date_to_sales_person) > 0){

            $lead_assign_date = mysql_fetch_object($get_lead_assigned_date_to_sales_person);

            if($lead_assign_date -> assigned_year == $current_year ){

                if($lead_assign_date -> assigned_month == $current_month){
                        $update_sales_person_capacity = 'UPDATE `sales_person_capacities` SET'
                    . ' remaining_capacity = remaining_capacity + 1  '
                    . ' WHERE sales_person_id = '.$data['sales_person_id'].' AND month = '.$current_month.' AND year = "'.$current_year.'"';
            
                    mysql_query($update_sales_person_capacity);
                }
            }
        }

		// Log history 
		$details		= 'Lead has been rejected by sales person '.$sales_person_name.' on '.$current_date.'';
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
        
        /** Internal mail of Lead Reject to ASM **/
        
        $current_lead_state = getLead($data['enquiry_id']);
        $meeting_data       = array();
        $site_visit_data    = array();
        
        
        if($current_lead_state['disposition_status_id'] == 3){
            
            $meeting_data = getLeadMeetingData($data['enquiry_id'], $current_lead_state['meeting_id']);  
            $project      = json_decode($meeting_data['project'],true);
            
            // get meeting reject mail 
            
            $meeting_reject_mail_template = mysql_query('SELECT * FROM email_templates WHERE email_category = "internal" AND event = "meeting_reject" LIMIT 1');
            
            if($meeting_reject_mail_template && mysql_num_rows($meeting_reject_mail_template) > 0){
                
                $meeting_reject_mail_template_object = mysql_fetch_object($meeting_reject_mail_template);;
                
                $mail_keywords = array(
                    '{{area_sales_manager}}',
                    '{{sales_person_name}}',
                    '{{enquiry_no}}',
                    '{{lead_no}}',
                    '{{meeting_schedule_date}}',
                    '{{meeting_address}}',
                    '{{project_name}}',
                    '{{project_city}}',
                    '{{reason}}'
                );
                
                $mail_keywords_values = array(
                    $sales_person_manager_detail['manager_name'],
                    $sales_person_name,
                    $data['enquiry_id'],
                    $lead_number,
                    date('d-m-Y H:i:s' , $meeting_data['meeting_timestamp']/1000 ),
                    $meeting_data['meeting_address'],
                    $project[0]['project_name'],
                    $project[0]['project_city'],
                    $current_lead_state['lead_rejection_reason']
                );
                
                $mail_body = str_replace($mail_keywords, $mail_keywords_values, $meeting_reject_mail_template_object -> message_body);
                
                $default_to_users   = '';
                $default_cc_users   = '';
                $default_bcc_users  = '';

                if($meeting_reject_mail_template_object -> to_users != ''){
                    $default_to_users = $meeting_reject_mail_template_object -> to_users;
                }
                if($meeting_reject_mail_template_object -> cc_users != ''){
                    $default_cc_users = $meeting_reject_mail_template_object -> cc_users;
                }
                if($meeting_reject_mail_template_object -> bcc_users != ''){
                    $default_bcc_users = $meeting_reject_mail_template_object -> bcc_users;
                }

                $mail_data = array(
                    MESSAGE => $mail_body,
                    DEFAULT_TO_USERS	=> $default_to_users,
                    DEFAULT_CC_USERS	=> $default_cc_users,
                    DEFAULT_BCC_USERS	=> $default_bcc_users,
                    TO	=> $sales_person_manager_detail['manager_email'],
                    CC	=> '',
                    BCC => '',
                    SUBJECT => $meeting_reject_mail_template_object -> subject,
                    TO_NAME => $sales_person_manager_detail['manager_name']
                );

                sendMailData($mail_data, $data['enquiry_id']);
                
            }
            
        }
        else if($current_lead_state['disposition_status_id'] == 6){
            
            $site_visit_data = getLeadSiteVisitData($data['enquiry_id'], $current_lead_state['site_visit_id']);
            $project         = json_decode($site_visit_data['project'],true);
            
            // get site visit reject mail 
            
            $site_visit_reject_mail_template = mysql_query('SELECT * FROM email_templates WHERE email_category = "internal" AND event = "site_visit_reject" LIMIT 1');
            
            if($site_visit_reject_mail_template && mysql_num_rows($site_visit_reject_mail_template) > 0){
                
                $site_visit_reject_mail_template_object = mysql_fetch_object($site_visit_reject_mail_template);;
                
                $mail_keywords = array(
                    '{{area_sales_manager}}',
                    '{{sales_person_name}}',
                    '{{enquiry_no}}',
                    '{{lead_no}}',
                    '{{site_visit_schedule_date}}',
                    '{{site_visit_address}}',
                    '{{project_name}}',
                    '{{project_city}}',
                    '{{reason}}'
                );
                
                $mail_keywords_values = array(
                    $sales_person_manager_detail['manager_name'],
                    $sales_person_name,
                    $data['enquiry_id'],
                    $lead_number,
                    date('d-m-Y H:i:s' , $site_visit_data['site_visit_timestamp']/1000 ),
                    $site_visit_data['site_location'],
                    $project[0]['project_name'],
                    $project[0]['project_city'],
                    $current_lead_state['lead_rejection_reason']
                );
                
                $mail_body = str_replace($mail_keywords, $mail_keywords_values, $site_visit_reject_mail_template_object -> message_body);
                
                $default_to_users   = '';
                $default_cc_users   = '';
                $default_bcc_users  = '';

                if($site_visit_reject_mail_template_object -> to_users != ''){
                    $default_to_users = $site_visit_reject_mail_template_object -> to_users;
                }
                if($site_visit_reject_mail_template_object -> cc_users != ''){
                    $default_cc_users = $site_visit_reject_mail_template_object -> cc_users;
                }
                if($site_visit_reject_mail_template_object -> bcc_users != ''){
                    $default_bcc_users = $site_visit_reject_mail_template_object -> bcc_users;
                }

                $mail_data = array(
                    MESSAGE => $mail_body,
                    DEFAULT_TO_USERS	=> $default_to_users,
                    DEFAULT_CC_USERS	=> $default_cc_users,
                    DEFAULT_BCC_USERS	=> $default_bcc_users,
                    TO	=> $sales_person_manager_detail['manager_email'],
                    CC	=> '',
                    BCC => '',
                    SUBJECT => $site_visit_reject_mail_template_object -> subject,
                    TO_NAME => $sales_person_manager_detail['manager_name']
                );

                sendMailData($mail_data, $data['enquiry_id']);
                
            }
        }
        else{
            
        }
        
        /** End: Internal mail */
        
		echo 1; exit;
	}else{
		echo 0; exit;
	}		
}else{
	echo 0; exit;
}
