<?php

require_once 'db_connection.php';


$post_data = file_get_contents('php://input');

if($post_data == ''){
    echo 0; exit; 
}


$post_data_array = json_decode($post_data, true);

$assigned_modules   = array();

$unassinged_modules = array();

$designation_id     = '';

foreach($post_data_array as $module){
    
    $designation_id = $module['designation_id'];
    
    if($module['assign'] == 1){
        $temp = [];
        $temp['module_id']  = $module['id'];
        $temp['permission'] = $module['permission'];
        $temp['parent']     = $module['parent'];
        
        array_push($assigned_modules,$temp);
    }else{
        array_push($unassinged_modules, $module['id']);
    }
}

if(empty($assigned_modules)){
    
    // delete all the modules under designation 
    $execute = mysql_query('DELETE FROM '.  strtolower('designationmodulemaster').' WHERE designationId = '.$designation_id.'');
    if($execute){
        echo json_encode(array('success' => 1,'message' => 'Modules updated successfully'),true); exit;
    }else{
        echo json_encode(array('success' => 0,'message' => 'Some Server error occured. Modules couldn\' be updated'),true); exit;
    }
}else{
    
    $execute = mysql_query('DELETE FROM '.  strtolower('designationmodulemaster').' WHERE designationId = '.$designation_id.'');

    if($execute){
     
        $insert_module = 'INSERT INTO designationmodulemaster (designationId,moduleId,permission) VALUES ';
        
        // Insert modules
        $values = '';
        foreach($assigned_modules as $module){
            
            if(in_array($module['parent'], $unassinged_modules) == FALSE){
                $values .= '( ';
                $values .= $designation_id . ',' .$module['module_id'] .' ,' .$module['permission'];
                $values .= ' ),';
            }
        }
        
        // If $values is blank then we will not execute insert query 
        if($values != ''){
            
            $values = rtrim($values,',');
            $insert_module = $insert_module.$values.';';
        
            $write = mysql_query($insert_module);

            if($write){
                echo json_encode(array('success' => 1,'message' => 'Modules updated successfully'),true); exit;
            }else{
                echo json_encode(array('success' => 0,'message' => 'Some Server error occured. Modules couldn\' be updated'),true); exit;
            }
        }else{
            echo json_encode(array('success' => 1,'message' => 'Modules updated successfully'),true); exit;
        }
        
    }else{
        echo json_encode(array('success' => 0,'message' => 'Some Server error occured. Modules couldn\' be updated'),true); exit;
    }
}
