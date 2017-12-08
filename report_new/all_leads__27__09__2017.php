<?php
	
	session_start();
	require_once '../apis/function.php';
	require_once '../apis/user_authentication.php';

	if(!$is_authenticate){
		echo unauthorizedResponse();
		exit;
	}
	$leads = array();
	
    if((!empty($_GET))) {	// Check whether the date is empty	
    
    	
        if(isset($_GET['startDate'])){	
       
            $startDate = date('Y-m-d',strtotime($_GET['startDate']));
        }
        if(isset($_GET['endDate'])){
        	
            $endDate = date('Y-m-d',strtotime($_GET['endDate']));    
        }
        
        if(isset($startDate) && isset($endDate)){
        	
        	$startDate = $startDate.' 00:00:00';
        	
        	$endDate = $endDate.' 23:59:59';
        	
        	$filename = 'all_leads_from_'.$startDate."_to_".$endDate;
        	
           	$sqlQuery = "SELECT a.enquiry_id, f.meeting_id, f.site_visit_id, f.leadPrimarySource, f.leadSecondarySource, DATE(f.leadAddDate) AS lead_Add_Date, TIME(f.leadAddDate) AS lead_Add_Time, DATE(a.date) AS Lead_Updated_Date, TIME(a.date) AS Lead_Updated_Time, f.customerName, f.customerMobile, f.customer_alternate_mobile, f.customerProfession, f.customerCity, REPLACE(REPLACE(f.customerAddress , '\r', ' '), '\n', ' ') as Adderess, f.customerEmail,
           	
           	CONCAT(c.firstname, ' ', c.lastname) AS CRM,
           	
           	d.status_title, e.sub_status_title, REPLACE(REPLACE(a.remark , '\r', ' '), '\n', ' ') as crm_reamrk, CONCAT(g.firstname, ' ', g.lastname) AS TM_Name, f.lead_assigned_to_asm_on, CONCAT(h.firstname, ' ', h.lastname) AS Sales_Person, f.lead_assigned_to_sp_on 
           	
           	FROM lead_status as a 
           	
           	LEFT JOIN employees as b ON (b.id = a.user_id)
           	
           	LEFT JOIN disposition_status_substatus_master as d ON (d.id = a.disposition_status_id)
           	
           	LEFT JOIN disposition_status_substatus_master as e ON (e.id = a.disposition_sub_status_id)
           	
           	LEFT JOIN lead as f ON (f.enquiry_id = a.enquiry_id)
           	
           	LEFT JOIN employees as c ON (c.id = a.user_id)
           	
           	LEFT JOIN employees as g ON (g.id = f.lead_assigned_to_asm)
           	
           	LEFT JOIN employees as h ON (h.id = f.lead_assigned_to_sp)
           	
           	WHERE a.user_type='agent' AND a.date > '$startDate' AND a.date < '$endDate' ORDER BY a.enquiry_id ASC";

//			die($sqlQuery);
		  	
		  	$lead_resource = mysql_query($sqlQuery);	
           
			if(mysql_num_rows($lead_resource) > 0){
	
				while($row = mysql_fetch_assoc($lead_resource)){
					
					$salesQuery = mysql_query("SELECT date, dispostion_status_id, dispostion_sub_status_id, REPLACE(REPLACE(remark , '\r', ' '), '\n', ' ') as remark FROM lead_status WHERE enquiry_id = ".$row['enquiry_id']." AND user_type='sales_person' ORDER BY id ASC LIMIT 1");
					
					if(mysql_num_rows( $salesQuery ) > 0 ){
						
						while($salesRow = mysql_fetch_assoc($salesQuery)){
					
							if($salesRow['date']){
								$row['date'] = $salesRow['date'];
							}
							
							if($salesRow['dispostion_status_id']){
								
								$row['sp_status'] = getStatusLabel($salesRow['dispostion_status_id']);
							}
							
							if($salesRow['dispostion_sub_status_id']){
								$row['sp_sub_status'] = getStatusLabel($salesRow['dispostion_sub_status_id'],$salesRow['dispostion_status_id']);
							}
							
							if($salesRow['remark']){
								$row['remark'] = $salesRow['remark'];
							}
						}
					}else{
						$row['date'] = NULL;
						$row['sp_status'] = NULL;
						$row['sp_sub_status'] = NULL;
						$row['remark'] = NULL;
					}
					
					// Get Projects
					$projectQuery = mysql_query("SELECT project_name FROM lead_enquiry_projects WHERE enquiry_id = ".$row['enquiry_id']." ORDER BY id ASC");
					if(mysql_num_rows( $projectQuery ) > 0 ){
						$project = array();
						while($projectRow = mysql_fetch_assoc($projectQuery)){
					
							if($projectRow['project_name']){
								$project[] = $projectRow['project_name'];
							}
						}
					
						$row['project_name'] = implode(',', $project);
					
					}else{
					
						$row['project_name'] = NULL;
					}
					
					// Meeting Data 
					if($row['meeting_id'] && ($row['site_visit_id']=='' OR $row['site_visit_id']==NULL)){
						
						$meetingQuery = mysql_query("SELECT DATE(FROM_UNIXTIME(meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s')) as meeting_date, TIME(FROM_UNIXTIME(meeting_timestamp/1000,'%Y-%m-%d %H:%i:%s')) AS meeting_time FROM lead_meeting WHERE enquiry_id = ".$row['enquiry_id']." ORDER BY id ASC");
						
						if(mysql_num_rows( $meetingQuery ) > 0 ){
							
							while($meetingRow = mysql_fetch_assoc($meetingQuery)){
						
								if($meetingRow['meeting_date']){
									$row['meeting_date'] = $meetingRow['meeting_date'];
								}
								
								if($meetingRow['meeting_time']){
									$row['meeting_time'] = $meetingRow['meeting_time'];
								}
							}
						}else{
							$row['meeting_time'] = NULL;
							$row['meeting_date'] = NULL;
						}
					}
					
					// Meeting Data 
					elseif($row['site_visit_id'] && ($row['meeting_id']=='' OR $row['meeting_id']==NULL)){
						
						$meetingQuery = mysql_query("SELECT DATE(FROM_UNIXTIME(site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s')) as meeting_date, TIME(FROM_UNIXTIME(site_visit_timestamp/1000,'%Y-%m-%d %H:%i:%s')) as meeting_time FROM site_visit WHERE enquiry_id = ".$row['enquiry_id']." ORDER BY id ASC");
						
						if(mysql_num_rows( $meetingQuery ) > 0 ){
							
							while($meetingRow = mysql_fetch_assoc($meetingQuery)){
						
								if($meetingRow['meeting_date']){
									$row['meeting_date'] = $meetingRow['meeting_date'];
								}
								
								if($meetingRow['meeting_time']){
									$row['meeting_time'] = $meetingRow['meeting_time'];
								}
							}
						}else{
						
							$row['meeting_date'] = NULL;
							$row['meeting_time'] = NULL;
						}
					}else{
						$row['meeting_date'] = NULL;
						$row['meeting_time'] = NULL;
					}
					
					// Get Source 
					$sourceQuery = mysql_query("SELECT title FROM campaign_master WHERE id = ".$row['leadPrimarySource']." ORDER BY id ASC LIMIT 1");
						
					if(mysql_num_rows( $sourceQuery ) > 0 ){
						
						while($sourceRow = mysql_fetch_assoc($sourceQuery)){
					
							if($sourceRow['title']){
								$row['primary_source'] = $sourceRow['title'];
							}
						}
					}else{
					
						$row['primary_source'] = NULL;
					}
					
					if($row['leadSecondarySource']){
						$row['secondarySource'] = $row['leadSecondarySource'];
					}
					unset($row['leadPrimarySource']);
					unset($row['meeting_id']);
					unset($row['site_visit_id']);
					unset($row['leadSecondarySource']);
							
					array_push($leads, $row);
							
				}
//				echo "<pre/>";
//				print_r($leads); die;
				$header = [];
			    $header[] = 'Enquiry Id';
			    $header[] = 'Lead Added Date';  
			    $header[] = 'Lead Added Time';
			    $header[] = 'Lead Updated Date';
			    $header[] = 'Lead Updated Time';
			    $header[] = 'Customer';
			    $header[] = 'Mobile No.';
			    $header[] = 'Alternate Contact';
			    $header[] = 'Profession';
			    $header[] = 'City';
			    $header[] = 'Address';
			    $header[] = 'Email ID';
			    $header[] = 'CRM';
			    $header[] = 'CRM Status';
			    $header[] = 'CRM Sub Status';
			    $header[] = 'CRM Remark';
			    $header[] = 'TM Name';
			    $header[] = 'TM Assign Date';
			   	$header[] = 'Sales Person';
			   	$header[] = 'Sales Person Assign Date';
			    $header[] = 'SP Updated Date Time';
			    $header[] = 'Updated Status By SP';
			    $header[] = 'Updated Sub Status By SP';
			    $header[] = 'Updated Remark By SP';
			    $header[] = 'Enquiry Project';
			    $header[] = 'Meeting/Site Visit Date';
			    $header[] = 'Meeting/Site Visit Time';
			    $header[] = 'Primary Lead Source';
			    $header[] = 'Secondary Lead Source';
			    
			    
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
			        while($x <= count($leads)) {
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