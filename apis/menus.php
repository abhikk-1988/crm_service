<?php

$modules = file_get_contents('http://localhost/test.crm/apis/getEmpModules.php?emp_id=1');

echo $modules; exit;
echo '<pre>';print_r($modules); exit;

$menus = array();

$menus['modules']['Employee'] = array();
$menus['modules']['Lead'] = array();
$menus['modules']['customer'] = array();
$menus['modules']['report'] = array();




$menus['modules']['Employee']['link'] = 'employee';
$menus['modules']['Employee']['title'] = 'Employee';
$menus['modules']['Employee']['params'] = array('id' => 1, 'role' => 'admin');

$menus['modules']['Lead']['link'] = 'lead';
$menus['modules']['Lead']['title'] = 'Lead';
$menus['modules']['Lead']['params'] = array('id' => 1, 'role' => 'admin');

$menus['modules']['customer']['link'] = 'customer';
$menus['modules']['customer']['title'] = 'Customer';
$menus['modules']['customer']['params'] = array('id' => 1, 'role' => 'admin');

$menus['modules']['report']['link'] = 'report';
$menus['modules']['report']['title'] = 'Report';
$menus['modules']['report']['params'] = array('id' => 1, 'role' => 'admin');

$menus['modules']['Employee']['submenu'] = array();
$menus['modules']['Employee']['submenu']['add_employee'] = array();
$menus['modules']['Employee']['submenu']['add_employee']['title'] = 'Add Employee';
$menus['modules']['Employee']['submenu']['add_employee']['link'] = 'addEmployee';
$menus['modules']['Employee']['submenu']['add_employee']['action_rights']['add'] = true;
$menus['modules']['Employee']['submenu']['add_employee']['action_rights']['edit'] = true;
$menus['modules']['Employee']['submenu']['add_employee']['action_rights']['delete'] = true;
$menus['modules']['Employee']['submenu']['add_employee']['params']['id'] = 1;
$menus['modules']['Employee']['submenu']['add_employee']['params']['name'] = 'Abhishek';

$menus['modules']['Lead']['submenu'] = array();
$menus['modules']['Lead']['submenu']['add_employee'] = array();
$menus['modules']['Lead']['submenu']['add_employee']['title'] = 'Add Employee';
$menus['modules']['Lead']['submenu']['add_employee']['link'] = 'addEmployee';
$menus['modules']['Lead']['submenu']['add_employee']['action_rights']['add'] = true;
$menus['modules']['Lead']['submenu']['add_employee']['action_rights']['edit'] = true;
$menus['modules']['Lead']['submenu']['add_employee']['action_rights']['delete'] = true;
$menus['modules']['Lead']['submenu']['add_employee']['params']['id'] = 1;
$menus['modules']['Lead']['submenu']['add_employee']['params']['name'] = 'Abhishek';


$menus['modules']['Employee']['submenu']['search_employee'] = array();
$menus['modules']['Employee']['submenu']['search_employee']['title'] = 'Search Employee';
$menus['modules']['Employee']['submenu']['search_employee']['link'] = 'searchEmployee';


$menus['modules']['Lead']['submenu']['search_employee'] = array();
$menus['modules']['Lead']['submenu']['search_employee']['title'] = 'Search Employee';
$menus['modules']['Lead']['submenu']['search_employee']['link'] = 'searchEmployee';

$menus['modules']['Employee']['submenu']['designation'] = array();
$menus['modules']['Employee']['submenu']['designation']['title'] = 'Designation';
$menus['modules']['Employee']['submenu']['designation']['link'] = 'designation';
//
$menus['modules']['Lead']['submenu']['designation'] = array();
$menus['modules']['Lead']['submenu']['designation']['title'] = 'Designation';
$menus['modules']['Lead']['submenu']['designation']['link'] = 'designation';

$menus['modules']['Employee']['submenu']['employee_zones'] = array();
$menus['modules']['Employee']['submenu']['employee_zones']['title'] = 'Employee/Zones';
$menus['modules']['Employee']['submenu']['employee_zones']['link'] = 'employee_group';

//

echo json_encode($menus,true);
//$menus['modules']['Lead']['submenu']['employee_zones'] = array();
//$menus['modules']['Lead']['submenu']['employee_zones']['title'] = 'Employee/Zones';
//$menus['modules']['Lead']['submenu']['employee_zones']['link'] = 'employee_group';
