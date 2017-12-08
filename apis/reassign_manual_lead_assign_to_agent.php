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
$data 		= json_decode($data,true);

$enquiry_id = array();
$agent_id     = '';

$errors     = array();

if(count($data['enquiry_id']) > 0 && !empty($data['enquiry_id'])){
	$enquiry_id = $data['enquiry_id'];
}else{
	$errors['enquiry_id'] = 'Enquiry Id not provided';
}

if(isset($data['agent_id']) && $data['agent_id'] != ''){
	$agent_id = $data['agent_id'];
}else{
	$errors['agent_id'] = 'Agent id not provided';
}

// Validation check for enquiry id or agent id 
if(!empty($errors)){

	echo json_encode(array('success' => 0, 'message' => 'Either enqnuiry id or Agent id not provided'),true); exit;
}

// Get category of $enquiry_id 

$current_date	= date('Y-m-d H:i:s');

$user_id			= $_SESSION['currentUser']['id']; 

$designation_id     = $_SESSION['currentUser']['designation'];

$designationName = '';

$user_name = '';

// We query from database for user designation incase user designation cannot be found in user session data 
// otherwise we will use user session to get designation

if(isset($_SESSION['currentUser']['designation_title'])){
	$designationName = $_SESSION['currentUser']['designation_title'];
}
else{
	$query = mysql_query("SELECT designation FROM designationmaster WHERE id = '$designation_id' LIMIT 1");
	$result = mysql_fetch_assoc($query);
	$designationName = $result['designation'];
}

$user_name      = $crm_manager =  $_SESSION['currentUser']['firstname'].' '.$_SESSION['currentUser']['lastname'];

