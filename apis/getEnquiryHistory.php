<?php

session_start();

require_once 'function.php';

$user_id  = $_SESSION['currentUser']['id']; 

$designationId = $_SESSION['currentUser']['designation']; 

// Getting designation name
$sql_designation = mysql_query("SELECT designation FROM designationmaster WHERE id = '$designationId' LIMIT 1");

$designationRes = mysql_fetch_assoc($sql_designation);

$designationName = $designationRes['designation'];

$data = json_decode(file_get_contents('php://input'),TRUE);

$history = array();

if(!empty($data) && !empty($data['enquiry_id'])){
	
	$enquiry_id = $data['enquiry_id'];

	// Current Re-assign User id
	$sql_re_assign_id = mysql_query("SELECT lead_added_by_user, reassign_user_id, lead_assigned_to_asm, lead_assigned_to_sp FROM lead WHERE enquiry_id = '$enquiry_id' LIMIT 1");

	$sql_re_assign_idRes = mysql_fetch_assoc($sql_re_assign_id);

	$re_assign_id = $sql_re_assign_idRes['reassign_user_id'];
	
	$lead_added_by_user = $sql_re_assign_idRes['lead_added_by_user'];
	
	$asm_id = $sql_re_assign_idRes['lead_assigned_to_asm'];

	$sp_id = $sql_re_assign_idRes['lead_assigned_to_sp'];
	$temp = 0;
	if($designationName == 'Agent'){
		
		if($re_assign_id == $user_id){
			
			$cond = '';	
		
		}elseif($lead_added_by_user == $user_id && $re_assign_id==NULL ){
			
			$cond = '';	
		
		}elseif($lead_added_by_user == $user_id && $re_assign_id!=NULL ){
			
			$cond = ' AND employee_id='.$user_id;
			$temp = 1;
		}else{
			$temp = 1;
			$cond = ' AND employee_id='.$user_id;
		}
		
	}elseif($designationName == 'Area sales manager'){
		
		if($asm_id == $user_id){
			
			$cond = '';	
			
		}else{
			$SQL = "SELECT date FROM lead_re_assign where id = (SELECT id-1 FROM lead_re_assign WHERE enquiry_id = '$enquiry_id' AND user_type = 'area_sales_manager' AND to_user_id = '$user_id' ORDER BY id ASC LIMIT 1) LIMIT 1";
			
			
			$sql_reassign_agent = mysql_query($SQL);
			
			$result_agent_id = mysql_fetch_assoc($sql_reassign_agent);
			
			$SQL1 = "SELECT date FROM lead_re_assign where id = (SELECT id+2 FROM lead_re_assign WHERE enquiry_id = '$enquiry_id' AND user_type = 'area_sales_manager' AND to_user_id = '$user_id' ORDER BY id ASC LIMIT 1) LIMIT 1";
			
			
			$sql_reassign_sp = mysql_query($SQL1);
			
			$result_sp_id = mysql_fetch_assoc($sql_reassign_sp);
		
			if($result_sp_id['date'] && $result_agent_id['date']){
				$agent_date = $result_agent_id['date'];
				$sp_date = $result_sp_id['date'];
				$cond = ' AND created_at >="'.$agent_date.'" AND created_at <= "'.$sp_date.'"';
			}else{
				
				$cond = '';
			}
		}
				
	}elseif($designationName == 'Sales Person'){
		if($sp_id){
			
			$cond = '';
			
		}else{
			$SQL = "SELECT date FROM lead_re_assign where id = (SELECT id-1 FROM lead_re_assign WHERE enquiry_id = '$enquiry_id' AND user_type = 'area_sales_manager' AND to_user_id = '$user_id' ORDER BY id ASC LIMIT 1) LIMIT 1";
			
			
			$sql_reassign_agent = mysql_query($SQL);
			
			$result_agent_id = mysql_fetch_assoc($sql_reassign_agent);
			
			$SQL1 = "SELECT date FROM lead_re_assign where id = (SELECT id+2 FROM lead_re_assign WHERE enquiry_id = '$enquiry_id' AND user_type = 'area_sales_manager' AND to_user_id = '$user_id' ORDER BY id ASC LIMIT 1) LIMIT 1";
			
			
			$sql_reassign_sp = mysql_query($SQL1);
			
			$result_sp_id = mysql_fetch_assoc($sql_reassign_sp);
		
			if($result_sp_id['date'] && $result_agent_id['date']){
				$agent_date = $result_agent_id['date'];
				$sp_date = $result_sp_id['date'];
				$cond = ' AND created_at >="'.$agent_date.'" AND created_at <= "'.$sp_date.'"';
			}else{
				
				$cond = '';
			}
		}
	
	}else{
		
		$cond = '';
	}
	
//	if($temp){
//		$select_history_id = "SELECT id, created_at FROM `lead_history` WHERE enquiry_id = '".$enquiry_id."' AND type='re-assign' ORDER BY id ASC LIMIT 1";
//		$result_id = mysql_query($select_history_id);
//		$row_id = mysql_fetch_assoc($result_id);
//		$re_assign_date = $row_id['created_at'];
//		$cond = " AND created_at < '$re_assign_date'";
//	}
	
	$select_history_sql = "SELECT * FROM `lead_history` WHERE enquiry_id = '".$enquiry_id."' $cond ORDER BY `created_at` DESC";
	
//	die($select_history_sql);
	
	$result = mysql_query($select_history_sql);

	if($result && mysql_num_rows($result) > 0){
		
		while($row = mysql_fetch_assoc($result)){
			
			// json decode meta data if not blank
			if($row['meta_data'] != ''){
				
				$meta_data = json_decode($row['meta_data'], true);
				unset($row['meta_data']);
				$row['meta_data'] = $meta_data;
			}
			
			array_push($history, $row);
			
		}	
	}
}

echo json_encode($history,true); exit; 