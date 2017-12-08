        <?php

        session_start();

        require 'function.php';

        $_post_data = filter_input_array(INPUT_POST);

        $enquiry_id = '';

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
                        // set flag of email sent or not
                        mysql_query('UPDATE lead SET `is_email_template_sent` = "'.date('Y-m-d H:i:s').'" WHERE enquiry_id = '.$enquiry_id.'');
                    }    
                }

                curl_close($curl);
                return $result;
            }

        function sendSMS($numbers = array() , $message = ''){
	
	       if( !empty($numbers)){
		
//		      foreach($numbers as $number){
			
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

        if( isset($_post_data['enquiry_id']) && $_post_data['enquiry_id']!= ''){

            $enquiry_id = $_post_data['enquiry_id'];

            $lead_information = array();

                $select_lead = 'SELECT '
                        . ' customerMobile, customer_alternate_mobile, '
                        . ' customerLandline, customerEmail, customerName, '
                        . ' customerProfession, customerCity, customerAddress, '
                        . ' email_template_id, lead_id, disposition_status_id, '
                        . ' disposition_sub_status_id, lead_assigned_to_asm, lead_assigned_to_sp,future_followup_date, future_followup_time,'
                        . ' lead_added_by_user'
                        . ' FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';

                $result = mysql_query($select_lead);

                if($result && mysql_num_rows($result) > 0){

                    $lead_information = mysql_fetch_assoc($result);
                    
                    // fetch project information
                    $projects = array();

                    $select_projects = 'SELECT * FROM `lead_enquiry_projects` WHERE enquiry_id = '.$enquiry_id.'';

                    $projects_resource = mysql_query($select_projects);

                    $project_link1 = '';
                    $project_link2 = '';
                    $project_link3 = '';

                    if($projects_resource && mysql_num_rows($projects_resource) > 0){

                       while($row = mysql_fetch_assoc($projects_resource)){
                            array_push($projects, $row);
                        }

                        if(!empty($projects)){

                            foreach($projects as $index => $val){

                                if($index == 0){
                                    $project_link1 = $val['project_url'];
                                }

                                if($index == 1){
                                    $project_link2 = $val['project_url'];
                                }  

                                if($index == 2){
                                    $project_link3 = $val['project_url'];
                                }
                            }
                        }

                    }
                    
                    
                    if($lead_information['email_template_id'] != ''){

                        /*********************************************************************************/
                        // EXTERNAL MAIL TO CLIENT
                        /*********************************************************************************/

                        $email_template = 'SELECT email_category, event, subject, to_users, cc_users, bcc_users, message_body 
                                            FROM email_templates 
                                            WHERE template_id = '.$lead_information['email_template_id'] ;

                        $email_template_result = mysql_query($email_template);

                        if( $email_template_result && mysql_num_rows($email_template_result) > 0){

                            $email_template_data = mysql_fetch_object($email_template_result);

                            $keyword_to_replace =  array(
                                '{{customer_name}}',
                                '{{project_name}}',
                                '{{project_city}}',
                                '{{project_link}}',
                                '{{callback_date}}',
                                '{{callback_time}}'
                            );

                            $replacement_values = array(
                                    $lead_information['customerName'], 
                                    ($projects[0]['project_name'] ? $projects[0]['project_name'] : ''), 
                                    ($projects[0]['project_city'] ? $projects[0]['project_city'] : ''), 
                                    ($projects[0]['project_url']  ? $projects[0]['project_url']   : ''),
                                    date('d-M-Y', strtotime($lead_information['future_followup_date'])), 
                                    $lead_information['future_followup_time']
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
                            

                            // End: EXTERNAL MAIL
                            /****************************************************************************************************/

                            /****************************************************************************************************/
                            // INTERNAL MAIL TO CLIENT
                            /****************************************************************************************************/

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
                                    '{{project_name}}',
                                    '{{project_city}}'
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
                                    ($projects[0]['project_name'] ? $projects[0]['project_name'] : ''), 
                                    ($projects[0]['project_city'] ? $projects[0]['project_city'] : ''), 
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

                            // End: INTERNAL MAIL OF CALLBACK
                            /****************************************************************************************************/

                            //Message 

                            // Message to client (EXTERNAL SMS)

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
                                    '{{project_name}}',
                                    '{{project_city}}',
                                    '{{callback_date}}',
                                    '{{callback_time}}',
                                    '{{project_link1}}',
                                    '{{project_link2}}',
                                    '{{project_link3}}'
                                );

                                $external_message_keywords_values = array(
                                    $lead_information['customerName'],
                                    $projects[0]['project_name'],
                                    $projects[0]['project_city'],
                                    $lead_information['future_followup_date'],
                                    $lead_information['future_followup_time'],
                                    $project_link1,
                                    '',
                                    ''
                                );

                                $external_message_text			= str_replace($external_message_keywords, $external_message_keywords_values, $message_template_callback_ext_object -> message) ;

                                sendSMS($sms_receiver_numbers, $external_message_text);
                            }

                            // End: Message to client
                            /***********************************************************/

                            // Message to agent (INTERNAL SMS)

                                $message_template_callback_int = 'SELECT * FROM message_templates WHERE message_category = "internal" AND event = "call_back" LIMIT 1';

                                $message_template_callback_int_resource = mysql_query($message_template_callback_int);

                                if($message_template_callback_int_resource && mysql_num_rows($message_template_callback_int_resource) > 0){

                                $message_template_callback_int_object = mysql_fetch_object($message_template_callback_int_resource);

                                $sms_receiver_numbers = array();

                                if( $message_template_callback_int_object -> default_numbers != ''){

                                    // create an array of numbers 
                                    $sms_receiver_numbers = explode(',', $message_template_callback_int_object -> default_numbers);
                                }

                                // Add Agent reporting manager number too
                                $agent_manager = getEmployeeManager($agent_id);
                                if(!empty($agent_manager)){
                                    array_push($sms_receiver_numbers, $agent_manager['manager_number']);
                                }
                                
                                array_push($sms_receiver_numbers, $agent_mobile);

                                $internal_message_keywords = array(
                                    '{{enquiry_no}}',
                                    '{{project_name}}',
                                    '{{project_city}}',
                                    '{{callback_date}}',
                                    '{{callback_time}}'
                                );

                                $internal_message_keywords_values = array(
                                    $enquiry_id,
                                    $projects[0]['project_name'],
                                    $projects[0]['project_city'],
                                    $lead_information['future_followup_date'],
                                    $lead_information['future_followup_time']
                                );

                                $internal_message_text			= str_replace($internal_message_keywords, $internal_message_keywords_values, $message_template_callback_int_object -> message) ;

                                // Internal callback message is closed for now on 3 May/ 2017
                                // sendSMS($sms_receiver_numbers, $internal_message_text);
                            }

                            // End: Internal SMS
                            /************************************************************/

                        }

                    }
                }
        }