<?php
session_start();

require 'function.php';

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

// Get POST data  
$data = filter_input_array(INPUT_POST);
$user = $_SESSION['currentUser'];
// echo '<pre>';
// print_r($data);
// exit;

// User Information 

// If user is out from session then we will not allowed to go ahead  
if(! isset($user) && count($user) <= 0){

    $json_resonse = json_encode(array(
        'success' => '0',
        'message' => 'User not authenticated',
        'error_title' => 'Authentication Error',
        'http_status_code' => 401
    ),true); 
    
    echo $json_resonse; exit;

}

// Client Infomation 
$client_info = array();

// Lead Source Information 
$lead_source = array();

// Cleint  Preference infotmation
$client_pref = array();

// Projects Information 
$projects = array();

// Project City 
$project_city = '';

// Enquiry Information
$enquiry = array();

// Is Cold Call
$isColdCall = '';

// Lead Model
$lead = array();

// Form Errors
$errors = array();

// Validation on data 

// Mobile number
// Email Address (valid email if provided) 
// Enquiry Status -
//  - primary status, secondary status, callback date, callback time, remark, address (if meeting or sitevisit) 
// Project (in case of meeting or sitevisit status)
// 

// Iterating & Info 
if(isset($data['client_info'])){

    $client_info = $data['client_info'];

    foreach($client_info as $col => $val){

        // Client Mobile Number 
        if($col == 'mobile_number'){

            // validate & mobile number 
            if($val == ''){
                $errors['mobile_number'] = 'Please enter & mobile number';
            }
            else if(strlen($val) < 10){
                $errors['mobile_number'] = 'Please Enter 10 digit mobile number';
            }
            else if(preg_match('/\s/',$val)){
                $errors['mobile_number'] = 'Space is not allowed in mobile number';
            }
            else{
                // Type case number from string to int
                $lead['customerMobile'] = $val;
                if (filter_var((int) $val, FILTER_VALIDATE_INT) === false) {
                    $errors['mobile_number'] = 'Please enter a valid mobile number';
                }
            }
        }

        // Client Email
        if($col == 'email'){

            if($val != ''){
                if (filter_var($val, FILTER_VALIDATE_EMAIL) === false) {
                    $errors['email'] = 'Please enter a valid email id';
                }else{
                    $lead['customerEmail']  = $val;
                }
            }
        }

        // Client Fullname 
        if($col == 'fullname'){

            if($val != ''){
                $lead['customerName'] = $val;
            }else{
                $errors['customerName'] = 'Please enter client name or use default name';
            }
        }

        // Client Landline Number
        if($col == 'landline_number'){
            $lead['customerLandline'] = $val['std_code'].'-'.$val['number'].'-'.$val['ext'];

            if( $lead['customerLandline'] == '--' ){
                // Blank Input
                unset($lead['customerLandline']);
            }

        }

        // Client Alternate Number 
        if($col == 'alternate_mobile_number' && $val != ''){
            $lead['customer_alternate_mobile'] = $val;
        }

        // Client Gender
        if($col == 'gender' && $val != ''){
            $lead['customer_gender'] = $val;
        }

        // Client DOB
        if($col == 'dob' && $val != ''){
            $lead['customerDOB'] = date('Y-m-d', strtotime($val));
        }



        // Client Profession
        if($col == 'profession' && $val != ''){
            $lead['customerProfession'] = $val;
        }    

        // Client City ID
        if($col == 'city_id' && $val != ''){
            $lead['customerCityId'] = $val;
        }

        // Client City Name
        if($col == 'city_name' && $val != ''){
            $lead['customerCity']  =    $val;
        }

        // Client State ID
        if($col == 'state_id' && $val != ''){
            $lead['customerStateId'] = $val;
        }

        // Client State Name
        if($col == 'state_name' && $val != ''){
            $lead['customerState'] = $val;
        }

        // CLient Country 
        if($col == 'country' && $val != ''){
            $lead['customerCountry'] = $val;
        }

        // Client Address
        if($col == 'address' && $val != ''){
            $lead['customerAddress'] = mysql_real_escape_string($val);
        }

        // Client Remark
        if($col == 'remark' && $val != ''){
            $lead['customerRemark'] = mysql_real_escape_string($val);
        }

    } // end of foreach
}

// Iterating Lead Source 

