<?php
	
session_start();
require_once '../apis/function.php';
require_once '../apis/user_authentication.php';

if(!$is_authenticate){
	echo unauthorizedResponse();
	exit;
}
$leads = array();
	
if((!empty($_GET))){	// Check whether the date is empty	
    
    $subStatus = array();
    
    $status = array();
    
    $sub_status_sql = mysql_query("SELECT id FROM `disposition_status_substatus_master` WHERE `status_title` IS NULL AND `active_state` = 1");
    if(mysql_num_rows($sub_status_sql) > 0){
		while($row = mysql_fetch_array($sub_status_sql)){
			$subStatus[] = $row['id'];
		}
	}
	
	$status_sql = mysql_query("SELECT id FROM `disposition_status_substatus_master` WHERE `status_title` IS NOT NULL AND `active_state` = 1");
    if(mysql_num_rows($status_sql) > 0){
		while($row = mysql_fetch_array($status_sql)){
			$status[] = $row['id'];
		}
	}
    
    if(count($subStatus) > 0){
		$subStatusString = implode(',', $subStatus);
	}
	
	if(count($status) > 0){
		$statusString = implode(',', $status);
	}
	
	
	if(isset($_GET['startDate'])){	
       
		$startDate = date('Y-m-d',strtotime($_GET['startDate']));
	}
	if(isset($_GET['endDate'])){
        	
		$endDate = date('Y-m-d',strtotime($_GET['endDate']));    
	}
	if(isset($_GET['updatedDate'])){
        	
		$updateDate = date('Y-m-d',strtotime($_GET['updatedDate']));
	} 
        
	if(isset($startDate) && isset($endDate)){
		$startDate = $startDate.' 00:00:00';
        	
		$endDate = $endDate.' 23:59:59';
        	
		$filename = 'all_meeting_sitevisit_from_'.$startDate."_to_".$endDate;

		$sqlQuery = "SELECT a.enquiry_id, DATE(a.leadAddDate) as lead_Add_Date, TIME(a.leadAddDate) as lead_Add_Time,
		(CASE WHEN CONCAT(c.firstname, ' ', c.lastname) IS NOT NULL THEN CONCAT(c.firstname, ' ', c.lastname) WHEN CONCAT(b.firstname, ' ', b.lastname) IS NOT NULL THEN CONCAT(b.firstname, ' ', b.lastname) END) AS CRM, a.customerName, a.customerMobile, a.customer_alternate_mobile, a.customerProfession, f.project_name as Project, a.customerCity, 
           	
		(CASE WHEN REPLACE(REPLACE(h.meeting_address , '\r', ''), '\n', '') IS NULL THEN REPLACE(REPLACE(g.site_location , '\r', ''), '\n', '') WHEN REPLACE(REPLACE(g.site_location , '\r', ''), '\n', '') IS NULL THEN REPLACE(REPLACE(h.meeting_address , '\r', ''), '\n', '') END) as address
           	
		, 
           	
           	
		a.customerEmail, CONCAT(d.firstname, ' ', d.lastname) as TM_Name , CONCAT(e.firstname, ' ', e.lastname) as Sales_Manager, CONCAT(i.status_title, ' ', j.sub_status_title) as site_visit_status, FROM_UNIXTIME((CASE WHEN g.site_visit_timestamp IS NULL THEN h.meeting_timestamp WHEN h.meeting_timestamp IS NULL THEN g.site_visit_timestamp END)/1000,'%Y-%M-%d %H:%i:%s') AS site_visit_date, CONCAT(l.status_title, ' ', IFNULL(m.sub_status_title,'')) AS TM_SM_Status, k.hot_warm_cold_status, REPLACE(REPLACE(k.remark , '\r', ''), '\n', '') AS TM_SM_Remarks, n.title as source_of_lead, a.leadSecondarySource
           	
		FROM lead AS a 
           	
		LEFT JOIN employees as b ON (b.id = a.lead_added_by_user)
           	
		LEFT JOIN employees as c ON (c.id = a.reassign_user_id)
        
        LEFT JOIN lead_assignment_sales as Q ON (Q.enquiry_id = a.enquiry_id)
          	
		LEFT JOIN employees as d ON (d.id = a.lead_assigned_to_asm OR d.id = Q.asm_id)
           	
		LEFT JOIN employees as e ON (e.id = a.lead_assigned_to_sp OR e.id = Q.sp_id)
           	
		LEFT JOIN lead_enquiry_projects as f ON (f.enquiry_id = a.enquiry_id)
           	
		LEFT JOIN site_visit as g ON (g.enquiry_id = a.enquiry_id AND g.site_visit_id=a.site_visit_id)
           	
		LEFT JOIN lead_meeting as h ON (h.enquiry_id = a.enquiry_id AND h.meetingId=a.meeting_id)
           	
		LEFT JOIN disposition_status_substatus_master as i ON i.id = (SELECT disposition_status_id FROM lead_status WHERE disposition_status_id IN ($statusString) AND user_type = 'agent' AND enquiry_id=a.enquiry_id ORDER BY id DESC LIMIT 1)
           	
		LEFT JOIN disposition_status_substatus_master as j ON j.id = (SELECT disposition_sub_status_id FROM lead_status WHERE disposition_sub_status_id IN ($subStatusString) AND user_type = 'agent' AND enquiry_id=a.enquiry_id ORDER BY id DESC LIMIT 1)
           	
		LEFT JOIN lead_status as k ON (k.enquiry_id = a.enquiry_id AND (k.user_type='sales_person' OR k.user_type='area_sales_manager') AND NOT EXISTS (SELECT 1 FROM lead_status p1 WHERE p1.enquiry_id = a.enquiry_id AND p1.id > k.id ))
           	
		LEFT JOIN disposition_status_substatus_master as l ON (l.id = k.disposition_status_id)
           	
		LEFT JOIN disposition_status_substatus_master as m ON (m.id = k.disposition_sub_status_id AND k.disposition_sub_status_id IS NOT NULL)
           	
		LEFT JOIN campaign_master as n ON (n.id = a.leadPrimarySource)
		
        WHERE ((a.lead_assigned_to_asm_on >= '$startDate' AND a.lead_assigned_to_asm_on <='$endDate') OR (Q.asm_assign_date >= '$startDate' AND Q.asm_assign_date <='$endDate')) GROUP BY a.enquiry_id ORDER BY a.enquiry_id ASC";
           	 
//			die($sqlQuery);
           
		$lead_resource = mysql_query($sqlQuery);	
           
		if(mysql_num_rows($lead_resource) > 0){
	
			while($row = mysql_fetch_assoc($lead_resource)){
				array_push($leads, $row);
			}
				
			$header = [];
			$header[] = 'Enquiry Id';
			$header[] = 'Lead Added Date';  
			$header[] = 'Lead Added Time';
			$header[] = 'CRM';
			$header[] = 'Customer';
			$header[] = 'Mobile No.';
			$header[] = 'Alternate Contact';
			$header[] = 'Profession';
			$header[] = 'Project';
			$header[] = 'City';
			$header[] = 'Address';
			$header[] = 'Email ID';
			$header[] = 'TM Name';
			$header[] = 'Sales Manager';
			$header[] = 'Status';
			$header[] = 'Meeting/Sitevisit Date';
			$header[] = 'TM SM Status';
			$header[] = 'TM SM Activity';
			$header[] = 'TM SM Remarks';
			$header[] = 'Primary Source of Leads';
			$header[] = 'Secondary Source of Leads';
			    
			    
			    
			    
			if(count($leads) > 0){
				$str = '<div class="media">';
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=$filename.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				$data = array();
			        
				foreach($header as $h){
					echo $h.',';
				}
				echo PHP_EOL;
				$x = 0;
				while($x <= count($leads)){
					foreach($leads[$x] as $key => $value){
						$value = str_replace(',','-',$value);
						echo $value.',';
	            			
					}
					echo PHP_EOL;
			        	
					$x++;
				}
			}else{
				
				echo "<p>No record found</p>";	
			}	
		}else{
			
			echo "<p>No record found!</p>";	
		}
            
	}else{
		
		echo "<p>Date range format not correct!</p>";
	}
} else{
	
	echo "<p>You have not selected any date!</p>";
}

?>