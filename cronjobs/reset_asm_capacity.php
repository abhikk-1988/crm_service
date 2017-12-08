<?php

/* API to reset ALl Area Sales Manager's capacities */

require '../function.php';


// SELECT ALL ASM USERs

$designation_slug = 'area_sales_manager';

// GET DESIGNATION ID

$select_designation = 'SELECT id FROM `designationmaster` WHERE designation_slug = "'.$designation_slug.'" LIMIT 1';

$designation_result = mysql_query($select_designation);

$designation_id = '';

if($designation_result && mysql_num_rows($designation_result) > 0){
    
    $designation_object = mysql_fetch_object($designation_result);
    
    $designation_id     = $designation_object -> id;
    
    $select_all_asm     = 'SELECT id, firstname, lastname FROM employees WHERE designation = '.$designation_id.' AND isDelete = 0 ';
    
    $result = mysql_query($select_all_asm);
    
    $asm = array();
    
    if($result && mysql_num_rows($result) > 0){
        
        while($row = mysql_fetch_assoc($result)){
            
            array_push($asm, $row['id']);
        }
    }
    
    
    $ids_string = '';
    
    if(count($asm) > 0){
     
        $ids_string = "'" . implode("','", $asm) . "'";
    }
    
    $reset_capacity = 'UPDATE employees SET total_capacity = NULL WHERE id IN ('.$ids_string.')';
    
    if(mysql_query($reset_capacity)){ 
        // create a log for reseting area sales managers capacity and to prompt admin to fill capacity of area sales managers for current month
        
        $timestamp = time();
    
        mysql_query('UPDATE crm_settings SET `reset_area_sales_manager_capacity` = "'.$timestamp.'"');
    };
}