<?php
session_start();
require 'function.php';
$asm_id = '';
$current_month	= (int) date('m') - 1;
$current_year	= date('Y');
$sales_persons	= array();

$user_id			= $_SESSION['currentUser']['id']; 

$data		= file_get_contents("php://input");
$data 		= json_decode($data,true);

if(isset($user_id) && !isset($data->flag) && $user_id != 1){
    
    $select_sales_person  = 'SELECT employee.id, concat(employee.firstname," ", employee.lastname) as sales_person_name, sp_capacity.capacity,sp_capacity.remaining_capacity '
            . ' FROM `employees` as employee '
            . ' LEFT JOIN sales_person_capacities as sp_capacity ON (employee.id = sp_capacity.sales_person_id AND sp_capacity.month = '.$current_month.' AND sp_capacity.year = "'.$current_year.'" AND sp_capacity.remaining_capacity > 0) '
            . ' WHERE employee.reportingTo = '.$user_id.' AND employee.isDelete = 0 AND employee.activeStatus = 1';

}elseif($data->flag  || $user_id == 1){
	
    $select_sales_person  = 'SELECT employee.id, concat(employee.firstname," ", employee.lastname) as sales_person_name, sp_capacity.capacity,sp_capacity.remaining_capacity '
            . ' FROM `employees` as employee '
            . ' LEFT JOIN sales_person_capacities as sp_capacity ON (employee.id = sp_capacity.sales_person_id AND sp_capacity.month = '.$current_month.' AND sp_capacity.year = "'.$current_year.'" AND sp_capacity.remaining_capacity > 0) '
            . ' WHERE employee.designation = 7 AND employee.isDelete = 0 AND employee.activeStatus = 1';
	
}

$result = mysql_query($select_sales_person);

if($result && mysql_num_rows($result) > 0){

    while($row = mysql_fetch_assoc($result)){
            
        if($row['capacity'] != NULL){
            array_push($sales_persons, $row);
        }
    }
}

echo json_encode($sales_persons, true);