        <?php

//
// ─── API to send callback email and sms to client ────────────────────────────────────────────────────────────
//

session_start();


require 'function.php';


$_post_data = filter_input_array(INPUT_POST);


$enquiry_id = '';


//
// ─── Mail sending function  ────────────────────────────────────────────────────────────
// 
function sendMailData( $email_data = '', $enquiry_id = '', $update_lead = true){
	
	
	$curl_url	= BASE_URL . 'apis/sendEmailReminder.php';
	
	$curl		= curl_init($curl_url);
	
	curl_setopt_array($curl, array(
	CURLOPT_RETURNTRANSFER => false,
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => $email_data
	));
	
	$result = curl_exec($curl);
    
	if($result){
		
		if($update_lead){
			
			// 			set flag of email sent or not
			mysql_query('UPDATE lead SET `is_email_template_sent` = "'.date('Y-m-d H:i:s').'" WHERE enquiry_id = '.$enquiry_id.'');
			
		}
	}

	curl_close($curl);	
	return $result;
}


// ────────────────────End of function code─────────────────────────────────────────────


//
// ─── SMS sending function ────────────────────────────────────────────────────────────
//

function sendSMS($numbers = array() , $message = ''){
	
	
	if( !empty($numbers)){
		
		
		$message = urlencode($message);
		
		
		$number_string = implode(',', $numbers);
		
		
		if(count($numbers) == 1){
			
			$url = 'http://promotionsms.in/api/swsendSingle.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$number_string.'&message='.$message;
			
		}
		else{
			
			$url = 'http://promotionsms.in/api/swsend.asp?username=t2Bookmyhouse&password=56506898&sender=BKMYHS&sendto='.$number_string.'&message='.$message;
			
		}
		
		
		// 		Get cURL resource
		$curl = curl_init();
		// echo $curl;
		// 		Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url,
		CURLOPT_TIMEOUT => 120
		));
		
		
		// 		Send the request & save response to $resp
		$resp = curl_exec($curl);
		// 		Close request to clear up some resources
		curl_close($curl);
	}
	// 	end if condition 
}


// ────────────────────────End Function code─────────────────────────────────────────


