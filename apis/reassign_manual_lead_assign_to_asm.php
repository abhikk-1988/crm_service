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


if(!function_exists('get_project_city')){
	function get_project_city($project_id = null){
	
		$curl = curl_init();
		curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => 'http://localhost/apimain/api/get_project_city.php',
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

$data		= file_get_contents("php://input");
$data 		= json_decode($data);
$enquiry_id = '';
$asm_id     = '';

$errors     = array();

if(isset($data->enquiry_id)){
	$enquiry_id = $data->enquiry_id;
}else{
	$errors['enquiry_id'] = 'Enquiry Id not provided';
}

if(isset($data->asm_id) && $data->asm_id != ''){
	$asm_id = $data->asm_id;
}else{
	$errors['asm_id'] = 'Area Sales Manager id not provided';
}

// Validation check for enquiry id or asm id 
if(!empty($errors)){
	echo json_encode(array('success' => 0, 'message' => 'Either enqnuiry number or Area Sales Manager id not provided'),true); exit;
}

// Get category of $enquiry_id 
$current_month	= (int) date('m') - 1;
$current_year	= date('Y');
$current_date	= date('Y-m-d H:i:s');
$user			= $_SESSION['currentUser']['id']; 

$designation_id = $_SESSION['currentUser']['designation'];

$query = mysql_query("SELECT designation FROM designationmaster WHERE id = '$designation_id' LIMIT 1");

$result = mysql_fetch_assoc($query);

$designationName = $result['designation'];

$user_name      = $crm_manager =  $_SESSION['currentUser']['firstname'].' '.$_SESSION['currentUser']['lastname'];
$lead_category	= '';

// Get lead category type 
$get_lead_category_type = 'SELECT lead_category,customerName,customerEmail, customerMobile, disposition_status_id,disposition_sub_status_id, meeting_id, site_visit_id, customer_alternate_mobile, customerProfession, customerAddress, lead_added_by_user, lead_assigned_to_asm FROM lead WHERE enquiry_id = '.$enquiry_id.'  LIMIT 1';

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
    
	$lead_owner = getEmployeeName($lead_data -> lead_added_by_user); // Who created lead
	
    $lead_added_by_id = $lead_data -> lead_added_by_user;
    
    $reassign_user_id = $lead_data -> reassign_user_id;
    
    $lead_assigned_to_asm = $lead_data->lead_assigned_to_asm;
}

// Get enquired project

$get_project_enquired = 'SELECT `project_id`, `project_name`, `project_url` FROM `lead_enquiry_projects` WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
	
$enquiry_project_result = mysql_query($get_project_enquired);
$project_id				= '';
$project_name			= '';
$project_url			= '';
$project_city           = '';

if($enquiry_project_result && mysql_num_rows($enquiry_project_result) > 0){
			
	// get area sales manager with project capacity of current month 
	// if capacity is not defined for current month 
	// then lead should not be assigned to that area sales manager.
	// Also get the remaining space of lead capacities of area sales manager for that particular project
			
	$project_detail = mysql_fetch_object($enquiry_project_result);
	$project_id		=  $project_detail -> project_id;
	$project_name	=  $project_detail -> project_name;
	$project_url	=  $project_detail -> project_url;
	$project_city   =  get_project_city($project_id);
	
	// Check here for are sales manager capacity according to the lead type 
	// if lead type is SPL then check for his remaining SPL lead capacity 
	// if lead type is MPL then check for his remaining MPL lead capacity
	
	
	if(strtolower($lead_category) === 'spl'){
		
		$select_area_sales_manager_sql = 'SELECT userId, capacity, remaining_capacity FROM capacity_master WHERE pId = '.$project_id.' AND capacity_month = '.$current_month.' AND capacity_year = "'.$current_year.'"  AND userId = '.$asm_id.' AND remaining_capacity > 0';
		
		$area_sales_manager_result = mysql_query($select_area_sales_manager_sql);
		
		if($area_sales_manager_result && mysql_num_rows($area_sales_manager_result) > 0){

			// Here we assign lead to area sales manager
			// update in lead table against enquiry id on column <lead_assigned_to_asm>

			$asm_user = mysql_fetch_object($area_sales_manager_result);

			$leadDetails = getLead($enquiry_id);
			
			if($lead_assigned_to_asm){
				
				mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='area_sales_manager' AND enquiry_id='$enquiry_id' AND change_status='pending' ORDER BY ID ASC) s)");
				
				$re_assign_query = "INSERT INTO lead_re_assign (enquiry_id,user_type,from_user_id,to_user_id,disposition_status_id,disposition_sub_status_id, lead_type, remark, added_by) VALUES ('$enquiry_id', 'area_sales_manager', '$lead_assigned_to_asm', '$asm_id', '".$leadDetails['disposition_status_id']."',  '".$leadDetails['disposition_sub_status_id']."', 're-assign','lead re-assign to other asm','$user')"; 
				
			}else{
				
				mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='area_sales_manager' AND enquiry_id='$enquiry_id' AND change_status='pending' ORDER BY ID ASC) s)");
				
				$re_assign_query = "INSERT INTO lead_re_assign (enquiry_id,user_type,from_user_id,to_user_id,disposition_status_id,disposition_sub_status_id, lead_type, remark, added_by) VALUES ('$enquiry_id', 'area_sales_manager', '0', '$asm_id', '".$leadDetails['disposition_status_id']."',  '".$leadDetails['disposition_sub_status_id']."', 're-assign','lead re-assign to other asm', '$user')"; 
				
			}
			
			$reAssignId = mysql_query($re_assign_query);
			
			$asm_name = getEmployeeName($asm_id);
			$asm_email = getEmployeeEmailAddress($asm_id);
			$asm_number = getEmployeeMobileNumber($asm_id);
            
			if($reAssignId){
				
//				mysql_query("UPDATE lead SET lead_assigned_to_asm='$asm_id',lead_assigned_to_asm_on='$current_date', is_reassign = 1, reassign_user_id = '$asm_id', reassign_user_type = 'area_sales_manager' WHERE enquiry_id = '$enquiry_id' LIMIT 1");
				mysql_query("UPDATE lead SET lead_assigned_to_asm='$asm_id', lead_assigned_to_asm_on='$current_date' WHERE enquiry_id = '$enquiry_id' LIMIT 1");
				
				// Update remaining capacity of area sales manager in current month capacity
				
				$update_remaining_capacity = 'UPDATE capacity_master SET remaining_capacity = remaining_capacity - 1 WHERE userId = '.$asm_user -> userId.' AND pId= '.$project_id.' AND capacity_month = '.$current_month.' AND capacity_year = "'.$current_year.'" LIMIT 1';
				
				mysql_query($update_remaining_capacity);
			
				// on successfull update of asm id create history 

				// Lead has been assigned to area sales manager <Area Sales Manager_NAME> on <CURRENT_DATE>
				// Lead Details - 
				// Enquiry ID
				// Project ID

			
				$assignee_name  = getEmployeeName($user);    
				$history_text	= 'Lead re-assign by '.$designationName.' ('.$assignee_name.') to Area Sales Manager ('.$asm_name.') at '. date('d-m-Y H:i:s');
				$details		= '. Re-assign Lead Details:- 1.Enquiry ID - #'.$enquiry_id.', 2.Project - '.$project_name;
				$history_text	.= $details;	
				$lead_number	= getLeadNumber($enquiry_id);
				
				// create meta data for future reference
				if($reassign_user_id){
					$meta_data = array('from_user_id'=>$reassign_user_id, 'to_user_id'=>$agent_id,'user_type'=>'Agent','remark'=>'re-assign lead','enquiry_id'=>$enquiry_id,'assigned_by'=>$user_id,'date'=>$current_date);
				}else if($lead_added_by_id){
					$meta_data = array('from_user_id'=>$lead_added_by_id, 'to_user_id'=>$agent_id,'user_type'=>'Agent','remark'=>'re-assign lead','enquiry_id'=>$enquiry_id,'assigned_by'=>$user_id,'date'=>$current_date);
				}

				$assignment_history = array(
					'enquiry_id' => $enquiry_id,
					'lead_number' => $lead_number,
					'details' => $history_text,
					'employee_id' => $user,
					'type' => 're-assign',
					'meta_data' =>mysql_real_escape_string(json_encode($meta_data))
				);
                
				createLog($assignment_history);    
                
				/****************************************************************/
				// SEND MAIL TO Area Sales Manager OF LEAD ASSIGNMENT
				/****************************************************************/    

            
				$get_email_template = 'SELECT * FROM `email_templates` WHERE email_category = "internal" AND event = "re_assign_asm" LIMIT 1';
            
				$email_template_resource = mysql_query($get_email_template);
            
				if($email_template_resource && mysql_num_rows($email_template_resource) > 0){
                
					$email_template_object = mysql_fetch_object($email_template_resource);
                
					$address = '';
					$scheduled_datetime = '';
                
					if($lead_meeting_id != ''){
						$meeting_data = getLeadMeetingData($enquiry_id, $lead_meeting_id);
						$address = $meeting_data['meeting_address'];
						$scheduled_date = date('d-M-Y', $meeting_data['meeting_timestamp']/1000);
						$scheduled_time = date('H:i A', $meeting_data['meeting_timestamp']/1000);
                    
					}
					else if($lead_site_visit_id != ''){
						$site_visit_data = getSiteVisitDataById($lead_site_visit_id);
						$address = $site_visit_data['site_location'];
						$scheduled_date  = date('d-M-Y', $site_visit_data['site_visit_timestamp']/1000);
						$scheduled_time  = date('H:i A', $site_visit_data['site_visit_timestamp']/1000);
                    
					}
					
					$mail_keywords = array(
						'{{enquiry_id}}',
						'{{event_date}}',
						'{{event_time}}',
						'{{lead_owner}}',
						'{{client_name}}',
						'{{client_mobile_number}}',
						'{{client_alternate_number}}',
						'{{profession}}',
						'{{client_address}}',
						'{{project_name}}',
						'{{sales_person}}',
						'{{sales_manager}}',
						'{{crm_manager}}'
					);
                
					$keyword_replacement_values = array(
						$enquiry_id,
						$scheduled_date,
						$scheduled_time,
						$lead_owner,
						$client_name,
						$client_mobile,
						$client_alternate_number,
						$client_profession,
						$client_address,
						$project_name,
						'',
						$asm_name,
						$crm_manager
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
						TO	=> $asm_email,
//						TO	=> "umesh@bookmyhouse.com",
						CC	=> '',
						BCC => '', // add if any 
						SUBJECT => $email_template_object -> subject,
						TO_NAME => $asm_name
					);
        
					sendMailData($mail_data, $enquiry_id);
                
            
                
					// End: MAIL TO Area Sales Manager    
					/****************************************************************/    
                
            
					/*****************************************************************/
					// SEND SMS TO Area Sales Manager OF LEAD ASSIGNMENT    
					/*****************************************************************/    
                
					$sms_template = mysql_query('SELECT * FROM message_templates WHERE message_category = "internal" AND event = "re_assign_asm" LIMIT 1');
    
					if($sms_template && mysql_num_rows($sms_template) > 0){
         
						$sms_template_object = mysql_fetch_object($sms_template);
						
						$sms_keywords = array(
							'{{enquiry_id}}',
							'{{event_date}}',
							'{{event_time}}',
							'{{lead_owner}}',
							'{{client_name}}',
							'{{client_mobile_number}}',
							'{{client_alternate_number}}',
							'{{profession}}',
							'{{client_address}}',
							'{{project_name}}',
							'{{sales_person}}',
							'{{sales_manager}}',
							'{{crm_manager}}'
						);
         
						$sms_keyword_values = array(
							$enquiry_id,
							$scheduled_date,
							$scheduled_time,
							$lead_owner,
							$client_name,
							$client_mobile,
							$client_alternate_number,
							$client_profession,
							$client_address,
							$project_name,
							'',
							$asm_name,
							$crm_manager
						);

						$sms_body = str_replace($sms_keywords, $sms_keyword_values, $sms_template_object -> message);
         
						$sms_receiver_numbers = array();
						
						if( $sms_template_object -> default_numbers != ''){				
							// create an array of numbers 
							$sms_receiver_numbers = explode(',', $sms_template_object -> default_numbers);
						}
						
						array_push($sms_receiver_numbers, $asm_number );
//        				array_push($sms_receiver_numbers, "9818511886" );
						sendSMS($sms_receiver_numbers, $sms_body);
					}
                
					# END:      
					/*****************************************************************/    
                
				}  
                
				// Apply Round Robin lead Assignment Process to sales person in manual lead assignment process of Area Sales Manager also  

				$request_url	= BASE_URL .'apis/round_robin_assignment.php';
				$ch				= curl_init();
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_URL,$request_url);
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS,array('enquiry_ids' => serialize(array($enquiry_id)), 'asm_id' => $asm_user -> userId ));
				curl_exec ( $ch );
				curl_close($ch);
			
				// Success response 
				$response = array('success' => 1, 'message' => 'Lead/ Enquiry has been successfully assigned.');
				echo json_encode($response, true); exit;
			}	
			else{
				$error_response = array('success' => 0, 'message' => 'Lead couldn\'t be assigned to area sales manager. Please try again later.');
				echo json_encode($error_response,true); exit;
			}
		}else{
			$response = array('success' => 0, 'message' => 'Lead could not be assigned. No Area Sales Manager found for assignment');
			echo json_encode($response,true); exit;
		}
	}
	
	if(strtolower($lead_category) === 'mpl'){
		
	
		// Get the area sales manager and remaining capacity of MPL category
	
		// Query to Random selection of asm
//		$asm = 'SELECT emp.id as user_id, concat(emp.firstname ," ", emp.lastname) as username
//		FROM employees as emp LEFT JOIN mpl_capacity as mpl ON (emp.id = mpl.user_id) WHERE emp.designation = (SELECT id from designationmaster where designation_slug = "area_sales_manager") AND mpl.capacity IS NOT NULL AND mpl.remaining_capacity > 0 ORDER  BY rand() LIMIT 1';
		
		// Query to forcely select asm by asm id 
		$asm = 'SELECT emp.id as user_id, concat(emp.firstname ," ", emp.lastname) as username FROM employees as emp
		LEFT JOIN mpl_capacity as mpl ON (emp.id = mpl.user_id) WHERE emp.id = '.$asm_id.' AND mpl.capacity IS NOT NULL AND mpl.remaining_capacity > 0 ORDER  BY rand() LIMIT 1';
        
		$asm_result = mysql_query($asm);
		
		if($asm_result && mysql_num_rows($asm_result) > 0){
		
			$asm_user	= mysql_fetch_object($asm_result);
			
			$asm_name	= $asm_user -> username; 
			
			// Get Lead details using enquiry id
			$leadDetails = getLead($enquiry_id);
			
			
			if($lead_assigned_to_asm){
				
				mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='area_sales_manager' AND enquiry_id='$enquiry_id' AND change_status='pending' ORDER BY ID ASC) s)");
				
				
				$re_assign_query = "INSERT INTO lead_re_assign (enquiry_id,user_type,from_user_id,to_user_id,disposition_status_id,disposition_sub_status_id, lead_type, remark, added_by) VALUES ('$enquiry_id', 'area_sales_manager', '$lead_assigned_to_asm', '$asm_id', '".$leadDetails['disposition_status_id']."',  '".$leadDetails['disposition_sub_status_id']."', 're-assign','lead re-assign to other asm', '$user')"; 
				
			}else{
				
				mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='area_sales_manager' AND enquiry_id='$enquiry_id' AND change_status='pending' ORDER BY ID ASC) s)");
				
				
				$re_assign_query = "INSERT INTO lead_re_assign (enquiry_id,user_type,from_user_id,to_user_id,disposition_status_id,disposition_sub_status_id, lead_type, remark, added_by) VALUES ('$enquiry_id', 'area_sales_manager', '0', '$asm_id', '".$leadDetails['disposition_status_id']."', '".$leadDetails['disposition_sub_status_id']."', 're-assign','lead re-assign to other asm', '$user')"; 
				
			}
			
			
			$reAssignId = mysql_query($re_assign_query);
			
			if($reAssignId){
				
				// Re-assigning lead to asm 
				mysql_query("UPDATE lead SET lead_assigned_to_asm='$asm_id',lead_assigned_to_asm_on='$current_date', is_reassign = 1, reassign_user_type = 'area_sales_manager', reassign_user_id = '$asm_id' WHERE enquiry_id = '$enquiry_id' LIMIT 1");
					
				// update capacity 
				
				$update_remaining_capacity = 'UPDATE mpl_capacity SET remaining_capacity = remaining_capacity - 1 , edited_by = '.$user.' WHERE user_id = '.$asm_id.' LIMIT 1';
				mysql_query($update_remaining_capacity);
				
				// log history 
				
				$history_text	= 'Multiple project lead has been re-assigned by '.$designationName.' to Area Sales Manager '.$asm_name.' on '. date('Y-m-d H:i:s');
				$details		= 'Re-assign Lead Details: 1.Enquiry ID - #'.$enquiry_id;
				$history_text	.= $details;	
				$lead_number	= getLeadNumber($enquiry_id);
			
				$insert_history = 'INSERT lead_history (lead_number, enquiry_id, details, type) VALUES ("'.$lead_number.'",'.$enquiry_id.', "'.$history_text.'","re-assing")';

				mysql_query($insert_history);
				
				// Assigning to sales person from round robin method 

				$request_url	= BASE_URL .'apis/round_robin_assignment.php';
				$ch				= curl_init();
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_URL,$request_url);
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS,array('enquiry_ids' => serialize(array($enquiry_id)), 'asm_id' => $asm_id ));
				
				curl_exec ( $ch );
				curl_close($ch);
				
				// Success response 
				$response = array('success' => 1, 'message' => 'Lead/ Enquiry has been successfully assigned.');
				echo json_encode($response, true); exit;
			}
		}else{
			
			// error response 
			$error_response = array('success' => 0, 'message' => 'Lead could not be assigned. No Area sales manager found for assignment');
			echo json_encode($error_response,true); exit;
		}
	}
}else{
	
	$response = array('success' => 0, 'message' => 'Assignment could not be done. No Project found for this Lead/ Enquiry');
	echo json_encode($response,true); exit;
}