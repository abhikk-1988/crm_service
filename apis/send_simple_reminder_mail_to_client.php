    <?php

session_start();

require 'function.php';


$_post_data = filter_input_array(INPUT_POST);

$hot_projects = array();

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
		
		
		// 		set flag of email sent or not
		mysql_query('UPDATE lead SET `is_email_template_sent` = "'.date('Y-m-d H:i:s').'" WHERE enquiry_id = '.$enquiry_id.'');
		
	}

	curl_close($curl);
	
	return $result;	
}


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
			
			
			// 			Get cURL resource
			$curl = curl_init();
			
			// 			Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $url,
			CURLOPT_TIMEOUT => 120
			));
			
			// Send the request & save response to $resp
			$resp = curl_exec($curl);
			
			// Close request to clear up some resources
			curl_close($curl);
		}
		// 		end if condition 
	}
	
	
	if( isset($_post_data['enquiry_id']) && $_post_data['enquiry_id'] != ''){
		
		
		$enquiry_id = $_post_data['enquiry_id'];
		
		// 		Hot projects 
		if(isset($_post_data['hot_projects'])){
			
			$hot_projects = unserialize(base64_decode($_post_data['hot_projects']));
			
		}
		
		
		$lead_information = array();

		$lead_information = getLead($enquiry_id);
			
			// 			Current Enquiry status 
			$current_enquiry_status = $lead_information['disposition_status_id'];
			
			
			// fetch inquired project information
			$inquired_projects = array();
			$select_inquired_projects = 'SELECT * FROM `lead_enquiry_projects` WHERE enquiry_id = '.$enquiry_id.'';
			$inq_projects_resource = mysql_query($select_inquired_projects);
			if($inq_projects_resource && mysql_num_rows($inq_projects_resource) > 0){
                while($row = mysql_fetch_assoc($inq_projects_resource)){
                    array_push($inquired_projects, $row);
                }
			}

            $html               = '';
            $sms_project_string = '';
            if(!empty($inquired_projects)){
                require_once 'email_project_partial.php';
                require_once 'sms_project_partial.php';
            }

            // ─────────────────Get Email Template ────────────────────────────────────────────────

				$email_template = 'SELECT email_category, event, subject, to_users, cc_users, bcc_users, message_body 
                                    FROM email_templates 
                                    WHERE template_id = '.$lead_information['email_template_id'].' LIMIT 1';
				
				
				$email_template_result = mysql_query($email_template);
				
				
				// 	HTML template of hot projects to be used in email and sms
				$hot_project_html = '';
                $hot_project_text = '';
				require_once 'hot_projects_partial.php';
				// END: hot proejct html 
				
				if( $email_template_result && mysql_num_rows($email_template_result) > 0){

					$email_template_data = mysql_fetch_object($email_template_result);
					
					
					$keyword_to_replace =  array(
					'{{customer_name}}',
                    '{{projects}}',
					'{{hot_project_list}}'
					);
					
					
					$replacement_values = array(
					$lead_information['customerName'], 
					$html,
					(isset($hot_project_html) ? $hot_project_html : '')
					);
				
					$message  = str_replace($keyword_to_replace, $replacement_values, $email_template_data -> message_body);
					
					$default_to_users = '';
					
					$default_cc_users = '';
					
					$default_bcc_users = '';
					
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
					
					// sending mail
					$result = sendMailData($mail_data, $enquiry_id);
					
					// Send just enquiry message here 
					// Get SMS template according to enquiry status 
					
					$sms_template        = '';
					
					$sms_template_object = '';
					
					if($current_enquiry_status == 1){
						
						// get sms template of not interested
						
						$sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "external" AND event = "not_interested" LIMIT 1');
						
						if($sms_template && mysql_num_rows($sms_template) > 0){
							
							$sms_template_object = mysql_fetch_object($sms_template);
							
							$sms_keywords = array('{{customer_name}}','{{hot_project_list}}');
							
							$sms_keywords_value = array($lead_information['customerName'], $hot_project_text);
						}
					}
					
					else if($current_enquiry_status == 34){
						
						// get sms template of just enquiry 

						$sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "external" AND event = "just_enquiry" LIMIT 1');
						
						if($sms_template && mysql_num_rows($sms_template) > 0){
							
							$sms_template_object = mysql_fetch_object($sms_template);
							
							$sms_keywords = array(
                                '{{customer_name}}',
                                '{{projects}}',
                                '{{hot_project_list}}'
							);	
							
							$sms_keywords_value = array(
                                $lead_information['customerName'], 
                                $sms_project_string,
                                $hot_project_text
							);
						}
					}
					
					$sms_body               = str_replace($sms_keywords,$sms_keywords_value,$sms_template_object->message);
					$sms_receiver_numbers   = array();
					
					if( $sms_template_object -> default_numbers != ''){
						// create an array of numbers 
						$sms_receiver_numbers = explode(',', $sms_template_object -> default_numbers);
						
					}
					
					array_push($sms_receiver_numbers, $lead_information['customerMobile']);
				    sendSMS($sms_receiver_numbers, $sms_body);
				}
	}