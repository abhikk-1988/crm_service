<?php

/* API to send reminder mails for meeting status(s)*/
session_start();
require 'function.php';

$_post_data = filter_input_array(INPUT_POST);

$enquiry_id		      = '';
$client_data	      = array();
$project_data	      = array();
$site_visit_data      = array();

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
		
		// set flag of email sent or not
		mysql_query('UPDATE lead SET `is_email_template_sent` = "'.date('Y-m-d H:i:s').'" WHERE enquiry_id = '.$enquiry_id.'');
	}
	
	curl_close($curl);
}

function sendSMS($numbers = array() , $message = ''){
	
	if( !empty($numbers)){
		
//		foreach($numbers as $number){
			
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
//		} // end foreach
	} // end if condition 
}

if( isset($_post_data) && isset($_post_data['enquiry_id'])){	
	$enquiry_id = $_post_data['enquiry_id'];
}


if( $enquiry_id != ''){
	
	// Getting client info from lead table 
	
	$client_info_query = 'SELECT '
			. ' customerMobile, customer_alternate_mobile, '
			. ' customerLandline, customerEmail, customerName, '
			. ' customerProfession, customerCity, customerAddress, '
			. ' email_template_id, lead_id, disposition_status_id, '
			. ' disposition_sub_status_id, lead_assigned_to_asm, lead_assigned_to_sp, site_visit_id '
			. ' FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
	
	$client_info_result = mysql_query($client_info_query);

	if($client_info_result && mysql_num_rows($client_info_result) > 0) {
		
		$client_data  = mysql_fetch_object($client_info_result); // object
	}

    // Getting project info site visit table 

	$project_info = 'SELECT 
                            project, site_visit_timestamp, site_location, number_of_person_visited, vehicle_accomodated, executiveId, executiveName, project, client
                     FROM `site_visit` 
                     WHERE site_visit_id = "'.$client_data -> site_visit_id.'" AND enquiry_id = '.$enquiry_id.' LIMIT 1';
	
	$project_info_result = mysql_query($project_info);
	
	if($project_info_result && mysql_num_rows($project_info_result) > 0){
        
		while($row = mysql_fetch_assoc($project_info_result)){
            
			// decode JSON string into object 
			$project_data = json_decode($row['project'], true); 
            
            // push site visit data
            $site_visit_data = array(
                'site_location' => $row['site_location'],
                'number_of_person_visited' => $row['number_of_person_visited'],
                'vehicle_accomodated' => $row['vehicle_accomodated'],
                'executiveId' => $row['executiveId'],
                'executiveName' => $row['executiveName'],
                'site_visit_date' => date('Y-m-d H:i:s',$row['site_visit_timestamp'] / 1000 ),
                'project' => json_decode($row['project'], true),
                'client' => json_decode($row['client'], true)
            );
		}
	}
    
	$project_details = new stdClass();

	if( !empty($project_data)){
		
		$project_details -> project_name = $project_data[0]['project_name'];
		$project_details -> project_city = $project_data[0]['project_city'];
		$project_details -> project_url  = $project_data[0]['project_url'];
	}
    
	// Get Email Template with default users 
	if( $client_data -> email_template_id != ''){
		
		$email_template = 'SELECT email_category, event, subject, to_users, cc_users, bcc_users, message_body 
         FROM email_templates 
         WHERE template_id = '.$client_data -> email_template_id ;
		
		$email_template_result = mysql_query($email_template);
		
		if( $email_template_result && mysql_num_rows($email_template_result) > 0){
			
            // getting Email template Data
			$email_template_data = mysql_fetch_object($email_template_result);
            
			switch( $email_template_data -> event){
				
				case 'site_visit_schedule': 
                    
					$keyword_to_replace =  array('{{customer_name}}','{{project_name}}','{{project_city}}','{{site_visit_date}}','{{project_link}}');
					
					$replacement_values = array(
                        $client_data -> customerName, 
                        $project_details->project_name, 
                        $project_details->project_city, 
                        $site_visit_data['site_visit_date'], 
                        $project_details->project_url
                    );
                    
					$message			= str_replace($keyword_to_replace, $replacement_values, $email_template_data -> message_body);
							
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
						TO	=> $client_data -> customerEmail,
						CC	=> '',
						BCC => '',
						SUBJECT => $email_template_data -> subject,
						TO_NAME => $client_data -> customerName
					);
					
					sendMailData($mail_data, $enquiry_id);
					
					$sms_template = 'SELECT default_numbers, message 
                    FROM message_templates 
                    WHERE message_category = "external" AND event = "site_visit_schedule" LIMIT 1';
					
					$sms_template_result = mysql_query($sms_template);
					
					if($sms_template_result && mysql_num_rows($sms_template_result) > 0){
						
						$sms_template_data = mysql_fetch_object($sms_template_result);
						
						$sms_receiver_numbers = array();
						
						if( $sms_template_data -> default_numbers != ''){
							
							// create an array of numbers 
							$sms_receiver_numbers = explode(',', $sms_template_data -> default_numbers);
						}
						
						array_push($sms_receiver_numbers, $client_data -> customerMobile);
						
						$meeting_data = getLeadMeetingData($enquiry_id); // meeting data 
						
						$sms_keyword_to_replace = array('{{customer_name}}','{{project_name}}','{{project_city}}','{{project_link}}','{{site_visit_date}}');
                        
						$sms_keyword_values		= array(
                            $client_data -> customerName, 
                            $project_details -> project_name, 
                            $project_details -> project_city, 
                            $project_details -> project_url, 
                            $site_visit_data['site_visit_date']
                        );
                        
						$sms_message			= str_replace($sms_keyword_to_replace, $sms_keyword_values, $sms_template_data -> message) ;
						sendSMS($sms_receiver_numbers, $sms_message);
					}
					
					break;
				
				case 'site_visit_reschedule';
					
					$siteVisitData = getSiteVisitDataById($client_data -> site_visit_id); // meeting data 
					
                    // site visit project 
                    if(isset($siteVisitData['project']) && !empty($siteVisitData['project'])){
                        
                        // json decode project string
                        $siteVisitProject = json_decode($siteVisitData['project'],true);
                    }
                    
					$keyword_to_replace =  array('{{customer_name}}','{{project_name}}','{{project_city}}','{{customer_number}}','{{project_link}}','{{site_visit_date}}');
                    
					$replacement_values = array(
                        $client_data -> customerName, 
                        $siteVisitProject[0]['project_name'], 
                        $siteVisitProject[0]['project_city'], 
                        $client_data->customerMobile, 
                        $siteVisitProject[0]['project_url'], 
                        $site_visit_data['site_visit_date']
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
						TO	=> $client_data -> customerEmail,
						CC	=> '',
						BCC => '',
						SUBJECT => $email_template_data -> subject,
						TO_NAME => $client_data -> customerName
					);
					
					sendMailData($mail_data, $enquiry_id);
					
                    // Fetching SMS template   
					$sms_template = 'SELECT default_numbers, message FROM message_templates WHERE message_category = "external" AND event = "site_visit_reschedule" LIMIT 1';
					
					$sms_template_result = mysql_query($sms_template);
					
					if($sms_template_result && mysql_num_rows($sms_template_result) > 0){
						
						$sms_template_data = mysql_fetch_object($sms_template_result);
						
						$sms_receiver_numbers = array();
						
						if( $sms_template_data -> default_numbers != ''){
							
							// create an array of numbers 
							$sms_receiver_numbers = explode(',', $sms_template_data -> default_numbers);
						}
						
						array_push($sms_receiver_numbers, $client_data -> customerMobile);
						
						$sms_keyword_to_replace = array('{{customer_name}}','{{project_name}}','{{project_city}}','{{project_link}}','{{site_visit_date}}');
						$sms_keyword_values		= array(
                            $client_data -> customerName, 
                            $siteVisitProject[0]['project_name'], 
                            $siteVisitProject[0]['project_city'], 
                            $siteVisitProject[0]['project_url'], 
                            $site_visit_data['site_visit_date']
                        );
                        
						$sms_message  = str_replace($sms_keyword_to_replace, $sms_keyword_values, $sms_template_data -> message) ;
                        
						sendSMS($sms_receiver_numbers, $sms_message);
					}
					
					break;
			
				case 'site_visit_done':
				    
                    // sending site visit done notification to users
                    
					$siteVisitData = getSiteVisitDataById($client_data -> site_visit_id); // site visit data 
                    
                    $client_info    = array();
                    $project_info   = array();
                    
                    if(!empty ($siteVisitData)){
                        
                        $client_info    = json_decode($siteVisitData['client'], true);
                        $project_info   = json_decode($siteVisitData['project'], true);   
                    }
                    
                    $keyword_to_replace =  array('{{customer_name}}','{{project_name}}','{{project_city}}','{{sales_person_name}}','{{project_link}}','{{site_visit_date}}');
                    
					$replacement_values = array(
                        $client_data -> customerName, 
                        $project_info[0]['project_name'], 
                        $project_info[0]['project_city'], 
                        $siteVisitData['executiveName'], 
                        $project_info[0]['project_url'], 
                        $site_visit_data['site_visit_date']
                    );
				
					$message  = str_replace($keyword_to_replace, $replacement_values, $email_template_data -> message_body);
							
					$default_to_users  = '';
					$default_cc_users  = '';
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
						TO	=> $client_data -> customerEmail,
						CC	=> '',
						BCC => '',
						SUBJECT => $email_template_data -> subject,
						TO_NAME => $client_data -> customerName
					);
					
					sendMailData($mail_data, $enquiry_id);
                    
                    
                    // SMS Sending
                    
                    $select_sms_template = 'SELECT default_numbers, message FROM message_templates WHERE message_category = "external" AND event = "site_visit_done" LIMIT 1';
                    
                    $sms_result = mysql_query($select_sms_template);
                    
                    if($sms_result && mysql_num_rows($sms_result) > 0){
                    
                        $sms_template_data = mysql_fetch_object($sms_result);
						
						$sms_receiver_numbers = array();
						
						if( $sms_template_data -> default_numbers != ''){
							
							// create an array of numbers 
							$sms_receiver_numbers = explode(',', $sms_template_data -> default_numbers);
						}
						
						array_push($sms_receiver_numbers, $client_data -> customerMobile);
						
						$sms_keyword_to_replace = array('{{customer_name}}','{{project_name}}','{{project_city}}','{{project_link}}','{{site_visit_date}}','{{sales_person_name}}');
						$sms_keyword_values		= array(
                            $client_data -> customerName, 
                            $siteVisitProject[0]['project_name'], 
                            $siteVisitProject[0]['project_city'], 
                            $siteVisitProject[0]['project_url'], 
                            $site_visit_data['site_visit_date'],
                            $siteVisitData['executiveName']
                        );
                        
						$sms_message  = str_replace($sms_keyword_to_replace, $sms_keyword_values, $sms_template_data -> message) ;
                        
						sendSMS($sms_receiver_numbers, $sms_message);
                    }
                    
					break;
			}
		}
	}
}

