<?php
session_start();
require_once 'function.php';
require_once 'user_authentication.php';

if (!$is_authenticate) {
	echo unauthorizedResponse();
	exit;
}
$data		= file_get_contents("php://input");
$data 		= json_decode($data);

$user_id			= $_SESSION['currentUser']['id']; 

$current_date		= date('Y-m-d H:i:s'); 
// Calculate expire date
$expire_date = date('Y-m-d', strtotime("+14 days"));

if($data->enquiry_id && $data->sp_id){
	
	$leads_query 	= 'SELECT * FROM lead_extend_date WHERE enquiry_id ="'.$data->enquiry_id.'" AND sp_id="'.$data->sp_id.'" AND status!="expired" LIMIT 1';
	
	$result = mysql_query($leads_query);

	if(mysql_num_rows($result) > 0){

		$row = mysql_fetch_assoc($result);

		$no_of_extension = $row['no_of_extension']+1;
		
		$sqlUpdate = "UPDATE lead_extend_date SET extend_by_user_id='$user_id', no_of_extension= '$no_of_extension', status= 'extended', extension_date = NOW() WHERE enquiry_id ='".$data->enquiry_id."' AND sp_id='".$data->sp_id."' AND status!='expired' ";		
//		die($sqlUpdate);
		$updated = mysql_query($sqlUpdate);
		
		
		$sqlUpdateLead = "UPDATE lead SET lead_expire_date_of_sp='$expire_date' WHERE enquiry_id ='".$data->enquiry_id."' AND lead_assigned_to_sp='".$data->sp_id."'";		
		
		$updatedLead = mysql_query($sqlUpdateLead);
		
		$sp_name = getEmployeeName($data->sp_id);
		
		$update_by_name = getEmployeeName($user_id);
		
		$meta_data = array('sales_person_id'=>$data->sp_id);
		
		$assignmentHistory = array(
            'enquiry_id'  => $data->enquiry_id,
            'details' => "Lead with enquiry id: ".$data->enquiry_id." has been extended by $update_by_name on dated $current_date for $sp_name",
            'type' => 'new',
            'meta_data' =>mysql_real_escape_string(json_encode($meta_data)),
            'employee_id' => $user_id
        );

		if($updated){
			
			createLog($assignmentHistory);
			
			sendSMS(array($sp_mobile, $asm_mobile),"ENQ ID: #$enquiry_id has been extended successfully.");

			//Send Email
			$subject = "Lead/Enquiry Id: #$enquiry_id has been expired";
			$content = "<p>Hi,</p><br/><p>Lead/Enquiry Id: #$enquiry_id has been expired.</p><br/><br/><br/><p>Thanks & Regards<p><p>Team crm support</p>";
			sendEmail(array('toEmail'=>$sp_email,'ccEmail'=>$asm_email), $subject, $content);	
			
			$success = 1;
			
			$http_status_code = 401;

		}else{

			$success = 0;
			
			$http_status_code = 401;
		}
		
	}else{

		$success = 0;
			
		$http_status_code = 401;
	}
}else{

	$success = 0;
			
	$http_status_code = 401;
}

$resultArr = array('success' => $success,'http_status_code' =>$http_status_code);

$utf_result  	= array_utf8_encode($resultArr);

$json_result 	= json_encode($utf_result,true);

echo $json_result; 





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

exit;