if( isset($_post_data['enquiry_id']) && $_post_data['enquiry_id']!= ''){
	
	
	$enquiry_id = $_post_data['enquiry_id'];
	
	
	$lead_information = array();
	
	
	$lead_information = getLead($enquiry_id);
	
	// 	───Get Inquired Projects──────────────────────────────────────────────────────────────
	$inquired_projects = array();
	
    // inquired project html
    $html =  '';
	$sms_project_string = '';

	$select_inquired_projects = 'SELECT * FROM `lead_enquiry_projects` WHERE enquiry_id = '.$enquiry_id.'';

	$inquired_projects_resource = mysql_query($select_inquired_projects);
	
	if($inquired_projects_resource && mysql_num_rows($inquired_projects_resource) > 0){
		while($row = mysql_fetch_assoc($inquired_projects_resource)){
			array_push($inquired_projects, $row);	
        }
	}
	
	if(!empty($inquired_projects)){

        // project html partial 
		require_once 'email_project_partial.php';
        require_once 'sms_project_partial.php';
    }
		
		//
		// 		EXTERNAL MAIL TO CLIENT
		//
		
		$email_template = 'SELECT email_category, event, subject, to_users, cc_users, bcc_users, message_body 
                                    FROM email_templates 
                                    WHERE email_category = "external" AND event = "call_back" LIMIT 1';
		
		
		$email_template_result = mysql_query($email_template);
		
		
		if( $email_template_result && mysql_num_rows($email_template_result) > 0){

		    $email_template_data = mysql_fetch_object($email_template_result);

			$keyword_to_replace =  array(
                '{{customer_name}}',
                '{{callback_date}}',
                '{{callback_time}}',
                '{{projects}}'
			);
			
			$replacement_values = array(
                $lead_information['customerName'], // customer name
                date('d-M-Y', strtotime($lead_information['future_followup_date'])), // callback date
                $lead_information['future_followup_time'], // callback time 
                $html // project list 
			);
		
            
			$message  = str_replace($keyword_to_replace, $replacement_values, $email_template_data -> message_body);

			$default_to_users   = '';
			
			$default_cc_users   = '';
			
			$default_bcc_users  = '';
			
			
			if($email_template_data -> to_users != ''){
				
				$default_to_users = $email_template_data -> to_users;
				
			}
			
			if($email_template_data -> cc_users != ''){
				
				$default_cc_users = $email_template_data -> cc_users;
				
			}
			
			if($email_template_data -> bcc_users != ''){
				
				$default_bcc_users = $email_template_data -> bcc_users;
				
			}
			
			
			$mail_data = array(
                MESSAGE => $message,
                DEFAULT_TO_USERS	=> $default_to_users,
                DEFAULT_CC_USERS	=> $default_cc_users,
                DEFAULT_BCC_USERS	=> $default_bcc_users,
                TO	=> $lead_information['customerEmail'],
                CC	=> '',
                BCC => '',
                SUBJECT => $email_template_data -> subject,
                TO_NAME => $lead_information['customerName']
			);
			
			
			sendMailData($mail_data, $enquiry_id);
		

			//	
			// 			INTERNAL MAIL TO AGENT
			//
			
			
			$get_email_template_callback = 'SELECT * FROM email_templates WHERE email_category = "internal" AND event = "call_back" LIMIT 1';
			
			
			$template_resource = mysql_query($get_email_template_callback);
			
			
			if($template_resource && mysql_num_rows($template_resource) > 0){
				
				
				$callback_email_template_object = mysql_fetch_object($template_resource);
				
				
				$mail_keywords = array(
                    '{{agent}}', 
                    '{{client_name}}',
                    '{{enquiry_no}}',
                    '{{callback_date}}',
                    '{{callback_time}}',
                    '{{projects}}'
				);
				
				
				$agent_id       = $lead_information['lead_added_by_user'];
				
				$agent_name     = getEmployeeName($lead_information['lead_added_by_user']);
				
				$agent_email    = getEmployeeEmailAddress($lead_information['lead_added_by_user']);
				
				$agent_mobile   = getEmployeeMobileNumber($lead_information['lead_added_by_user']);
				
				
				$keywords_values = array(
				    $agent_name,
				    $lead_information['customerName'],
				    $enquiry_id,
				    date('d-M-Y', strtotime($lead_information['future_followup_date'])), 
				    $lead_information['future_followup_time'],
				    $html 
				);
				
				
				$internal_callback_message  = str_replace($mail_keywords, $keywords_values, $callback_email_template_object -> message_body);
				
				$default_to_users   = '';
				
				$default_cc_users   = '';
				
				$default_bcc_users  = '';
				
				
				if($callback_email_template_object -> to_users != ''){
					$default_to_users = $callback_email_template_object -> to_users;
				}
				
				if($callback_email_template_object -> cc_users != ''){
					
					$default_cc_users = $callback_email_template_object -> cc_users;
					
				}
				
				if($callback_email_template_object -> bcc_users != ''){
					
					$default_bcc_users = $callback_email_template_object -> bcc_users;
					
				}
				
				
				$mail_data = array(
				MESSAGE => $internal_callback_message,
				DEFAULT_TO_USERS	=> $default_to_users,
				DEFAULT_CC_USERS	=> $default_cc_users,
				DEFAULT_BCC_USERS	=> $default_bcc_users,
				TO	=> $agent_email,
				CC	=> '',
				BCC => '',
				SUBJECT => $email_template_data -> subject,
				TO_NAME => $agent_name
				);
				
				
				sendMailData($mail_data, $enquiry_id,false);
			}

			/* Text Message to client */
			
			
			$message_template_callback_ext = 'SELECT * FROM message_templates WHERE message_category = "external" AND event = "call_back" LIMIT 1';

			$message_template_callback_ext_resource = mysql_query($message_template_callback_ext);
		
			if($message_template_callback_ext_resource && mysql_num_rows($message_template_callback_ext_resource) > 0){

				$message_template_callback_ext_object = mysql_fetch_object($message_template_callback_ext_resource);
				
				$sms_receiver_numbers = array();
				
				if( $message_template_callback_ext_object -> default_numbers != ''){
					
					
					// create an array of numbers 
					$sms_receiver_numbers = explode(',', $message_template_callback_ext_object -> default_numbers);
					
				}
				
				
				array_push($sms_receiver_numbers, $lead_information['customerMobile']);
				
				
				$external_message_keywords = array(
				'{{customer_name}}',
				'{{callback_date}}',
				'{{callback_time}}',
                '{{projects}}'
				);
				
				
				$external_message_keywords_values = array(
				$lead_information['customerName'],
				$lead_information['future_followup_date'],
				$lead_information['future_followup_time'],
                $sms_project_string
				);
				
				
				$external_message_text			= str_replace($external_message_keywords, $external_message_keywords_values, $message_template_callback_ext_object -> message) ;
				sendSMS($sms_receiver_numbers, $external_message_text);
				
			}
			
			
			// 			End: Message to client
			
			/***********************************************************/
			
			
			// 			Message to agent (INTERNAL SMS)
			
			$message_template_callback_int = 'SELECT * FROM message_templates WHERE message_category = "internal" AND event = "call_back" LIMIT 1';
			
			
			$message_template_callback_int_resource = mysql_query($message_template_callback_int);
			
			
			if($message_template_callback_int_resource && mysql_num_rows($message_template_callback_int_resource) > 0){
				
				
				$message_template_callback_int_object = mysql_fetch_object($message_template_callback_int_resource);
				
				
				$sms_receiver_numbers = array();
				
				
				if( $message_template_callback_int_object -> default_numbers != ''){
					
					
					// 					create an array of numbers 
					$sms_receiver_numbers = explode(',', $message_template_callback_int_object -> default_numbers);
					
				}
				
				
				// 				Add Agent reporting manager number too
				$agent_manager = getEmployeeManager($agent_id);
				
				if(!empty($agent_manager)){
					
					array_push($sms_receiver_numbers, $agent_manager['manager_number']);
					
				}
				
				
				array_push($sms_receiver_numbers, $agent_mobile);
				
				
				$internal_message_keywords = array(
				'{{enquiry_no}}',
                '{{client_name}}',
                '{{client_number}}',
				'{{callback_date}}',
				'{{callback_time}}',
                '{{projects}}'
				);
				
				
				$internal_message_keywords_values = array(
				$enquiry_id,
                $lead_information['customerName'],
                $lead_information['customerMobile'],
				$lead_information['future_followup_date'],
				$lead_information['future_followup_time'],
                $sms_project_string
				);
				
				
				$internal_message_text			= str_replace($internal_message_keywords, $internal_message_keywords_values, $message_template_callback_int_object -> message) ;
				
				
				// 				Internal callback message is closed for now on 3 May/ 2017
				// 				sendSMS($sms_receiver_numbers, $internal_message_text);
				
			}
			
			
			// 			End: Internal SMS
			
			/************************************************************/
			
			
		}
}
