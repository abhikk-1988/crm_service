<?php
session_start();
require 'function.php';
$date = date('Y-m-d', time());

$user = '';
$lead_type = '';
$status = '';
$sub_status = '';
$sub_status_condition = '';
$leads_collection = array();

$post = file_get_contents('php://input');

$post_data = json_decode($post,true);

if(isset($post_data['user']) && $post_data['user'] != ''){
	
	$user 		= $post_data['user'];
	$lead_type 	= $post_data['lead_type'];
	
	// Fetch Followup leads of today	
	
	if($lead_type['primary'] !=''){
		
		$status = $lead_type['primary'];
		
		if($lead_type['secondary'] != ''){
			$sub_status = $lead_type['secondary'];
			$sub_status_condition = ' AND disposition_sub_status_id = '.$sub_status.' ';
		}
		
		// Query to database  	
		$leads = mysql_query('SELECT * FROM lead WHERE disposition_status_id = '.$status.' '. $sub_status_condition . ' AND future_followup_date LIKE "%'.$date.'%" AND lead_assigned_to_asm IS NULL ORDER BY leadAddDate DESC ');
	
		if($leads && mysql_num_rows($leads) > 0){
			
			$projects = '';
			
			while($row = mysql_fetch_assoc($leads)){
				
				$row['projects'] = array();
				
				if($status == 3){
					$projects = mysql_query('SELECT project, meeting_address FROM lead_meeting WHERE enquiry_id = '.$row['enquiry_id'].' AND meetingId = "'.$row['meeting_id'].'" LIMIT 1');	
				}
				else if($status == 6){
					$projects = mysql_query('SELECT project, site_location FROM site_visit WHERE enquiry_id = '.$row['enquiry_id'].' AND site_visit_id = "'.$row['site_visit_id'].'" LIMIT 1');
				}
				
				if($projects && mysql_num_rows($projects)>0){
					
						$p_data = mysql_fetch_object($projects);
						
						if($p_data -> project != ''){
						
							// convert json string to array
							$projects = json_decode($p_data -> project,true);
							
							if(is_array($projects) && !empty($projects)){
							
								foreach($projects as $val){
									array_push($row['projects'], $val['project_name']);
								}
							}
						}else{
							array_push($row['projects'], 'NA');
						}

						// Meeting or site visit address
						if($status == 3){
							$row['meeting_address'] = $p_data -> meeting_address;
						}else{
							$row['meeting_address'] = $p_data -> site_location;
						}	

					}else{
					array_push($row['projects'], 'NA');
				}
			
				// Name of the agent who created lead
                if($row['reassign_user_id'] != ''){
                    $row['lead_created_by'] = getEmployeeName($row['reassign_user_id']);     
                }else{
                    $row['lead_created_by'] = getEmployeeName($row['reassign_user_id']);
                }
				
				array_push($leads_collection, $row);	
			}
		}
	}
}

echo json_encode($leads_collection, true);