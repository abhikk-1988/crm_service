<?php

session_start();

require 'function.php';

// Select Request Criteria 
// is_otp_verified = true
// is_user_verified = true
// password_reset_date = NULL

$requests = array();

$get_requests = 'SELECT * '
		. ' FROM reset_password '
		. ' WHERE is_otp_verified = 1 AND is_user_verified = 1 AND password_reset_date IS NULL GROUP BY request_userid ORDER BY request_datetime DESC';

$result = mysql_query($get_requests);


if($result && mysql_num_rows($result) > 0){
	
	while($row = mysql_fetch_assoc($result)){
		
		$row['employee_name'] = getEmployeeName($row['request_userid']);
		
		$row['employee_email'] = getEmployeeEmailAddress($row['request_userid']);
		
		array_push($requests, $row);
		
	}
}

echo json_encode($requests, true);
