<?php
/**
 * API to get all sales persons with their superior area sales managers with capacity 
 */
session_start();

require 'function.php';

$sales_managers = array();

$data = filter_input_array(INPUT_POST);

$month	= date('m') - 1;
$year	= 2017;

$select_sales_person_sql = 'SELECT '
		. '	emp1.id, CONCAT(emp1.firstname," ",emp1.lastname) as fullname, emp1.email, emp1.doj, emp1.contactNumber, emp1.designation, spc.capacity as total_capacity,'
		. ' emp1.empCode, emp1.disposition_group, emp1.assigned_disposition_status_json, emp1.reportingTo as manager_id,'
		. ' concat(emp2.firstname," ",emp2.lastname) as manager_name, emp2.email as manager_email, emp2.total_capacity as threshold_capacity, mpl.capacity as manager_mpl_capacity'
		. ' FROM employees as emp1'
		. ' LEFT JOIN employees as emp2 ON (emp1.reportingTo = emp2.id)'
		. ' LEFT JOIN sales_person_capacities as spc ON (emp1.id = spc.sales_person_id)'
		. ' LEFT JOIN mpl_capacity as mpl ON (emp1.reportingTo = mpl.user_id)'
		. ' WHERE emp1.designation = (SELECT id FROM designationmaster WHERE designation_slug = "sales_person") AND emp1.reportingTo != 0';

$result = mysql_query($select_sales_person_sql);

if($result && mysql_num_rows($result) > 0){
		
	while($row = mysql_fetch_assoc($result)){
		
		$row['manager'] = array(
			'id' => $row['manager_id'], 
			'name' => $row['manager_name'],
			'email'=> $row['manager_email'],
			'capacity' => $row['threshold_capacity'],
			'mpl_capacity' => $row['manager_mpl_capacity']);
		
		unset($row['manager_id']);
		unset($row['manager_name']);
		unset($row['manager_email']);
		unset($row['threshold_capacity']);
		
		array_push($sales_managers, $row);
	}
}

echo json_encode($sales_managers, true);