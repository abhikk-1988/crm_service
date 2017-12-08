<?php
session_start();
require 'function.php';
require_once 'user_authentication.php';
    
if(!$is_authenticate){
	echo unauthorizedResponse(); exit; 
}

function getEnquiryProjects($projects= '', $type=''){
	
	$enquiry_projects = array();
	
	if($projects && mysql_num_rows($projects)>0){

		if($type == 'S' || $type == 'M'){
			
			$p_data = mysql_fetch_object($projects);	
			
			if($p_data -> project != ''){

				// convert json string to array
				$project_json_to_array = json_decode($p_data -> project,true);

				if(is_array($project_json_to_array) && !empty($project_json_to_array)){

					foreach($project_json_to_array as $val){
						array_push($enquiry_projects, $val['project_name']);
					}
				}
			}else{
				array_push($enquiry_projects, 'NA');
			}
		}else{
			while($project = mysql_fetch_assoc($projects)){
				array_push($enquiry_projects, $project['project_name']);
			}
		}
	}else{
		array_push($enquiry_projects, 'NA');
	}
	
	return $enquiry_projects; 
}



$leads = array();

$data = filter_input_array(INPUT_POST);

$enquiry_filter = '';

$enquiry_filter_condition = '';

if( isset($data['enquiry_filter']) && $data['enquiry_filter']){

	$enquiry_filter = $data['enquiry_filter'];

	$enquiry_filter_condition = ' AND (lead.disposition_status_id = '. $enquiry_filter .' OR lead.disposition_sub_status_id = '.$enquiry_filter.')';

}

$date_range_condition = '';
$from   = '';
$to     = '';

// Date Range Filter
if(isset($data['date_range_filter']) && $data['date_range_filter'] != ''){
        
	// explode string and extract from and to date 
        
	$range = explode(' / ', $data['date_range_filter']);

	$from   = $range[0] .' 00:00:00';

	$to     = $range[1]. ' 23:59:59';
        
	$date_range_condition = ' AND lead.leadAddDate BETWEEN "'. $from .'" AND "'. $to.'" ';
}

// Date filter on lead update date 
	$update_date_filter = '';

	if(isset($data['lead_update_date_filter']) && $data['lead_update_date_filter'] != ''){
        
		// explode string and extract from and to date 
        
		$update_filter_date = explode(' / ', $data['lead_update_date_filter']);

		$from1   = $update_filter_date[0] .' 00:00:00';

		$to1     = $update_filter_date[1]. ' 23:59:59';
        
		if($date_range_condition == ''){

			$update_date_filter = ' AND lead.leadUpdateDate BETWEEN "'. $from1 .'" AND "'. $to1.'" ';
		
		}else{
		
			$update_date_filter = ' OR lead.leadUpdateDate BETWEEN "'. $from1 .'" AND "'. $to1.'" ';
		}
	}

