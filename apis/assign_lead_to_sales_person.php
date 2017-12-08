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
                    CURLOPT_RETURNTRANSFER =>  true, // set true to return the result as a string 
                    CURLOPT_URL => $url,
                    CURLOPT_TIMEOUT => 120
                ));
                // Send the request & save response to $resp
                $resp = curl_exec($curl);
                // Close request to clear up some resources
                curl_close($curl);

	   } // end if condition 
    }

    $data = filter_input_array(INPUT_POST);


    if(!empty($data)){

        if(isset($data['enquiry_id']) && isset($data['sales_person'])){

            $enquiry_id			= $data['enquiry_id'];
            $sales_person		= $data['sales_person']; // Id of the assigned sales person
            $current_date		= date('Y-m-d H:i:s'); 
            // Calculate expire date
            $expire_date = date('Y-m-d', strtotime("+14 days"));
            
            $sales_person_name	= getEmployeeName($sales_person);
            $sales_person_email	= getEmployeeEmailAddress($sales_person);
            $sales_person_number = getEmployeeMobileNumber($sales_person);
            
            $current_month		= (int) date('m') - 1;
            $current_year		= date('Y'); 

            $login_user_id		= $_SESSION['currentUser']['id']; // This should probably the ASM id who logged in currently
            $login_user_name    = getEmployeeName($login_user_id);

			// Get Lead Info 
			$get_lead_category_type = 'SELECT lead_category,customerName,customerEmail,customerAddress, customer_alternate_mobile,customerProfession, customerMobile,lead_added_by_user, disposition_status_id,disposition_sub_status_id, meeting_id, site_visit_id FROM lead WHERE enquiry_id = '.$enquiry_id.'  LIMIT 1';

			$lead_category_result	= mysql_query($get_lead_category_type);

			if($lead_category_result && mysql_num_rows($lead_category_result) > 0){
				
			    $lead_data 					= mysql_fetch_object($lead_category_result);
				
			    $disposition_status_id 		= $lead_data -> disposition_status_id; // Primary status 
			    
			    $disposition_sub_status_id 	= $lead_data -> disposition_sub_status_id; // Secondary Status
			    
				$lead_added_by_user 		= $lead_data -> lead_added_by_user; // lead CRM
			}
            
            
            // assign lead to sales person
            $update_lead = 'UPDATE `lead` '
                    . ' SET lead_assigned_to_sp = '.$sales_person.','
                    . '  lead_assigned_to_sp_on = "'.$current_date.'",'
                    . '  lead_expire_date_of_sp = "'.$expire_date.'"'
                    . 'WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
                    

            if(mysql_query($update_lead)){
            	
                
                // If ASM is assigning the lead to itself then we will not execute below queries
                // Here we assume login user id will always be area sales manager id
                
                if($sales_person != $login_user_id){
                
                    // insert data into re-assign table as a log purpose (Umesh)
                    mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='processed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='area_sales_manager' AND enquiry_id='$enquiry_id' AND change_status='pending' ORDER BY ID DESC) s) LIMIT 1");
				
				    mysql_query("INSERT INTO lead_re_assign(enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, lead_type, remark, change_status, added_by) VALUES('$enquiry_id','sales_person','0','$sales_person','$disposition_status_id','$disposition_sub_status_id','assign','lead assign to sp','pending','$login_user_id')");

                    // update remaining capacity value of sales person in current month capacity
                    $update_remaining_capacity = 'UPDATE sales_person_capacities '
                            . ' SET remaining_capacity = remaining_capacity - 1 '
                            . ' WHERE sales_person_id = '.$sales_person.' AND month = '.$current_month.' AND year = "'.$current_year.'"';

                    mysql_query($update_remaining_capacity);

                }else{
                    
                    // SET column value of lead accepted flag and datetime for Area Sales Manager
                    
                    mysql_query('UPDATE lead SET is_lead_accepted = 1 AND lead_accept_datetime = "'.date('Y-m-d H:i:s').'" WHERE enquiry_id = '. $enquiry_id .' LIMIT 1');
                    
                }
                
                // Log history of lead assignment
                $asm_name = $login_user_name;
                $assignment_date = date('d-m-Y H:i:s');
                
                if($sales_person != $login_user_id){
                     $details = 'Lead Assign by ASM ('.$asm_name.') to SP ('.$sales_person_name.') at '.$assignment_date.'';
                }
                else{
                     $details = 'Lead Assign by ASM ('.$asm_name.') to self at '.$assignment_date.'';
                }
                
                $lead_number = getLeadNumber($enquiry_id);
                $assignmentHistory = array(
                    'enquiry_id'  => $enquiry_id,
                    'lead_number' => $lead_number,
                    'details' => $details,
                    'type' => 'new',
                    'employee_id' => $login_user_id
                );

                createLog($assignmentHistory);
                
                
                /**********************************************************************************************/
                // SEND INTERNAL MAIL TO SALES PERSON OF LEAD ASSIGNMENT
                /**********************************************************************************************/
                
                $get_email_template = 'SELECT * FROM email_templates WHERE email_category = "internal" AND event = "lead_assignment_level_3" LIMIT 1';
                
                $email_template_resource = mysql_query($get_email_template);
                
                if($email_template_resource && mysql_num_rows($email_template_resource) > 0){
                    
                    $email_template_object = mysql_fetch_object($email_template_resource);
                    
                    $client_info = getCLientInfoByEnquiry($enquiry_id);
                    
                    $lead_info = getLead($enquiry_id);
                    
                    $address = '';
                    $scheduled_datetime = '';
                    $project_name = '';
                    $project_city = '';
                    $lead_status = getCurrentEnquiryStatus($enquiry_id);

                    if($lead_info['meeting_id'] != ''){
                        $meeting_data = getLeadMeetingData($enquiry_id, $lead_info['meeting_id']);
                        $project_data = json_decode($meeting_data['project'],true);
                        
                        if(!empty($project_data)){
                            $project_name = $project_data[0]['project_name'];
                            $project_city = $project_data[0]['project_city'];
                        }
                        
                        $address = $meeting_data['meeting_address'];
                        $scheduled_date = date('d-M-Y', $meeting_data['meeting_timestamp']/1000);
                        $scheduled_time = date('H:i A', $meeting_data['meeting_timestamp']/1000);
                    
                    }
                    else if($lead_info['site_visit_id'] != ''){
                        $site_visit_data    = getSiteVisitDataById($lead_info['site_visit_id']);
                        $project_data       = json_decode($site_visit_data['project'],true);
                        
                        if(!empty($project_data)){
                            $project_name = $project_data[0]['project_name'];
                            $project_city = $project_data[0]['project_city'];
                        }
                        
                        $address            = $site_visit_data['site_location'];
                        $scheduled_date = date('d-M-Y', $site_visit_data['site_visit_timestamp']/1000);
                        $scheduled_time = date('H:i A', $site_visit_data['site_visit_timestamp']/1000);
                    }
                    
                    $mail_keywords = array(
                        '{{enquiry_id}}',
                        '{{status}}',
                        '{{scheduled_date}}',
                        '{{scheduled_time}}',
                        '{{lead_owner}}',
                        '{{client_name}}',
                        '{{client_number}}',
                        '{{client_alternate_number}}',
                        '{{client_profession}}',
                        '{{client_address}}',
                        '{{project_name}}',
                        '{{tm}}',
                        '{{sales_person}}'
                        );
                    
                    $keyword_replacement_values = array(
                        $enquiry_id,
                        $lead_status['primary_status_title'].' '. $lead_status['secondary_status_title'],
                        $scheduled_date,
                        $scheduled_time,
                        getEmployeeName($lead_info['lead_added_by_user']),
                        $client_info['customerName'],
                        $client_info['customerMobile'],
                        $client_info['customer_alternate_mobile'],
                        $client_info['customerProfession'],
                        $address,
                        $project_name,
                        $asm_name,                        
                        $sales_person_name
                    );
                    
                    $mail_body = str_replace($mail_keywords, $keyword_replacement_values, $email_template_object -> message_body);
                           
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
                        TO	=> $sales_person_email,
                        CC	=> '',
                        BCC => '', // add if any 
                        SUBJECT => $email_template_object -> subject,
                        TO_NAME => $sales_person_name
                    );
        
                    sendMailData($mail_data, $enquiry_id);
                    
                    // END: INTERNAL MAIL TO SLES PERSON
                    /**********************************************************************************************/
                    
                    
                    /***********************************************************************************************/
                    // SEND SMS TO SALES PERSON OF LEAD ASSIGNMENT
                    /***********************************************************************************************/
                    
                    $sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "internal" AND event = "lead_assign_to_sp" LIMIT 1');
    
                    if($sms_template && mysql_num_rows($sms_template) > 0){
         
                        $sms_template_object = mysql_fetch_object($sms_template);
         
                        $sms_keywords = array(
                            '{{enquiry_id}}',
                            '{{status}}',
                            '{{scheduled_date}}',
                            '{{scheduled_time}}',
                            '{{lead_owner}}',
                            '{{client_name}}',
                            '{{client_number}}',
                            '{{client_alternate_number}}',
                            '{{client_profession}}',
                            '{{client_address}}',
                            '{{project_name}}',
                            '{{tm}}',
                            '{{sales_person}}'
                        );
         
                         $sms_keyword_values = array(
                            $enquiry_id,
                            $lead_status['primary_status_title'].' '.$lead_status['secondary_status_title'],
                            $scheduled_date,
                            $scheduled_time,
                            getEmployeeName($lead_info['lead_added_by_user']),
                            $client_info['customerName'],
                            $client_info['customerMobile'],
                            $client_info['customer_alternate_mobile'],
                            $client_info['customerProfession'],
                            $address, // this is meeting or site visit address
                            $project_name,
                            $asm_name,
                            $sales_person_name
						);
         
                        $sms_body = str_replace($sms_keywords, $sms_keyword_values, $sms_template_object -> message);
         
                        $sms_receiver_numbers = array();
						
                        if( $sms_template_object -> default_numbers != ''){				
                            // create an array of numbers 
                            $sms_receiver_numbers = explode(',', $sms_template_object -> default_numbers);
                        }
						
                        array_push($sms_receiver_numbers, $sales_person_number);
                        sendSMS($sms_receiver_numbers, $sms_body);
                    }
                    
                    # END
                    ###############################################################################################
                    
                }
                
                // Send Internal reminder mail according to the status of the lead 
                
                $lead_current_status = getCurrentEnquiryStatus($enquiry_id);
                
                if(!empty($lead_current_status)){
                    
                    
                    if($lead_current_status['primary_status_id'] == 3){ // Meeting 
                        
                        switch($lead_current_status['secondary_status_id']){
                                
                            case 22: // meeting schedule
                                sendInternalReminderMail('send_internal_meeting_schedule_reminder_mail.php',$enquiry_id);
                                break;
                            case 12: // meeting reschedule
                                sendInternalReminderMail('send_internal_meeting_reschedule_reminder_mail.php',$enquiry_id);
                                break;
                            case 11: // meeting done
                                break;
                        }
                        
                    }
                    else if ($lead_current_status['primary_status_id'] == 6 ){ // Site visit 
                        
                        switch($lead_current_status['secondary_status_id']){
                                
                            case 23: // schedule 
                                sendInternalReminderMail('send_internal_site_visit_schedule_reminder_mail.php',$enquiry_id);
                                break;
                            case 15: // reschedule
                                sendInternalReminderMail('send_internal_site_visit_reschedule_reminder_mail.php',$enquiry_id);
                                break;
                            case 14: // done
                                break;
                        }   
                    }
                }
                
                

                echo json_encode(
                    array(
                    'success' => 1, 
                    'message' => 'Lead has been assigned to sales person successfully'
                    ),true
                ); exit;
            }
            else{
                echo json_encode(
                    array(
                    'success' => 0, 
                    'error' => 'Something went wrong. Lead couldn\'t be assigned. Please try again later'
                    ),true
                ); exit;
            }
        }
        else{
            json_encode(
                array(
                'success' => 0, 
                'error' => 
                'No data recieved'
                ), true
            );
        }
    }
    else{
        json_encode(
            array(
            'success' => 0, 
            'error' => 'No data recieved'
            ), true
        ); exit;
    }