if(isset($data['lead_source'])){

    // Primary Lead Source 
    foreach($data['lead_source']['primary'] as $key => $val){
        if($key == 'source_id' && $val != ''){
            $lead['leadPrimarySource'] = $val;
        }
    }

    // Secondary Source 
    foreach($data['lead_source']['secondary'] as $key => $val){
        if($key == 'source_name' && $val != ''){
            $lead['leadSecondarySource'] = $val;
        }
    }
    
}


// Iterating Client Preferences 
$lead['client_property_preferences'] = array();

if(isset($data['filters'])){

    // We have to capture filters for keeping track of user's last selected preferences for any project 
    
    $budget_string = '';
    foreach($data['filters']['budget'] as $key => $val)
    {
        if($key == 'min_label' && $val != ''){
            $budget_string .= $val;
        }

        if($key == 'max_label' && $val != ''){
            $budget_string .= ' - '.$val;
        }
    }

    if($budget_string != ''){

        $lead['client_property_preferences']['budget'] = array();
        array_push($lead['client_property_preferences']['budget'], $budget_string);
    }

    // BHK Filter 
    if(isset($data['filters']['bhk'])){
        
        $lead['client_property_preferences']['bhk'] = array();

        foreach($data['filters']['bhk'] as $key => $val)
        {
            // $Key will be numeric indexes 
            // $val will be an array 
            // We have to put the label of BHK filter in array
            array_push($lead['client_property_preferences']['bhk'], $val['label']);
        }
    }

    // Property Status Filter
    if(isset($data['filters']['property_status'])){
        
        $lead['client_property_preferences']['property_status'] = array();

        foreach($data['filters']['property_status'] as $key => $val)
        {
            // $Key will be numeric indexes 
            // $val will be an array 
            // We have to put the label of Property Status filter in array
            array_push($lead['client_property_preferences']['property_status'], $val['label']);
        }
    }

    // Property Type Filter
    if(isset($data['filters']['property_types'])){
        
        $lead['client_property_preferences']['property_types'] = array();

        foreach($data['filters']['property_types'] as $key => $val)
        {
            // $Key will be numeric indexes 
            // $val will be an array 
            // We have to put the label of Property Types filter in array
            array_push($lead['client_property_preferences']['property_types'], $val['label']);
        }
    }
}

// If there were filter then encode it in a json string
if(!empty($lead['client_property_preferences'])){
    $filter_json = json_encode($lead['client_property_preferences'],true);
    $lead['client_property_preferences'] = mysql_real_escape_string($filter_json);
}else{
    unset($lead['client_property_preferences']);
}
    
// To Capture date and time for applicable events
$callback_date = '';
$callback_time = '';
$enquiry_title = ''; // Title of primary enquiry status 
$sub_enquiry_title = ''; // Title of secondary or sub status 
$address       = ''; // Meeting or site visit address 
$time_meridian = ''; // Time meridian if time is required 
$status_id     = ''; // primary enquiry status id 
$sub_status_id = ''; // secondary or sub enquiry status id 
$remark        = ''; // Remark on enquiry 

$callback_date_required_status  = [3,6,4];
$address_required_status        = [3,6];

// Query to DB for disposition status having sub status 
$disposition_status_having_child_status = mysql_query('SELECT id FROM `disposition_status_substatus_master` WHERE id IN (SELECT parent_status FROM disposition_status_substatus_master)');
$parent_status = array();

if($disposition_status_having_child_status && mysql_num_rows($disposition_status_having_child_status) > 0){
    while($row = mysql_fetch_assoc($disposition_status_having_child_status)){
        array_push($parent_status, $row['id']);
    }
}

