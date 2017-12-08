<?php

require_once 'db_connection.php';

$query = 'SELECT tbl1.id, tbl1.status_title, GROUP_CONCAT(tbl2.sub_status_title) as child_status_title, GROUP_CONCAT(tbl2.id) as child_status_id,tbl1.active_state,tbl1.is_activity_status 
FROM `disposition_status_substatus_master` as tbl1 
LEFT JOIN `disposition_status_substatus_master` as tbl2 ON (tbl1.id = tbl2.parent_status) 
            WHERE tbl1.status_title IS NOT NULL
            GROUP BY tbl1.status_title ORDER BY tbl1.status_title';

$result = mysql_query($query);

$status = array();

if($result){
    
    while($row = mysql_fetch_assoc($result)){
        $temp = array();
        $temp['status_title'] = $row['status_title'];
        $temp['status_id'] = $row['id'];
        $temp['childs'] = array();
        $temp['is_activity_status'] = (bool) $row['is_activity_status'];
        $temp['active_state'] = (bool) $row['active_state'];
        
        if($row['child_status_id'] != NULL){
            
            $child_status_id = explode(',',$row['child_status_id']);
            $child_status_title = explode(',',$row['child_status_title']);
            
            foreach($child_status_id as $key=>$val){
                
                $child = array();
                
                $child['child_status_id'] = $val;
                $child['child_status_title'] = $child_status_title[$key];
                array_push($temp['childs'],$child);
                
             }
            
        }
        
        array_push($status,$temp);
    }
}
echo json_encode($status,true); exit;