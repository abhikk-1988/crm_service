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

$leads = array();

$user_id			= $_SESSION['currentUser']['id']; 


//$data->status = 3;
if($user_id==1 && $data->status){
	
	$leads_query = 'SELECT a.*, c.from_user_id, c.to_user_id FROM lead as a LEFT JOIN lead_re_assign as c ON c.enquiry_id = a.enquiry_id WHERE a.disposition_status_id = '.$data->status.' AND a.is_reassign = 1 AND c.change_status = "pending" ORDER BY c.date DESC';
	
}elseif($user_id && $data->status){
	
	$leads_query = 'SELECT a.*, c.from_user_id, c.to_user_id  FROM lead as a LEFT JOIN lead_re_assign as c ON c.enquiry_id = a.enquiry_id WHERE a.disposition_status_id = '.$data->status.' AND a.is_reassign = 1 AND c.change_status = "pending" AND c.to_user_id = '.$user_id.' ORDER BY c.date DESC';

	
}
// Logic to get leads with different criteria
//echo $leads_query; exit;
$lead_resource = mysql_query($leads_query);

if ($lead_resource) {

	while ($row = mysql_fetch_assoc($lead_resource)) {
		$projects = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getEnquiryProjects&params=enquiry_id:' . $row['enquiry_id'] . '/lead_id:' . $row['lead_id']), true);
		
		$row['enquiry_projects'] = $projects;
		
		array_push($leads, $row);
	}
}

$result = array('success' => 1,'http_status_code' => 200,'data' => $leads);
$utf_result  = array_utf8_encode($result);
$json_result = json_encode($utf_result,true);
echo $json_result; exit;