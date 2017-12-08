<?php

require '../function.php';

$designation = getDesignationBySlug('agent','array');

if(!isset($_GET['asm_id'])){
    echo 'Please select ASM to get CRM list';
    exit;
}

$get_all_crm = 'SELECT id as crm_id, CONCAT(firstname," ",lastname) as crm_name 
FROM employees 
WHERE designation = '.$designation['id'] .' AND (reportingTo IS NULL OR reportingTo = '.$_GET['asm_id'].')';



$execute_get_all_crm = mysql_query($get_all_crm);

$crm_list = array();

if($execute_get_all_crm){

    while ($record = mysql_fetch_assoc($execute_get_all_crm)){
        array_push($crm_list, $record);
    }
}

// JSON response
echo json_encode($crm_list,true);
