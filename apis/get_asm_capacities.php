<?php
session_start();
require 'function.php';

$capacites			= array();
$capacity_month		= (int)date('m') - 1;
$capacity_year		= date('Y');

$condition = ' WHERE capacity_month = '.$capacity_month.' AND capacity_year = "'.$capacity_year.'"';

//$select_capacities = 'SELECT 
//	CONCAT(emp.firstname," ",emp.lastname) as employee_name, 
//	emp.id as user_id, 
//	emp.total_capacity, 
//	GROUP_CONCAT(cap.pName) as projects,
//	GROUP_CONCAT(cap.pId) as project_ids,
//	GROUP_CONCAT(cap.capacity) as project_capacity, 
//	cap.capacity_month, 
//	cap.capacity_year, 
//	SUM(cap.remaining_capacity) as remaining_capacity
//	FROM employees as emp
//		LEFT JOIN capacity_master as cap ON (emp.id = cap.userId AND cap.capacity_month = '.$capacity_month.' AND cap.capacity_year="'.$capacity_year.'")
//	WHERE designation = (SELECT `id` FROM `designationmaster` WHERE designation_slug = "area_sales_manager")
//	GROUP BY emp.id';


$select_capacities_query = 'SELECT '
		. 'capacity.userId as asm_id, '
		. 'SUM(capacity.capacity) as total_capacity_of_month, '
		. 'SUM(capacity.remaining_capacity) as total_remaining_capacity_of_the_month, '
		. 'GROUP_CONCAT(capacity.pName) as projects, '
		. 'GROUP_CONCAT(capacity.capacity) as project_capacity, '
		. 'CONCAT(emp.firstname," ",emp.lastname) as asm_name '
		. 'FROM `capacity_master` as capacity '
		. 'LEFT JOIN employees as emp ON (capacity.userId = emp.id) '
		. 'WHERE capacity.capacity_month = '.$capacity_month.' AND capacity.capacity_year = "'.$capacity_year.'" AND emp.isDelete = 0 AND emp.activeStatus = 1 '
		. 'GROUP BY capacity.userId ORDER BY emp.firstname ASC';

//echo $select_capacities_query; exit;
$result = mysql_query($select_capacities_query);

if($result && mysql_num_rows($result) > 0){
	
	while($row = mysql_fetch_assoc($result)){	
		
		$_projects					= explode(',', $row['projects']);
		$_capacities				= explode(',', $row['project_capacity']); 
				
		$_project_with_capacities	=  array();
		foreach ($_projects as $key => $val){
			array_push($_project_with_capacities, array('project_name' => $val, 'capacity' => $_capacities[$key]));
		}

		unset($row['projects']);
		unset($row['project_capacity']);
		
		$row['projects'] = $_project_with_capacities;
		array_push($capacites, $row); 
	}
}

//echo '<pre>'; print_r($capacites); exit;
echo json_encode($capacites,true); exit;