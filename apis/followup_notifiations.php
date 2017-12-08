<?php

    session_start();

    require 'function.php';

    $_post_data = filter_input_array(INPUT_POST);

    $enquiry_id = '';
    
    $current_date = date('Y-m-d');
    
    $current_time = date('H:i A');
    
    $current_date1 = date('Y-m-d');
    
    $current_time1 = date('H:i A', strtotime('60 minute')); 
    
	$leadSQL = "SELECT enquiry_id, lead_added_by_user, reassign_user_id, future_followup_date, future_followup_time FROM lead WHERE disposition_status_id = '4' AND (disposition_sub_status_id = '10' OR disposition_sub_status_id=37) AND future_followup_date = '$current_date' AND SUBSTRING(future_followup_time, 1, CHAR_LENGTH(future_followup_time) - 3) >= '$current_time' AND SUBSTRING(future_followup_time, 1, CHAR_LENGTH(future_followup_time) - 3) < '$current_time1'";

	
	$resultSQL = mysql_query($leadSQL);
	
	$data = array();
	
	if(mysql_num_rows($resultSQL) > 0){
		
		while($row = mysql_fetch_assoc($resultSQL)){
			
			if($row['reassign_user_id']){
				
				$user_id = $row['reassign_user_id'];
			}else{
				
				$user_id = $row['lead_added_by_user'];
			}
			$user_id = 1;

			$title = 'Future reference follow up';
			
			$enquiry_id = $row['enquiry_id'];
			
			$future_followup_date = $row['future_followup_date'];
			
			$future_followup_time = $row['future_followup_time'];
			
			$message = "Enquiry Id: #$enquiry_id is scheduled for follow up, at  $future_followup_date  $future_followup_time";
			$row['user_id'] = $user_id;
			
			$row['title'] = $title;
			
			$row['message'] = $message;
			
			unset($row['lead_added_by_user']);
			
			unset($row['reassign_user_id']);
			
			unset($row['future_followup_date']);
			
			unset($row['future_followup_time']);
			
			$row['event'] = "my-event";
			
			$row['notification_type'] = "info";
			
			array_push($data, $row);
		}
	}

	if(count($data) > 0){
		foreach($data as $val){
			# Set url
			$curl_url	= BASE_URL . 'apis/Pusher/server.php';
			
			$ch = curl_init( $curl_url );
			
			curl_setopt($ch, CURLOPT_POST, true);
			
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $val );
			
	//		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			
			# Return response instead of printing.
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			
			# Send request.
			$result = curl_exec($ch);
			
			curl_close($ch);
	    	
	    	echo $result;
		}
	}else{
		
		echo "Failed";
	}
