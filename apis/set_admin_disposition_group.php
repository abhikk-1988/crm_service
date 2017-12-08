<?php

session_start();
require_once 'constant.php';
require_once 'function.php';
require_once 'user_authentication.php';


if(!$is_authenticate){
   echo  json_encode(array('success' => 0, 'http_status_code' => 401),true); exit; 
}

$data = json_decode(file_get_contents('php://input'),true);

if($data['status'] === 1){
    
    // save group 
    $set_disposition_group = 'UPDATE employees SET disposition_group = '.$data['group_id'].' WHERE id = '.$data['employee_id'].' LIMIT 1';
    $message = 'Group is assigned successfully';
}else{
    
    // delete group 
    $set_disposition_group = 'UPDATE employees SET disposition_group = NULL WHERE id = '.$data['employee_id'].' LIMIT 1';
    $message = 'Group is unassigned successfully';
}

if(mysql_query($set_disposition_group)){
    
    echo json_encode(array('success' => 1,'message' => $message),true); exit; 
    
}else{
    echo json_encode(array('success' => 0,'message' => 'Can\'t update the group. Please try again'),true); exit;
}
