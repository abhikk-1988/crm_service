<?php
session_start();
require_once 'function.php';
require_once 'user_authentication.php';

$current_date =  date('Y-m-d H:i:s');

$start_date =  '2017-07-11 00:00:00';

//$sqlENQ = "SELECT enquiry_id, DATEDIFF(CURDATE(), lead_assigned_to_sp_on) AS DAYS, lead_assigned_to_asm, lead_assigned_to_sp, reassign_user_id, lead_added_by_user, lead_assigned_to_sp_on, lead_expire_date_of_sp FROM lead WHERE lead_assigned_to_sp_on > '$start_date' AND lead_assigned_to_sp_on < '$current_date' AND disposition_status_id IN ('3','4','6','38') AND lead_assigned_to_sp !='' AND lead_expire_date_of_sp!='' AND lead_assigned_to_sp='71' LIMIT 5";

$sqlENQ = "SELECT enquiry_id, DATEDIFF(lead_expire_date_of_sp,lead_assigned_to_sp_on) AS DAYS, lead_assigned_to_asm, lead_assigned_to_sp, reassign_user_id, lead_added_by_user, lead_assigned_to_sp_on, lead_expire_date_of_sp FROM lead WHERE DATEDIFF(lead_expire_date_of_sp,CURDATE()) < 4 AND disposition_status_id IN ('3','4','6','38') AND lead_assigned_to_sp !='' AND lead_expire_date_of_sp!='' AND lead_assigned_to_asm='51'";

