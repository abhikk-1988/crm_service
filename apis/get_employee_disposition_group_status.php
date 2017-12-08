<?php
session_start();

require_once 'function.php';

$employee_id = '';

if(isset($_GET['employee_id']) && $_GET['employee_id'] != ''){
	
	$employee_id = $_GET['employee_id'];
}

if(!empty($employee_id)){
	
	$assigned_employee_disposition_group_status = file_get_contents(BASE_URL . 'apis/helper.php?method=getEmployeeAssignedGroupStatus&params=employee_id:'.$employee_id);
	
	$group_parent_status		= array();
	$group_child_status		= array();
	$mobile 		= array();
	// Decode json string to iterate like php array 
	
	$decoded_data = json_decode($assigned_employee_disposition_group_status,true);
	
	if(!empty($decoded_data)){
		
		foreach($decoded_data as $key => $arr){
			
			foreach($arr as $parent_id => $childs_arr){
				
				$temp			= array();
				$child_temp		= array();
				$mobile_temp 	= array(); 
				
				$temp['id']		= $parent_id;
				$temp['title']		= getStatusLabel($parent_id,'parent');
				$temp['is_activity_status'] = isActivityStatusAttached($parent_id);
                
                
				if(isset($_GET['env']) == 'mobile'){
					$mobile_temp['group_id'] 	= $parent_id;
					$mobile_temp['group_name'] 	= $temp['title'];
					$mobile_temp['childs'] = array();
				}

				array_push($group_parent_status, $temp);
				
				if(!empty($childs_arr)){
					
					$child_temp['group_id']		= $parent_id;
					$child_temp['group_name'] 	= $temp['title']; 
					$child_temp['childs']			= array();
					
					// $mobile['childs'] = array();

					foreach($childs_arr as $k => $v){
					
						$temp1		= array();
						$temp1['id']		= $v;
						$temp1['status']	= getStatusLabel($v,'chld');
                        $temp1['is_activity_status'] = isActivityStatusAttached($temp1['id']);
						array_push($child_temp['childs'], $temp1);

						if(isset($_GET['env']) == 'mobile'){
							array_push($mobile_temp['childs'] , $temp1);
						}
					}

					array_push($group_child_status,$child_temp);

					if(isset($_GET['env']) == 'mobile'){
						array_push($mobile, $mobile_temp);
					}

				}
			}
		}
	}
	
	// return respons
	
	if(!isset($_GET['env'])){
		$respone  = array('success' => 1, 'parent_status' => $group_parent_status, 'sub_status' => $group_child_status, 'mobile_status' => $mobile);
	}
	else{
		$respone  = array('success' => 1,'mobile_status' => $mobile);
	}
	
	echo json_encode($respone,true); exit; 
	
}else{
	return json_encode(array(),true); exit; 
}
