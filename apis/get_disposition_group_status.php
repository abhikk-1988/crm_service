<?php
session_start();
require_once('constant.php');
require_once 'function.php';
require_once 'user_authentication.php';

if(!$is_authenticate){
    echo json_encode(array('success' => 0,'http_status_code' => 401),true); exit;
}

$group_id = '';
$group_status_ids = '';

if(isset($_GET['group_id'])){
    $group_id = $_GET['group_id'];
}

if($group_id != ''){
    $group_status_ids = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=get_disposition_group_status&params=group_id:'.$group_id),true);
}

$group_hierarchy = array();

if(!empty($group_status_ids)){
  
    $ids = implode(',',$group_status_ids);
   
    $group_hierarchy = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getDispositionStatusHeirarchy&params=ids:'.$ids),true);
    
    echo json_encode(array('success' => 1, 'status_data' => $group_hierarchy),true);
}else{
    echo json_encode(array('success' => 0, 'status_data' => array()),true);
}
