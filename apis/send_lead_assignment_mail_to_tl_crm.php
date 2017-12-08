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
	
	curl_close($curl);
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

$_post = filter_input_array(INPUT_POST);
 
// Get TL CRM user 
$tl_crm = 'SELECT firstname, lastname, email, contactNumber FROM employees WHERE designation = (SELECT id FROM designationmaster WHERE designation_slug = "sr_team_leader" LIMIT 1)';

$tl_result = mysql_query($tl_crm);

if($tl_result && mysql_num_rows($tl_result) > 0){
    
    $tl_crm_object = mysql_fetch_object($tl_result);

    // get email template 
    
    $email_template = mysql_query('SELECT * 
    FROM email_templates WHERE email_category = "internal" AND event = "lead_assignment_level_1" LIMIT 1');
    
    if($email_template && mysql_num_rows($email_template) > 0){
        
        $email_template_object = mysql_fetch_object($email_template);
        
        $keywords = array(
            '{{enquiry_id}}',
            '{{lead_number}}',
            '{{client_name}}',
            '{{address}}',
            '{{project_city}}',
            '{{project_name}}'
        );
     
        $keywords_replacement_values = array(
            $_post['enquiry_id'],
            $_post['lead_number'],
            $_post['client_name'],
            $_post['address'],
            $_post['project_city'],
            $_post['project_name']  
        );
        
        $mail_body = str_replace($keywords, $keywords_replacement_values, $email_template_object -> message_body);
        
        $default_to_users = '';
        $default_cc_users = '';
        $default_bcc_users = '';
					
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
            TO	=> $tl_crm_object -> email,
            CC	=> '',
            BCC => '',
            SUBJECT => $email_template_object -> subject,
            TO_NAME => $tl_crm_object ->firstname .' '.$tl_crm_object -> lastname
        );
					
       sendMailData($mail_data, $_post['enquiry_id']);   
    }
    
    
    // SMS to TL CRM
    
     $sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "internal" AND event = "lead_assign_to_tl_crm" LIMIT 1');
    
     if($sms_template && mysql_num_rows($sms_template) > 0){
         
         $sms_template_object = mysql_fetch_object($sms_template);
         
         $sms_keywords = array(
            '{{enquiry_id}}',
            '{{lead_number}}',
            '{{client_name}}',
			'{{client_number}}',
            '{{address}}',
            '{{project_city}}',
            '{{project_name}}'
         );
         
         $sms_keyword_values = array(
            $_post['enquiry_id'],
            $_post['lead_number'],
            $_post['client_name'],
			$_post['client_number'],
            $_post['address'],
            $_post['project_city'],
            $_post['project_name']
         );
         
         $sms_body = str_replace($sms_keywords, $sms_keyword_values, $sms_template_object -> message);
         
         $sms_receiver_numbers = array();
						
         if( $sms_template_object -> default_numbers != ''){
							
            // create an array of numbers 
            $sms_receiver_numbers = explode(',', $sms_template_object -> default_numbers);
         }
						
         array_push($sms_receiver_numbers, $tl_crm_object -> contactNumber );
        
         sendSMS($sms_receiver_numbers, $sms_body);
     }
}
