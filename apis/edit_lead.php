<?php
session_start();

require_once 'function.php';

// post data 
$post_data = filter_input_array(INPUT_POST);

function getMeetingSubstatus($status_slug){
	
    /* Meeting Event or sub status
	   1. schedule
	   2. re-schedule
	   3. done
    */
    
	$status_slug_lowercase = str_replace('-','',$status_slug);
	return 'meeting_'.$status_slug_lowercase;
}

function getSiteVisitSubStatus($status_slug){
	
    /* Meeting Event or sub status
	   1. schedule
	   2. re-schedule
	   3. done
    */
    
	$status_slug_lowercase = str_replace('-','',$status_slug);
	return 'site_visit_'.$status_slug_lowercase;
}

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

if(!empty($post_data)){
	
	$lead_auto_number = $post_data['client_basic_info']['id']; // auto increment number
	
	$enquiry_id		= $post_data['client_basic_info']['enquiry_id'];
	$lead_number	= $post_data['client_basic_info']['lead_id'];
	
	// Customer details 
	$customer_mobile			= $post_data['client_basic_info']['customerMobile'];
	$customer_alternate_mobile	= $post_data['client_basic_info']['customer_alternate_mobile'];
	$customer_name				= $post_data['client_basic_info']['customerName'];
	$customer_email				= $post_data['client_basic_info']['customerEmail'];
	$customer_profession		= $post_data['client_basic_info']['customerProfession'];
	$customer_dob				= $post_data['client_basic_info']['customerDOB'];
	$customer_city				= $post_data['client_basic_info']['customerCity'];
	$customer_state				= $post_data['client_basic_info']['customerState'];
	$customer_address			= $post_data['client_basic_info']['customerAddress'];
	$customer_remark			= $post_data['client_basic_info']['customerRemark'];
	$customer_gender			= $post_data['client_basic_info']['customer_gender'];
	$customer_landline			= '-';
	$landline					= '';
	$lead_primary_source		= '';
	$lead_secondary_source		= '';
    
	if(isset($post_data['landline'])){
		
		$landline = $post_data['landline'];
		$customer_landline = $landline['std']. '-'.$landline['number'].'-'.$landline['ext'];
	}
	
	// lead source 
	$lead_primary_source	=	$post_data['client_basic_info']['leadPrimarySource'];
	$lead_secondary_source	=	$post_data['client_basic_info']['leadSecondarySource'];
	
	// customer preferences 
	
	$customer_bhk_preference				= '';
	$customer_property_state_preference		= '';
	$customer_budget_preference			= '';
	$customer_property_type_preference		= '';
	
	if(isset($post_data['filters'])){
		
		$filter = $post_data['filters'];
		
		if($filter['bhk'] != ''){
			$customer_bhk_preference = $filter['bhk'];
		}
		
		if($filter['property_status']){
			$customer_property_state_preference = $filter['property_status'];
		}
		
		if(!empty($filter['property_types'])){
			$customer_property_type_preference = implode(',', $filter['property_types']);
		}
		
		if(!empty($filter['budget'])){
			
			if($filter['budget']['min'] != '' && $filter['budget']['max']){
				
				$customer_budget_preference = $filter['budget']['min'] . '-'. $filter['budget']['max'];
			}
		}
	}
	
	// Lead update date
	$lead_update_date = date('Y-m-d H:i:s');
	
	// lead updated by 
	if(isset($post_data['user'])){
		$lead_update_by = $post_data['user']['id'];
	}

	// Callback date and time 
	$callback_date			= '';
	$callback_time			= '';
	$disposition_status_remark		= '';
	
	if(isset($post_data['followup']) && !empty($post_data['followup'])){
		
		if($post_data['followup']['callback_date'] != ''){
			$callback_date = date('Y-m-d', strtotime($post_data['followup']['callback_date']));
		}
		
		if($post_data['followup']['callback_time'] != ''){
			$callback_time = $post_data['followup']['callback_time'];
		}
		
		$disposition_status_remark = $post_data['followup']['status_remark'];
	}
	
	// disposition status 
	$disposition_status_id			= $post_data['client_basic_info']['disposition_status_id'];
	$disposition_status_sub_id		= $post_data['client_basic_info']['disposition_sub_status_id'];
	$disposition_status_title		= getStatusLabel($post_data['client_basic_info']['disposition_sub_status_id'],'parent');
	$disposition_status_sub_title	= getStatusLabel($post_data['client_basic_info']['disposition_sub_status_id'],'child');
	
    
    /***********************************************************************************************************/
    // SAVE ENQUIRY PROJECTS 
    /***********************************************************************************************************/
	
    $projects           = array();
    $enquired_projects  = array();
    
	if (isset($post_data['projects']) && !empty($post_data['projects']['projects'])) {

		$projects  = $post_data['projects']['projects'];
		
			foreach ($projects as $key => $val) {

				$project_id		=	$val['id'];
				$project_name	=	$val['project_name'];
				$project_url	=	$val['project_url'];
                $project_city   =   get_project_city($project_id);
                
                // Push new enquired projects in a saperate array
                array_push( $enquired_projects, array(
                
                    'project_id' => $project_id,
                    'project_name' => $project_name,
                    'project_city' => $project_city,
                    'project_url' => $project_url
                ));
                
				$enquiry_project_lead_number = 'NULL';

				if($lead_number != ''){
					$enquiry_project_lead_number = $lead_number;
				}

				$save_enquiry_projects = 'INSERT INTO `lead_enquiry_projects`'
						. '  (enquiry_id,lead_number,project_id,project_name,project_url) '
						. ' VALUES (' . $enquiry_id . ','.$enquiry_project_lead_number.',' . $project_id . ',"' . $project_name . '","' . $project_url . '")';
				
				mysql_query($save_enquiry_projects);			
			}
		}
	
    #############################################################################################################
    
	// get disposition status title and prepare slug by joining title with underscore
	if(isset($post_data['lead_enquiry'])){
		
		$disposition_status_id		= $post_data['lead_enquiry']['id'];
		$disposition_status_title	= str_replace(' ','_',$post_data['lead_enquiry']['group_title']);
		
		if($post_data['lead_enquiry']['sub_status_id'] != ''){
			$disposition_status_sub_id = $post_data['lead_enquiry']['sub_status_id'];
		}
		
		if($post_data['lead_enquiry']['sub_status_title'] != ''){
			$disposition_status_sub_title = str_replace(' ','_',$post_data['lead_enquiry']['sub_status_title']);
		}
		$user = $_SESSION['currentUser'];
		$time = $callback_date.''.$callback_time;

		$new_date = date('Y-m-d H:i:s',strtotime($time)); 
		$ip = $_SERVER['REMOTE_ADDR'];
			
		
			$data = array('transaction_id' => 'CTI_SET_DISPOSITION','agent_id'=>$user['crm_id'],'ip'=>$ip,'cust_disp'=>$disposition_status_title,'category'=>$disposition_status_sub_title,'next_call_time'=>$new_date,'resFormat'=>'1');
			$curl = curl_init();
			$url = "";
			foreach($data as $key => $value){
				$url .= urlencode($key).'='.urlencode($value).'&';
			}
			//echo $url;
			$hitURL = "http://agent.c-zentrixcloud.com/apps/appsHandler.php";
			$content = file_get_contents($hitURL.'?'.$url);
		// Add new enquiry status if any changes have been made 
		
		/******************************************************************************************/
        // CREATE NEW MEETING
        /******************************************************************************************/
		if(strtolower($disposition_status_title) == 'meeting'){
            
            
            // Identify email template id 
            $meeting_sub_status = getMeetingSubstatus(strtolower($disposition_status_sub_title));
            $email_template_id	= getEmailTemplateId('external',$meeting_sub_status);
            
            
            // update email template id for current enquiry 
            $update_email_template = mysql_query('UPDATE lead SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.'');
            
            /* Meeting Data */
			$meeting_status		= $disposition_status_sub_title;
			$meeting_date		= $callback_date;
			$meeting_time		= $callback_time;
			$meeting_remark		=  $disposition_status_remark;

            $things_to_replace = array(' ','AM','PM');
            $meeting_callback = str_replace($things_to_replace,'',$callback_date.' '.$callback_time);
            $meeting_timestamp = strtotime($meeting_callback) * 1000; // meeting timestamp in unix 
            $meeting_projects = json_encode($enquired_projects,true);
            $meeting_client   = json_encode(
                array('name' => $customer_name,
                      'phone' => $customer_mobile, 
                      'email' => $customer_email,
                      'city' => $customer_city),true);
            
            $employee_id   = $post_data['user']['id'];
            $employee_name = getEmployeeName($post_data['user']['id']);
            $meeting_address = $post_data['lead_enquiry']['address'];
            $meeting_location = 'other';
            
            $meeting_data = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_number,
					'employee_id' => $employee_id,
					'employee_name' => $employee_name,
					'meeting_address' => $meeting_address,
					'remark' => $meeting_remark,
					'meeting_time' => $meeting_timestamp, // converting to timestamp in ms
					'meeting_location_type' => $meeting_location,
					'attendees' => 1,
					'client' => $meeting_client,
					'project' => $meeting_projects
				);
            
                // CURL Request to create new meeting
				$create_meeting_url	= BASE_URL . 'apis/create_meeting.php';
				$ch		= curl_init($create_meeting_url);
				curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $meeting_data
				));
				$result	= curl_exec($ch);
          
				curl_close($ch);
            
				if($result != ''){
                    
					// getting meeting id as response
                    
                    $meeting_status_history = array(
                        'enquiry_id' => $enquiry_id,
                        'lead_number' => $lead_number,
                        'details' => 'Lead/ Enquiry status has changed to Meeting '.$disposition_status_sub_title.' on '. date('d-M-Y H:i:s'). ' by '. $employee_name,
                        'type' => 'new',
                        'employee_id' => $employee_id
                    );
                    
					createLog($meeting_status_history); // function to create log
				}
                
                // LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
                $internal_mail_data = array(
                    'enquiry_id' => $enquiry_id,
                    'lead_number' => $lead_number,
                    'client_name' => $customer_name,
                    'address' => $meeting_address,
                    'project_city' => get_project_city($enquired_projects[0]['id']),
                    'project_name' => $enquired_projects[0]['project_name']
                );
                sendLeadAssginementMailToTLCRM($internal_mail_data);
            
		}

        # END: CREATE NEW MEETING
	    ############################################################################################
        
        
        /*******************************************************************************************/
        // CREATE NEW SITE VISIT
        /*******************************************************************************************/
		if(strtolower($disposition_status_title) == 'site_visit'){
            
            // Identify email template id 
            $site_visit_sub_status   = getSiteVisitSubStatus(strtolower($disposition_status_sub_title));
            $email_template_id	     = getEmailTemplateId('external',$site_visit_sub_status);
            
            
            // update email template id
            $update_email_tempplate_id = mysql_query('UPDATE lead SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.'');
            
          
            $things_to_replace      = array(' ','AM','PM');
            $site_visit_callback    = str_replace($things_to_replace,'',$callback_date.' '.$callback_time);
            $site_visit_timestamp   = strtotime($site_visit_callback)*1000;
            
            $site_visit_project     = json_encode($enquired_projects,true);
            $site_visit_client_info = json_encode(
                array('name' => $customer_name,
                      'phone' => $customer_mobile, 
                      'email' => $customer_email,
                      'city' => $customer_city),true);
            
            $employee_id        = $post_data['user']['id'];
            $employee_name      = getEmployeeName($post_data['user']['id']);
			$site_visit_status	= $disposition_status_sub_title;
			$site_visit_date	= $callback_date;
			$site_visit_time	= $callback_time;
			$remark		        = $disposition_status_remark;
            $site_visit_address = $post_data['lead_enquiry']['address'];
        
            $site_visit_data = array(
                
                'enquiry_id' => $enquiry_id,
                'lead_number' => $lead_number,
                'site_visit_timestamp' => $site_visit_timestamp,
				'executiveId' => $employee_id,
				'executiveName' => $employee_name,
				'site_location' => $site_visit_address,
				'project' => $site_visit_project,
				'client' => $site_visit_client_info,
				'vehicle_accomodated' => '',
				'number_of_person_visited' => '',
				'site_visit_status' => 0,
				'remark' => $disposition_status_remark
                );
            
                // CURL Request to create new meeting
				$create_sitevisit_url	= BASE_URL . 'apis/create_site_visit.php';
				$ch		= curl_init($create_sitevisit_url);
				curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $site_visit_data
				));
				$result	= curl_exec($ch);
               
				curl_close($ch);
            
				if($result != ''){
					// getting meeting id as response
                    
                    $sitevisit_status_history = array(
                        'enquiry_id' => $enquiry_id,
                        'lead_number' => $lead_number,
                        'details' => 'Lead/ Enquiry status has changed to Site visit '.$disposition_status_sub_title.' on '. date('d-M-Y H:i:s'). ' by '. $employee_name,
                        'type' => 'new',
                        'employee_id' => $employee_id
                    );
                    
					createLog($sitevisit_status_history); // function to create log
				}
                
                // LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
                $internal_mail_data = array(
                    'enquiry_id' => $enquiry_id,
                    'lead_number' => $lead_number,
                    'client_name' => $customer_name,
                    'address' => $site_visit_address,
                    'project_city' => get_project_city($enquired_projects[0]['id']),
                    'project_name' => $enquired_projects[0]['project_name']
                );
            
                sendLeadAssginementMailToTLCRM($internal_mail_data);
		}
	
        # END: CREATE NEW SITE VISIT
        ##############################################################################################
        
        if(strtolower($disposition_status_title) === 'just_enquiry' || strtolower($disposition_status_title) === 'not_interested'){
            
            $email_template_id	= getEmailTemplateId('external', $disposition_status_title);
            
            // update email template idate
            $update_email_template_id = mysql_query('UPDATE lead SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.'');
            
            // Send Reminder mail
            sendSimpleReminderMail($enquiry_id);
        }
	}
    
	$is_cold_call = 0; // Flag for cold call
	$future_callback_date	= '';
	$future_callback_time	= '';
	
	if (strtolower($disposition_status_title) == 'future_references' || strtolower($disposition_status_title) === 'future_reference') {

		if (strtolower($disposition_status_sub_title) === 'cold_call') {
			$is_cold_call = 1;	
		} 
        else if(strtolower($disposition_status_sub_title) === 'follow_up'){
            sendFollowupReminder($enquiry_id);
			// insert followup counter
            $followup_counter = array();
			array_push($followup_counter,array(
				'follow_up_date' => $callback_date,
				'follow_up_time' => $callback_time,
				'remark' => $enquiry_remark
			));
            $followup_counter = mysql_real_escape_string(json_encode($followup_counter,true));
			mysql_query('UPDATE lead SET followup_counter = "'.$followup_counter.'" WHERE enquiry_id = '.$enquiry_id.'');
        }
        else {
			$future_callback_date = date('Y-m-d', strtotime($callback_date));
			$future_callback_time = $callback_time;
            
            $email_template_id	= getEmailTemplateId('external','call_back');
            
            // update email template id 
            
            $update_email_template = mysql_query('UPDATE lead SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.'');
            
            // Send callback mail
            sendCallBackMailReminder($enquiry_id);
			
			$callback_counter = array();
			array_push($callback_counter,array(
				'follow_up_date' => $callback_date,
				'follow_up_time' => $callback_time,
				'remark' => $enquiry_remark
			));                    
            $callback_counter = mysql_real_escape_string(json_encode($callback_counter,true));
            mysql_query('UPDATE lead SET callback_counter = "'.$callback_counter.'" WHERE enquiry_id = '.$enquiry_id.'');
		}
        
	}

	/**
	 *  Now update data in lead table
	 */
    
	$lead_data = array();
	$lead_data['lead_id']						= ( $lead_number!= '' ? $lead_number :  NULL);
	$lead_data['enquiry_id']					= $enquiry_id;
	$lead_data['customerMobile']				= $customer_mobile;
	$lead_data['customer_alternate_mobile']		= $customer_alternate_mobile;
	$lead_data['customerLandline']				= $customer_landline;
	$lead_data['customerEmail']					= $customer_email;
	$lead_data['customerName']					= $customer_name;
	$lead_data['customerProfession']			= $customer_profession;
	$lead_data['customer_gender']				= $customer_gender;
	$lead_data['customerCity']					= $customer_city;
	$lead_data['customerState']					= $customer_state;
	$lead_data['customerDOB']					= date('Y-m-d', strtotime($customer_dob));
	$lead_data['customerAddress']				= $customer_address;
	$lead_data['customerRemark']				= $customer_remark;
	$lead_data['leadPrimarySource']				= $lead_primary_source;
	$lead_data['leadSecondarySource']			= $lead_secondary_source;
	$lead_data['disposition_status_id']			= $disposition_status_id;
	$lead_data['disposition_sub_status_id']		= $disposition_status_sub_id;
	$lead_data['lead_updated_by']				= $lead_update_by;
	$lead_data['future_followup_date']			= $future_callback_date;
	$lead_data['future_followup_time']			= $future_callback_time;
	$lead_data['is_cold_call']					= $is_cold_call;
	$lead_data['customer_bhk_preference']			= $customer_bhk_preference;
	$lead_data['customer_project_state_preference']	= $customer_property_state_preference;
	$lead_data['customer_property_type_preference']	= $customer_property_type_preference;
	$lead_data['customer_budget_preference']		= $customer_budget_preference;
	$lead_data['enquiry_status_remark']			    = $disposition_status_remark;
	
	$lead_updated_on_id = $lead_auto_number;
    
	$update_lead = 'UPDATE `lead` SET ';
	
	foreach($lead_data as $column => $value){
		$update_lead .= ' '.$column.' = "'.$value.'" ,';
	}
	
	$update_lead_sql = rtrim($update_lead,',');
	
	$update_lead_sql .= ' WHERE `id` = '.$lead_updated_on_id.' AND `enquiry_id` = '.$enquiry_id.'';
	
	if(mysql_query($update_lead_sql)){
        
        // Log enquiry remarks
        $remark_log = array(
            'remark' => $disposition_status_remark,
            'enquiry_id' => $enquiry_id,
            'employee_id' => $lead_update_by ,
            'remark_creation_date' => date('Y-m-d H:i:s')
        );
        createRemarkLog($remark_log);
        
        $log_history = array();
	    $log_history['lead_number']		=	( $lead_number!= '' ? $lead_number :  NULL);
	    $log_history['enquiry_id']		=	$enquiry_id;
        $log_history['employee_id']		=	$post_data['user']['id'];
        $log_history['type']	        = 'edit';
        if(!empty($post_data['updated_fields'])){
            $updated_information =  implode(',', $post_data['updated_fields']);
            $log_history['details']			=	'Lead/ Enquiry has been updated with the following information like '. $updated_information;
        }else{
            $log_history['details']			=	'Lead/ Enquiry has been updated';    
        }
        
        createLog($log_history);
        
        $success_response = array('success' => 1, 'message' => 'Enquiry/ Lead has been successfully updated');
		echo json_encode($success_response,true); exit; 
	}
    else{
        
        $error_code     = mysql_errno();
        $error_message  = mysql_error();
        
		$failure_response = array(
            'success' => 0, 
            'message' => 'Something went wrong Lead/ Enquiry could not be updated. '. $error_message
        );
        
		echo json_encode($failure_response,true); exit; 
	}
}else{
	
	// No data error resonse to client 
	$no_data = array('success' => 0, 'message' => 'No Data Recieved');
	echo json_encode($no_data,true); exit; 
}