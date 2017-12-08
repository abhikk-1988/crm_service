<?php

session_start();
require_once 'function.php';


exit;

$names = getMultipleEmployeeName(array(23,'',76));

echo '<pre>';
print_r($names);
exit;


require_once 'user_authentication.php';

if(!$is_authenticate){
	echo unauthorizedResponse();
	exit;
}

$leads = array();

//$data->status = 3;
	
$sqlQuery = "SELECT b.enquiry_id, a.leadAddDate, a.customerName, a.customerMobile, a.customer_alternate_mobile, a.customerProfession, a.customerCity, a.customerAddress, a.customerEmail, a.lead_added_by_user, a.reassign_user_id, a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, b.disposition_status_id, b.disposition_sub_status_id, b.remark as agent_remark, a.meeting_id, a.site_visit_id FROM lead_status AS b JOIN lead AS a ON b.enquiry_id = a.enquiry_id WHERE b.user_type='agent' AND (b.disposition_status_id=3 OR b.disposition_status_id=6) AND a.leadAddDate between '$startDate' AND '$endDate'";
	
$lead_resource = mysql_query($sqlQuery);	
// Logic to get leads with different criteria

if($lead_resource){

	while($row = mysql_fetch_assoc($lead_resource)){
		
		if($row['reassign_user_id']){
			$row['crm'] = getEmployeeName($row['reassign_user_id']);
			
			unset($row['reassign_user_id']);
			
			unset($row['lead_added_by_user']);
			
		}elseif($row['lead_added_by_user']){
			
			$row['crm'] = getEmployeeName($row['lead_added_by_user']);
			
			unset($row['lead_added_by_user']);
			
			unset($row['reassign_user_id']);
		
		}
		
		if($row['lead_assigned_to_asm']){
			$row['tm_name'] = getEmployeeName($row['lead_assigned_to_asm']);
		}else{
			$row['tm_name'] = "";
		}
		
		if($row['lead_assigned_to_asm_on']){
			$row['tm_name_assign_date'] = date("d/m/Y H:i:s", strtotime($row['lead_assigned_to_asm_on']));
			unset($row['lead_assigned_to_asm_on']);
		}else{
			$row['tm_name_assign_date'] = "";
			unset($row['lead_assigned_to_asm_on']);
		}
		
		if($row['lead_assigned_to_sp']){
			$row['sales_manager'] = getEmployeeName($row['lead_assigned_to_sp']);
			unset($row['lead_assigned_to_sp']);
		}else{
			$row['sales_manager'] = "";
			unset($row['lead_assigned_to_sp']);
		}
		
		if($row['lead_assigned_to_sp_on']){
			$row['sales_manager_assign_date'] = date("d/m/Y H:i:s", strtotime($row['lead_assigned_to_sp_on']));
			unset($row['lead_assigned_to_sp_on']);
		}else{
			$row['sales_manager_assign_date'] = "";
			unset($row['lead_assigned_to_sp_on']);
		}
		
		if($row['leadAddDate']){
			$splitDateTime = explode(' ',$row['leadAddDate']);
			$row['added_date'] = $splitDateTime[0];
			$row['added_time'] = $splitDateTime[1];
			unset($row['leadAddDate']);
		
		}
		
		if($row['disposition_status_id']){
			$row['agent_status'] = getStatusLabel($row['disposition_status_id']);
			unset($row['disposition_status_id']);
		
		}
		
		if($row['disposition_sub_status_id']){
			$row['agent_sub_status'] = getStatusLabel($row['disposition_sub_status_id'], $row['disposition_status_id']);
			unset($row['disposition_sub_status_id']);
		}
			
		
		// Get Project List
		$project_query = 'SELECT a.project_name FROM lead_enquiry_projects as a WHERE a.enquiry_id='.$row['enquiry_id'].'';
			
		$project_resource = mysql_query($project_query);
		
		$projectName = '';
		
		while($row_project = mysql_fetch_assoc($project_resource)){
				
			$projectName .= $row_project['project_name'];
				
		}

		$row['enquiry_projects'] = $projectName;
		// End Project
		
		$sales_person_query = "SELECT a.disposition_status_id, a.disposition_sub_status_id, a.remark, a.date FROM lead_status as a WHERE user_type='sales_person' AND a.enquiry_id='".$row['enquiry_id']."'";
			
		$sales_person_resource = mysql_query($sales_person_query);
		
		$row['sales_person_status'] = "";
		
		$row['sales_person_sub_status'] = "";
		
		$row['sales_person_remark'] = "";
		
		$row['sales_person_updated_date'] = "";
		
		while($sales_person_status = mysql_fetch_assoc($sales_person_resource)){
				
			if($sales_person_status['disposition_status_id']){
				$row['sales_person_status'] = getStatusLabel($sales_person_status['disposition_status_id']);
				unset($sales_person_status['disposition_status_id']);
			}
			
			if($sales_person_status['disposition_sub_status_id']){
				$row['sales_person_sub_status'] = getStatusLabel($sales_person_status['disposition_sub_status_id'],$sales_person_status['disposition_status_id']);
				unset($sales_person_status['disposition_sub_status_id']);
			}
			
			if($sales_person_status['remark']){
				$row['sales_person_remark'] = $sales_person_status['remark'];
				unset($sales_person_status['remark']);
			}
			
			if($sales_person_status['date']){
				$row['sales_person_updated_date'] = date("d/m/Y H:i:s", strtotime($sales_person_status['date']));
				unset($sales_person_status['date']);
			}
		}
		
		if($row['site_visit_id']){

			$site_visit_query = "SELECT a.site_visit_timestamp FROM site_visit as a WHERE a.enquiry_id='".$row['enquiry_id']."' AND a.site_visit_id='".$row['site_visit_id']."' ORDER BY a.id DESC LIMIT 1";
			
			$site_visit_resource = mysql_query($site_visit_query);
			
			while($site_visit = mysql_fetch_assoc($site_visit_resource)){
				
				if($site_visit['site_visit_timestamp']){
			
					$row['site_visit_date_time'] = date("d/m/Y H:i:s", strtotime(date('m/d/Y H:i:s', ($site_visit['site_visit_timestamp']/1000))));
			
					unset($site_visit['site_visit_timestamp']);
				}
			}			
		}
		
		if($row['meeting_id']){
			
			$meeting_query = "SELECT a.meeting_timestamp FROM lead_meeting as a WHERE a.enquiry_id='".$row['enquiry_id']."' AND a.meetingId='".$row['meeting_id']."' ORDER BY a.id DESC LIMIT 1";
			
			$meeting_resource = mysql_query($meeting_query);
			
			while($meeting = mysql_fetch_assoc($meeting_resource)){
			
				if($meeting['meeting_timestamp']){
			
					$row['meeting_date_time'] = date("d/m/Y H:i:s", strtotime(date('m/d/Y H:i:s', ($meeting['meeting_timestamp']/1000))));
			
					unset($meeting['meeting_timestamp']);
				}
			}
		}
			
		unset($row['site_visit_id']);
		
		unset($row['meeting_id']);
		
		unset($row['lead_assigned_to_asm']);
		
		unset($row['lead_assigned_to_sp']);
				
		array_push($leads, $row);
			
	}
	
//	echo "<pre/>";
//	print_r($leads);
//	die;


	$header = [];
    $header[] = 'Enquiry Id';
    $header[] = 'Customer';
    $header[] = 'Mobile No.';
    $header[] = 'Alternate Contact';
    $header[] = 'Profession';
    $header[] = 'City';
    $header[] = 'Address';
    $header[] = 'Email ID';
    $header[] = 'Agent Remark';
    $header[] = 'CRM';
    $header[] = 'TM Name';
    $header[] = 'TM Assign Date';
    $header[] = 'Sales Person';
    $header[] = 'Sales Person Assign Date';
    $header[] = 'Lead Added Date';  
    $header[] = 'Lead Added Time';
    $header[] = 'Agent Status';
    $header[] = 'Agent Sub Status';
    $header[] = 'Enquiry Project';
    $header[] = 'Updated Status By SP';
    $header[] = 'Updated Sub Status By SP';
    $header[] = 'Updated Remark By SP';
    $header[] = 'SP Updated Date Time';
    $header[] = 'Meeting/Site Visit Date Time';
    
    if(count($leads) > 0){
		$str = '<div class="media">';
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        $data = array();
        
        foreach($header as $h){
            echo $h.',';
        }
        echo PHP_EOL;
        $x = 0;
        while($x <= count($leads)) {
        	foreach($leads[$x] as $key => $value){
	            $value = str_replace(',','-',$value);
	            echo $value.',';
	        }
	        echo PHP_EOL;
        	
        	$x++;
		}
	}	
	echo "Good";
}

?>