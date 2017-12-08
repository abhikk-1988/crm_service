<?php

/**
 * API - Employee.php 
 * @uses To provide data of all the employees or single employee data except admin user 
 * @param varchar $employee employee_id <optional>
 */

require_once 'function.php';

$employee_id                = '';
$where_employee_condition   = '';

if(isset($_GET['employee_id'])){
    $employee_id                = $_GET['employee_id'];
    $where_employee_condition   = 'AND emp.id = '. $employee_id;
}

$designation_id = '';

if( isset($_GET['designation_id']) && $_GET['designation_id'] != ''){
    $designation_id             = $_GET['designation_id'];
    $where_employee_condition   = 'AND emp.designation = '. $designation_id;	
}

// Get admin employee id by role 
$employee_to_discard = getEmployeeByRole(ROLE_ADMIN);

if(is_object($employee_to_discard)){
	
	$admin_id = $employee_to_discard -> id;
}else{
	$admin_id = '';
}

$employee_select = 'SELECT '
		. '		emp.* , role.designation as employee_designation, CONCAT(report_emp.firstname," ", report_emp.lastname) as reportingToEmployee '
            . '			FROM `employees` as emp '
        . '			LEFT JOIN `designationmaster` as role ON (emp.designation = role.id)'
		. ' LEFT JOIN employees as report_emp ON (emp.reportingTo = report_emp.id )' 
        . '			WHERE emp.activeStatus = "1" AND emp.isDelete = 0 AND emp.role != "'.$admin_id.'" '. $where_employee_condition;


$result = mysql_query($employee_select);

$rows = mysql_num_rows($result);

$employees = array();

if($rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        if($row['profile_image'] != NULL){
            
            $profile_image = base64_encode($row['profile_image']);
            unset($row['profile_image']);
            $row['profile_image'] = $profile_image;
        }
        
        array_push($employees,$row);
    }
}
// Remove employee profile image

echo json_encode($employees,TRUE);