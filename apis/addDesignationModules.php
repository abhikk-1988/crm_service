<?php

require 'db_connection.php';

$post_data = file_get_contents('php://input');

if($post_data == ''){
    echo json_encode(array('success' => 0,'message' => 'Please assign a module'),true); exit;
}

$post_data_array = json_decode($post_data, true);

$assigned_modules   = array();
$designation_id     = '';

if(!empty($post_data_array)){
    
        // Pre check for module's parent assigned to designation or not 
        // If module's parent is not assigned to designation yet then assigned parent also
        
        $designation_id = $post_data_array['designation_id'];
        
        if($post_data_array['parent'] == NULL || $post_data_array['parent'] == ''){
            $insert_module = 'INSERT INTO '.strtolower('designationModuleMaster').' (designationId,moduleId,permission) VALUES '
             . ' ('.$post_data_array['designation_id'].', '.$post_data_array['id'].', '.$post_data_array['permission'].');';
        
            $write = mysql_query($insert_module);
        }else{
            
            // In case a child module is going to assigned
			
            $is_parent_assigned = file_get_contents(BASE_URL .'apis/helper.php?method=isParentAssignedToDesignation&params=module_id:'.$post_data_array['parent'].'/designation_id:'.$designation_id);
			
            if($is_parent_assigned){
                
                $insert_module = 'INSERT INTO '.strtolower('designationModuleMaster').' (designationId,moduleId,permission) VALUES '
             . ' ('.$post_data_array['designation_id'].', '.$post_data_array['id'].', '.$post_data_array['permission'].');';
        
                
            }else{
                
                $insert_module = 'INSERT INTO '.strtolower('designationModuleMaster').' (designationId,moduleId,permission) VALUES '
             . ' ('.$post_data_array['designation_id'].', '.$post_data_array['parent'].', 4),'
                        . ' ('.$post_data_array['designation_id'].','.$post_data_array['id'].','.$post_data_array['permission'].')';
                
            }       
			
            $write = mysql_query($insert_module);
        }
        
        
        if($write){
            echo json_encode(array('success' => 1,'message' => 'Modules added successfully'),true); exit;
        }else{
            echo json_encode(array('success' => 0,'message' => 'Some Server error occured. Modules couldn\' be added'),true); exit;
        }
}