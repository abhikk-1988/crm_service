<?php
session_start();
require_once 'function.php';
require_once 'user_authentication.php';

if (!$is_authenticate) {
	echo unauthorizedResponse();
	exit;
}
$data		= 	file_get_contents("php://input");
$data 		= 	json_decode($data);

$leads = array();
$where = ' ';

$status = 'success';

if($status=='success'){
	$projuect_query  = "SELECT project_name, project_id FROM `lead_enquiry_projects` GROUP BY project_name ORDER BY project_name ASC";
	
	$lead_resource = mysql_query($projuect_query);
	
	if (mysql_num_rows($lead_resource) > 0) {

		while ($row = mysql_fetch_assoc($lead_resource)) {
			$leads[$row['project_id']] = $row['project_name'];
		}
	}
}else{
	
	$result = array('success' => 0,'http_status_code' => 201,'data' => $leads, 'Umesh'=>$projuect_query);
}
if(!empty($leads)){
	$result = array('success' => 1,'http_status_code' => 200,'data' => $leads, 'Umesh'=>$projuect_query);
	
}else{
	
	$result = array('success' => 0,'http_status_code' => 201,'data' => $leads, 'Umesh'=>$projuect_query);
}


$utf_result  = array_utf8_encode($result);
$json_result = json_encode($utf_result,true);
echo $json_result; exit;