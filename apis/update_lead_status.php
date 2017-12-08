<?php
session_start();
require_once 'function.php';

if(!function_exists('get_project_city')){
	function get_project_city($project_id = null){
  
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'http://52.77.73.171/apimain/api/get_project_city.php',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => array('project_id' => $project_id)
		));
		
		$resp = curl_exec($curl);
		curl_close($curl);
		if(!$resp){
			return '';
		}else{
			
			$response_obj = json_decode($resp,true);
			return $response_obj['city_name']; 
		}
	}
}


$data = json_decode(file_get_contents('php://input'), TRUE);

if (!empty($data) && isset($data['enquiry_id'])) {

	$enquiry_id = $data['enquiry_id'];

	if (isset($data['lead_id']) && $data['lead_id'] != '') {
		$lead_id = $data['lead_id'];
	} else {
		$lead_id = 'NULL';
	}

	$status_id			= $data['status_id'];
	$sub_status_id		= $data['sub_status_id'];
	$status_title		= getStatusLabel($status_id, 'parent');
	$sub_status_title	= getStatusLabel($sub_status_id, 'child');

	$callback_date		= '';
	$callback_time		= '';

    $errors = array();
    
	if (isset($data['callback_date']) && $data['callback_date'] != '') {
		$callback_date = $data['callback_date'];
	}

	if (isset($data['callback_time']) && $data['callback_time'] != '') {
		$callback_time = $data['callback_time'];
	}

	// update status  remark 
	$remark = ( isset($data['remark']) ? $data['remark'] : '' );

   // Skip remark validation for send mail    
   if($status_id != 46){
        if($remark == '' && $data['isRemarksMandatory']){
            $errors['remark'] = 'Please enter remark';
        }    
    }
	
	#########IVR Disposition Push By Sudhanshu##################
    $datevalue = $callback_date;
	$timevalue = date('H:i:s',strtotime($callback_time)); 
	$new_date = $datevalue.' '.$timevalue; 
	$ip = $_SERVER['REMOTE_ADDR'];
	$user = $_SESSION['currentUser'];
	
	$dataarray = array('transaction_id' => 'CTI_SET_DISPOSITION','agent_id'=>$user['crm_id'],'ip'=>$ip,'cust_disp'=>$status_title,'category'=>$sub_status_title,'next_call_time'=>$new_date,'resFormat'=>'1');
	$curl = curl_init();
	$url = "";
	//print_r($dataarray);
	foreach($dataarray as $key => $value){
		$url .= urlencode($key).'='.urlencode($value).'&';
	}

	$hitURL = "http://admin.c-zentrixcloud.com/apps/appsHandler.php";
	$content = file_get_contents($hitURL.'?'.$url);	
    
	
	##################################################
    
    ############################################################################################
    
    // Validation check for site visit booking time 
	if(isset($data['is_site_visit_scheduled']) || isset($data['is_site_visit_rescheduled'])){
        
        if($data['is_site_visit_scheduled'] == 1 || $data['is_site_visit_rescheduled'] == 1){
            
            // Check if site visit date and time is selected or not
        
            if($callback_date == ''){
                $errors['callback_date'] = 'Please select site visit date';
            }
        
            if($callback_time == ''){
                $errors['callback_time'] = 'Please select site visit time';
            }
        
            // Server side validation for site visit booking 
            if($callback_date != '' && $callback_time != ''){
            
                $is_site_visit_booking_time_valid = validateSiteBookingVisitTime($callback_date,$callback_time);
            
                if(is_array($is_site_visit_booking_time_valid) && !empty($is_site_visit_booking_time_valid)){
                    $errors['site_visit'] = $is_site_visit_booking_time_valid['site_visit'];
                }
            }       
        }    
    }

    ############################################################################################
    
    // If errors
    if(!empty($errors)){
        
        echo json_encode(array(
            'success' => 0,
            'errors' => $errors
        ),true); exit;
    }
    
	// employee who did update 
	$employee_id 		= ( isset($data['employee_id']) ? $data['employee_id'] : '' );
	$employee_name 		= getEmployeeName($employee_id);

	// Get Employee designation Slug 
	$designation_slug 		= getEmployeeDesignation($employee_id);
	$employee_designation 	= $designation_slug[1];
	$previous_status 		= getCurrentEnquiryStatus($enquiry_id);
	
    // Store the previous callback and followup data 
    $callback_counter = '';
    $followup_counter = '';
    
    $followup_and_callback_data = mysql_query('SELECT future_followup_date, future_followup_time, enquiry_status_remark,followup_counter,callback_counter FROM lead WHERE enquiry_id = '.$enquiry_id.'');
        
    if($followup_and_callback_data && mysql_num_rows($followup_and_callback_data) > 0){
        
        $followup_and_callback_data_object = mysql_fetch_object($followup_and_callback_data);
        
        if($followup_and_callback_data_object -> followup_counter != ''){
                    
            // decode json in array 
            $followup_counter = json_decode($followup_and_callback_data_object -> followup_counter,true);
        }
            
        if($followup_and_callback_data_object -> callback_counter != ''){
                    
            // decode json in array 
            $callback_counter = json_decode($followup_and_callback_data_object -> callback_counter,true);
        }

    }
    
    // Old Callback disposition in Future Reference
    if($status_id == 4){
    
        if($sub_status_id == 37){
            
            if(is_array($followup_counter)){
            
                if(!empty($followup_counter)){
                 
                    array_push($followup_counter,array(
                    'follow_up_date' => $callback_date,
                    'follow_up_time' => $callback_time,
                    'remark' => $remark
                    ));
                }
            }else{
                $followup_counter = array();
                array_push($followup_counter,array(
                    'follow_up_date' => $callback_date,
                    'follow_up_time' => $callback_time,
                    'remark' => $remark
                ));
            }
                
            $followup_counter = mysql_real_escape_string(json_encode($followup_counter,true));
            
            if(is_array($callback_counter) && !empty($callback_counter)){
                $callback_counter = mysql_real_escape_string(json_encode($callback_counter,true));
            }
            
        }
        
        if($sub_status_id == 10){
            
            if(is_array($callback_counter)){
            
                if(!empty($callback_counter)){
                 
                    array_push($callback_counter,array(
                    'follow_up_date' => $callback_date,
                    'follow_up_time' => $callback_time,
                    'remark' => $remark
                    ));
                }
            }else{
                $callback_counter = array();
                array_push($callback_counter,array(
                    'follow_up_date' => $callback_date,
                    'follow_up_time' => $callback_time,
                    'remark' => $remark
                ));
            }
            
            $callback_counter = mysql_real_escape_string(json_encode($callback_counter,true));
            
            if(is_array($followup_counter) && !empty($followup_counter)){
                $followup_counter = mysql_real_escape_string(json_encode($followup_counter,true));
            }
        }
        
        
        // Insert followup date/time in future references table 
        
        mysql_query('INSERT INTO `future_references` (enquiry_id, callback_date, callback_time, user_id, disposition_status_id, disposition_sub_status_id, remark) VALUES ('.$enquiry_id.',"'.$callback_date.'","'.$callback_time.'",'.$employee_id.','.$status_id.','.$sub_status_id.',"'.$remark.'") ');
         
    }  

    
    /*
     * 47 is new Callback Activity
     */
    
    if($status_id == 47){
        
        if(is_array($callback_counter)){
            
                if(!empty($callback_counter)){
                 
                    array_push($callback_counter,array(
                    'follow_up_date' => $callback_date,
                    'follow_up_time' => $callback_time,
                    'remark' => $remark
                    ));
                }
            }else{
                $callback_counter = array();
                array_push($callback_counter,array(
                    'follow_up_date' => $callback_date,
                    'follow_up_time' => $callback_time,
                    'remark' => $remark
                ));
            }
            
            $callback_counter = mysql_real_escape_string(json_encode($callback_counter,true));
            
    }
    

    /*
     * Lead Status Data Array
     */
    
	$update_lead = array(
            'disposition_status_id' => $status_id,
            'disposition_sub_status_id' => ( isset($sub_status_id) && $sub_status_id != 'NULL' ? $sub_status_id : 'NULL' ),
            'future_followup_date' => $callback_date,
            'future_followup_time' => $callback_time,
            'enquiry_status_remark' => $remark,
            'followup_counter' => $followup_counter,
            'callback_counter' => $callback_counter,
            'is_overdue' => "0",
            'is_overdue_mail_sent' => "0",
            'leadStatus' => (isset($data['hot_warm_cold_status']) ? $data['hot_warm_cold_status'] : "")
 	);
 	
 	if($status_id==1 || $status_id==34){
 		
		$update_lead['lead_assigned_to_sp'] = 'NULL';
		$update_lead['lead_assigned_to_asm'] = 'NULL';
		$update_lead['lead_assigned_to_sp_on'] = 'NULL';
		$update_lead['lead_assigned_to_asm_on'] = 'NULL';
		$update_lead['lead_expire_date_of_sp'] = 'NULL';
		$update_lead['is_lead_accepted'] = 0;
		$update_lead['is_lead_rejected'] = 0;
		
		// Insert last agent, sp, asm in a table 
		mysql_query("INSERT INTO lead_assignment_sales (enquiry_id, agent_id, asm_id, sp_id, disposition_status_id, disposition_sub_status_id, asm_assign_date, sp_assign_date) SELECT '$enquiry_id', CASE WHEN reassign_user_id IS NULL THEN (lead_added_by_user) ELSE (reassign_user_id) END, lead_assigned_to_asm, lead_assigned_to_sp, '$status_id', '$sub_status_id', lead_assigned_to_asm_on, lead_assigned_to_sp_on FROM lead WHERE enquiry_id = $enquiry_id");
	}
    
	$update_sql = 'UPDATE `lead` SET ';

	foreach ($update_lead as $column => $val) {
            if($val=='NULL'){
                $update_sql .= $column . ' = '.$val. ',';
            }else{
                $update_sql .= $column . ' = "' . $val . '" , ';			
            }
	}
    
	$update_sql_trimmed = rtrim($update_sql, ' , ');

	$update_sql_trimmed .= ' WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1';
    
	$is_lead_update = false; // Flag to save result for update query
	
	if (mysql_query($update_sql_trimmed)) {
	
		// Insert lead status in saperate table
		mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='processed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM lead_re_assign WHERE lead_re_assign.user_type ='$employee_designation' AND enquiry_id='$enquiry_id' AND to_user_id='$employee_id' AND change_status='pending' ORDER BY ID DESC LIMIT 1) s)");
		
		
		if(!mysql_query('INSERT INTO `lead_status` (lead_id,enquiry_id, disposition_status_id, disposition_sub_status_id,hot_warm_cold_status,user_type,user_id,remark) VALUES ("'.$lead_id.'",'.$enquiry_id.','.$status_id.','.( isset($sub_status_id) && $sub_status_id != 'NULL' ? $sub_status_id : 'NULL' ).',"'.(isset($data['hot_warm_cold_status']) ? $data['hot_warm_cold_status'] : NULL).'","'.$employee_designation.'",'.$employee_id.',"'.$remark.'") ')){
			//echo mysql_error(); 
		}
		
		// Update `change status` in lead_re_assign table
    	$enquiry_to_change_status = mysql_query('SELECT id FROM lead_re_assign WHERE enquiry_id = '.$enquiry_id.' AND to_user_id = '.$employee_id.' AND user_type = "'.$employee_designation.'" ORDER BY id DESC LIMIT 1');
		if($enquiry_to_change_status && mysql_num_rows($enquiry_to_change_status) > 0){
			$enquiry_result = mysql_fetch_object($enquiry_to_change_status);
			mysql_query('UPDATE `lead_re_assign` SET change_status = "processed" WHERE id = '.$enquiry_result -> id.' LIMIT 1');
		}

		$is_lead_update = true;
        
         // Log enquiry remarks
         $remark_log = array(
            'remark' => $remark,
            'enquiry_id' => $enquiry_id,
            'employee_id' => $employee_id,
            'remark_creation_date' => date('Y-m-d H:i:s')
         );
            
         createRemarkLog($remark_log);
	}

	// when lead status is successfully updated in lead table we store further status data 
	if ($is_lead_update) {

		// if meeting scheduled or re-scheduled event is happening
		if (isset($data['is_meeting_scheduled']) || isset($data['is_meeting_rescheduled'])) {

			// save meeting data in lead meeting 

			if ($data['is_meeting_scheduled'] == 1 || $data['is_meeting_rescheduled'] == 1) {
                
				// Meeting secondary status
				$meeting_event = '';
				if( $data['is_meeting_scheduled'] == 1 ){
					$meeting_event = 'meeting_schedule';
				}
				
				if( $data['is_meeting_rescheduled'] == 1 ){
					$meeting_event = 'meeting_reschedule';
				}
				
				$email_template_id = getEmailTemplateId('external', $meeting_event);
				// update Email template Id 
				mysql_query('UPDATE `lead` SET email_template_id = "'.$email_template_id.'" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
				
				// meeting project
				$project_name = $project_url = $project_city = $project_id = '';
                $meeting_project = array();
                
				if( isset($data['meeting_project']) ){
                    
				    $project_id   = $data['meeting_project']['id']; 
					$project_name = $data['meeting_project']['name'];
                    $project_url  = $data['meeting_project']['url']; 
                    if($data['meeting_project']['city'] == ''){
                        $project_city = get_project_city($project_id);
                    }else{
                        $project_city = $data['meeting_project']['city'];    
                    }
                    
                    array_push($meeting_project, array(
                        
                            'project_name' => $project_name,
                            'project_id' => $project_id,
                            'project_city' => $project_city,
                            'project_url' => $project_url 
                    ));
				}
                
				// generate lead number if not generated before 
				if ($lead_id == '' || strtolower($lead_id) == 'null') {

					$lead_id = generateLeadNumber($enquiry_id);
					// update lead number with 
					mysql_query('UPDATE lead SET lead_id = "' . $lead_id . '" WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');
				}
				
				$callback_date = date('Y-m-d', strtotime($callback_date));
				$callback_time = str_replace(' ','', $callback_time);
				$meeting_timestamp = strtotime ( date('Y-m-d H:i:s', strtotime($callback_date.' '.$callback_time)) );
				$client = getCLientInfoByEnquiry($enquiry_id);
				
				// new 
				$meeting_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'employee_id' => $employee_id,
					'employee_name' => $employee_name,
					'meeting_address' => ( isset($data['meeting_address']) ? $data['meeting_address'] : ''),
					'remark' => $remark,
					'meeting_time' => $meeting_timestamp * 1000, // converting to timestamp in ms
					'meeting_location_type' => ( isset($data['meeting_location_type']) ? $data['meeting_location_type'] : '' ),
					'attendees' => 1,
					'client' => json_encode(array('name' => $client['customerName'],'phone' => $client['customerMobile'], 'email' => $client['customerEmail'],'city' => $client['customerCity']), true),
					'project' => json_encode($meeting_project, true)
				);
				
				// CURL Request 
				$create_meeting_url	= BASE_URL . 'apis/create_meeting.php';
				$ch		= curl_init($create_meeting_url);
				curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $meeting_data
				));
				
				$meeting_id	= curl_exec($ch); // getting meeting id as response
				
				curl_close($ch);
                
				if($meeting_id != ''){
					
					$history_detail = 'Status ' .$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to Meeting '. $sub_status_title . ' has been changed by ' . $employee_name .' at '. date('Y-m-d H:i:s');
					
                    $history_data = array(
						'enquiry_id' => $enquiry_id,
						'lead_number' => $lead_id,
						'details' => mysql_real_escape_string($history_detail),
						'employee_id' => $employee_id,
						'type' => 'edit'
					);

					createLog($history_data);
				}
			}
		}

		// If meeting done event is happening
		if (isset($data['is_meeting_done'])) {

			if ($data['is_meeting_done'] == 1) {

				// update meeting status as done  

				$meeting_id 		= $data['meeting_id'];
				$update_timestamp 	= time() * 1000;
				
				$update_meeting = 'UPDATE `lead_meeting` SET meeting_status = 1, meeting_update_on = "'.$update_timestamp.'", remark = "'.$remark.'"  WHERE meetingId = "'.$meeting_id.'" LIMIT 1';
                
				if (mysql_query($update_meeting)) {
                    
					// log for lead history 
					$log_details = 'Status '.$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to Meeting '.$sub_status_title.' has been changed by ' . $employee_name .' at '. date('Y-m-d H:i:s');

					

					$log = 'INSERT INTO `lead_history` '
							. ' SET '
							. ' lead_number = "' . $lead_id . '",'
							. ' enquiry_id = ' . $enquiry_id . ','
							. ' details = "' . $log_details . '",'
							. ' employee_id = "' . $employee_id . '",'
							. ' type = "new"';

					
					mysql_query($log);
					
                    // update email template id
					$email_template_id = getEmailTemplateId('external','meeting_done');
                    
					if(mysql_query('UPDATE `lead` SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.'')){
                        sendMeetingReminder($enquiry_id);
                    }
				}
			}
		}

		// If meeting not done event is fired
		if(isset($data['is_meeting_not_done'])){

            if($data['is_meeting_not_done'] == '1'){
                
			     $meeting_id = $data['meeting_id'];
			     $update_timestamp = time() * 1000;

			     $update_meeting = 'UPDATE `lead_meeting` SET meeting_status = 2, meeting_update_on = "'.$update_timestamp.'", remark = "'.$remark.'"  WHERE meetingId = "'.$meeting_id.'" LIMIT 1';
			
                if (mysql_query($update_meeting)) {

                        // log for lead history 
                        $log_details = 'Status '.$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to Meeting '.$sub_status_title.' has been changed by ' . $employee_name .' at '. date('Y-m-d H:i:s');
                        createLog(array('enquiry_id' => $enquiry_id, 'lead_number' => $lead_id, 'details' => $log_details,'type' => 'update','employee_id' => $employee_id));
                }
            }
        }
		

		// if site_visit_scheduled or re-scheduled event is happening 
		if (isset($data['is_site_visit_scheduled']) || isset($data['is_site_visit_rescheduled'])) {

			// save Site Visit data in site_visit table 

			if ($data['is_site_visit_scheduled'] == 1 || $data['is_site_visit_rescheduled'] == 1) {

                
                // Event 
                $site_visit_event = '';
                if($data['is_site_visit_scheduled'] == 1){
                    $site_visit_event = 'site_visit_schedule';
                }
                
                if($data['is_site_visit_rescheduled'] == 1){
                    $site_visit_event = 'site_visit_reschedule';
                }
                
                // Fetch email template id
                $email_template_id = getEmailTemplateId('external', $site_visit_event);
                
				// update Email template Id 
				mysql_query('UPDATE `lead` SET email_template_id = "'.$email_template_id.'" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
                
                // generate lead number if not generated before 
				if ($lead_id == '' || strtolower($lead_id) == 'null') {
                    
					$lead_id = generateLeadNumber($enquiry_id);
                    
					// update lead number with 
					mysql_query('UPDATE lead SET lead_id = "' . $lead_id . '" WHERE enquiry_id = ' . $enquiry_id . ' LIMIT 1');
				}
                
                // Site visit project 
                $site_visit_project  = array();
                if(!empty($data['site_visit_project'])){
                    array_push($site_visit_project,$data['site_visit_project']);
                }
                
                // CLIENT INFO
                $client = getCLientInfoByEnquiry($enquiry_id);
                
                // SITE VISIT TIMESTAMP
                $callback_date          = date('Y-m-d', strtotime($callback_date));
				$callback_time          = str_replace(' ','', $callback_time);
				$site_visit_timestamp   = strtotime($callback_date.' '.$callback_time) * 1000; // time in miliseconds 
				
                
                // CREATE NEW SITE VISIT
                
                 $site_visit_data = array(
                
                    'enquiry_id' => $enquiry_id,
                    'lead_number' => $lead_id,
                    'site_visit_timestamp' => $site_visit_timestamp,
					'executiveId' => $data['employee_id'],
					'executiveName' => $employee_name,
					'site_location' => $data['site_visit_address'],
					'project' => json_encode($site_visit_project, true),
					'client' => json_encode(array('name' => $client['customerName'],'phone' => $client['customerMobile'], 'email' => $client['customerEmail'],'city' => $client['customerCity']), true),
					'vehicle_accomodated' => 'NULL',
					'number_of_person_visited' => $data['no_of_people_for_site_visit'],
					'site_visit_status' => 0,
					'remark' => $data['remark']
                );
                
                $create_sitevisit_url	= BASE_URL . 'apis/create_site_visit.php';
				$ch		= curl_init($create_sitevisit_url);
				curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $site_visit_data
				));
                
				$site_visit_id	= curl_exec($ch); // site visit ID return from server
                
				curl_close($ch);
                if($site_visit_id != ''){
					// getting site visit id as response
                    
                    $log_data = array (
                        'lead_number' => $lead_id,
                        'enquiry_id' => $enquiry_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'employee_id' => $employee_id,
                        'type' => 'new',
                        'details' => 'Status ' .$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to Site Visit '. $sub_status_title . ' has been changed by ' . $employee_name .' at '. date('d-m-Y H:i A')                    );
					createLog($log_data); // function to create log
				}
			}
		}

		if (isset($data['site_visit_done'])) {

			if ($data['site_visit_done'] == 1) {

				// Get Site visit ID
                
                $site_visit_id      = $data['site_visit_id'];
				$update_timestamp   = time() * 1000; // timestamp in miliseconds
				
				$update_site_visit = 'UPDATE `site_visit` SET site_visit_status = 1, site_visit_updated_at = "'.$update_timestamp.'", remark = "'.$remark.'" WHERE site_visit_id = "'.$site_visit_id.'" LIMIT 1';
				
				if (mysql_query($update_site_visit)) {
                    
					$email_template_id = getEmailTemplateId('external','site_visit_done');
					mysql_query('UPDATE `lead` SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.'');
                    
					sendSiteVisitReminder($enquiry_id);
                    
                    $log_data  = array(
                        'enquiry_id' => $enquiry_id,
                        'lead_number' => $lead_id,
                        'employee_id' => $employee_id,
                        'type' => 'new',
                        'details' => 'Status '. $previous_status['primary_status_title'] .' '.$previous_status['secondary_status_title'].' to Site Visit '. $sub_status_title .' has been changed by '. $employee_name . ' at '. date('d-m-Y H:i A')
                    );
                
                    createLog($log_data); // creating Log
				}
			}
		}

		// if call back status is set 
		if (isset($data['is_call_back'])) {

			if ($data['is_call_back'] == 1) {
                
                // Update email template ID
                $email_template_id = getEmailTemplateId('external', 'call_back');
                
                // update Email template Id 
                mysql_query('UPDATE `lead` SET email_template_id = "'.$email_template_id.'" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
                
                sendCallBackMailReminder($enquiry_id);

                $history_detail = 'Status ' .$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to Callback has been changed by ' . $employee_name .' at '. date('d-m-Y H:i A');
                
                
				$history_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'details' => mysql_real_escape_string($history_detail),
					'employee_id' => $employee_id,
					'type' => 'edit'
				);

				createLog($history_data);
			}
		}

		// if technical issue is set 
		if (isset($data['is_technical_issue'])) {

			// Log history of enquiry/ lead
			if ($data['is_technical_issue'] == 1) {

                
                $last_activity_status = $previous_status['primary_status_title'];
                if($previous_status['secondary_status_title'] != ''){
                    $last_activity_status .= ' '. $previous_status['secondary_status_title'];
                }

                $current_activity_status = $status_title;
                if($sub_status_title != ''){
                    $current_activity_status .= ' '. $sub_status_title;
                }
                
                
//				$history_detail = 'Status ' .$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to '.$sub_status_title.' has been changed by ' . $employee_name .' at '. date('d-m-Y H:i A');
//                
                
                $history_detail = 'Status ' . $last_activity_status .' to '.$current_activity_status .' has been changed by ' . $employee_name .' at '. date('d-M-Y H:i A');

                
				$history_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'details' => mysql_real_escape_string($history_detail),
					'employee_id' => $employee_id,
					'type' => 'edit'
				);

                createLog($history_data);
			}
		}

		if (isset($data['is_not_intrested'])) {

			if ($data['is_not_intrested']) {

                
                // Update email template ID
                $email_template_id = getEmailTemplateId('external', 'not_interested');
                
                // update Email template Id 
                mysql_query('UPDATE `lead` SET email_template_id = "'.$email_template_id.'" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
            
                
                // Mail notification
                sendSimpleReminderMail($enquiry_id);
            
                $last_activity_status = $previous_status['primary_status_title'];
                if($previous_status['secondary_status_title'] != ''){
                    $last_activity_status .= ' '. $previous_status['secondary_status_title'];
                }

                $current_activity_status = $status_title;
                if($sub_status_title != ''){
                    $current_activity_status .= ' '. $sub_status_title;
                }
                
                
                $history_detail = 'Status ' . $last_activity_status .' to '.$current_activity_status .' has been changed by ' . $employee_name .' at '. date('d-M-Y H:i A');
                
//				$history_detail = 'Status '.$previous_status['primary_status_title'].' '. $previous_status['secondary_status_title'] .' to '.$status_title.' has been changed by ' . $employee_name .' at '. date('d-m-Y H:i A');

				$history_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'details' => mysql_real_escape_string($history_detail),
					'employee_id' => $employee_id,
					'type' => 'edit'
				);

				createLog($history_data);
			}
		}
        
        if( isset($data['just_enquiry']) && $data['just_enquiry'] == 1 ){
            
            // Update email template ID
            $email_template_id = getEmailTemplateId('external', 'just_enquiry');
                
            // update Email template Id 
            mysql_query('UPDATE `lead` SET email_template_id = "'.$email_template_id.'" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
            
            // Mail notification
            sendSimpleReminderMail($enquiry_id);
            $history_detail = 'Status '.$previous_status['primary_status_title'].' '. $previous_status['secondary_status_title'].' to '.$status_title.' has been changed by ' . $employee_name .' at '. date('d-m-Y H:i A');

				$history_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'details' => mysql_real_escape_string($history_detail),
					'employee_id' => $employee_id,
					'type' => 'edit'
				);

				createLog($history_data);
        }
        
        
        /* Status Update for Follow Up */
        
        if(isset($data['is_follow_up']) && $data['is_follow_up'] == 1){
            
            // Send Internal mail to agent  
            sendFollowupReminder($enquiry_id);
                
            $history_detail = 'Status ' .$previous_status['primary_status_title'].' '.$previous_status['secondary_status_title'].' to '.$sub_status_title.' has been changed by ' . $employee_name .' at '. date('d-M-Y H:i A');

            $history_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'details' => mysql_real_escape_string($history_detail),
					'employee_id' => $employee_id,
					'type' => 'edit'
            );

            createLog($history_data);
            
        }
        /* End: Status Update for follow up */
        
		#### NO RESPONSE STATUS ################################################################################
        
            if( isset($data['is_no_response']) && $data['is_no_response'] == 1){
                
                $last_activity_status = $previous_status['primary_status_title'];
                if($previous_status['secondary_status_title'] != ''){
                    $last_activity_status .= ' '. $previous_status['secondary_status_title'];
                }

                $current_activity_status = $status_title;
                if($sub_status_title != ''){
                    $current_activity_status .= ' '. $sub_status_title;
                }
                
                // Log text
                $history_detail = 'Status ' . $last_activity_status .' to '.$current_activity_status .' has been changed by ' . $employee_name .' at '. date('d-M-Y H:i A');

                $history_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_id,
					'details' => mysql_real_escape_string($history_detail),
					'employee_id' => $employee_id,
					'type' => 'edit'
                );
                createLog($history_data);
            }
        
        ########################################################################################################
		
		
        /*******************************************************************************/
        // SEND INTERNAL COMMUNICATION MAIL ACCORDING TO THE ENQUIRY STATUS OF THE LEAD
        /*******************************************************************************/ 
        
        $is_lead_assigned = isLeadAssigned($enquiry_id);
        
        if(!empty($is_lead_assigned) && $is_lead_assigned['sp'] != ''){
            
            $_lead_current_status = getCurrentEnquiryStatus($enquiry_id);
            
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
            
        }
        
        // END : INTERNAL COMMUNICATION MAIL
        /*******************************************************************************/
        
        
		echo json_encode(array('success' => 1, 'message' => 'Lead status has been updated successfully'), true);
		exit;
	} else {
		// Failure response of lead not updated 
		echo json_encode(array('success' => 0, 'message' => 'Lead could not be updated'), true);
		exit;
	}
} else {

	// Error response from server 

	echo json_encode(array('success' => 0, 'message' => 'Lead could not be updated. Insufficient data passed.'), true);
	exit;
}