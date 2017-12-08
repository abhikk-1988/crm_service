<?php

require_once 'db_connection.php';

$data = file_get_contents('php://input');

if($data == ''){
    
    $no_data = array('success' => 0, 'message' => 'No data Received');
    echo json_encode($no_data,true); exit;
}

$data_array = json_decode($data,TRUE);

/**
 * Validation on input data
 */

//status_id : null,
//            status_title : '',
//            parent_status_id

$errors = array();

if(!isset($data_array['status_title']) && empty($data_array['status_title'])){

    $errors['title'] = 'Status Title is required';
}

if(!isset($data_array['status_id']) && empty($data_array['status_id'])){
    $errors['status_id'] = 'Action not authorized';
}

if(isset($data_array['parent_status_id']) && $data_array['parent_status_id'] === null){
   $errors['parent_status_id'] = 'Please select parent status';
}

if(!empty($errors)){
    
    echo json_encode(array('success' => -1, 'errors' => $errors),true); exit;
}

if($data_array['parent_status_id'] == NULL){
    $parent_status_id = 'NULL';
}else{
    $parent_status_id = $data_array['parent_status_id'];
}

// Update value 

$relation = $data_array['relation'];

if($relation == 'parent'){
    $update = 'UPDATE `disposition_status_substatus_master` '
        . ' SET status_title = "'.$data_array['status_title'].'" ,'
        . ' parent_status = '.$parent_status_id.''
        . ' WHERE id = '.$data_array['status_id'].'';
}else{
    $update = 'UPDATE `disposition_status_substatus_master` '
        . ' SET status_title = NULL ,'
        . ' sub_status_title = "'.$data_array['status_title'].'" ,'
        . ' parent_status = '.$parent_status_id.''
        . ' WHERE id = '.$data_array['status_id'].'';
}

if(mysql_query($update)){
    echo json_encode(array('success' => 1)); exit;
}else{
    echo json_encode(array('success' => 0)); exit;
}