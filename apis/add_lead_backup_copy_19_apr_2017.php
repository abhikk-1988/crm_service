<?php
session_start();



require_once 'function.php';




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
		
	}
	
	
	else{
		
		
		
		
		$response_obj = json_decode($resp,true);
		
		
		
		return $response_obj['city_name'];	
	}
}


if (isset($_POST)) {
	
	
	
	
	// 	SESSION User information 
	
	$user_id	= $_SESSION['currentUser']['id'];
	
	
	
	$user_name	= $_SESSION['currentUser']['firstname'].' '.$_SESSION['currentUser']['lastname'];
	
	
	
	$user_email = $_SESSION['currentUser']['email'];
	
	
	
	
	$currentDateTime = date('Y-m-d H:i:s');
	
	
	
	
	// 	Client/ customer information 
	$client_name				= '';
	
	
	
	$client_email				= '';
	
	
	
	$client_dob					= '';
	
	
	
	$client_number				= '';
	
	
	
	$client_alternate_number	= '';
	
	
	
	$client_landline_number		= '';
	
	
	// 	landline number with STD code and extension 
	$client_country = '';
	
	
	
	$client_state	= '';
	
	
	
	$client_city	= '';
	
	
	
	$client_profession = '';
	
	
	
	$client_remarks = '';
	
	
	
	$client_gender	= '';
	
	
	
	$client_address = '';
	
	
	
	
	// 	Lead source information
	$primary_lead_source = '';
	
	
	
	$secondary_lead_source = '';
	
	
	
	
	// 	Client Preferences
	$client_preference = array();
	
	
	
	
	// 	Projects 
	$projects 		= array();
	
	
	// 	array of project name and url 
	$project_city 	= '';
	
	
	
	$filters 		= array();
	
	
	
	
	// 	Enquiry Status
	$enquiry_status_remark = '';
	
	
	
	
	// 	Email template id 
	$email_template_id = '';
	
	
	
	
	// 	Array of form errors 
	$form_errors = array();
	
	
	
	
	// 	Lead number and Enquiry number 
	$enquiry_id = '';
	
	
	
	$lead_number = 'NULL';
	
	
	
	
	// 	collecting form data 
	if (isset($_POST['client_info']['fullname']) && !empty($_POST['client_info']['fullname'])) {
		
		
		
		$client_name = $_POST['client_info']['fullname'];
		
		
		
	}
	
	
	else {
		
		
		
		//$		form_errors['name'] = 'Client name is required';
		
		
		
	}
	
	
	
	
	if (isset($_POST['client_info']['email']) && !empty($_POST['client_info']['email'])) {
		
		
		
		
		$client_email = filter_var($_POST['client_info']['email'], FILTER_SANITIZE_EMAIL);
		
		
		
		
		if (filter_var($client_email, FILTER_VALIDATE_EMAIL) === FALSE) {
			
			
			
			$form_errors['email'] = 'Invalid email address';
			
			
			
		}
		
		
		
	}
	
	
	else {
		
		
		
		//$		form_errors['email'] = 'Invalid email address';
		
		
		
	}
	
	
	
	
	if (isset($_POST['client_info']['gender']) && !empty($_POST['client_info']['gender'])) {
		$client_gender = $_POST['client_info']['gender'];
	}
	
	
	
	
	if (isset($_POST['client_info']['mobile_number']) && !empty($_POST['client_info']['mobile_number'])) {
		
		
		
		
		$client_number = $_POST['client_info']['mobile_number'];
		
		
		
		
		if (strlen($client_number) < 10) {
			
			
			
			$form_errors['mobile_number'] = 'Please enter 10 digit mobile number';
			
			
			
		}
		
		
		else if (preg_match('/^[A-Z]+$/i', $client_number)) {
			
			
			
			$form_errors['mobile_number'] = "Please enter only number's in mobile number";
			
			
			
		}
		
		
		
	}
	
	
	else {
		
		
		
		$form_errors['mobile_number'] = 'Please enter mobile number';
		
		
		
	}
	
	
	
	
	if (isset($_POST['client_info']['alternate_mobile_number']) && !empty($_POST['client_info']['alternate_mobile_number'])) {
		
		$client_alternate_number = $_POST['client_info']['alternate_mobile_number'];
		
	}
	
	
	
	
	if (isset($_POST['client_info']['dob']) && !empty($_POST['client_info']['dob'])) {
		
		$client_dob = date('Y-m-d', strtotime($_POST['client_info']['dob']));
		
	}
	
	
	if (isset($_POST['client_info']['profession']) && !empty($_POST['client_info']['profession'])) {
		
		$client_profession = $_POST['client_info']['profession'];
		
	}
	
	
	else {
		
		//$		form_errors['profession'] = 'Please enter profession';
		
	}
	
	
	if (isset($_POST['client_info']['address']) && !empty($_POST['client_info']['address'])) {
		
		$client_address = $_POST['client_info']['address'];
		
	}
	
	
	else {
		
		//$		form_errors['address'] = 'Please enter client address';
		
	}
	
	
	
	
	if (isset($_POST['client_info']['landline_number']) && !empty($_POST['client_info']['landline_number'])) {
		
		$client_landline_number = $_POST['client_info']['landline_number']['std_code'] . '-' . $_POST['client_info']['landline_number']['number'] . '-' . $_POST['client_info']['landline_number']['ext'];
		
	}
	
	
	
	
	if (isset($_POST['client_info']['city_name']) && !empty($_POST['client_info']['city_name'])) {
		
		
		
		$client_city = $_POST['client_info']['city_name'];
		
		
		
	}
	
	
	else {
		
		
		
		//$		form_errors['city'] = 'Please select a city';
		
		
		
	}
	
	
	
	
	if (isset($_POST['client_info']['state_name']) && !empty($_POST['client_info']['state_name'])) {
		
		
		
		$client_state = $_POST['client_info']['state_name'];
		
		
		
	}
	
	
	else {
		
		
		
		//$		form_errors['state'] = 'Please select a state';
		
		
		
	}
	
	
	
	
	if (isset($_POST['client_info']['remark']) && !empty($_POST['client_info']['remark'])) {
		
		
		
		$client_remarks = $_POST['client_info']['remark'];
		
		
		
	}
	
	
	
	
	if (isset($_POST['client_info']['country']) && !empty($_POST['client_info']['country'])) {
		
		
		
		$client_country = $_POST['client_info']['country'];
		
		
		
	}
	
	
	
	
	if (isset($_POST['lead_source']['primary']['source_id']) && !empty($_POST['lead_source']['primary']['source_id'])) {
		
		
		
		$primary_lead_source = $_POST['lead_source']['primary']['source_id'];
		
		
		
	}
	
	
	else {
		
		
		
		//$		form_errors['primary_lead_source'] = 'Please select primary lead source';
		
		
		
	}
	
	
	
	
	if (isset($_POST['lead_source']['secondary']['source_id']) && !empty($_POST['lead_source']['secondary']['source_id'])) {
		
		
		
		$secondary_lead_source = $_POST['lead_source']['secondary']['source_id'];
		
		
		
	}
	
	
	else {
		
		
		
		//$		form_errors['secondary_lead_source'] = 'Please select secondary lead source';
		
		
		
	}
	
	
	
	
	
	$callback_date			    = '';
	
	
	
	$callback_time			    = '';
	
	
	
	$enquiry_remark			    = '';
	
	
	
	$lead_enquiry_status_id		= 'NULL';
	
	
	
	$lead_enquiry_status_title	= '';
	
	
	
	$callback_status_remark		= '';
	
	
	
	$enquiry_sub_status_id		= 'NULL';
	
	
	
	$enquiry_sub_status_title	= '';
	
	
	
	$future_callback_date		= '';
	
	
	
	$future_callback_time		= '';
	
	
	
	$is_cold_call				= 0;
	
	
	
	
	
	if ( isset($_POST['enquiry']['id']) && !empty($_POST['enquiry']['id']) ) {
		
		
		
		
		// 		Enquiry Status Data 
		$lead_enquiry_status_id		= $_POST['enquiry']['id'];
		
		
		
		$lead_enquiry_status_title	= str_replace(' ', "_", trim($_POST['enquiry']['group_title']));
		
		
		
		
		// 		Check for primary enquiry status 
		$lead_enquiry_status_title_lowercase = strtolower( $lead_enquiry_status_title );
		
		
		
		
		// 		NOT INTERESTED OR JUST ENQUIRY
		if( $lead_enquiry_status_title_lowercase === 'not_interested' || $lead_enquiry_status_title_lowercase === 'just_enquiry'){
			
			
			if( isset($_POST['enquiry']['sub_status_id']) ){
				
				$enquiry_sub_status_id = $_POST['enquiry']['sub_status_id'];
				
			}
			
			
			else{
				
				$enquiry_sub_status_id = '';
				
			}
			
			
			// 			Enquiry status remark validation 
			if( isset($_POST['enquiry']['status_remark']) && !empty($_POST['enquiry']['status_remark'])){
				
				$enquiry_remark = $_POST['enquiry']['status_remark'];
				
			}
			
			
			else{
				
				$form_errors['status_remark'] = 'Please fill status remark';
				
			}
			
			
			// 			identify email template from status title 
			if( $lead_enquiry_status_title_lowercase === 'not_interested' || $lead_enquiry_status_title_lowercase === 'just_enquiry'){
				
				$email_template_id	= getEmailTemplateId('external', $lead_enquiry_status_title_lowercase);
				
			}
			
			
		}
		
		
		
		else if($lead_enquiry_status_title_lowercase == 'no_response'){
			
			if( isset($_POST['enquiry']['sub_status_id']) ){
				
				$enquiry_sub_status_id = $_POST['enquiry']['sub_status_id'];
				
			}
			
			else{
				
				$enquiry_sub_status_id = '';
				
			}
			
			
			// 			just capture the remark 
			$enquiry_remark = $_POST['enquiry']['status_remark'];
			
			
			
		}
		
		
		
		// 		for rest of the primary enquiry status and sub status 
		else{
			
			
			
			
			$enquiry_sub_status_id	= $_POST['enquiry']['sub_status_id'];
			
			
			
			
			// 			Validation if no sub status selected
			if($enquiry_sub_status_id == ''){
				
				
				
				$form_errors['sub status'] = 'Please select Sub Status';
				
				
				
			}
			
			
			
			else{
				
				
				
				
				// 				secondary status title 
				$enquiry_sub_status_title				= $_POST['enquiry']['sub_status_title'];
				
				
				
				
				// 				title in lowercase
				$enquiry_sub_status_title_in_lowercase	= strtolower( $enquiry_sub_status_title);
				
				
				
				
				switch( $lead_enquiry_status_title_lowercase ){
					
					
					
					
					case 'meeting':
					
					if($_POST['enquiry']['callback_date'] === ''  || $_POST['enquiry']['callback_time'] === '' ){
						
						
						
						$form_errors['meeting_status_error'] = 'Either callback date and time or status remark is not filled';
						
						
						
					}
					
					
					else{
						
						
						
						$callback_date	= $future_callback_date	= $_POST['enquiry']['callback_date'];
						
						
						
						$callback_time	= $future_callback_time = $_POST['enquiry']['callback_time'];
						
						
						
					}
					
					
					
					
					if ( $_POST['enquiry']['status_remark'] === '' ){
						
						
						
						$form_errors['enquiry_status_remark'] = 'Enquiry remark is not filled';
						
						
						
					}
					
					
					else{
						
						
						
						$enquiry_remark	= $_POST['enquiry']['status_remark'];
						
						
						
					}
					
					
					
					
					if ( $_POST['enquiry']['address'] === ''){
						
						
						
						$form_errors['meeting_address'] = 'Meting address is not provided';
						
						
						
					}
					
					
					else{
						
						
						
						$meeting_or_site_visit_address = $_POST['enquiry']['address'];
						
						
						
					}
					
					
					
					
					// 					Identify email template id 
					$meeting_sub_status = getMeetingSubstatus($enquiry_sub_status_title_in_lowercase);
					
					$email_template_id	= getEmailTemplateId('external',$meeting_sub_status);
					
					
					break;
					
					
					
					
					case 'site_visit':
					
					if($_POST['enquiry']['callback_date'] === ''  || $_POST['enquiry']['callback_time'] === '' ){
						
						
						
						$form_errors['site_visit_status_error'] = 'Either callback date and time is not filled';
						
						
						
					}
					
					
					else{
						
						
						
						$callback_date	= $future_callback_date	= $_POST['enquiry']['callback_date'];
						
						
						
						$callback_time	= $future_callback_time = $_POST['enquiry']['callback_time'];
						
						
						
						
						$site_visit_timestamp = '';
						
						
						
					}
					
					
					
					
					if( $_POST['enquiry']['status_remark'] === '' ){
						
						
						
						$form_errors['site_visit_enquiry_remark'] = 'Enquiry remark is not filled';
						
						
						
					}
					
					
					else{
						
						
						
						$enquiry_remark	= $_POST['enquiry']['status_remark'];
						
						
						
					}
					
					
					
					
					if( $_POST['enquiry']['address'] === ''){
						
						
						
						$form_errors['site_visit_address'] = 'Site visit address is not provided';
						
						
						
					}
					
					
					else{
						
						
						
						$meeting_or_site_visit_address = $_POST['enquiry']['address'];
						
						
						
					}
					
					
					
					
					// 					Identify email template id 
					$site_visit_sub_status    = getSiteVisitSubStatus($enquiry_sub_status_title_in_lowercase);
					
					
					
					$email_template_id	      = getEmailTemplateId('external',$site_visit_sub_status);
					
					
					
					
					break;
					
					
					
					
					case 'future_references':
					
					$future_ref_sub_status_title = str_replace(' ', "_", trim($enquiry_sub_status_title));
					
					
					
					
					if (strtolower($future_ref_sub_status_title) === 'cold_call' || $_POST['cold_call'] == 1) {
						
						
						
						$is_cold_call = 1;
						
						
						
					}
					
					
					
					
					// 					only for call_back as sub status we will check for extra information 
					if( strtolower($future_ref_sub_status_title) === 'call_back' ){
						
						
						
						
						if($_POST['enquiry']['callback_date'] === ''  || $_POST['enquiry']['callback_time'] === '' ){
							
							
							
							$form_errors['future_reference_callback_datetime_error'] = 'Either callback date or time is not filled';
							
							
							
						}
						
						
						else{
							
							
							
							$callback_date	= $future_callback_date	= $_POST['enquiry']['callback_date'];
							
							
							
							$callback_time	= $future_callback_time = $_POST['enquiry']['callback_time'];
							
							
							
						}
						
						
						
						
						if( $_POST['enquiry']['status_remark'] === '' ){
							
							
							
							$form_errors['future_reference_status_remark'] = 'Enquiry remark is not filled';
							
							
							
						}
						
						
						else{
							
							
							
							$enquiry_remark	= $_POST['enquiry']['status_remark'];
							
							
							
						}
						
						
						
						
						// 						Get Email template ID of callback event 
						
						$email_template_id	= getEmailTemplateId('external','call_back');
						
						
						
					}
					
					
					
					
					//						for follow up			
					
					if(strtolower($future_ref_sub_status_title) === 'follow_up'){
						
						
						
						
						if($_POST['enquiry']['callback_date'] === ''  || $_POST['enquiry']['callback_time'] === '' ){
							
							
							
							
							$form_errors['future_reference_callback_datetime_error'] = 'Either callback date or time is not filled';
							
							
							
						}
						
						
						else{
							
							
							
							$callback_date	= $future_callback_date	= $_POST['enquiry']['callback_date'];
							
							
							
							$callback_time	= $future_callback_time = $_POST['enquiry']['callback_time'];
							
							
							
						}
						
						
						
						
						if( $_POST['enquiry']['status_remark'] === '' ){
							
							
							
							$form_errors['future_reference_status_remark'] = 'Enquiry remark is not filled';
							
							
							
						}
						
						
						else{
							
							
							
							$enquiry_remark	= $_POST['enquiry']['status_remark'];
							
							
							
						}
						
						
						
					}
					
					
					
					
					break;
					
					
					
					
					case 'technical_issue':
					
					if($_POST['enquiry']['status_remark'] === ''){
						
						
						
						$form_errors['technical_issue_status_error'] = 'Enquiry remark is not filled';
						
						
						
					}
					
					
					else{
						
						
						
						$enquiry_remark = $_POST['enquiry']['status_remark'];
						
						
						
					}
					
					
					
					
					break;
					
					
					
					
				}
				
				
				// 				end of switch statement 
			}
			
			
			
		}
		
		
		
	}
	
	
	
	else {
		
		
		
		
		$form_errors['enquiry_status'] = 'Please select enquiry status';
		
		
		
	}
	
	
	
	
	// 	Projects
	if (isset($_POST['projects']['ids']) && !empty($_POST['projects']['ids'])) {
		
		
		
		
		// 		JSON encode projects array as we will save whole array in DB table as json string 
		$projects = $_POST['projects'];
		
		
		
	}
	
	
	
	
	
	// 	Project city 
	if (isset($_POST['project_city']) && !empty($_POST['project_city'])) {
		
		
		
		
		$project_city = $_POST['project_city'];
		
		
		
	}
	
	
	
	
	
	// 	project search criteria 
	if (isset($_POST['filters']) && !empty($_POST['filters'])) {
		
		
		
		
		// 		JSON encode filters array as we will save whole array in DB table as json string 
		$filters = $_POST['filters'];
		
		
		
		
		// 		Store customer preference in property searching 
		
		// 		BHK preference 
		$customer_bhk_preference  = '';
		
		
		
		if($filters['bhk']){
			
			
			
			$customer_bhk_preference = $filters['bhk'];
			
			
			
		}
		
		
		
		
		// 		Budget preference 
		$customer_budget_preference = '';
		
		
		
		if($filters['budget']['min'] != '' && $filters['budget']['max'] != ''){
			
			
			
			
			$customer_budget_preference = $filters['budget']['min'] .'-'.$filters['budget']['max'];
			
			
			
		}
		
		
		
		
		// 		Property State preference 
		$customer_property_state_preference = '';
		
		
		
		if($filters['property_status'] != ''){
			
			
			
			$customer_property_state_preference = $filters['property_status'];
			
			
			
		}
		
		
		
		
		// 		Property type preference 
		$customer_property_type_preference = '';
		
		
		
		if(!empty($filters['property_types'])){
			
			
			
			
			$customer_property_type_preference = implode(',', $filters['property_types']);
			
			
			
		}
		
		
		
	}
	
	
	
	
	// 	If $form_errors array is not empty then throw error back to user 
	if (!empty($form_errors)) {
		
		
		
		echo json_encode(array('success' => -1, 'errors' => $form_errors, 'message' => 'Please correct following errors'), true);
		
		
		
		exit;
		
		
		
	}
	
	
	else {
		
		
		
		
		// 		Form values is all clear we can save the data in DB
		// 		Generate Enquiry number   
		
		$enquiry_id = generateEnquiryID(array(1, 100000));
		
		
		
		
		
		
		
		/**
		 * If user has update status of enquiry as meeting or site visit then we have to create a lead number 
		 */
		
		
		
		if (strtolower($lead_enquiry_status_title) === 'meeting' || strtolower($lead_enquiry_status_title) === 'site_visit') {
			
			
			
			
			// 			Create a Lead Number 
			$lead_number = generateLeadNumber($enquiry_id);
			
			
			
		}
		
		
		
		
		// 		Get the employee to which lead/enquiry will be assigned 
		
		$insert_lead_sql = 'INSERT INTO `lead`'
		. ' SET '
		. ' lead_id = "'.$lead_number.'",'
		. ' enquiry_id = '.$enquiry_id.','
		. ' customerName= "'.$client_name.'",'
		. ' customerMobile = "'.$client_number.'",'
		. ' customerLandline = "'.$client_landline_number.'",'
		. ' customerEmail = "'.$client_email.'",'
		. ' customerProfession = "'.$client_profession.'",'
		. ' customerCity = "'.$client_city.'",'
		. ' customerState = "'.$client_state.'",'
		. ' customerCountry = "'.$client_country.'",'
		. ' customerDOB = "'.$client_dob.'",'
		. ' customerAddress = "'.$client_address.'",'
		. ' leadPrimarySource = "'.$primary_lead_source.'" ,'
		. ' leadSecondarySource = "'.$secondary_lead_source.'",'
		. ' customerRemark = "'.$client_remarks.'",'
		. ' lead_added_by_user = '.$user_id.','
		. ' leadAddDate = "'.date('Y-m-d H:i:s').'" ,'
		. ' enquiry_status_remark = "'.$enquiry_remark.'" ,'
		. ' disposition_status_id ='.$lead_enquiry_status_id.' ,'
		. ' disposition_sub_status_id =  "' .$enquiry_sub_status_id. '",'
		. ' is_cold_call = '.$is_cold_call.','
		. ' future_followup_date = "'.date('Y-m-d', strtotime($callback_date)).'",'
		. ' future_followup_time = "'.$callback_time.'",'
		. ' customer_gender = "'.$client_gender.'",'
		. ' customer_alternate_mobile = "'.$client_alternate_number.'",'
		. ' customer_bhk_preference = "'.$customer_bhk_preference.'",'
		. ' customer_project_state_preference = "'.$customer_property_state_preference.'",'
		. ' customer_budget_preference = "'.$customer_budget_preference.'",'
		. ' customer_property_type_preference = "'.$customer_property_type_preference.'",'
		. ' email_template_id = "'.$email_template_id.'"';
		
		
		
		
		if (mysql_query($insert_lead_sql)) {
			
			
			
			
			// 			Log enquiry remarks
			$remark_log = array(
			'remark' => $enquiry_remark,
			'enquiry_id' => $enquiry_id,
			'employee_id' => $user_id,
			'remark_creation_date' => date('Y-m-d H:i:s')
			);
			
			createRemarkLog($remark_log);
			
			
			
			
			// 			save enquiry projects 
			$flag_of_save_enquiry_projects = FALSE;
			
			
			
			
			
			$enquired_projects = array();
			
			
			
			
			// 			IF some project is selected
			if (!empty($projects['projects'])) {
				
				
				
				
				
				
				
				/**
				 * count no. of enquired projects and update lead category accordingly
				 * Code block added on 6 Jan 2017
				 */
				
				
				
				
				$no_of_projects_enquired	= count($projects['projects']);
				
				
				
				$lead_category				= '';
				
				
				
				if($no_of_projects_enquired > 1){
					
					
					
					$lead_category = 'MPL';
					
					
					
				}
				
				
				else{
					
					
					
					$lead_category = 'SPL';
					
					
					
				}
				
				
				
				
				// 				Updating lead type SPL or MPL according to seleced projects 
				mysql_query('UPDATE lead SET lead_category = "'.$lead_category.'" WHERE enquiry_id = '.$enquiry_id.'');
				
				
				
				
				
				foreach ($projects['projects'] as $key => $val) {
					
					
					
					
					$project_id		= $projects['ids'][$key];
					
					
					
					$project_name	= $val['project_name'];
					
					
					
					$project_url	= $val['project_url'];
					
					
					
					$project_city   = get_project_city($project_id);
					
					
					
					
					// 					Push each enquired projects in array
					array_push($enquired_projects, array('project_id' => $project_id,'project_name' => $project_name,'project_url' => $project_url,'project_city' => $project_city));
					
					
					
					
					
					$save_enquiry_projects = 'INSERT INTO `lead_enquiry_projects`'
					. '  (enquiry_id,lead_number,project_id,project_name,project_url,project_city) '
					. ' VALUES (' . $enquiry_id .',"'.$lead_number.'", '. $project_id . ', "' . $project_name . ' ","' . $project_url . '","'.$project_city.'")';
					
					
					
					
					if (mysql_query($save_enquiry_projects)) {
						
						
						
						$flag_of_save_enquiry_projects = true;
						
						
						
					}
					
					
					
				}
				
				
				
			}
			
			
			
			
			
			// 			Save add lead history 			
			$lead_details	= 'A new Enquiry/Lead has been created by '.$user_name. ' on '. date('d-m-Y H:i:s') . ' with status '.$_POST['enquiry']['group_title'].' '.$_POST['enquiry']['sub_status_title'];
			
			
			
			$add_lead_history = array(
			
			'type' => 'new',
			'details' => $lead_details,
			'enquiry_id' => $enquiry_id,
			'lead_number' => $lead_number,
			'employee_id' => $user_id
			);
			
			
			
			createLog($add_lead_history);
			
			
			
			
			
			// 			save auto assign lead to TL CRM history
			$tl_crm = getTLCRMName();
			
			$autoAssignLeadToTLCRMHistory = array(
			'type' => 'new',
			'details' => 'Lead auto assign to TL CRM ('.$tl_crm.') at '. date('d-m-Y H:i:s'),
			'enquiry_id' => $enquiry_id,
			'lead_number' => $lead_number,
			);
			
			
			
			createLog($autoAssignLeadToTLCRMHistory);
			
			
			
			// 			Save lead enquiry status details
			$meeting_data = array();
			
			
			
			$meeting_attendees = 1;
			
			
			// 			bydefault
			
			if (strtolower($lead_enquiry_status_title) === 'meeting') {
				
				
				
				
				$callback_date = date('Y-m-d', strtotime($callback_date));
				
				
				
				$callback_time = str_replace(array(' ','AM','PM'),'', $callback_time);
				
				
				
				$meeting_timestamp = strtotime($callback_date.' '.$callback_time) * 1000;
				
				
				// 				timestamp in miliseconds
				
				// 				meeting data 
				$meeting_data = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_number,
				'employee_id' => $user_id,
				'employee_name' => $user_name,
				'meeting_address' => $meeting_or_site_visit_address,
				'remark' => $callback_status_remark,
				'meeting_time' => $meeting_timestamp, // 				converting to timestamp in ms
				'meeting_location_type' => 'other',
				'attendees' => $meeting_attendees,
				'client' => json_encode(array('name' => $client_name,'phone' => $client_number, 'email' => $client_email,'city' => $client_city), true),
				'project' => json_encode($enquired_projects, true)
				);
				
				
				
				
				
				// 				CURL Request to create new meeting
				$create_meeting_url	= BASE_URL . 'apis/create_meeting.php';
				
				
				
				$ch		= curl_init($create_meeting_url);
				
				
				
				curl_setopt_array($ch, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $meeting_data
				));
				
				
				
				$result	= curl_exec($ch);
				
				
				
				curl_close($ch);
				
				
				
				$is_meeting_created = 0;
				
				
				
				if($result != ''){
					
					
					
					// 					getting meeting id as response
					$is_meeting_created = 1;
					
					
					
					
					$meeting_status_history = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_number,
					'details' => 'Lead/ Enquiry status has changed to Meeting '.$enquiry_sub_status_title.' on '. date('d-M-Y H:i:s'). ' by '. $user_name,
					'type' => 'new',
					'employee_id' => $user_id
					);
					
					
					
					
					createLog($meeting_status_history);
					
					
					// 					function to create log
				}
				
				
				
				
				// 				LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
				$internal_mail_data = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_number,
				'client_name' => $client_name,
				'client_number' => $client_number,
				'address' => $meeting_or_site_visit_address,
				'project_city' => $project_city,
				'project_name' => $project_name
				);
				
				
				sendLeadAssginementMailToTLCRM($internal_mail_data);
				
			}
			
			
			
			else if (strtolower($lead_enquiry_status_title) === 'site_visit') {
				
				
				
				
				$callback_date = date('Y-m-d', strtotime($callback_date));
				
				
				
				$callback_time = str_replace(array(' ','PM','AM'),'', $callback_time);
				
				
				
				$site_visit_timestamp = strtotime($callback_date.' '.$callback_time) * 1000;
				
				
				// 				timestamp in miliseconds
				
				// 				Site visit data 
				$site_visit_data = array(
				
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_number,
				'site_visit_timestamp' => $site_visit_timestamp,
				'executiveId' => $user_id,
				'executiveName' => $user_name,
				'site_location' => $meeting_or_site_visit_address,
				'project' => json_encode($enquired_projects, true),
				'client' => json_encode(array('client_name' => $client_name, 'email' => $client_email, 'phone' => $client_number),true),
				'vehicle_accomodated' => '',
				'number_of_person_visited' => '',
				'site_visit_status' => 0,
				'remark' => $enquiry_remark
				);
				
				
				
				
				// 				CURL Request to create new meeting
				$create_sitevisit_url	= BASE_URL . 'apis/create_site_visit.php';
				
				
				
				$ch		= curl_init($create_sitevisit_url);
				
				
				
				curl_setopt_array($ch, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $site_visit_data
				));
				
				
				
				$result	= curl_exec($ch);
				
				
				
				
				curl_close($ch);
				
				
				
				$is_site_visit_created = 0;
				
				
				
				if($result != ''){
					
					
					
					// 					getting meeting id as response
					$is_site_visit_created = 1;
					
					
					
					
					$sitevisit_status_history = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_number,
					'details' => 'Lead/ Enquiry status has changed to Site visit '.$enquiry_sub_status_title.' on '. date('d-M-Y H:i:s'). ' by '. $user_name,
					'type' => 'new',
					'employee_id' => $user_id
					);
					
					
					
					
					createLog($sitevisit_status_history);
					
					
					// 					function to create log
				}
				
				
				
				
				
				// 				LEAD ASSIGNMENT MAIL FROM AGENT/ EXECUTIVE TO TL CRM
				$internal_mail_data = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_number,
				'client_name' => $client_name,
				'client_number' => $client_number,
				'address' => $meeting_or_site_visit_address,
				'project_city' => $project_city,
				'project_name' => $project_name
				);
				
				
				
				sendLeadAssginementMailToTLCRM($internal_mail_data);
				
				
				
				
			}
			
			
			
			else if(strtolower($lead_enquiry_status_title) === 'not_interested' || strtolower($lead_enquiry_status_title) === 'just_enquiry'){
				
				
				
				
				// 				Send Reminder mail
				sendSimpleReminderMail($enquiry_id);
				
				
				
			}
			
			
			
			else if (strtolower($lead_enquiry_status_title) === 'future_references'){
				
				
				
				
				$sub_status_title = str_replace(' ', "_", trim($enquiry_sub_status_title));
				
				
				
				
				if( strtolower($sub_status_title) == 'call_back' ){
					
					
					
					sendCallBackMailReminder($enquiry_id);
					
					
					
					
					// 					insert followup counter
					$callback_counter = array();
					
					
					
					array_push($callback_counter,array(
					'follow_up_date' => $callback_date,
					'follow_up_time' => $callback_time,
					'remark' => $enquiry_remark
					));
					
					
					
					
					$callback_counter = mysql_real_escape_string(json_encode($callback_counter,true));
					
					mysql_query('UPDATE lead SET callback_counter = "'.$callback_counter.'" WHERE enquiry_id = '.$enquiry_id.'');
					
					
				}
				
				
				
				else if(strtolower($sub_status_title) == 'follow_up'){
					
					
					sendFollowupReminder($enquiry_id);
					
					// 					insert followup counter
					$followup_counter = array();
					
					
					
					array_push($followup_counter,array(
					'follow_up_date' => $callback_date,
					'follow_up_time' => $callback_time,
					'remark' => $enquiry_remark
					));
					
					
					
					
					$followup_counter = mysql_real_escape_string(json_encode($followup_counter,true));
					
					
					
					
					mysql_query('UPDATE lead SET followup_counter = "'.$followup_counter.'" WHERE enquiry_id = '.$enquiry_id.'');
					
					
					
				}
				
				
				
			}
			
			
			
			/*********************************************************************/
			
			
			
			// 			AUTO ASSIGN LEAD TO RESPECTIVE ASM 
			
			
			
			/*********************************************************************/
			
			
			
			$request_url = BASE_URL .'apis/auto_allocate_lead_to_asm.php';
			
			
			
			$ch = curl_init();
			
			
			
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			
			
			
			curl_setopt ($ch, CURLOPT_URL,$request_url);
			
			
			
			curl_setopt($ch, CURLOPT_POST, 1);
			
			
			
			curl_setopt($ch, CURLOPT_POSTFIELDS,array('enquiry_id' => $enquiry_id));
			
			
			
			$response = curl_exec ( $ch );
			
			
			
			curl_close($ch);
			
			
			
			
			// 			End: AUTO ASSIGNMENT LEAD TO ASM
			
			
			
			/**********************************************************************/
			
			
			
			
			
			echo json_encode(array('success' => 1, 'message' => 'A new lead/ Enquiry has been created successfully', 'save_enquiry_projects_flag' => $flag_of_save_enquiry_projects), TRUE);
			
			
			
			exit;
			
			
			
		}
		
		
		else {
			
			
			
			
			$error = mysql_error();
			
			
			
			echo json_encode(array(
			'success' => 0, 
			'message' => 'Lead couldn\'t be added at this time.'.$error), true);
			
			exit;
			
		}
		
	}
	
}

else {
	
	echo json_encode(array('success' => 0, 'message' => 'Enquiry/ Lead could not be created at this time'), TRUE);
	
	exit;
	
}


