<?php

require_once 'db_connection.php';

$data_string = file_get_contents('php://input');

if($data_string === ''){
    echo json_encode(array('success' => 0),true); exit;
}

$data_array = json_decode($data_string,true);

$group_id = $data_array['group_id'];

$status_ids = $data_array['status_ids'];

$delete_mapping = 'DELETE FROM `disposition_group_status_master`'
            . ' WHERE disposition_group_id = '.$group_id.'';

mysql_query($delete_mapping);

//    if(mysql_query($delete_mapping)){
//        echo json_encode(array('success' => 1),true); exit;
//    }else{
//        echo json_encode(array('success' => 0),true); exit;
//    }  

if(!empty($status_ids)){
    
    $insert_mapping = 'INSERT INTO `disposition_group_status_master` (disposition_group_id,disposition_status_id) VALUES ';

    $values = '';
    
    foreach($status_ids as $val){
        $values .= '('.$group_id.','.$val['status_id'].'),';
    }

    $values = rtrim($values, ',');

    $insert_mapping = $insert_mapping . $values;
    
    if(mysql_query($insert_mapping)){
        echo json_encode(array('success' => 1),true); exit;
    }else{
        echo json_encode(array('success' => 0),true); exit;
    }
}else{
    echo json_encode(array('success' => 1),true); exit;
}