<?php

require_once 'db_connection.php';

$data = file_get_contents('php://input');

if($data == ''){
    // Error Response of no data recieved 
    
    $no_data = array('success' => 0, 'message' => 'No data recieved');
    echo json_encode($no_data,true); exit;
}


$data_array = json_decode($data,true);

//  params - 
// 1. action_type : add/edit/delete/active/deactive
// 2. group title : varchar (25)
// 3. id@ : optional in case of add action 
// 4. delete_state : in case of delete/ undelete group 
// 5. active_state : in case of active/ deactive group

// Validation on form data

$errors =   array();

if(!isset($data_array['action']) || $data_array['action'] == null){
    $errors['action'] = 'Action not authorized';
}

if(!isset($data_array['title']) || $data_array['title'] == null){
    $errors['title'] = 'Disposition group title is required';
}

if(isset($data_array['edit']) || isset($data_array['delete']) || isset($data_array['active']) || isset($data_array['deactive'])){
    
    if(!isset($data_array['id']) || $data_array['id'] == null){
        $errors['id'] = 'Action not authorized';
    }
}

if(!empty($errors)){
    
    $error_response = array('success' => -1, 'errors' => $errors);
    echo json_encode($error_response); exit;
}

$action = $data_array['action'];
$title  = $data_array['title'];
$id     = null;
$delete_state = 0;
$active_state = 1;

if(isset($data_array['id']) && $data_array['id'] != null){
    $id = $data_array['id'];
}

if(isset($data_array['delete_state'])){
    $delete_state = $data_array['delete_state'];
}

if(isset($data_array['active_state'])){
    $active_state = $data_array['active_state'];
}

switch($action){
    
    case 'add':
        
        $insert_group = 'INSERT INTO `disposition_group` '
            . ' (group_title,added_on) VALUES ("'.$title.'","'.date('Y-m-d').'")';
        
        if(mysql_query($insert_group)){
            echo json_encode(array('success' => 1),true); exit;
        }else{
            echo json_encode(array('success' => 0),true); exit;
        }
        
        break;
    
    case 'edit':
        
        $update_group = 'UPDATE `disposition_group` SET `group_title` = "'.$title.'"'
            . ' WHERE id = '.$id.' LIMIT 1';
        
        if(mysql_query($update_group)){
            echo json_encode(array('success' => 1),true); exit;
        }else{
            echo json_encode(array('success' => 0),true); exit;
        }
        break;
    
    case 'delete':
        $delete_group = 'UPDATE `disposition_group` SET `delete_state` = '.$delete_state.''
            . ' WHERE id = '.$id.' LIMIT 1';
        
        if(mysql_query($delete_group)){
            echo json_encode(array('success' => 1),true); exit;
        }else{
            echo json_encode(array('success' => 0),true); exit;
        }
        
        break;
    
    case 'active':
        
        $active_state = 'UPDATE `disposition_group` SET `active_state` = '.$active_state.''
            . ' WHERE id = '.$id.' LIMIT 1';
        
        if(mysql_query($active_state)){
            echo json_encode(array('success' => 1),true); exit;
        }else{
            echo json_encode(array('success' => 0),true); exit;
        }
        break;
    
    case 'deactive':
        $deactive_state = 'UPDATE `disposition_group` SET `active_state` = '.$active_state.''
            . ' WHERE id = '.$id.' LIMIT 1';
        
        
        if(mysql_query($deactive_state)){
            echo json_encode(array('success' => 1),true); exit;
        }else{
            echo json_encode(array('success' => 0),true); exit;
        }
        break;
}
