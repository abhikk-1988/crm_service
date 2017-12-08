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
$data->status = 1;

//$data->status = 3;
if(isset($data->status) && $data->status==6){
	
	$leads_query = 'SELECT a.* FROM lead as a LEFT JOIN site_visit as c ON c.site_visit_id = a.site_visit_id AND c.enquiry_id = a.enquiry_id WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL c.site_visit_status = 0 AND a.lead_assigned_to_sp IS NULL ORDER BY c.site_visit_created_at DESC';
	
}elseif(isset($data->status) && ($data->status == 1 || $data->status == 4 || $data->status == 34 || $data->status == 38 ) ){
	
//	$leads_query = "SELECT a.* FROM lead as a LEFT JOIN lead_history as b ON b.enquiry_id = a.enquiry_id WHERE a.disposition_status_id = ".$data->status." AND a.lead_assigned_to_asm IS NULL AND b.details NOT LIKE '%re-assigned%' GROUP BY a.id ORDER BY a.leadAddDate DESC";

 	$leads_query = "SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm FROM lead as a WHERE a.disposition_status_id = 1 AND a.lead_assigned_to_asm IS NULL ORDER BY a.leadAddDate DESC";
	
}elseif(isset($data->status) && $data->status==3){
	
	$leads_query = 'SELECT a.* FROM lead as a LEFT JOIN lead_meeting as b ON b.enquiry_id = a.enquiry_id AND b.meetingId=a.meeting_id WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND a.lead_assigned_to_sp IS NULL AND b.meeting_status = 0 ORDER BY b.meeting_created_at DESC';


}
// Logic to get leads with different criteria

$lead_resource = mysql_query($leads_query);

if ($lead_resource) {

	while ($row = mysql_fetch_assoc($lead_resource)) {
		$projects = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getEnquiryProjects&params=enquiry_id:' . $row['enquiry_id'] . '/lead_id:' . $row['lead_id']), true);
		
		//Get Last Status updated By agent
		
//		$history_query = 'SELECT a.* FROM lead_history as a WHERE a.enquiry_id='.$row['enquiry_id'].' AND a.employee_id='.$row['lead_added_by_user'].' ORDER BY a.id DESC LIMIT 1';
//		
//		$history_resource = mysql_query($history_query);
//		
//		while ($row_history = mysql_fetch_assoc($history_resource)) {
//			
//			$row['agent_status'] = $row_history['details'];
//			
//		}
		// End
		
		$row['enquiry_projects'] = $projects;
		
		array_push($leads, $row);
	}
}

function getInnerSubstring($string,$delim){
    // "foo a foo" becomes: array(""," a ","")
    $string = explode($delim, $string, 3); // also, we only need 2 items at most
    // we check whether the 2nd is set and return it, otherwise we return an empty string
    return isset($string[1]) ? $string[1] : '';
}

function getInnerSubstringLast($string,$delim){
    // "foo a foo" becomes: array(""," a ","")
    $string = explode($delim, $string, 3); // also, we only need 2 items at most
    // we check whether the 2nd is set and return it, otherwise we return an empty string
    return isset($string[0]) ? $string[0] : '';
}
$result = array('success' => 1,'http_status_code' => 200,'data' => $leads);
$utf_result  = array_utf8_encode($result);
$json_result = json_encode($utf_result,true);
echo $json_result; exit;