if(isset($data['user_id'])){
	
	$order_by = ' ORDER BY leadAddDate DESC';

	$sql = "SELECT lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate, lead.is_cold_call,lead.lead_added_by_user, lead.customerName, lead.customerEmail, lead.customerMobile, lead.customerLandline, CONCAT(emp.firstname,' ', emp.lastname) as lead_added_by_employee,lead.reassign_user_id , lead.lead_assigned_to_asm, lead.lead_assigned_to_sp, lead.lead_assigned_to_sp_on, lead.is_lead_accepted, lead.is_lead_rejected, lead.meeting_id, lead.site_visit_id FROM lead as lead LEFT JOIN employees as emp ON (lead.lead_added_by_user = emp.id) WHERE lead.lead_assigned_to_asm = ".$data['user_id']."  AND lead_closure_date IS NULL " .$enquiry_filter_condition. "". $date_range_condition ." ". $update_date_filter;
	
	/**
	* there will no re-assignment between/among ASM because of tyagi made many changes like CRM Team will be mapped directly to ASM, 
	* CRM Leads of status "Meeting and Site Visit" will automatically assign to Thier respective ASM. BY Umesh  
	*/
	if(!$enquiry_filter_condition){
		
		$asm_reassign_enquiries = mysql_query('SELECT distinct enquiry_id FROM lead_re_assign WHERE to_user_id = '.$data['user_id'].' AND user_type = "area_sales_manager" AND change_status="processed" ORDER BY id DESC');
	
	}

	$re_assigned_lead_query = '';
	
	$reAssignIds = array();
	
	if($asm_reassign_enquiries && mysql_num_rows($asm_reassign_enquiries) > 0){

		$reasign_enquiry_ids = array();
	
		while($row = mysql_fetch_assoc($asm_reassign_enquiries)){
	
			$reAssignIds[] = $row['enquiry_id'];
	
			array_push($reasign_enquiry_ids, $row['enquiry_id']);
		}
		
		$re_assigned_lead_query = ' UNION ALL ';
	
		$re_assigned_lead_query .= ' SELECT lead.lead_id, lead.enquiry_id, lead.disposition_status_id, lead.disposition_sub_status_id, lead.leadAddDate,lead.leadUpdateDate, lead.is_cold_call,lead.lead_added_by_user, lead.customerName, lead.customerEmail, lead.customerMobile, lead.customerLandline, CONCAT(emp.firstname," ", emp.lastname) as lead_added_by_employee, lead.reassign_user_id, lead.lead_assigned_to_asm, lead.lead_assigned_to_sp, lead.lead_assigned_to_sp_on, lead.is_lead_accepted, lead.is_lead_rejected, lead.meeting_id, lead.site_visit_id FROM lead as lead LEFT JOIN employees as emp ON (lead.lead_added_by_user = emp.id) WHERE enquiry_id IN ('.implode(',', $reasign_enquiry_ids).') '. $date_range_condition. ' '. $update_date_filter;
		
	}			
	
	if(!empty($reAssignIds)){
	
		 $sql = $sql . ' AND enquiry_id NOT IN ('.implode(',', $reAssignIds).')';
	}
		

	
	$sql = $sql . ' '. $re_assigned_lead_query;

	$sql .= $order_by;
	
	$result = mysql_query($sql);

	if($result && mysql_num_rows($result) > 0){

		while($row = mysql_fetch_assoc($result)){

			//	$row['primary_status_title']	= getstatuslabel($row['disposition_status_id'],'parent');
			//	$row['secondary_status_title']	= getstatuslabel($row['disposition_sub_status_id'],'child');
			$row['sp_name']	= getemployeename($row['lead_assigned_to_sp']);
			
			$SQL_SALES = "SELECT enquiry_id FROM lead_assignment_sales WHERE asm_id = '".$data['user_id']."' AND enquiry_id = '".$row['enquiry_id']."' ORDER BY id DESC LIMIT 1";
				
			$SQL_SALES_SP = MYSQL_QUERY($SQL_SALES);
			
			
			
			if($data['user_id']==$row['lead_assigned_to_asm']){	
				
				$row['remove_lead'] = FALSE;
			
			}elseif(mysql_num_rows($SQL_SALES_SP) > 0){
				
				$row['remove_lead'] = TRUE;
				
			}else{
				
				$row['remove_lead'] = FALSE;
			}
	
			$row['projects'] = array();
			
			$status_type = '';
			
			if($row['disposition_status_id'] == 3){
				
				$status_type = 'M';
				
				$projects = mysql_query('SELECT project FROM lead_meeting WHERE enquiry_id = '.$row['enquiry_id'].' AND meetingId = "'.$row['meeting_id'].'" LIMIT 1');	
				
			}else if($row['disposition_status_id'] == 6){
			
				$status_type = 'S';
			
				$projects = mysql_query('SELECT project FROM site_visit WHERE enquiry_id = '.$row['enquiry_id'].' AND site_visit_id = "'.$row['site_visit_id'].'" LIMIT 1');
			
			}else{
			
				$status_type = 'O';
			
				$projects = mysql_query('SELECT project_name FROM lead_enquiry_projects WHERE enquiry_id = '.$row['enquiry_id'].'');
			}
				
			$enquiry_projects = getEnquiryProjects($projects,$status_type);
					
			$row['projects'] = $enquiry_projects; 
			
			if($row['reassign_user_id'] != '' && $row['reassign_user_id'] != NULL){
				
				unset($row['lead_added_by_employee']);
			
				$row['lead_added_by_employee'] = getEmployeeName($row['reassign_user_id']);
			}
			
			
			// Get latest status of Sales Person
			$where_asm = " ";
			
			if($row['lead_assigned_to_asm'] == $data['user_id']){ // current ASM
            	
				$query = "SELECT disposition_status_id, disposition_sub_status_id FROM lead_status WHERE enquiry_id = '".$row['enquiry_id']."' AND user_type ='sales_person' AND user_id ='".$row['lead_assigned_to_sp']."' ORDER BY id DESC LIMIT 1";
				
			
			}else{		// Previous AMS  
				$ENQ_ID = $row['enquiry_id'];   
				$sql = "SELECT to_user_id FROM lead_re_assign WHERE id > (SELECT id FROM lead_re_assign WHERE to_user_id = '".$data['user_id']."' AND user_type = 'area_sales_manager' AND enquiry_id = $ENQ_ID ORDER BY id desc LIMIT 1) AND enquiry_id = '$ENQ_ID' AND user_type = 'sales_person' LIMIT 1";
				
				$getSP = MYSQL_QUERY($sql);
				
				if(mysql_num_rows($getSP) > 0){
					
					$resp = mysql_fetch_assoc($getSP);
					
					$row['sp_name'] = getEmployeeName($resp['to_user_id']);
					
					$query = "SELECT disposition_status_id, disposition_sub_status_id FROM lead_status WHERE enquiry_id = '".$row['enquiry_id']."' AND user_type ='sales_person' AND user_id ='".$resp['to_user_id']."' ORDER BY id DESC LIMIT 1";
				}
			}
			
			
			$sales_last_disposition = mysql_query($query);
			if($sales_last_disposition && mysql_num_rows($sales_last_disposition) > 0){
	                                                
				$sales_disposition_data = mysql_fetch_assoc($sales_last_disposition);
                
				$row['sales_disposition']     = getstatuslabel($sales_disposition_data['disposition_status_id'], 'parent');
                
				if($sales_disposition_data['disposition_sub_status_id'] != ''){
					$row['sales_disposition'] .= ' ' . getstatuslabel($sales_disposition_data['disposition_sub_status_id'], 'child');
				}
                
			}else{
			
				$row['sales_disposition'] = 'N/A';
			
			}
			
			if($row['sp_name']==''){
				$row['sp_name'] = "N/A";
			}
			
            
            // Current CRM 
            $row['current_crm'] = currentAssignedCRM($row['enquiry_id']);
            
           
			array_push($leads, $row);
		}
	}

	// response in JSON format
	$response = array_utf8_encode(array(
		'success' => 1,
		'http_status_code' => 200,
		'data' => $leads,
	));
	echo json_encode($response,true); exit;	
}else{
	$error_response = array(
		'success' => 0,
		'http_status_code' => 401,
		'message' => 'Unauthorized access'
	);
	
	echo json_encode($error_response,true); exit; 
	
}