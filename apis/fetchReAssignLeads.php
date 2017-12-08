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

//print_r($data);
//die;

$leads = array();
$where = ' ';
if($data->user_id){
	
	$sqlEmp = mysql_query("SELECT designation_slug FROM employees AS Q JOIN designationmaster AS P ON P.id = Q.designation WHERE Q.id='".$data->user_id."' LIMIT 1");
	
	if(mysql_num_rows($sqlEmp) > 0){
		
		$resource = mysql_fetch_assoc($sqlEmp);
		
		if($resource['designation_slug']=='agent'){
			
			$where = " AND (a.lead_added_by_user = '".$data->user_id."' OR a.reassign_user_id = '".$data->user_id."')";
		
		}elseif($resource['designation_slug']=='area_sales_manager'){
			
			$where = " AND a.lead_assigned_to_asm = '".$data->user_id."'";
		
		}
	}else{
		
		$where = '';
	}
}

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
	
}elseif(isset($data->user_type) && $data->user_type == 2){
	
	$whereSubQuery .= " AND C.user_type='sales_person'";
	
}else{
	$whereSubQuery = '';
	
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
	$keyword1 = ucfirst($keyword);
	
	$SQL = MYSQL_QUERY("SELECT id FROM employees WHERE (firstname = '$keyword1' OR firstname = UPPER('$keyword') OR firstname = LOWER('$keyword')) ORDER BY id DESC LIMIT 1");
	
	$result_agent = mysql_fetch_assoc($SQL);
	
	if($result_agent['id']){
		
		$id_agent = $result_agent['id'];
		
		$where .= " AND (a.reassign_user_id = '$id_agent'  OR a.lead_added_by_user = '$id_agent')";
		
	}else{
		
		$where .= " AND (a.enquiry_id LIKE '%".$keyword."%' OR a.customerMobile LIKE '%".$keyword."%' OR a.customerName LIKE '%".$keyword."%')";
		
	}
	
	
	
}else{
	$keyword = '';
}

//Project Filter
if($data->project !='NA' && $data->project!=''){
	$PROJECT = $data->project;
	
	$join = "INNER JOIN lead_enquiry_projects as d ON (d.enquiry_id = a.enquiry_id AND d.project_name = '$PROJECT')";

}else{
	$PROJECT = '';
	$join = "";
}

//$data->status = 3;
if(isset($data->status) && $data->status==6){
	
	$leads_query = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate, a.leadUpdateDate ,a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.site_visit_id, lead_assigned_to_sp,reassign_user_id FROM lead as a LEFT JOIN site_visit as c ON c.site_visit_id = a.site_visit_id AND c.enquiry_id = a.enquiry_id '.$join.' WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND c.site_visit_status = 0'.$where.' AND a.lead_assigned_to_sp IS NULL ORDER BY c.site_visit_created_at DESC LIMIT '.$offset.', '.$limit;
	
	$leads_query_count = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate,a.leadUpdateDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.site_visit_id, lead_assigned_to_sp,reassign_user_id FROM lead as a LEFT JOIN site_visit as c ON c.site_visit_id = a.site_visit_id AND c.enquiry_id = a.enquiry_id '.$join.' WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND c.site_visit_status = 0'.$where.' AND a.lead_assigned_to_sp IS NULL ORDER BY c.site_visit_created_at DESC';
	
}elseif(isset($data->status) && $data->status==3){
	
	$leads_query = 'SELECT a.id,a.lead_id, a.enquiry_id, a.leadAddDate,a.leadUpdateDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, reassign_user_id FROM lead as a LEFT JOIN lead_meeting as b ON b.enquiry_id = a.enquiry_id AND b.meetingId=a.meeting_id '.$join.' WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND a.lead_assigned_to_sp IS NULL AND b.meeting_status = 0 '.$where.' ORDER BY b.meeting_created_at DESC LIMIT '.$offset.', '.$limit;
	
	$leads_query_count = 'SELECT a.enquiry_id FROM lead as a LEFT JOIN lead_meeting as b ON b.enquiry_id = a.enquiry_id AND b.meetingId=a.meeting_id '.$join.' WHERE a.disposition_status_id = '.$data->status.' AND a.lead_assigned_to_asm IS NOT NULL AND a.lead_assigned_to_sp IS NULL AND b.meeting_status = 0 '.$where.' ORDER BY b.meeting_created_at DESC';

}elseif(isset($data->status)){
	if($whereSubQuery){
		$leads_query = "SELECT C.enquiry_id, a.id, a.lead_id, a.enquiry_id, a.leadAddDate, a.leadUpdateDate ,a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, a.reassign_user_id FROM lead_status C INNER JOIN (SELECT MAX(id) as id FROM lead_status GROUP BY enquiry_id) b ON b.id = C.id INNER JOIN lead a ON a.enquiry_id = C.enquiry_id ".$join." WHERE C.disposition_status_id='".$data->status."' $whereSubQuery $where ORDER BY a.leadAddDate DESC LIMIT $offset, $limit";
	
		$leads_query_count = "SELECT C.enquiry_id FROM lead_status C INNER JOIN (SELECT MAX(id) as id FROM lead_status GROUP BY enquiry_id) b ON b.id = C.id INNER JOIN lead a ON a.enquiry_id = C.enquiry_id ".$join." WHERE C.disposition_status_id='".$data->status."' $whereSubQuery $where ORDER BY a.leadAddDate DESC";
	
	}else{
		
		$leads_query = "SELECT a.id, a.lead_id, a.enquiry_id, a.leadAddDate, a.leadUpdateDate, a.customerName, a.customerMobile, a.lead_added_by_user, a.lead_assigned_to_asm, reassign_user_id FROM lead as a ".$join." WHERE a.disposition_status_id = ".$data->status."  ".$where." ORDER BY a.leadAddDate DESC LIMIT $offset, $limit";
		
		$leads_query_count = "SELECT a.enquiry_id FROM lead as a ".$join." WHERE a.disposition_status_id = ".$data->status."  ".$where." ORDER BY a.leadAddDate DESC";
	}
	
}
// Logic to get leads with different criteria