$result  = mysql_query($sqlENQ);
$arr = array();
if(mysql_num_rows($result) > 0){
	while($rows = mysql_fetch_assoc($result)){
		$days = $rows['DAYS'];
		$enquiry_id = $rows['enquiry_id'];
		
		$asm_id = $rows['lead_assigned_to_asm'];
		$asm_email = getEmployeeEmailAddress($rows['lead_assigned_to_asm']);
		$asm_mobile = getEmployeeMobileNumber($asm_email);
		$asm_name = getEmployeeName($rows['lead_assigned_to_asm']);
		
		$sp_id = $rows['lead_assigned_to_sp'];
		$sp_email = getEmployeeEmailAddress($rows['lead_assigned_to_sp']);
		$sp_mobile = getEmployeeMobileNumber($sp_email);
		$sp_name = getEmployeeName($rows['lead_assigned_to_sp']);
		
		$sp_lead_assign_date = $rows['lead_assigned_to_sp_on'];
		$sp_lead_expire_date = $rows['lead_expire_date_of_sp'];
		
		if($rows['reassign_user_id']){
			
			$agent_id = $rows['reassign_user_id'];
			$current_crm_name = getEmployeeName($rows['reassign_user_id']);
		
		}else{
		
			$agent_id = $rows['lead_added_by_user'];
			$current_crm_name = getEmployeeName($rows['lead_added_by_user']);
		}
		
		if($days >= 12 && $days <= 14){
			// remove lead from sp panel
			$SQ_SELECT = mysql_query("SELECT * FROM lead_extend_date WHERE enquiry_id='$enquiry_id' AND sp_id='$sp_id' AND status!='expired' AND status!='extended'");
			
			if(mysql_num_rows($SQ_SELECT) > 0){
				
				$resultRow = mysql_fetch_assoc($SQ_SELECT);
			
				$no_of_reminder = $resultRow['no_of_reminder']+1;
			
				$insertedId = mysql_query("UPDATE lead_extend_date SET no_of_reminder='$no_of_reminder' WHERE enquiry_id='$enquiry_id' AND sp_id='$sp_id' AND status!='expired'");
			
				
			}else{
				$sql = "INSERT INTO lead_extend_date(enquiry_id,agent_id, asm_id, sp_id, sp_lead_assign_date, sp_lead_expire_date, no_of_reminder) VALUES ('$enquiry_id','$agent_id','$asm_id','$sp_id','$sp_lead_assign_date', '$sp_lead_expire_date',1)";
				$insertedId = mysql_query($sql);
			}
			
			if($insertedId){
				sendSMS(array($sp_mobile),"ENQ: $enquiry_id is about to expire on $sp_lead_expire_date. Please contact your reporting manager");	
				sendSMS(array($asm_mobile),"ENQ: $enquiry_id is about to expire on $sp_lead_expire_date, plz login for extension/reassign");
				
				
				//Send Email
				$subject = "Remider of lead/enquiry id #$enquiry_id expiration";
				$content = "<p>Dear,</p><br/><p>Lead/Enquiry Id: #$enquiry_id is about to expire on $sp_lead_expire_date, Please do aprroval</p><br/><br/><br/><p>Thanks & Regards<p><p>Team crm support</p>";
				sendEmail(array('toEmail'=>$sp_email,'ccEmail'=>$asm_email), $subject, $content);

				$assignmentHistory = array(
		            'enquiry_id'  => $enquiry_id,
		            'details' => "Remider of lead/enquiry id: #$enquiry_id expiration email and sms has been sent.",
		            'type' => 'new',
		            'employee_id' => 0
		        );
		        
		        createLog($assignmentHistory);
		        
			}
		}elseif($days > 14){
			// remove lead from sp panel
			$SQ_SELECT = mysql_query("SELECT * FROM lead_extend_date WHERE enquiry_id='$enquiry_id' AND sp_id='$sp_id' AND status!='expired'");
			
			if(mysql_num_rows($SQ_SELECT) > 0){
			
				$updateId = mysql_query("UPDATE lead_extend_date SET status='expired' WHERE enquiry_id='$enquiry_id' AND sp_id='$sp_id' AND status!='expired'");	
				
				$insertedId = mysql_query("UPDATE lead SET lead_assigned_to_sp=NULL, lead_assigned_to_sp_on = NULL, lead_expire_date_of_sp = NULL WHERE enquiry_id = '$enquiry_id'");
				
				sendSMS(array($sp_mobile, $asm_mobile),"ENQ ID: #$enquiry_id has been expired.");	
				
				
				//Send Email
				$subject = "Lead/Enquiry Id: #$enquiry_id has been expired";
				$content = "<p>Dear,</p><br/><p>Lead/Enquiry Id: #$enquiry_id has been expired.</p><br/><br/><br/><p>Thanks & Regards<p><p>Team crm support</p>";
				sendEmail(array('toEmail'=>$sp_email,'ccEmail'=>$asm_email), $subject, $content);	
				
				$assignmentHistory = array(
		            'enquiry_id'  => $enquiry_id,
		            'details' => "Lead/Enquiry Id: #$enquiry_id has been expired",
		            'type' => 'new',
		            'employee_id' => $sp_id
		        );
		        
		       	createLog($assignmentHistory);
		        
							
			}else{
				$sql = "INSERT INTO lead_extend_date(enquiry_id,agent_id, asm_id, sp_id, sp_lead_assign_date, sp_lead_expire_date, no_of_reminder, status) VALUES ('$enquiry_id','$agent_id','$asm_id','$sp_id','$sp_lead_assign_date', '$sp_lead_expire_date',0,'expired')";
				$insertedId = mysql_query($sql);
				
				//Send SMS
				sendSMS(array($sp_mobile, $asm_mobile),"ENQ ID: #$enquiry_id has been expired.");

				//Send Email
				$subject = "Lead/Enquiry Id: #$enquiry_id has been expired";
				$content = "<p>Dear,</p><br/><p>Lead/Enquiry Id: #$enquiry_id has been expired.</p><br/><br/><br/><p>Thanks & Regards<p><p>Team crm support</p>";
				
				sendEmail(array('toEmail'=>$sp_email,'ccEmail'=>$asm_email), $subject, $content);	
				
				$assignmentHistory = array(
		            'enquiry_id'  => $enquiry_id,
		            'details' => "Lead/Enquiry Id: #$enquiry_id has been expired",
		            'type' => 'new',
		            'employee_id' => $sp_id
		        );
		        createLog($assignmentHistory);
		        
			}
		}
		
	}
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
        
        return $resp;
    	//} // end foreach
    } // end if condition 
}

function sendEmail($email, $subject, $content){
	if($email['toEmail']){
		$sp_email = $email['toEmail'];
	}
	
	if($email['ccEmail']){
		$asm_email = $email['ccEmail'];
	}
				
	$val = array('toEmail'=>$sp_email,'ccEmail'=>$asm_email,'subject'=>$subject,'content'=>$content);
	// Get cURL resource
	$url = BASE_URL."apis/sendEmail.php";
# Set url
	$ch = curl_init( $url );
	
	curl_setopt($ch, CURLOPT_POST, true);
	
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $val );
	
	# Return response instead of printing.
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	
	# Send request.
	$result = curl_exec($ch);
	
	curl_close($ch);
	
	return $result;
}