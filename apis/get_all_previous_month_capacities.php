<?php
session_start();
require 'function.php';

// user designation type 
// user id 

//$data = file_get_contents('php://input');

$encoded_data = filter_input_array(INPUT_POST);
$table = '';
$capacity_data	= array();

if( !empty($encoded_data) && isset($encoded_data['user_id'])){
	
	$previous_month = '';
	$previous_year	= '';
	
	if(isset($encoded_data['previous_month'])){
		$previous_month = $encoded_data['previous_month'];
	}
	
	if( isset($encoded_data['previous_year']) ){
		$previous_year = $encoded_data['previous_year'];
	}
	
    
    $current_month = date('m') - 1;
    $current_year  = date('Y'); 
    
	$user_id			= $encoded_data['user_id'];
	$designation_slug	= (isset($encoded_data['designation_slug']) ? $encoded_data['designation_slug'] : '');
		
	if($designation_slug === 'area_sales_manager'){
			
		$sql = 'SELECT '
				. '	SUM(capacity.capacity) as total_capacity, '
				. '	SUM(capacity.remaining_capacity) as total_consumed_capacity, '
				. '	GROUP_CONCAT(capacity.pName) as projects, '
				. ' GROUP_CONCAT(capacity.capacity) as project_capacity, '
				. '	GROUP_CONCAT(capacity.remaining_capacity) as project_remaining_capacities, '
				. '	capacity.capacity_month, capacity.capacity_year '
			. ' FROM `capacity_master` as capacity '
			. ' WHERE capacity.userId = '.$user_id.' AND capacity.capacity_month = '.$previous_month.' AND capacity.capacity_year = "'.$previous_year.'" '
			. '	GROUP by capacity.capacity_month '
			. '	ORDER BY capacity.capacity_year DESC, capacity.capacity_month DESC';
	
        
        
        
        
        
		$result = mysql_query($sql);
		if($result && mysql_num_rows($result) > 0){
			
			while($row = mysql_fetch_assoc($result)){
				
				$row['project']				= array();
				$_projects						= explode(',', $row['projects']);
				$_project_capacity				= explode(',', $row['project_capacity']);
				$_project_remaining_capacity	= explode(',', $row['project_remaining_capacities']);
				
				foreach ($_projects as $key => $val){
					array_push($row['project'], array('project' => $val, 'capacity' => $_project_capacity[$key],'remaining_capacity' =>$_project_remaining_capacity[$key]));
				}
				
				array_push($capacity_data, $row);
			}
		}
	}else if($designation_slug == 'sales_person'){
			
			$sql = 'SELECT * FROM `sales_person_capacities` 
                    WHERE `sales_person_id` = '.$user_id.' AND month NOT IN ('.$current_month.') AND year = "'.$current_year.'"
                    ORDER BY year, month DESC';
			
			$result = mysql_query($sql);
			
			if($result && mysql_num_rows($result) > 0){
				while($row = mysql_fetch_assoc($result)){
					array_push($capacity_data, $row);
				}
			}
		}

	echo json_encode($capacity_data,true); exit;
}else{
	echo json_encode(array(),true); exit;
}