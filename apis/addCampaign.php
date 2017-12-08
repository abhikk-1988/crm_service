<?php

require_once 'db_connection.php';

$data = file_get_contents('php://input');

// Recieves a JSON string 
if($data == ''){
   
    echo json_encode(array(
       'success' => 0, 'message' => 'No data recieved'
    ),true); exit;
}

// Decode JSON string into php array 
$data_array = json_decode($data,true);

$errors = array();

// Validate data 
if($data_array['title'] == ''){
    $errors['title'] = 'Campaign Title is required';
}

if($data_array['type'] == 'secondary'){
    
    if($data_array['primary_campaign_id'] == ''){
        $errors['primary_campaign_id'] = 'Please select primary campaign from list ';
    }
}

if(!empty($errors)){
    echo json_encode(array(
            'success' => -1,
            'error' => $errors
        ),true); exit;
}

if(isset($data_array['type']) && $data_array['type'] == 'secondary'){
    
    // Add a secondary campaign 
    $primary_campaign_id = $data_array['primary_campaign_id'];
    $title = $data_array['title'];
    $added_on = date('Y-m-d');
 
    $id = null;
    
    if(isset($data_array['id'])){
        $id = $data_array['id'];
    }

    
    if($id == NULL){
        $query = 'INSERT INTO campaign_master (primary_campaign_id,title,added_on)'
            . ' VALUES ('.$primary_campaign_id.',"'.$title.'","'.$added_on.'")';
    }else{
        $query = 'UPDATE campaign_master SET '
                . ' primary_campaign_id = '.$primary_campaign_id.' ,'
                . ' title = "'.$title.'" '
                . ' WHERE id = '.$id.' LIMIT 1';
        
    }
    
    if(mysql_query($query)){
        echo json_encode(array(
            'success' => 1,
            'message' => 'Campaign added successfully'
        ),true); exit;
    }else{
        echo json_encode(array(
            'success' => 0,
            'message' => 'Campaign couldn\'t be added'
        ),true); exit;
    }
    
}else{
    
    // Add a primary campaign
    $title = $data_array['title'];
    $added_on = date('Y-m-d');
    $id = null;
    
    if(isset($data_array['id'])){
        $id = $data_array['id'];
    }
    
    if($id == null){
        $query = 'INSERT INTO campaign_master (title,added_on)'
            . ' VALUES ("'.$title.'","'.$added_on.'")';
    }else{
        $query = 'UPDATE campaign_master SET'
                . ' title = "'.$title.'" '
                . ' WHERE id = '.$id.'';
    }
    
    
    if(mysql_query($query)){
        echo json_encode(array(
            'success' => 1,
            'message' => 'Campaign added successfully'
        ),true); exit;
    }else{
        echo json_encode(array(
            'success' => 0,
            'message' => 'Campaign couldn\'t be added'
        ),true); exit;
    }
}