// Iterating Lead Status 
if($data['enquiry'])
{
 
    $enquiry = $data['enquiry'];

    foreach($enquiry as $key => $val){

        if($key == 'id'){

            if($val != ''){
                $lead['disposition_status_id'] = $status_id = $val;
            }else{
                $errors['enquiry_status'] = 'Please select an enquiry status';
            }
        }

        if($key == 'sub_status_id' && $val != ''){
            $lead['disposition_sub_status_id'] = $sub_status_id =  $val;
        }

        if($key == 'sub_status_title' && $val != ''){
            $sub_enquiry_title = $val;
        }

        if($key == 'group_title'){
            $enquiry_title = $val; // primary enquiry status title
        }

        if($key == 'callback_date' && $val != ''){
            $callback_date = $lead['future_followup_date'] = date('Y-m-d', strtotime($val)); // Change given date to mysql date format 
        }

        if($key == 'callback_time' && $val != ''){
            $callback_time = $lead['future_followup_time'] = $val;
        }

        if($key == 'status_remark'){

            if($val != ''){
                $lead['enquiry_status_remark'] = $remark = mysql_real_escape_string($val);
            }
            else{
                $errors['enquiry_remark'] = 'Please enter remark';
            }
        }

        if($key == 'address' && $val != ''){
            $address = $val; 
        }

        if($key == 'meridian' & $val != ''){
            
            $time_meridian = $val;

            // Append meridian with time if time is selected
            if($callback_time != ''){
                $callback_time = $callback_time.''.$time_meridian;
                $lead['future_followup_time'] = $callback_time;
            }
        }

    }   // End foreach 


    // Validate information based on enquiry status selected
    
    // Validate callback date and time for meeting or sitevisit or callback and follow ups 
    if(in_array($status_id, $callback_date_required_status)){

         // Callback date and Time is required 

         if($callback_date == ''){
            $errors['callback date'] = 'Please select date';
         }else{ 
         }

         if($callback_time == ''){
            $errors['calback_time'] = 'Please select time';
         }

         if($time_meridian == ''){
            $errors['time_meridian'] = 'Please select time meridian';
         }
    }

     // Validate Address for meeting or site visit weather it is empty or not
    if(in_array($status_id, $address_required_status)){

        if($address == ''){
            $errors['address'] = 'Please enter address';
        }
    }

    // Validate sub status id 
    if(in_array($status_id, $parent_status))
    {
        if($sub_status_id == ''){
            $errors['sub_status'] = 'Please select sub status';
        }
    }

}

// Validation on Enquiry Projects
if(isset($data['projects'])){
    
    $projects = $data['projects'];

    $project_ids = $projects['ids'];

    // check if project is selected or not
    if(count($project_ids) == 0){
        $errors['project'] = 'Please select project';
    }else{

        // lead category type 
        if(count($project_ids) <= 1){
            $lead['lead_category'] = 'SPL';
        }else{
            $lead['lead_category'] = 'MPL';
        }
    }
}
else{ 
    $errors['projects'] = 'Please select project';
}

// upto here vaidation work is over and further we will proceed with saving data in DB

// Generate Enquiry ID 
$enquiry_id = $lead['enquiry_id'] = generateEnquiryID(array(1, 1000000));

// Genarate Lead Number 
$lead_id = $lead['lead_id'] = generateLeadNumber($enquiry_id);

// Reformat callback time in 24 hour time format
if($callback_time != ''){
    $callback_time = $lead['future_followup_time'] =  date("H:i A", strtotime($callback_time));
}

// If errors then submmit back to user 
if(!empty($errors)){

    // Send JSON data to client 
    $json_response = json_encode(array(

        'success' => 0,
        'errors' => $errors,
        'http_status_code' => 200,
        'message' => 'Please corrent following errors',
        'message_title' => 'Validation Error'
    ), true);

    echo $json_response; exit;
}

$lead['lead_added_by_user'] = $user['id'];
$lead['leadAddDate'] = date('Y-m-d H:i:s', time());
$sql_fetch = "SELECT * from crm_enquiry_capture where phone = '".$lead['customerMobile']."' or email = '".$lead['customerEmail']."'";
if(mysql_fetch_row(mysql_query($sql_fetch) > 0))
{
	$update_query = "UPDATE crm_enquiry_capture SET enquiry_assign_by_to_agent_id = '".$lead['lead_added_by_user']."', agent_assign_status = 1,agent_assign_date = '".date('Y-m-d')."',agent_assign_time = '".strtotime(date('Y-m-d H:i:s'))."' where phone = '".$lead['customerMobile']."' or email = '".$lead['customerEmail']."'";
	mysql_query($update_query);
	
	}else{
		
	$insert_query = "INSERT INTO crm_enquiry_capture (query_request_id,created_time,created_on,enquiry_from,syn_in_crm,phone,email,name,ivr_push_type,ivr_push_status,enquiry_assign_to_agent_id,agent_assign_status,agent_assign_date,agent_assign_time) VALUES('PORT".rand() ."','".strtotime(date('Y-m-d H:i:s'))."','".date('Y-m-d')."','PORTAL','1','".$lead['customerMobile']."','".$lead['customerEmail']."','".$lead['customerName']."','Pending','Pending','".$lead['lead_added_by_user']."','1','".date('Y-m-d')."','".strtotime(date('Y-m-d H:i:s'))."')";
	
	mysql_query($insert_query);
	
}
// Create New Lead
$insert_lead = 'INSERT INTO `lead` SET '; 
foreach($lead as $col => $val){
    $insert_lead .= $col . ' = "'. $val .'" ,';
}