$lead_resource = mysql_query($leads_query);
$lead_resource_count = mysql_num_rows(mysql_query($leads_query_count));


if ($lead_resource) {

	while ($row = mysql_fetch_assoc($lead_resource)) {
		
		$projects = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getEnquiryProjects&params=enquiry_id:' . $row['enquiry_id']), true);
			
		$row['enquiry_projects'] = $projects;
		
		
		//Get Last Status updated By agent
		
		$row['agent_name'] = getEmployeeName($row['lead_added_by_user']);
		
        $row['current_crm'] = currentAssignedCRM($row['enquiry_id']);
        
		$row['asm_name'] = getEmployeeName($row['lead_assigned_to_asm']);

		
		
        
        // CRM and Sakes Disposition
        $crm_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id FROM `lead_status` WHERE enquiry_id = '.$row['enquiry_id'].' AND user_type = "agent" ORDER BY date DESC LIMIT 1');
                                            
		if($crm_last_disposition && mysql_num_rows($crm_last_disposition) > 0){
                                
			$crm_disposition_data = mysql_fetch_assoc($crm_last_disposition);
                                
			$row['crm_disposition_status_id']      = $crm_disposition_data['disposition_status_id'];
            $row['last_crm_activity'] = getStatusLabel($crm_disposition_data['disposition_status_id']);
			$row['crm_sub_disposition_status_id']  = $crm_disposition_data['disposition_sub_status_id']; 
            $row['last_crm_sub_activity'] = getStatusLabel($crm_disposition_data['disposition_sub_status_id'],'child');
		}
                                            
                                            
		// Get Sales Team Last Disposition on enquiry  
		$sales_last_disposition = mysql_query('SELECT disposition_status_id, disposition_sub_status_id FROM lead_status WHERE enquiry_id = '.$row['enquiry_id'].' AND user_type != "agent" ORDER BY date DESC LIMIT 1');
        

		if($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0){
			$sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);
            $row['sales_disposition_status_id']     = $sales_disposition_data['disposition_status_id'];
            $row['last_sales_activity'] = getStatusLabel($sales_disposition_data['disposition_status_id']);
			$row['sales_sub_disposition_status_id'] = $sales_disposition_data['disposition_sub_status_id'];
            $row['last_sales_sub_activity'] = getStatusLabel($sales_disposition_data['disposition_sub_status_id']);
		}
        
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