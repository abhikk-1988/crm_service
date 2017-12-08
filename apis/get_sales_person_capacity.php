<?php

// API to fetch sales persons capacities for speified month and year 

session_start();

require 'function.php';
require 'user_authentication.php';

$sales_person_id	= '';
$month				= '';
$year				= '';

$_get = filter_input_array(INPUT_GET);

$capacities = array();

if( isset($_get) ) {
	
	$sales_person_id	= ( isset($_get['sales_person_id']) ? $_get['sales_person_id'] : '');
	$month				= ( isset($_get['month']) ? $_get['month'] : '' );
	$year				= ( isset($_get['year']) ? $_get['year']: '');
}

// optional where conditions for specific sales person

$where_sales_person_id = '';
if($sales_person_id != ''){
	$where_sales_person_id = ' AND spc.sales_person_id = '. $sales_person_id;
}

// mandatory month and year filter 
if( $month == ''){
	$month = date('m') - 1; // default set to current month
}

if( empty($year)){
	$year = date('Y'); // default set to current year
}

	$query1 = 'SELECT count(*) as total_records FROM sales_person_capacities';
	
	$result1 = mysql_query($query1);

	$total_records = '';
	
	if($result1 && mysql_num_rows($result1) > 0){
		$total_records = mysql_fetch_object($result1) -> total_records;
	}
	
	if($total_records > 0){
//		Non working query 
//		$query2 = 'SELECT spc.sales_person_id, spc.capacity, spc.month, spc.year, spc.remaining_capacity,'
//			. 'CONCAT(emp.firstname," ",emp.lastname) as sales_person_name, emp.reportingTo as manager_id,'
//			. 'SUM(cm.capacity) as manager_capacity '
//			. 'FROM sales_person_capacities spc '
//			. 'LEFT JOIN employees as emp ON (spc.sales_person_id = emp.id) '
//			. 'LEFT JOIN capacity_master as cm ON (emp.reportingTo = cm.userId AND cm.capacity_month = '.$month.' AND cm.capacity_year = "'.$year.'") '
//			. 'WHERE month = '.$month.' AND year = "'.$year.'" '. $where_sales_person_id;
//
        
        /* Working query */
        $query2 = 'SELECT 
spc.sales_person_id, spc.capacity, spc.month, spc.year, spc.remaining_capacity,
CONCAT(emp.firstname," ",emp.lastname) as sales_person_name,
emp.reportingTo as manager_id

FROM sales_person_capacities as spc 
LEFT JOIN employees as emp ON (spc.sales_person_id = emp.id) 
WHERE month = '.$month.' AND year = "'.$year.'"';
        
		$result = mysql_query($query2);
	
		if($result && mysql_num_rows($result) > 0 ){
		
		while($row = mysql_fetch_assoc($result)){
			
			$row['manager'] = array(
				'id' => $row['manager_id'], 
				'name' => getEmployeeName($row['manager_id']), 
				'capacity' => getASMCurrentMonthCapacity($row['manager_id'])
			);
			
			unset($row['manager_id']);
			unset($row['manager_capacity']);
			
			array_push($capacities, $row);
		}
	}
	}
	
	echo json_encode(array('success' => 1,'http_status_code' => 200, 'data' => $capacities), true);