// trim comma from sql string from right side 
$insert_lead = trim(rtrim($insert_lead, ' ,'));

  
$timevalue = substr($callback_time,0,5);

$new_date = $callback_date." ".$timevalue.":00"; 
if(mysql_query($insert_lead)){

    /***************************************************************************************************************/
    /* UMESH WORK OF REASSIGNED
    /* INSERT IN TWO TABLES <LEAD_RE_ASSIGN> <LEAD_STATUS>
    /* DO NOT REMOVE THIS CODE BLOCK 
    /****************************************************************************************************************/

    
    if($sub_status_id == ''){
        $sub_status_id = 0;
    }
	$ip = $_SERVER['REMOTE_ADDR'];
			$status_title		= getStatusLabel($status_id, 'parent');
			$sub_status_title	= getStatusLabel($sub_status_id, 'child');
			if($status_title != 'Future references'){
			$new_date = '';
			}
			$dataarray = array('transaction_id' => 'CTI_SET_DISPOSITION','agent_id'=>$user['crm_id'],'ip'=>$ip,'cust_disp'=>$status_title,'category'=>$sub_status_title,'next_call_time'=>$new_date,'resFormat'=>'1');
			$curl = curl_init();
			$url = "";
			
			foreach($dataarray as $key => $value){
				$url .= urlencode($key).'='.urlencode($value).'&';
			}
			//print_r($dataarray);
			
			
			$hitURL = "http://admin.c-zentrixcloud.com/apps/appsHandler.php";
			$content = file_get_contents($hitURL.'?'.$url);
		
    mysql_query('INSERT INTO lead_re_assign (enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, lead_type, remark, change_status, added_by) VALUES ('.$enquiry_id.',"agent",0,'.$user['id'].','.$status_id.','.$sub_status_id.',"create","'.$remark.'","pending",'.$user['id'].')');    
    mysql_query('INSERT INTO lead_status (lead_id, enquiry_id, disposition_status_id, disposition_sub_status_id, user_type, user_id, remark) VALUES ("'.$lead_id.'",'.$enquiry_id.','.$status_id.','.$sub_status_id.',"agent",'.$user['id'].',"'.$remark.'")');

    /******* End code block umesh work ******************************************************************************/


    // Now you have successfully insert a new lead 

    // Let's save other related data 

    // Save Enquiry Projects
    $insert_enquiry_projects_sql= '';
    $enquiry_projects_name = array();
    $project_json = array(); // will hold json string of enquired projects 

    foreach($projects['projects'] as $col => $val){

        array_push($enquiry_projects_name,$val['project_name']);

        $sql_string = 'INSERT INTO `lead_enquiry_projects` SET ';

        $temp = array();
        $temp['enquiry_id']     = $enquiry_id;
        $temp['lead_number']    = $lead_id;
        $temp['project_id']     = $val['id'];
        $temp['project_city']   = get_project_city($val['id']);
        $temp['project_url']    = $val['project_url'];
        $temp['project_name']   = $val['project_name'];

        array_push($project_json, $temp);

        foreach($temp as $col => $val){
            $sql_string .= $col .'= "'.$val .'" ,';
        }

        $insert_enquiry_projects_sql  = trim(rtrim($sql_string,' ,')) ;

        mysql_query($insert_enquiry_projects_sql);
    }


    // Update enquiry Status 

    switch($status_id){

        case '3': // Meeting

            $meeting_timestamp = strtotime(str_replace(array('','AM','PM','am','pm'),'',$callback_date . $callback_time))*1000;

            $meeting_project_json = '';

            if(count($project_json)>0){
                unset($project_json['enquiry_id']);
                unset($project_json['lead_number']);
                $meeting_project_json = json_encode($project_json,true);
            }

            $client_json_data = json_encode(array(
                'name' => $client_info['fullname'],
                'phone' => $client_info['mobile_number'],
                'email' => $client_info['email'],
                'city' => $client_info['city_name']
            ),true);    

            $executive_id = $user['id'];
            $executive_name = $user['firstname'].' '.$user['lastname'];
            $meeting_remark = $remark;
            $meeting_address = $address;

            // Update email template id 
            $meeting_sub_status = getMeetingSubstatus(strtolower($sub_enquiry_title));
			$email_template_id	= getEmailTemplateId('external',$meeting_sub_status);

            if($email_template_id != ''){

                // Update email template id 
                mysql_query('UPDATE `lead` SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
            }

            // Meeting data collection 
            $meeting = array(
                'enquiry_id' => $enquiry_id,
                'lead_number' => $lead_id,
                'meeting_time' => $meeting_timestamp,
                'employee_id' => $executive_id,
                'employee_name' => $executive_name,
                'project' => $meeting_project_json,
                'client' => $client_json_data,
                'remark' => $meeting_remark,
                'meeting_address' => $meeting_address,
            );

            callCURL('create_meeting.php',$meeting);

            // 	LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
			$internal_mail_data = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_id,
				'client_name' =>  $lead['customerName'],
				'client_number' => $lead['customerMobile'],
				'address' => $meeting_address,
				'project_city' => $project_json[0]['project_city'],
				'project_name' => $project_json[0]['project_name']
			);

			sendLeadAssginementMailToTLCRM($internal_mail_data);
            break;
        case '6': // Site Visit 

            $site_visit_timestamp = strtotime(str_replace(array('','AM','PM','am','pm'),'',$callback_date . $callback_time))*1000;

            $site_visit_project_json = '';

            if(count($project_json)>0){
                unset($project_json['enquiry_id']);
                unset($project_json['lead_number']);
                $site_visit_project_json = json_encode($project_json,true);
            }

            $client_json_data = json_encode(array(
                'name' => $client_info['fullname'],
                'phone' => $client_info['mobile_number'],
                'email' => $client_info['email'],
                'city' => $client_info['city_name']
            ),true);    

            $executive_id           = $user['id'];
            $executive_name         = $user['firstname'].' '.$user['lastname'];
            $site_visit_remark      = $remark;
            $site_visit_address     = $address;

            // Update email template id 
            $sitevisit_sub_status = getSiteVisitSubStatus(strtolower($sub_enquiry_title));
			$email_template_id	= getEmailTemplateId('external',$sitevisit_sub_status);

            if($email_template_id != ''){

                // Update email template id 
                mysql_query('UPDATE `lead` SET email_template_id = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
            }

            // Site Visit data collection 
            $site_visit = array(
                'enquiry_id' => $enquiry_id,
                'lead_number' => $lead_id,
                'site_visit_timestamp' => $site_visit_timestamp,
                'executiveId' => $executive_id,
                'executiveName' => $executive_name,
                'project' => $site_visit_project_json,
                'client' => $client_json_data,
                'remark' => $site_visit_remark,
                'site_location' =>$site_visit_address,
                'vehicle_accomodated' => '',
                'number_of_person_visited' => '',
                'site_visit_status' => 0,
            );

            callCURL('create_site_visit.php',$site_visit);

            // 	LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
			$internal_mail_data = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_id,
				'client_name' => $lead['customerName'],
				'client_number' => $lead['customerMobile'],
				'address' => $site_visit_address,
				'project_city' => $project_json[0]['project_city'],
				'project_name' => $project_json[0]['project_name']
			);	
			sendLeadAssginementMailToTLCRM($internal_mail_data);
            break;
        case '4': // FUP

            if(strtolower(str_replace(' ','_',$sub_enquiry_title)) == 'call_back'){
                sendCallBackMailReminder($enquiry_id);
                // insert followup counter
				$callback_counter = array();	
				array_push($callback_counter,array(
					'follow_up_date' => $callback_date,
					'follow_up_time' => $callback_time,
					'remark' => $remark
				));

                $callback_counter = mysql_real_escape_string(json_encode($callback_counter,true));
                mysql_query('UPDATE `lead` SET callback_counter = "'.$callback_counter.'" WHERE enquiry_id = '.$enquiry_id.'');

            }
            else if(strtolower(str_replace(' ','_',$sub_enquiry_title)) == 'follow_up'){
                sendFollowupReminder($enquiry_id);
                // insert followup counter
				$followup_counter = array();
                array_push($followup_counter,array(
					'follow_up_date' => $callback_date,
					'follow_up_time' => $callback_time,
					'remark' => $remark
				));

                $followup_counter = mysql_real_escape_string(json_encode($followup_counter,true));
                mysql_query('UPDATE `lead` SET followup_counter = "'.$followup_counter.'" WHERE enquiry_id = '.$enquiry_id.'');

            }
            else if(strtolower(str_replace(' ','_',$sub_enquiry_title)) == 'cold_call'){
                mysql_query('UPDATE `lead` SET is_cold_call = 1 WHERE enquiry_id = '.$enquiry_id.'');
            }


            // Add entry in new table "future refrences"
            mysql_query('INSERT INTO future_references (enquiry_id,callback_date, callback_time, user_id, disposition_status_id, disposition_sub_status_id, remark) VALUES ('.$enquiry_id.',"'.$callback_date.'", "'.$callback_time.'",'.$user['id'].','.$status_id.','.$sub_status_id.',"'.$remark.'")');

            break;
        case '1': // Not Interested 0

            $email_template_id	= getEmailTemplateId('external', strtolower(str_replace(' ','_', $enquiry_title)));

            if($email_template_id != ''){
                mysql_query('UPDATE `lead` SET `email_template_id` = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
            }

            // Send Reminder mail
			sendSimpleReminderMail($enquiry_id);
            break;      
        case '5': // Technical Issue
            break;
        case '38': // No response
            break;
        case '34': // Just Enquiry

            $email_template_id	= getEmailTemplateId('external', strtolower(str_replace(' ','_', $enquiry_title)));

            if($email_template_id != ''){
                mysql_query('UPDATE `lead` SET `email_template_id` = '.$email_template_id.' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
            }
            // Send Reminder mail
			sendSimpleReminderMail($enquiry_id);
            break;
    }    

    // Remarks Log 
    $remark_log = array(
		'remark' => $remark,
		'enquiry_id' => $enquiry_id,
		'employee_id' => $user['id'],
		'remark_creation_date' => date('Y-m-d H:i:s')
	);
	createRemarkLog($remark_log);

    sleep(2); // Delay script execution for 2 seconds to make a time difference in logs

    // If callback date is present then show in log along with disposition status
    $disposition_status_datetime = '';   
    if($callback_date != '')
    {
        $disposition_status_datetime = 'at ' . date('d/m/Y ',strtotime($callback_date)) . ' ' . $callback_time;
    }

    // Lead add Log
    $lead_details	= 'A new Lead has been created by Agent '.$user['firstname'].' '.$user['lastname']. ' on '. date('d-m-Y H:i:s') . ' with status '.$enquiry_title.' '.$sub_enquiry_title .' '. $disposition_status_datetime ;
	$add_lead_history = array(
		'type' => 'new',
		'details' => $lead_details,
		'enquiry_id' => $enquiry_id,
		'lead_number' => $lead_id,
		'employee_id' => $user['id']
	);
	createLog($add_lead_history);

    sleep(1);

    // save in history enquiry projects
    $save_enquiry_project_history = array(
        'enquiry_id' => $enquiry_id,
        'lead_number' => $lead_id,
        'details' => 'Lead is created with following projects '. implode(' , ', $enquiry_projects_name),
        'employee_id' => $user['id']
    );

    createLog($save_enquiry_project_history);

    sleep(2); // Delay script execution for 2 seconds to make a time difference in logs

    // TL CRM lead assignment log
    $tl_crm = getTLCRMName();
	$leadAssignLogTLCRM = array(
		'type' => 'new',
		'details' => 'Lead assign to TL CRM ('.$tl_crm.') at '. date('d-m-Y H:i A') .' with status - '. $enquiry_title . ' '. $sub_enquiry_title,
		'enquiry_id' => $enquiry_id,
		'lead_number' => $lead_id,
    );
    createLog($leadAssignLogTLCRM);

    // Auto allocation of lead to AMS if auto allocation is on 
    // auto_allocate_lead_to_asm.php
    //callCURL('auto_allocate_lead_to_asm.php',array('enquiry_id' => $enquiry_id));

    // Send response to client 
    $json_response = json_encode(array(
        'success' => 1,
        'http_status_code' => 200,
        'errors' => '',
        'message' => 'A new Lead has been created successfully',
        'message_title' => 'New Lead Created'
    ),true);

    echo $json_response; exit;

}else{

    $mysql_error = mysql_error();

    if($mysql_error){

        // send error response to user 

        $json_response = json_encode(array(
            'success' => -1,
            'errors' => $mysql_error,
            'message' => 'We could not create lead at this time. Please try again later',
            'http_status_code' => 200,
            'message_title' => 'Server Error'
        ), true);

        echo $json_response;exit;
    }
}