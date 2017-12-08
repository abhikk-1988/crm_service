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

// echo '<pre>';
// print_r($data);
// exit;

// User Information 
$user = $_SESSION['currentUser'];

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
            $lead['customerAddress'] = $val;
        }

        // Client Remark
        if($col == 'remark' && $val != ''){
            $lead['customerRemark'] = $val;
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
                $lead['enquiry_status_remark'] = $remark = $val;
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


// Create New Lead
$insert_lead = 'INSERT INTO `lead` SET '; 
foreach($lead as $col => $val){
    $insert_lead .= $col . ' = "'. $val .'" ,';
}

// trim comma from sql string from right side 
$insert_lead = trim(rtrim($insert_lead, ' ,'));

$time = $callback_date.''.$callback_time;

$new_date = date('Y-m-d H:i:s',strtotime($time)); 

	
					#########IVR Disposition Push By Sudhanshu##################
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$status_title		= getStatusLabel($status_id, 'parent');
			$sub_status_title	= getStatusLabel($sub_status_id, 'child');
			$data = array('transaction_id' => 'CTI_SET_DISPOSITION','agent_id'=>$user['crm_id'],'ip'=>$ip,'cust_disp'=>$status_title,'category'=>$sub_status_title,'next_call_time'=>$new_date,'resFormat'=>'1');
			$curl = curl_init();
			$url = "";
			foreach($data as $key => $value){
                            $url .= urlencode($key).'='.urlencode($value).'&';
			}
			echo $url;
			$hitURL = "http://agent.c-zentrixcloud.com/apps/appsHandler.php";
			$content = file_get_contents($hitURL.'?'.$url);
			echo $content;