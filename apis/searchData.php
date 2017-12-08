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
$where = ' ';

if(isset($data->source_status) && $data->source_status != 'NA'){
	$where .= " AND a.leadSecondarySource = '".$data->source_status."'";
	
}

if($data->lead_updated_date && strtolower($data->lead_updated_date)!=null){
	
	$newDate = date("Y-m-d", strtotime($data->lead_updated_date));
	
	$where .= " AND a.leadUpdateDate LIKE '%".$newDate."%'";
}
$whereSubQuery = '';
if(isset($data->user_type) && $data->user_type == 1){
	
	$whereSubQuery .= " AND C.user_type='agent'";
	
}

if(isset($data->user_type) && $data->user_type == 2){
	
	$whereSubQuery .= " AND C.user_type='sales_person'";
	
}
//die($whereSubQuery."Hello ");


// Paging
if(isset($data->start) && isset($data->limit)){
	$start = $data->start;
	$limit = $data->limit;
	
	$offset = ($start - 1) * $limit;
}else{
	$limit = 10;
	$offset = 0;
}

// Search
if(isset($data->keyword)){
	$keyword = $data->keyword;
	
	$where .= " AND (a.enquiry_id LIKE '%".$keyword."%' OR a.customerMobile LIKE '%".$keyword."%' OR a.customerName LIKE '%".$keyword."%')";

}else{
	$keyword = '';
}



//$data->status = 3;
if(isset($data->status) && $data->status==6){
	
	$leads_query = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.site_visit_id, lead_assigned_to_sp,reassign_user_id FROM lead as a LEFT JOIN site_visit as c ON c.site_visit_id = a.site_visit_id AND c.enquiry_id = a.enquiry_id WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND c.site_visit_status = 0'.$where.' AND a.lead_assigned_to_sp IS NULL ORDER BY c.site_visit_created_at DESC '.$offset.', '.$limit;
	
	$leads_query_count = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.site_visit_id, lead_assigned_to_sp,reassign_user_id FROM lead as a LEFT JOIN site_visit as c ON c.site_visit_id = a.site_visit_id AND c.enquiry_id = a.enquiry_id WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND c.site_visit_status = 0'.$where.' AND a.lead_assigned_to_sp IS NULL ORDER BY c.site_visit_created_at DESC ';
	
}elseif(isset($data->status) && ($data->status == 1 || $data->status == 4 || $data->status == 34 || $data->status == 38 )){
	
//	$leads_query = "SELECT a.id, a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, reassign_user_id FROM lead as a WHERE a.disposition_status_id = ".$data->status."  ".$where." ORDER BY a.leadAddDate DESC";

	$leads_query = "SELECT C.enquiry_id, a.id, a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.reassign_user_id FROM lead_status C INNER JOIN (SELECT MAX(id) as id FROM lead_status GROUP BY enquiry_id) b ON b.id = C.id INNER JOIN lead a ON a.enquiry_id = C.enquiry_id WHERE C.disposition_status_id='".$data->status."' $whereSubQuery $where ORDER BY a.leadAddDate DESC LIMIT $offset, $limit";
	
	$leads_query_count = "SELECT C.enquiry_id, a.id, a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.reassign_user_id FROM lead_status C INNER JOIN (SELECT MAX(id) as id FROM lead_status GROUP BY enquiry_id) b ON b.id = C.id INNER JOIN lead a ON a.enquiry_id = C.enquiry_id WHERE C.disposition_status_id='".$data->status."' $whereSubQuery $where ORDER BY a.leadAddDate DESC";
	
	
//	$leads_query = "SELECT a.id, a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, reassign_user_id FROM lead as a	WHERE a.disposition_status_id = ".$data->status." ".$where." AND a.enquiry_id = (SELECT enquiry_id FROM lead_status WHERE $whereSubQuery AND disposition_status_id=a.disposition_status_id AND disposition_sub_status_id = a.disposition_sub_status_id AND enquiry_id = a.enquiry_id ORDER BY id DESC LIMIT 1) ORDER BY a.leadAddDate DESC";

	
//	die($leads_query);
}elseif(isset($data->status) && $data->status==3){
	
	$leads_query = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, reassign_user_id FROM lead as a LEFT JOIN lead_meeting as b ON b.enquiry_id = a.enquiry_id AND b.meetingId=a.meeting_id WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND a.lead_assigned_to_sp IS NULL AND b.meeting_status = 0 '.$where.' ORDER BY b.meeting_created_at DESC'.$offset.', '.$limit;
	
	$leads_query_count = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, reassign_user_id FROM lead as a LEFT JOIN lead_meeting as b ON b.enquiry_id = a.enquiry_id AND b.meetingId=a.meeting_id WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND a.lead_assigned_to_sp IS NULL AND b.meeting_status = 0 '.$where.' ORDER BY b.meeting_created_at DESC';

}
// Logic to get leads with different criteria


die($leads_query);
$lead_resource = mysql_query($leads_query);
$lead_resource_count = mysql_num_rows(mysql_query($leads_query_count));


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
//		$row['agent_status'] = "";
		
		$row['agent_name'] = getEmployeeName($row['lead_added_by_user']);
		if($row['reassign_user_id']){
			
			$row['current_crm'] = getEmployeeName($row['reassign_user_id']);
		}else{
			
			$row['current_crm'] = "N/A";
		}
		
		$row['asm_name'] = getEmployeeName($row['lead_assigned_to_asm']);

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
$result = array('success' => 1,'http_status_code' => 200,'data' => $leads,"total_row"=>$lead_resource_count,'Umesh'=>$leads_query);
$utf_result  = array_utf8_encode($result);
$json_result = json_encode($utf_result,true);
echo $json_result; exit;