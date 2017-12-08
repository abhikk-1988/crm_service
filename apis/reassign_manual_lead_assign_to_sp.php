<?php
session_start();
require 'function.php';

function sendMailData( $email_data = '', $enquiry_id = ''){
	
	$curl_url	= BASE_URL . 'apis/sendEmailReminder.php';
	$curl		= curl_init($curl_url);
	curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => false,
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



$data		= file_get_contents("php://input");
$data 		= json_decode($data);
$enquiry_id = '';
$sp_id     = '';
$status = '';
$errors     = array();

if(isset($data->enquiry_id)){
	$enquiry_id = $data->enquiry_id;
}else{
	$errors['enquiry_id'] = 'Enquiry Id not provided';
}

if(isset($data->sp_id) && $data->sp_id != ''){
	$sp_id = $data->sp_id;
}else{
	$errors['sp_id'] = 'Sales Person id not provided';
}

// Validation check for enquiry id or sp id 
if(!empty($errors)){
	echo json_encode(array('success' => 0, 'message' => 'Either enqnuiry number or Sales Person id not provided'),true); 
	exit;
}


// Get category of $enquiry_id 
$current_month	= (int) date('m') - 1;
$current_year	= date('Y');
$current_date	= date('Y-m-d H:i:s');
// Calculate expire date
$expire_date = date('Y-m-d', strtotime("+14 days"));


$user_id			= $_SESSION['currentUser']['id'];

$designation_id = $_SESSION['currentUser']['designation'];

$query = mysql_query("SELECT designation FROM designationmaster WHERE id = '$designation_id' LIMIT 1");

$result = mysql_fetch_assoc($query);

$designationName = $result['designation'];
 
$user_name      = $_SESSION['currentUser']['firstname'].' '.$_SESSION['currentUser']['lastname'];
$lead_category	= '';

// Get lead category type 
$get_lead_category_type = 'SELECT lead_category, customerName, customerEmail, customerMobile, disposition_status_id, disposition_sub_status_id, meeting_id, site_visit_id, customer_alternate_mobile, customerProfession, customerAddress, lead_added_by_user FROM lead WHERE enquiry_id = '.$enquiry_id.'  LIMIT 1';

$lead_category_result	= mysql_query($get_lead_category_type);

if($lead_category_result && mysql_num_rows($lead_category_result) > 0){
	
	$lead_data = mysql_fetch_object($lead_category_result);
	
	$lead_category =  $lead_data -> lead_category;
    
	$client_name = $lead_data -> customerName;
    
	$client_email = $lead_data -> customerEmail;
    
	$client_mobile = $lead_data -> customerMobile;
    
	$client_address = $lead_data -> customerAddress;
    
	$client_alternate_number = $lead_data -> customer_alternate_mobile;
    
	$client_profession = $lead_data -> customerProfession;
    
	$lead_current_status = getStatusLabel($lead_data -> disposition_status_id, 'parent'); // Primary status 
    
	$lead_current_sub_status = getStatusLabel($lead_data -> disposition_sub_status_id, 'child'); // Secondary Status
    
	$lead_meeting_id = $lead_data -> meeting_id; // Meeting ID
    
	$lead_site_visit_id = $lead_data -> site_visit_id; // Site Visit ID
    
    $lead_added_by_id = $lead_data -> lead_added_by_user;
    
    $reassign_user_id = $lead_data -> reassign_user_id;
    
	$lead_owner = getEmployeeName($lead_data -> lead_added_by_user); // Who created lead
    
}


$select_area_sales_manager_sql = 'SELECT * FROM sales_person_capacities WHERE sales_person_id = '.$sp_id.' AND month = '.$current_month.' AND year = '.$current_year.' AND remaining_capacity > 0';

$area_sales_manager_result = mysql_query($select_area_sales_manager_sql);
		
if($area_sales_manager_result && mysql_num_rows($area_sales_manager_result) > 0){

	// Here we assign lead to area sales manager
	// update in lead table against enquiry id on column <lead_assigned_to_sp>

	$sp_user = mysql_fetch_object($area_sales_manager_result);

	$leadDetails = getLead($enquiry_id);
	
	//Update ASM Status in re-assing table
	
	
	
	$re_assign_query = "INSERT INTO lead_re_assign (enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, added_by) VALUES ('$enquiry_id', 'sales_person', '".$leadDetails['lead_assigned_to_sp']."', '$sp_id', '".$leadDetails['disposition_status_id']."',  '".$leadDetails['disposition_sub_status_id']."', '$user_id')"; 
	
	$reAssignId = mysql_query($re_assign_query);
			
	$sp_name = getEmployeeName($sp_id);
	$sp_email = getEmployeeEmailAddress($sp_id);
	$sp_number = getEmployeeMobileNumber($sp_id);
            
	if($reAssignId){
		$updateLeadData = 'UPDATE lead SET lead_assigned_to_sp = '.$sp_id.', lead_assigned_to_sp_on = "'.$current_date.'", lead_expire_date_of_sp = "'.$expire_date.'", is_lead_accepted = 0, is_lead_rejected = 0, is_overdue=0,is_overdue_mail_sent=0  WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
		
		mysql_query($updateLeadData);
		
		// Update remaining capacity of area sales manager in current month capacity
				
		$update_remaining_capacity = 'UPDATE sales_person_capacities SET remaining_capacity = remaining_capacity - 1 WHERE sales_person_id = '.$sp_id.' AND month = '.$current_month.' AND year = '.$current_year.' LIMIT 1';
		
		if(mysql_query($update_remaining_capacity)){
			// on successfull update of sp id create history 
				
			$assignee_name  = getEmployeeName($user_id); 
			   
			$history_text	= 'Lead re-assigned by '.$designationName.' ('.$assignee_name.') to Sales Person ('.$sp_name.') at '. date('d-m-Y H:i:s');
			
			$lead_number	= getLeadNumber($enquiry_id);
			
			// create meta data for future reference
			if($reassign_user_id){
				
				$meta_data = array('from_user_id'=>$reassign_user_id, 'to_user_id'=>$agent_id,'user_type'=>'sales_person','remark'=>'re-assign lead','enquiry_id'=>$enquiry_id,'assigned_by'=>$user_id,'date'=>$current_date);
			}else if($lead_added_by_id){
				
				$meta_data = array('from_user_id'=>$lead_added_by_id, 'to_user_id'=>$agent_id,'user_type'=>'sales_person','remark'=>'re-assign lead','enquiry_id'=>$enquiry_id,'assigned_by'=>$user_id,'date'=>$current_date);
			}
			
			$assignment_history = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => $lead_number,
				'details' => $history_text,
				'employee_id' => $user_id,
				'type' => 're-assign',
				'meta_data'=>mysql_real_escape_string(json_encode($meta_data))
			);
	                
			createLog($assignment_history);    
	                
			/************SEND MAIL TO Sales Person OF LEAD ASSIGNMENT*****************************/ 
		
			$get_email_template = 'SELECT * FROM email_templates WHERE email_category = "internal" AND event = "re_assign_sp" LIMIT 1';
            
			$email_template_resource = mysql_query($get_email_template);
            
			if($email_template_resource && mysql_num_rows($email_template_resource) > 0){
                
				$email_template_object = mysql_fetch_object($email_template_resource);
	                
				$client_info = getCLientInfoByEnquiry($enquiry_id);
                    
                $lead_info = getLead($enquiry_id);
                
                $address = '';
                $scheduled_datetime = '';
                $project_name = '';
                $project_city = '';
                
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
                    
                } else if($lead_info['site_visit_id'] != ''){
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
                    '{{sales_person}}',
                    '{{sales_manager}}'
                );
                
				$keyword_replacement_values = array(
                    $enquiry_id,
                    $status,
                    $scheduled_date,
                    $scheduled_time,
                    getEmployeeName($lead_info['lead_added_by_user']),
                    $client_info['customerName'],
                    $client_info['customerMobile'],
                    $client_info['customer_alternate_mobile'],
                    $client_info['customerProfession'],
                    $client_info['customerAddress'],
                    $project_name,
                    $sp_name,
                    $assignee_name
                );
                      
                      
				// Replace value in meessage 
                
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
					TO	=> $sp_email,
//					TO	=> "umesh@bookmyhouse.com",
					CC	=> '',
					BCC => '', // add if any 
					SUBJECT => $email_template_object -> subject,
					TO_NAME => $sp_name
				);
        
				sendMailData($mail_data, $enquiry_id);
            }
		    
			/************End: MAIL TO Sales Person********************************/
			
			/***************SEND SMS TO Sales Person OF LEAD ASSIGNMENT  ***********************************/    
            
			$sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "internal" AND event = "re_assign_sp" LIMIT 1');
    
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
                    '{{sales_person}}',
                    '{{sales_manager}}'
                );
         
				$sms_keyword_values = array(
                    $enquiry_id,
                    $status,
                    $scheduled_date,
                    $scheduled_time,
                    getEmployeeName($lead_info['lead_added_by_user']),
                    $client_info['customerName'],
                    $client_info['customerMobile'],
                    $client_info['customer_alternate_mobile'],
                    $client_info['customerProfession'],
                    $client_info['customerAddress'],
                    $project_name,
                    $sp_name,
                    $assignee_name
                );

				$sms_body = str_replace($sms_keywords, $sms_keyword_values, $sms_template_object -> message);
         
				$sms_receiver_numbers = array();
						
				if( $sms_template_object -> default_numbers != ''){				
					// create an array of numbers 
					$sms_receiver_numbers = explode(',', $sms_template_object -> default_numbers);
				}
						
				array_push($sms_receiver_numbers, $sp_number );
//				array_push($sms_receiver_numbers, "9818511886" );
				sendSMS($sms_receiver_numbers, $sms_body);
			} 
			/***************END**************************************************/    
			
			
			// Send Internal reminder mail according to the status of the lead 
                
            $lead_current_status = getCurrentEnquiryStatus($enquiry_id);
			
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
                } else if ($lead_current_status['primary_status_id'] == 6 ){ // Site visit 
                    
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
				
			// Success response 
			$response = array('success' => 1, 'message' => 'Lead/ Enquiry has been successfully assigned.');
			
			
		}else{
			
			$response = array('success' => 0, 'message' => 'Lead couldn\'t be assigned to sales person due some technical error');
		
		}
	}else{
		
		$response = array('success' => 0, 'message' => 'Lead could not be assigned due to some error');
	}
}else{
	
	$response = array('success' => 0, 'message' => 'Lead could not be assigned. There is no remaining capacity for lead assignment');

}	
echo json_encode($response,true); exit;