<?php
session_start();
require 'function.php';

if(!isset($_SESSION['currentUser'])){

    // User session is out 
    // send response of unauthorized access

    echo json_encode(
        array(
            'success' => (int) 0,
            'http_status_code' => (int) 401,
            'message' => 'We could not update the lead status.',
            'title' => 'Unauthorized access'
        ), true
    );
    exit;
}

$user = $_SESSION['currentUser']; // Loggedin user information

$post_data = filter_input_array(INPUT_POST);

if(!empty($post_data) && isset($post_data['enquiry_id']) && $post_data['enquiry_id'] != ''){

    $errors = array();

    // server validation on input
//     Array
// (
//     [disposition_status_id] => 4
//     [projects] => 
//     [address] => 
//     [disposition_sub_status_id] => 37
//     [date] => Wed May 10 2017
//     [time] => 08:15 AM
//     [remark] => followup testing
//     [user_id] => 5
//     [enquiry_id] => 415070
// )

    if($post_data['disposition_status_id'] == ''){
        array_push($errors,'Plese select disposition status');
    }
   
   
    // Validation of sub disposition status
    $has_sub_disposition_status  = mysql_query('SELECT count(id) as sub_status_count FROM `disposition_status_substatus_master` 
    WHERE parent_status = '.$post_data['disposition_status_id'].' AND active_state = 1');   
    
    if($has_sub_disposition_status && mysql_num_rows($has_sub_disposition_status) > 0){

        $sub_status_count = mysql_fetch_object($has_sub_disposition_status);

        if($sub_status_count -> sub_status_count > 0){

            // Check sub status is selected or not
            if($post_data['disposition_sub_status_id'] == ''){
                array_push($errors , 'Please select sub disposition status');
            }
        }
    }

    if(in_array($post_data['disposition_status_id'], array(3,6))){

        if($post_data['date'] == ''){
            array_push($errors , 'Please select date');
        }

        if($post_data['time'] == ''){
            array_push($errors , 'Please select time');
        }

        // Address validation
        if($post_data['address'] == ''){
            array_push($errors , 'Please enter address');
        }

        // Project validation
        if(empty($post_data['projects'])){
            array_push($errors, 'Please select project');
        }
    }

    
    
    // For lead closure Dead and time validation check 
    if(in_array($post_data['disposition_status_id'] , array(7))){

        if($post_data['date'] == ''){
            array_push($errors , 'Please select date');
        }

        if($post_data['time'] == ''){
            array_push($errors , 'Please select time');
        }
    }
    
    // Validation for remark
    if(empty($post_data['remark']) && $post_data['remarks_mandatory']){
        array_push($errors, 'Please enter remarks');
    }

    if(!empty($errors)){

        echo json_encode(
            array(
                'success' => (int) -1,
                'http_status_code' => (int) 200,
                'errors' => $errors,
                'title' => 'Errors'
            ), true
        );
        exit;
    }

    // Update lead status 

    $date = '';
    $time = '';
    $address = '';
    $remark = '';
    $projects = '';
    $disposition_status_id = "NULL";
    $disposition_sub_status_id = '';
	$activity_status 			= '';
	
    $lead_number = getLeadNumber($post_data['enquiry_id']);

    if($post_data['disposition_status_id']){
        $disposition_status_id = $post_data['disposition_status_id'];
    }    

    if(isset($post_data['disposition_sub_status_id'])){
        $disposition_sub_status_id = $post_data['disposition_sub_status_id'];
    }

    if(isset($post_data['date'])){

        $date = date('Y-m-d', strtotime($post_data['date']));
    }

    if(isset($post_data['time']) && $post_data['time'] !=''){
        $time = $post_data['time'];
    }

    if(isset($post_data['address']) && $post_data['address'] !=''){
        $address = $post_data['address'];
    }
    
    if(isset($post_data['remark']) && $post_data['remark'] !=''){
        $remark = $post_data['remark'];
    }

    if(isset($post_data['projects']) && !empty($post_data['projects'])){
        $projects = $post_data['projects'];
    }

	if(isset($post_data['activity_status'])){
		$activity_status = $post_data['activity_status'];
	}
	
    if($disposition_sub_status_id != ''){
        $update_lead = mysql_query('UPDATE lead SET disposition_status_id = '.$disposition_status_id.', disposition_sub_status_id = '.$disposition_sub_status_id.', enquiry_status_remark = "'.$remark.'", lead_updated_by = '.$post_data['user_id'].',is_overdue = "0" , is_overdue_mail_sent = "0", leadStatus = "'.$activity_status.'" WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');
    }else{
        $update_lead = mysql_query('UPDATE lead SET disposition_status_id = '.$disposition_status_id.', disposition_sub_status_id = "NULL", enquiry_status_remark = "'.$remark.'", lead_updated_by = '.$post_data['user_id'].', is_overdue = "0" , is_overdue_mail_sent = "0" , leadStatus = "'.$activity_status.'" WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');
    }

     // Insert lead status in saperate table

    if(!$disposition_sub_status_id){
        $disposition_sub_status_id = 0;
    }
    
    // Change lead status
    mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='processed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='agent' AND enquiry_id='".$post_data['enquiry_id']."' AND to_user_id='".$user['id']."' AND change_status='pending' ORDER BY ID DESC LIMIT 1) s)");
    
	if(!mysql_query('INSERT INTO `lead_status` (lead_id,enquiry_id, disposition_status_id, disposition_sub_status_id,hot_warm_cold_status,user_type,user_id,remark) VALUES ("'.$lead_number.'",'.$post_data['enquiry_id'].','.$disposition_status_id.','.$disposition_sub_status_id.',"'.($activity_status!= '' ? $activity_status : NULL).'","agent",'.$user['id'].',"'.$remark.'") ')){
		// echo mysql_error(); 
	}

    // Update `change status` in lead_re_assign table
    $enquiry_to_change_status = mysql_query('SELECT id FROM lead_re_assign WHERE enquiry_id = '.$post_data['enquiry_id'].' AND to_user_id = '.$user['id'].' AND user_type = "agent" ORDER BY id DESC LIMIT 1');

    if($enquiry_to_change_status && mysql_num_rows($enquiry_to_change_status) > 0){
        $enquiry_result = mysql_fetch_object($enquiry_to_change_status);
        mysql_query('UPDATE `lead_re_assign` SET change_status = "processed" WHERE id = '.$enquiry_result -> id.' LIMIT 1');
    }

    // Remarks Log
    $remark_log = array(
		'remark' => $remark,
		'enquiry_id' => $post_data['enquiry_id'],
		'employee_id' => $user['id'],
		'remark_creation_date' => date('Y-m-d H:i:s')
	);
	createRemarkLog($remark_log);

    sleep(2);

    if($update_lead){
		#########IVR Disposition Push By Sudhanshu##################
        $status_title = ucfirst(str_replace('_',' ',getDispositionStatusSlug($disposition_status_id)));
        $sub_status_title = ucfirst(str_replace('_',' ',getDispositionStatusSlug($disposition_sub_status_id)));
		$datevalue = $date;
		$timevalue = substr($time,0,5); 
		$new_date = $datevalue.' '.$timevalue.':00'; 
		if($status_title != 'Future references'){
			$new_date = '';
		}
		$ip = $_SERVER['REMOTE_ADDR'];
				
			$dataarray = array('transaction_id' => 'CTI_SET_DISPOSITION','agent_id'=>$user['crm_id'],'ip'=>$ip,'cust_disp'=>$status_title,'category'=>$sub_status_title,'next_call_time'=>$new_date,'resFormat'=>'1');
			$curl = curl_init();
			$url = "";
			//print_r($dataarray);
			foreach($dataarray as $key => $value){
				$url .= urlencode($key).'='.urlencode($value).'&';
			}
			//echo $url;
			$hitURL = "http://admin.c-zentrixcloud.com/apps/appsHandler.php";
			$content = file_get_contents($hitURL.'?'.$url);
	
	##################################################
        // Code block added on 29th May by Abhishek 
        if($disposition_status_id != 47){

            // Update is_callback_done to 1

            mysql_query('UPDATE lead SET is_callback_done = 1 WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
		    mysql_query('UPDATE future_references SET is_callback_done = 1 WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');    

        }
        /********************************/

        switch ($disposition_status_id){

            case '3':

               	$sub_status_slug = getDispositionStatusSlug($post_data['disposition_sub_status_id']);
                $email_template_id = '';

                if($sub_status_slug != ''){
                    $sub_status_slug_without_underscore = str_replace('_','',$sub_status_slug);

                    $event = 'meeting_'.$sub_status_slug_without_underscore;
                    $email_template_id = getEmailTemplateId('external', $event);
                }   

                mysql_query('UPDATE lead SET future_followup_date = "'.$date.'" , future_followup_time = "'.$time.'", email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');

                // create new meeting 
                $meeting_timestamp = strtotime(str_replace(array('','AM','PM','am','pm'),'',$date . $time))*1000;

                // Project
                unset($projects['id']);
                unset($projects['$$hashKey']);
                $project_json = json_encode(array($projects),true);

                // Client information
                $client = getCLientInfoByEnquiry($post_data['enquiry_id']);

                $json_client = json_encode(
                    array(
                        'name' => $client['customerName'],
                        'phone' => $client['customerMobile'],
                        'email'=> $client['customerEmail'],
                        'city' => $client['customerCity']
                        ), true);

                $meeting_status     = 0;
                $executiveId        = $user['id'];
                $executiveName      = $user['firstname'].' '.$user['lastname'];
                $meeting_remark     = $remark;
                $meeting_address    = $address;

                $meeting = array(
                    'enquiry_id' => $post_data['enquiry_id'],
                    'lead_number' => $lead_number,
                    'meeting_time' => $meeting_timestamp,
                    'employee_id' => $executiveId,
                    'employee_name' => $executiveName,
                    'project' => $project_json,
                    'client' => $json_client,
                    'remark' => $meeting_remark,
                    'meeting_address' => $meeting_address,
                );

                callCURL('create_meeting.php',$meeting);

                // 	LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
//                $internal_mail_data = array(
//                    'enquiry_id' => $post_data['enquiry_id'],
//                    'lead_number' => $lead_number,
//                    'client_name' => $client['customerName'],
//                    'client_number' => $client['customerMobile'],
//                    'address' => $meeting_address,
//                    'project_city' => $projects['project_city'],
//                    'project_name' => $projects['project_name']
//                );
//            
//			    sendLeadAssginementMailToTLCRM($internal_mail_data);
//				
            break;

            case '6':

                $sub_status_slug = getDispositionStatusSlug($post_data['disposition_sub_status_id']);
                $email_template_id = '';

                if($sub_status_slug != ''){
                    $sub_status_slug_without_underscore = str_replace('_','',$sub_status_slug);

                    $event = 'site_visit_'.$sub_status_slug_without_underscore;
                    $email_template_id = getEmailTemplateId('external', $event);
                }   

                mysql_query('UPDATE lead SET future_followup_date = "'.$date.'" , future_followup_time = "'.$time.'", email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');

                // create new meeting 
                $site_visit_timestamp = strtotime(str_replace(array('','AM','PM','am','pm'),'',$date . $time))*1000;
                
                // Project
                unset($projects['id']);
                unset($projects['$$hashKey']);
                $project_json = json_encode(array($projects),true);

                // Client information
                $client = getCLientInfoByEnquiry($post_data['enquiry_id']);

                $json_client = json_encode(
                    array(
                        'name' => $client['customerName'],
                        'phone' => $client['customerMobile'],
                        'email'=> $client['customerEmail'],
                        'city' => $client['customerCity']
                        ), true);

                $meeting_status     = 0;
                $executiveId        = $user['id'];
                $executiveName      = $user['firstname'].' '.$user['lastname'];
                $site_visit_remark     = $remark;
                $site_visit_address    = $address;

                $site_visit = array(
                    'enquiry_id' => $post_data['enquiry_id'],
                    'lead_number' => $lead_number,
                    'site_visit_timestamp' => $site_visit_timestamp,
                    'executiveId' => $executiveId,
                    'executiveName' => $executiveName,
                    'project' => $project_json,
                    'client' => $json_client,
                    'remark' => $site_visit_remark,
                    'site_location' =>$site_visit_address,
                    'vehicle_accomodated' => '',
                    'number_of_person_visited' => '',
                    'site_visit_status' => 0,
                );

                callCURL('create_site_visit.php',$site_visit);
                // 	LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
//			    $internal_mail_data = array(
//				    'enquiry_id' => $post_data['enquiry_id'],
//				    'lead_number' => $lead_number,
//				    'client_name' => $client['customerName'],
//				    'client_number' => $client['customerMobile'],
//				    'address' => $site_visit_address,
//				    'project_city' => $projects['project_city'],
//				    'project_name' => $projects['project_name']
//			    );	
//			    sendLeadAssginementMailToTLCRM($internal_mail_data);
//				
            break;

            case '47':
                
//                $sub_status_slug = getDispositionStatusSlug($post_data['disposition_sub_status_id']);
//
//                if($sub_status_slug === 'call_back'){
//
//                    mysql_query('UPDATE lead SET future_followup_date = "'.$date.'" , future_followup_time = "'.$time.'" WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');
//
//                    $stored_callback_json = getCallbackCounter($post_data['enquiry_id']);
//
//                    $previous_callback_counters = array();
//
//                    if($stored_callback_json != ''){
//                        $previous_callback_counters = json_decode($stored_callback_json);
//                    }
//
//                    sendCallBackMailReminder($post_data['enquiry_id']);
//                    // insert followup counter
//                    // $callback_counter = array();	
//                    array_push($previous_callback_counters,array(
//                        'follow_up_date' => $date,
//                        'follow_up_time' => $time,
//                        'remark' => $remark
//                    ));
//
//                    $previous_callback_counters = mysql_real_escape_string(json_encode($previous_callback_counters,true));
//                    mysql_query('UPDATE `lead` SET callback_counter = "'.$previous_callback_counters.'" WHERE enquiry_id = '.$post_data['enquiry_id'].'');
//                }    
//
//                if($sub_status_slug === 'follow_up'){
//
//                    mysql_query('UPDATE lead SET future_followup_date = "'.$date.'" , future_followup_time = "'.$time.'" WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');
//
//                    $stored_followups_json = getFollowupCounter($post_data['enquiry_id']);
//
//                    $previous_followup_counters = array();
//
//                    if($stored_followups_json != ''){
//                        $previous_followup_counters = json_decode($stored_followups_json);
//                    }
//
//                    sendFollowupReminder($post_data['enquiry_id']);
//                    // insert followup counter
//				    // $followup_counter = array();
//                    array_push($previous_followup_counters,array(
//                        'follow_up_date' => $date,
//                        'follow_up_time' => $time,
//                        'remark' => $remark
//                    ));
//
//                    $previous_followup_counters = mysql_real_escape_string(json_encode($previous_followup_counters,true));
//                    mysql_query('UPDATE `lead` SET followup_counter = "'.$previous_followup_counters.'" WHERE enquiry_id = '.$post_data['enquiry_id'].'');
//                }
//                
//                if($sub_status_slug === 'cold_call'){
//                     mysql_query('UPDATE `lead` SET is_cold_call = 1 WHERE enquiry_id = '.$post_data['enquiry_id'].'');
//                }


                mysql_query('UPDATE lead SET future_followup_date = "'.$date.'" , future_followup_time = "'.$time.'" WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');

                $stored_callback_json = getCallbackCounter($post_data['enquiry_id']);

                $previous_callback_counters = array();

                if($stored_callback_json != ''){
                    $previous_callback_counters = json_decode($stored_callback_json);
                }

                    sendCallBackMailReminder($post_data['enquiry_id']);
                    // insert followup counter
                    // $callback_counter = array();	
                    array_push($previous_callback_counters,array(
                        'follow_up_date' => $date,
                        'follow_up_time' => $time,
                        'remark' => $remark
                    ));

                    $previous_callback_counters = mysql_real_escape_string(json_encode($previous_callback_counters,true));
                    mysql_query('UPDATE `lead` SET callback_counter = "'.$previous_callback_counters.'" WHERE enquiry_id = '.$post_data['enquiry_id'].'');
                
                
                    // Add entry in new table "future refrences"
                    mysql_query('INSERT INTO future_references (enquiry_id,callback_date, callback_time, user_id, disposition_status_id, disposition_sub_status_id, remark) VALUES ('.$post_data['enquiry_id'].',"'.$date.'", "'.$time.'",'.$user['id'].','.$disposition_status_id.',0,"'.$remark.'")');
                break;

            case '1':

                $slug = getDispositionStatusSlug($post_data['disposition_status_id']);
                $email_template_id	= getEmailTemplateId('external', $slug);

                if($email_template_id != ''){
                    mysql_query('UPDATE `lead` SET `email_template_id` = '.$email_template_id.' WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');
                }

                // Send Reminder mail
			    sendSimpleReminderMail($post_data['enquiry_id']);
            break;

            case '34':
                $slug = getDispositionStatusSlug($post_data['disposition_status_id']);
                $email_template_id	= getEmailTemplateId('external', $slug);

                if($email_template_id != ''){
                    mysql_query('UPDATE `lead` SET `email_template_id` = '.$email_template_id.' WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');
                }

                // Send Reminder mail
			    sendSimpleReminderMail($post_data['enquiry_id']);
            break;

            case 7:
            
            // Update remark 
            // update lead_closed_by
            // update lead_closure_date

            mysql_query('UPDATE lead SET lead_closed_by = '.$user['id'].' , lead_closure_date ="'.$date.'", lead_closure_remark="'.$remark.'" WHERE enquiry_id = '.$post_data['enquiry_id'].' LIMIT 1');

            break;
        }


        // disposition status title
        $disposition_status_title = ucfirst(str_replace('_',' ',getDispositionStatusSlug($disposition_status_id)));
        $sub_disposition_title = ucfirst(str_replace('_',' ',getDispositionStatusSlug($disposition_sub_status_id)));

        // Lead update Log
        $current_enquiry_status = getCurrentEnquiryStatus($post_data['enquiry_id']); 


        $disposition_status_datetime = '';
        if($date != ''){
            $disposition_status_datetime = ' at '. date('d/m/Y', strtotime($date)) . ' '. $time;
        }

        $lead_details	= 'Lead status has been updated by '.$user['firstname'].' '.$user['lastname']. ' on '. date('d/m/Y H:i A') . ' with status '. $disposition_status_title . ' ' . $sub_disposition_title .' '. $disposition_status_datetime;
        $add_lead_history = array(		
            'type' => 'edit',
            'details' => $lead_details,
            'enquiry_id' => $post_data['enquiry_id'],
            'lead_number' => $lead_number,
            'employee_id' => $user['id']
        );
        
        createLog($add_lead_history);
        
        sleep(2);

        /*******************************************************************************/
         // CURL request to API for assigning lead to ASM directly 

        // Get agent ASM 
        $agent_asm_id = getReportingManager($user['id']);

        if($agent_asm_id != '' && ($disposition_status_id == 3 || $disposition_status_id == 6)){

            $agent_asm_name = getEmployeeName($agent_asm_id);
            $curl_url	= BASE_URL . 'apis/manual_lead_assign_to_asm.php'; 
            $curl		= curl_init($curl_url);
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, // We dont want to output the response in browser
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => array(
                    'enquiry_id' => $post_data['enquiry_id'], 
                    'asm_id' => $agent_asm_id,
                    'login_user_id' => $user['id']
                )
            ));

            $result = curl_exec($curl);
            curl_close($curl);
        }
        /** End *****************************************************************************/
		
         // Send response to client 
        $json_response = json_encode(array(
            'success' => (int) 1,
            'http_status_code' => (int) 200,
            'errors' => '',
            'message' => 'Lead has been updated successfully',
            'title' => 'Lead Status Updated'
        ),true);

        echo $json_response; exit;
    }
}else{
    echo json_encode(
        array(
            'success' => (int) 0,
            'http_status_code' => (int) 200,
            'message' => 'We could not update the lead status at this time. No data received',
            'title' => 'Edit Lead'
        ), true
    );
    exit;
}
