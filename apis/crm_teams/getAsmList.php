<?php

require '../function.php';

$designation = getDesignationBySlug('area_sales_manager','array');

// Get area sales managers who has team assigned 

$query = 'SELECT asm_id FROM crm_teams';

$result = mysql_query($query);

$asm_with_teams = array();

if($result){
    while ($row = mysql_fetch_assoc($result)){
        array_push($asm_with_teams, $row['asm_id']);
    }
}

// Get all asm from employee table but not included asm with team 

$exclude_asm = '';
if(!empty($asm_with_teams)){
    $exclude_asm = ' AND id NOT IN ('.implode(',', $asm_with_teams).')';
}

$get_all_asm = 'SELECT id as asm_id, CONCAT(firstname," ",lastname) as asm_name FROM employees WHERE designation = '.$designation['id'] . $exclude_asm . ' AND isDelete = 0';

$execute_get_all_asm = mysql_query($get_all_asm);

$asm_list = array();

if($execute_get_all_asm){

    while ($record = mysql_fetch_assoc($execute_get_all_asm)){
        array_push($asm_list, $record);
    }
}

// JSON response
echo json_encode($asm_list,true);
