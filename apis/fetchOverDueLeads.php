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

if($data->status){
	$status = $data->status;
}else{
	$status = 3;
}

$asm_user_id			= $_SESSION['currentUser']['id']; 
$designation			= $_SESSION['currentUser']['designation']; 

$designationName = getEmployeeByDesignationSlug($designation);

if($designation == 28){
	
	$where = ' AND a.lead_assigned_to_asm ='.$asm_user_id;
	
}elseif($designation == 27 || $designation == 2){
	
	$where = '';

}else{
	
	exit;
}


$leads = array();
	
$leads_query = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.site_visit_id, lead_assigned_to_sp, lead_assigned_to_sp_on, DATE(lead_expire_date_of_sp) AS expire_date, reassign_user_id,b.status FROM lead_extend_date as b JOIN lead as a ON a.enquiry_id = b.enquiry_id AND b.sp_id = a.lead_assigned_to_sp AND b.asm_id = a.lead_assigned_to_asm WHERE a.disposition_status_id='.$status.''.$where.' ORDER BY a.id DESC';
	
// LOGIC TO GET LEADS WITH DIFFERENT CRITERIA

//die($leads_query);

$lead_resource = mysql_query($leads_query);

if ($lead_resource) {

	while ($row = mysql_fetch_assoc($lead_resource)) {
		$projects = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getEnquiryProjects&params=enquiry_id:' . $row['enquiry_id'] . '/lead_id:' . $row['lead_id']), true);
		
		//GET LAST STATUS UPDATED BY AGENT
		
		$row['agent_name'] = getEmployeeName($row['lead_added_by_user']);
		if($row['reassign_user_id']){
			
			$row['current_crm'] = getEmployeeName($row['reassign_user_id']);
		}else{
			
			$row['current_crm'] = "N/A";
		}
		
		$row['sp_name'] = getEmployeeName($row['lead_assigned_to_sp']);
		
		if($designation != '28'){
		
			$row['asm_name'] = getEmployeeName($row['lead_assigned_to_asm']);
		}else{
			$row['asm_name'] = '';
		}

		$row['enquiry_projects'] = $projects;
		
		array_push($leads, $row);
	}
}
 
$result = array('success' => 1,'http_status_code' => 200,'data' => $leads, "umesh"=>$leads_query);
$utf_result  = array_utf8_encode($result);
$json_result = json_encode($utf_result,true);
echo $json_result; exit;