// Get old agent id
$reAssignEnqID = array();
foreach($enquiry_id as $enqId){
	
	$get_lead_category_type = 'SELECT lead_added_by_user, reassign_user_id, concat(b.firstname ," ", b.lastname) as username FROM lead AS a LEFT JOIN employees AS b ON b.id = a.lead_added_by_user WHERE enquiry_id = '.$enqId.' LIMIT 1';

	$lead_category_result	= mysql_query($get_lead_category_type);

	if($lead_category_result && mysql_num_rows($lead_category_result) > 0){
    
		$lead_data = mysql_fetch_object($lead_category_result);
	
		$lead_added_by_username =  $lead_data -> username;
	
		$lead_added_by_id =  $lead_data -> lead_added_by_user;
	
		$reassign_user_id =  $lead_data -> reassign_user_id;
	
	}
	// End Old User Details

	// Get New agent details
	$get_new_agent_id = 'SELECT * FROM employees WHERE id = '.$agent_id.' LIMIT 1';

	$get_new_agent_result	= mysql_query($get_new_agent_id);

	if($get_new_agent_result && mysql_num_rows($get_new_agent_result) > 0){
	
		$agent_data = mysql_fetch_object($get_new_agent_result);
	
		$new_agent_email =  $agent_data -> email;
	
	}
	// End Old User Details

	if($lead_category_result && mysql_num_rows($lead_category_result) > 0){
	
		// Get Lead details using enquiry id
		$leadDetails = getLead($enqId);
	
		// Insert Data into Re-assign table
		if($reassign_user_id!=''){
			#########START IVR Sticky Agent Push By Sudhanshu##################
			$query_emp = mysql_query("SELECT crm_id from employees where id = '$reassign_user_id'");
			$crm_id_data = mysql_fetch_assoc($query_emp);
			$urls = "";
			$stickyarray = array('mobile' => $leadDetails['customerMobile'],'agent_id'=>$crm_id_data,'camp_name'=>'Bookmyhouse');
			foreach($stickyarray as $keys => $values){
				$urls .= urlencode($keys).'='.urlencode($values).'&';
			}
        
			$hitsticky = "https://admin.c-zentrixcloud.com/apps/addlead.php";
			$contentsticky = file_get_contents($hitsticky.'?'.$urls);
			#########END IVR Sticky Agent Push By Sudhanshu##################
			mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='agent' AND enquiry_id='$enqId' AND to_user_id='$reassign_user_id' AND change_status='pending' ORDER BY ID DESC LIMIT 1) s)");
		
		}else{
		
			mysql_query("UPDATE lead_re_assign SET lead_re_assign.change_status='removed' WHERE lead_re_assign.id= (SELECT id FROM ( SELECT lead_re_assign.`id` FROM  lead_re_assign WHERE lead_re_assign.user_type ='agent' AND enquiry_id='$enqId' AND to_user_id='".$leadDetails['lead_added_by_user']."' AND change_status='pending' ORDER BY ID DESC LIMIT 1) s)");
		
		}
	
		if($leadDetails['reassign_user_id']){
			
			$from_user_id = $leadDetails['reassign_user_id'];
			
		}else{
			
			$from_user_id = $leadDetails['lead_added_by_user'];
		}
	
	
		$re_assign_query = "INSERT INTO lead_re_assign (enquiry_id, user_type, from_user_id, to_user_id, disposition_status_id, disposition_sub_status_id, added_by) VALUES ('$enqId', 'agent', '$from_user_id', '$agent_id', '".$leadDetails['disposition_status_id']."',  '".$leadDetails['disposition_sub_status_id']."', '$user_id')"; 
	
		$reAssignId = mysql_query($re_assign_query);
	
	
		if($reAssignId){
			// Update lead tbl
		
			mysql_query('UPDATE lead SET is_reassign = 1, reassign_user_id='.$agent_id.', reassign_user_type="agent" WHERE enquiry_id = '.$enqId.' LIMIT 1');
		
			$assignee_name  = getEmployeeName($user_id);
		
			$new_agent_name  = getEmployeeName($agent_id);
		
			if($reassign_user_id){
				$lead_added_by_username  = getEmployeeName($reassign_user_id);
			}
		
		
			$history_text	= "Lead/Enquiry Id: #$enqId has been re-assigned by $designationName ($assignee_name) from Agent ($lead_added_by_username) to Agent ($new_agent_name) at ".date('d-m-Y H:i:s');	
		
			$lead_number	= getLeadNumber($enqId);
		
			// create meta data for future reference
			$meta_data = array();
			if($reassign_user_id){
			
				$meta_data = array('from_user_id'=>$reassign_user_id, 'to_user_id'=>$agent_id,'user_type'=>'agent','remark'=>'re-assign lead','enquiry_id'=>$enqId,'assigned_by'=>$user_id,'date'=>$current_date);
			
			}else if($lead_added_by_id){
			
				$meta_data = array('from_user_id'=>$lead_added_by_id, 'to_user_id'=>$agent_id,'user_type'=>'agent','remark'=>'re-assign lead','enquiry_id'=>$enqId,'assigned_by'=>$user_id,'date'=>$current_date);
			}

			$assignment_history = array(
				'enquiry_id' => $enqId,
				'lead_number' => $lead_number,
				'details' => $history_text,
				'employee_id' => $user_id,
				'type' => 're-assign',
				'meta_data' =>mysql_real_escape_string(json_encode($meta_data))
			);
        
			createLog($assignment_history); 
	 
			$response = array('success' => 1, 'message' => 'Lead/ Enquiry has been successfully assigned.');
			
			$reAssignEnqID[] = $enqId;
	
		}else{
	
			$response = array('success' => 0, 'message' => 'Lead couldn\'t be re-assigned to agent. Please try again later.');
		}
	}
}

/****************************************************************/
// SEND MAIL TO ASM OF LEAD ASSIGNMENT
/****************************************************************/ 
if(count($reAssignEnqID) > 0){
	
	$get_email_template = 'SELECT * FROM `email_templates` WHERE email_category = "internal" AND event = "re_assign_agent" LIMIT 1';
    
	$email_template_resource = mysql_query($get_email_template);

	if($email_template_resource && mysql_num_rows($email_template_resource) > 0){

		$email_template_object = mysql_fetch_object($email_template_resource);

		$address = '';
	
		$scheduled_datetime = '';
	
		$mail_keywords = array(
			'{{enquiry_no}}',
			'{{agent}}'
		);
		$successReassignID = implode(',', $reAssignEnqID);
		$keyword_replacement_values = array(
			$successReassignID,
			$new_agent_name
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
			TO	=> $new_agent_email,
			//				TO	=> "umesh@bookmyhouse.com",
			CC	=> '',
			BCC => '', // add if any 
			SUBJECT => $email_template_object -> subject,
			TO_NAME => $new_agent_name
		);
		sendMailData($mail_data, $enqId);
	}	
}
// End: MAIL TO AGENT    
/****************************************************************/ 

echo json_encode($response,true); exit;