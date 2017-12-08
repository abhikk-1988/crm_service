<?php

require_once 'db_connection.php';

$data = file_get_contents('php://input');

if($data === ''){
  
    $no_data = array('success' => 0, 'message' => 'No data recieved');
    echo json_encode($no_data,true); exit;
}

$data_array = json_decode($data,TRUE);

// Validations 
$errors = array();

if(!isset($data_array['title']) && $data_array['title']){
    $errors['title'] = 'Status Title is required';
}

if(!empty($errors)){
    echo json_encode(array('success' => -1,'errors' => $errors),true); exit; 
}

$status_title = $data_array['title'];
$parent_status = NULL;

if(isset($data_array['parent_id']) && $data_array['parent_id'] != null){
    $parent_status = $data_array['parent_id'];
}

if($parent_status == NULL){
    
    // Add top level disposition status 
    
    $query = 'INSERT INTO `disposition_status_substatus_master` (status_title,parent_status,added_on) '
            . ' VALUES("'.$status_title.'",NULL,"'.date('Y-m-d').'")';
    
    if(mysql_query($query)){
        echo json_encode(array('success' => 1, 'message' => 'Status has been added successfully'),true); exit;
    }else{
        echo json_encode(array('success' => 0, 'message' => 'Status couldn\'t be added'),true); exit;
    }
}else{
    // Add top level disposition status 
    
    $query = 'INSERT INTO `disposition_status_substatus_master` (sub_status_title,parent_status,added_on) '
            . ' VALUES("'.$status_title.'",'.$parent_status.',"'.date('Y-m-d').'")';
    
    if(mysql_query($query)){
        echo json_encode(array('success' => 1, 'message' => 'Sub Status has been added successfully'),true); exit;
    }else{
        echo json_encode(array('success' => 0, 'message' => 'Sub Status couldn\'t be added'),true); exit;
    }
}

