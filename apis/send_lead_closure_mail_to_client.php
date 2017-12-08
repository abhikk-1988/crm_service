<?php

session_start();

require 'function.php';

function sendMailData( $email_data = '', $enquiry_id = ''){

    $curl_url	= BASE_URL . 'apis/sendEmailReminder.php';
    $curl		= curl_init($curl_url);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $email_data
    ));

    $result = curl_exec($curl);
    if($result){

    }
    curl_close($curl);
    return $result;
}

function sendSMS($numbers = array() , $message = ''){
	
    if( !empty($numbers)){
		
        //foreach($numbers as $number){
			
			    $message = urlencode($message);
			    $number_string = implode(',', $numbers);
				
					if(count($numbers) == 1){
						$url = 'http://promotionsms.in/api/swsendSingle.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$number_string.'&message='.$message;
					}else{
						$url = 'http://promotionsms.in/api/swsend.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$number_string.'&message='.$message;
					}
					
			    // Get cURL resource
			    $curl = curl_init();
			    
				// Set some options - we are passing in a useragent too here
                curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_URL => $url,
                        CURLOPT_TIMEOUT => 120
                ));
			 
                    // Send the request & save response to $resp
			         $resp = curl_exec($curl);
			         // Close request to clear up some resources
			         curl_close($curl);
        //} // end foreach
        
    } // end if condition 
}

$data = filter_input_array(INPUT_POST);

$enquiry_id = '';

$projects = array();

$project_name = '';
$project_city = '';

if( isset($data['enquiry_id']) && $data['enquiry_id'] != ''){
    
    $enquiry_id = $data['enquiry_id'];
    
    // get Lead info 
    $lead_info = getLead($enquiry_id);
    
    // Current state in which lead is 
    $current_lead_state = $lead_info['disposition_status_id'];
    
    if($current_lead_state == 3){
        
        // Meeting state
        $meeting_id = $lead_info['meeting_id'];
        
        $meeting_data = getLeadMeetingData($enquiry_id, $meeting_id);
        
        $projects = json_decode($meeting_data['project']);
        
        if(!empty($projects)){
            
            $project_name = $projects[0]['project_name'];
            $project_city = $projects['project_city'];
        }
    }
    elseif($current_lead_state == 6){
        // Site Visit State 
        $site_visit_id = $lead_info['site_visit_id'];
        
        $site_visit_data = getLeadSiteVisitData($enquiry_id, $meeting_id);
        
        $projects = json_decode($site_visit_data['project']);
        
        if(!empty($projects)){
            
            $project_name = $projects[0]['project_name'];
            $project_city = $projects['project_city'];
        }
        
    }else{
        // Other state
        
        $enquiry_projects = mysql_query('SELECT * FROM lead_enquiry_projects WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
        
        if($enquiry_projects && mysql_num_rows($enquiry_projects) > 0){
            
            $projects = mysql_fetch_assoc($enquiry_projects);
            
            $project_name = '';
            $project_link = '';
        
            if(!empty($projects)){

                $project_name = $projects['project_name'];
                $project_city = $projects['project_city'];
            }
        }
    }
    
    
    /**************************************************************/
    // EMAIL TO CLIENT
    /**************************************************************/
    
    
    $email_template = mysql_query('SELECT * FROM email_templates WHERE email_category = "external" AND event="lead_closure" LIMIT 1');
    
    if($email_template && mysql_num_rows($email_template) > 0){
        
        $email_template_object = mysql_fetch_object($email_template);
        
        $email_message_keywords = array('{{client_name}}','{{project_name}}','{{project_city}}');

        $email_message_keywords_values = array($lead_info['customerName'],$project_name, $project_city);
        
        $email_body = str_replace($email_message_keywords, $email_message_keywords_values, $email_template_object -> message_body);
        
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
            MESSAGE => $email_body,
            DEFAULT_TO_USERS	=> $default_to_users,
            DEFAULT_CC_USERS	=> $default_cc_users,
            DEFAULT_BCC_USERS	=> $default_bcc_users,
            TO	=> $lead_info['customerEmail'],
            CC	=> '',
            BCC => '',
            SUBJECT => $email_template_object -> subject,
            TO_NAME => $lead_info['customerName']
        );
        
        sendMailData($mail_data, $enquiry_id);
        
        # End: Mail sent to client
        #####################################################################################    
            
        
        /************************************************************************************/
        // SMS to Client
        /************************************************************************************/
        
        $sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "external" AND event = "lead_closure" LIMIT 1');
        
        if($sms_template && mysql_num_rows($sms_template) > 0){
            
            $sms_template_object = mysql_fetch_object($sms_template);
            
            $sms_keyword = array('{{project_name}}','{{project_city}}');
            $sms_keyword_values = array($project_name,$project_city);
            
            $sms_body = str_replace($sms_keyword, $sms_keyword_values, $sms_template_object -> message);
            
            $sms_receiver_numbers = array();

            if( $sms_template_object -> default_numbers != ''){

                // create an array of numbers 
                $sms_receiver_numbers = explode(',', $sms_template_object -> default_numbers);
            }

            array_push($sms_receiver_numbers, $lead_info['customerMobile']);
            
            if(sendSMS($sms_receiver_numbers, $sms_body)){}
        }
    }
}